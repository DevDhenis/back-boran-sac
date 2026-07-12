<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductPublicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'precio' => $this->unit_price,
            'image' => $this->image,
            'stock' => $this->stock,
            'on_promotion' => $this->on_promotion,
            'categoria' => $this->category->name ?? null,
            'unidad' => $this->unit->abbreviation ?? null,
        ];
    }
}
