<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Signals that a new order exists. Listeners re-read the latest values from the database.
 */
class OrderCreated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    public const CHANNEL = 'demo.orders';

    /**
     * Create a new event instance.
     */
    public function __construct(public int $orderId) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(self::CHANNEL),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array{orderId: int}
     */
    public function broadcastWith(): array
    {
        return ['orderId' => $this->orderId];
    }
}
