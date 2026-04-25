<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nombres' => $this->nombres,
            'apellido_paterno' => $this->apellido_paterno,
            'apellido_materno' => $this->apellido_materno,
            'direccion' => $this->direccion,
            'imagen' => (!$this->imagen || str_contains($this->imagen, 'user-default'))
                ? asset('images/user-default.jpg')
                : asset('storage/' . $this->imagen),

            'numero_documento' => $this->numero_documento,
            'document_type_id' => $this->whenLoaded('documentType'),
        ];
    }
}
