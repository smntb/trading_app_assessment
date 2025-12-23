<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return response()->json([
            'balance' => (float) $user->balance,
            'assets' => $user->assets->map(function($asset){
                return [
                    'symbol' => $asset->symbol,
                    'amount' => (float) $asset->amount,
                ];
            })
        ]);
    }

}
