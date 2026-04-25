<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'cantidad_compras' => $this->cantidad_compras,
            'cantidad_compras_aceptadas' => $this->cantidad_compras_aceptadas,
            'cantidad_compras_rechazadas' => $this->cantidad_compras_rechazadas,
            'cantidad_compras_devueltas' => $this->cantidad_compras_devueltas,
            'person' => new PersonResource($this->whenLoaded('person')),
        ];
    }
}