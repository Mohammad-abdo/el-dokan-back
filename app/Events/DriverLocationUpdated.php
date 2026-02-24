<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $deliveryId,
        public int $driverId,
        public float $latitude,
        public float $longitude
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('delivery.' . $this->deliveryId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'DriverLocationUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id' => $this->deliveryId,
            'driver_id' => $this->driverId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
