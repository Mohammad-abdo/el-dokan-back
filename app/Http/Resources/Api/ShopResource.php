<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'logo_url' => $this->logo_url,
            'cover_url' => $this->cover_url,
            'image_url' => $this->image_url,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'vendor_status' => $this->vendor_status,
            'rating' => (float) $this->rating ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'category' => $this->category,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'user' => new UserResource($this->whenLoaded('user')),
            'branches' => ShopBranchResource::collection($this->whenLoaded('branches')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
