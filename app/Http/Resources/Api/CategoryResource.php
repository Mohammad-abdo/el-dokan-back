<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'image_url' => $this->image_url,
            'icon' => $this->icon,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'subcategories' => CategoryResource::collection($this->whenLoaded('subcategories')),
            'parent_id' => $this->parent_id,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
