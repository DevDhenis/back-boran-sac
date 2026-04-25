<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShoppingCartItemResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'id' => $this->id,
      'product_id' => $this->product_id,
      'cantidad' => $this->cantidad,
      'precio_unitario' => $this->precio_unitario,
      'subtotal' => $this->subtotal,
      'descuento' => $this->descuento,
      'precio_final' => $this->precio_final,
      'product' => new ProductResource($this->whenLoaded('product')),
    ];
  }
}
