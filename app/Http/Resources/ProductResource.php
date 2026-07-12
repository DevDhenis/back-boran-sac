<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'internal_code' => $this->internal_code,
            'name' => $this->name,
            'description' => $this->description,
            'stock' => $this->stock,
            'minimum_quantity' => $this->minimum_quantity,
            'on_promotion' => $this->on_promotion,
            'unit_price' => $this->unit_price,
            'wholesale_unit_price' => $this->wholesale_unit_price,
            'wholesale_min_quantity' => $this->wholesale_min_quantity,
            'discount' => $this->discount,
            'final_price' => $this->final_price,
            'image' => $this->getImageUrl(),
            'unit_id' => $this->unit_id,
            'product_category_id' => $this->product_category_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'unit' => $this->whenLoaded('unit'),
            'category' => $this->whenLoaded('category'),
        ];
    }

    /**
     * Get the full URL for the product image
     */
    protected function getImageUrl(): ?string
    {
        if (! $this->image) {
            return asset('images/product-default.jpg');
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
