<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SalesItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'price' => $this->price,
            'discount' => $this->discount,
            'subtotal' => ($this->price - $this->discount) * $this->quantity,
        ];
    }
}
