<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'price' => (float) $this->price,
            'discount_percentage' => (float) $this->discount_percentage,
            'final_price' => (float) $this->final_price,
            'stock_quantity' => $this->stock_quantity,
            'image_url' => $this->image_url,
            'images' => $this->when($this->images, $this->images),
            'category_id' => $this->category_id,
            'shop_id' => $this->shop_id,
            'status' => $this->status,
            'is_featured' => $this->is_featured ?? false,
            'sku' => $this->sku,
            'rating' => (float) $this->rating ?? 0,
            'reviews_count' => $this->reviews_count ?? 0,
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
