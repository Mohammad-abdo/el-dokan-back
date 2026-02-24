<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $deliveryId,
        public string $status
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('delivery.' . $this->deliveryId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DeliveryStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->deliveryId,
            'status' => $this->status,
        ];
    }
}
