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
            'imagen' => $this->resolveImagenUrl(),

            'numero_documento' => $this->numero_documento,
            'document_type_id' => $this->whenLoaded('documentType'),
        ];
    }

    /**
     * Resuelve la URL de la imagen:
     * - sin imagen o "user-default" -> avatar por defecto
     * - URL completa (p. ej. Cloudinary) -> se devuelve tal cual
     * - ruta local heredada -> se sirve desde storage
     */
    protected function resolveImagenUrl(): string
    {
        if (!$this->imagen || str_contains($this->imagen, 'user-default')) {
            return asset('images/user-default.jpg');
        }

        if (filter_var($this->imagen, FILTER_VALIDATE_URL)) {
            return $this->imagen;
        }

        return asset('storage/' . $this->imagen);
    }
}
