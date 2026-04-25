<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'              => $this->id,
      'horario_laboral' => $this->horario_laboral,
      'sueldo'          => $this->sueldo,
      'estado_registro' => $this->estado_registro,
      'person'          => new PersonResource($this->whenLoaded('person')),
    ];
  }
}
