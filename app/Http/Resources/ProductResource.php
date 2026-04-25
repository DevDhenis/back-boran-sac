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
      'codigo_interno' => $this->codigo_interno,
      'nombre' => $this->nombre,
      'descripcion' => $this->descripcion,
      'stock' => $this->stock,
      'cantidad_minima' => $this->cantidad_minima,
      'en_promocion' => $this->en_promocion,
      'pre_uni' => $this->pre_uni,
      'pre_uni_may' => $this->pre_uni_may,
      'can_min_may' => $this->can_min_may,
      'descuento' => $this->descuento,
      'pre_fin' => $this->pre_fin,
      'imagen' => $this->getImageUrl(),
      'unit_id' => $this->unit_id,
      'product_category_id' => $this->product_category_id,
      'estado_registro' => $this->estado_registro,
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
    if (!$this->imagen) {
      return asset('images/product-default.jpg');
    }

    if (filter_var($this->imagen, FILTER_VALIDATE_URL)) {
      return $this->imagen;
    }

    return asset('storage/' . $this->imagen);
  }
}
