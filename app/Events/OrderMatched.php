<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderMatched implements ShouldBroadcast
{
    use SerializesModels;

    public $buyOrder;
    public $sellOrder;
    public $tradeValue;
    public $commission;

    public function __construct(Order $buyOrder, Order $sellOrder, $tradeValue, $commission)
    {
        $this->buyOrder = $buyOrder;
        $this->sellOrder = $sellOrder;
        $this->tradeValue = $tradeValue;
        $this->commission = $commission;
    }

    public function broadcastOn()
    {
        return [new PrivateChannel('user.'.$this->buyOrder->user_id),
                new PrivateChannel('user.'.$this->sellOrder->user_id)];
    }
}
