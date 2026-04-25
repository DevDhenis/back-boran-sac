<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductPublicResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'precio' => $this->pre_uni,
            'imagen' => $this->imagen,
            'stock' => $this->stock,
            'en_promocion' => $this->en_promocion,
            'categoria' => $this->category->nombre ?? null,
            'unidad' => $this->unit->abreviatura ?? null,
        ];
    }
}