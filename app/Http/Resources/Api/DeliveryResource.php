<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'driver_id' => $this->driver_id,
            'status' => $this->status,
            'pickup_time' => $this->pickup_time?->toISOString(),
            'delivery_time' => $this->delivery_time?->toISOString(),
            'driver_location_lat' => $this->driver_location_lat,
            'driver_location_lng' => $this->driver_location_lng,
            'driver' => new UserResource($this->whenLoaded('driver')),
            'order' => new OrderResource($this->whenLoaded('order')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
