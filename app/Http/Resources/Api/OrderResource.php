<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'total_amount' => (float) $this->total_amount,
            'discount_amount' => (float) $this->discount_amount,
            'delivery_fee' => (float) $this->delivery_fee,
            'final_amount' => (float) $this->final_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'user' => new UserResource($this->whenLoaded('user')),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery_address' => new AddressResource($this->whenLoaded('deliveryAddress')),
            'driver' => new UserResource($this->whenLoaded('driver')),
            'delivery' => DeliveryResource::collection($this->whenLoaded('delivery')),
            'status_history' => $this->whenLoaded('statusHistory'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
