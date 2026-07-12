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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'second_last_name' => $this->second_last_name,
            'address' => $this->address,
            'image' => $this->resolveImagenUrl(),

            'document_number' => $this->document_number,
            'document_type_id' => $this->whenLoaded('documentType'),
        ];
    }

    /**
     * Resuelve la URL de la image:
     * - sin image o "user-default" -> avatar por defecto
     * - URL completa (p. ej. Cloudinary) -> se devuelve tal cual
     * - ruta local heredada -> se sirve desde storage
     */
    protected function resolveImagenUrl(): string
    {
        if (! $this->image || str_contains($this->image, 'user-default')) {
            return asset('images/user-default.jpg');
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }
}
