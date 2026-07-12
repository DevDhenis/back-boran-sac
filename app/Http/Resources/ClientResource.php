<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'total_purchases' => $this->total_purchases,
            'accepted_purchases' => $this->accepted_purchases,
            'rejected_purchases' => $this->rejected_purchases,
            'returned_purchases' => $this->returned_purchases,
            'person' => new PersonResource($this->whenLoaded('person')),
        ];
    }
}
