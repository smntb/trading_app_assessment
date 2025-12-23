<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderMatched;
use App\Models\Order;
use App\Models\Asset;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $orders = Order::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $order = Order::where('id', $id)
            ->where('user_id', $user->id)
            ->where('status', 1)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found or already filled/cancelled'], 404);
        }

        DB::beginTransaction();
        try {
            if ($order->side === 'buy') {
                $user->balance += $order->price * $order->amount;
                $user->save();
            } else {
                $asset = Asset::where('user_id', $user->id)
                    ->where('symbol', $order->symbol)
                    ->first();
                if ($asset) {
                    $asset->amount += $order->amount;
                    $asset->locked_amount -= $order->amount;
                    $asset->save();
                }
            }

            $order->status = 3;
            $order->save();

            DB::commit();
            return response()->json(['message' => 'Order cancelled successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'symbol' => 'required|string',
            'side' => 'required|in:buy,sell',
            'price' => 'required|numeric|min:0.0001',
            'amount' => 'required|numeric|min:0.0001',
        ]);

        DB::beginTransaction();

        try {
            if ($request->side === 'buy') {
                $totalCost = $request->price * $request->amount;
                if ($user->balance < $totalCost) {
                    return response()->json(['message' => 'Insufficient USD balance'], 400);
                }
                $user->balance -= $totalCost;
                $user->save();
            } else {
                $asset = Asset::firstOrCreate(
                    ['user_id' => $user->id, 'symbol' => $request->symbol],
                    ['amount' => 0.0, 'locked_amount' => 0.0]
                );


                if ($asset->amount < $request->amount) {
                    return response()->json(['message' => 'Insufficient asset amount'], 400);
                }

                $asset->amount -= $request->amount;
                $asset->locked_amount += $request->amount;
                $asset->save();
            }

            $order = Order::create([
                'user_id' => $user->id,
                'symbol' => $request->symbol,
                'side' => $request->side,
                'price' => $request->price,
                'amount' => $request->amount,
                'status' => 1
            ]);

            DB::commit();

            $this->matchOrders($order);

            return response()->json($order, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function matchOrders(Order $newOrder)
    {
        DB::beginTransaction();

        try {
            if ($newOrder->side === 'buy') {
                $counter = Order::where('symbol', $newOrder->symbol)
                    ->where('side', 'sell')
                    ->where('status', 1)
                    ->where('price', '<=', $newOrder->price)
                    ->orderBy('created_at')
                    ->first();
            } else {
                $counter = Order::where('symbol', $newOrder->symbol)
                    ->where('side', 'buy')
                    ->where('status', 1)
                    ->where('price', '>=', $newOrder->price)
                    ->orderBy('created_at')
                    ->first();
            }

            if (!$counter || $counter->amount != $newOrder->amount) {
                DB::commit();
                return;
            }

            $commissionRate = 0.015;
            $tradeValue = $newOrder->price * $newOrder->amount;
            $commission = $tradeValue * $commissionRate;

            $buyer = $newOrder->side === 'buy' ? $newOrder->user : $counter->user;
            $seller = $newOrder->side === 'sell' ? $newOrder->user : $counter->user;

            $buyer->balance -= $commission;
            $buyer->save();

            $asset = Asset::where('user_id', $seller->id)->where('symbol', $newOrder->symbol)->first();
            $asset->locked_amount -= $newOrder->amount;
            $asset->save();

            $seller->balance += $tradeValue - $commission;
            $seller->save();

            $buyerAsset = Asset::firstOrCreate([
                'user_id' => $buyer->id,
                'symbol' => $newOrder->symbol
            ]);
            $buyerAsset->amount += $newOrder->amount;
            $buyerAsset->save();

            $newOrder->status = 2;
            $newOrder->save();

            $counter->status = 2;
            $counter->save();

            event(new OrderMatched($newOrder, $counter, $tradeValue, $commission));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
