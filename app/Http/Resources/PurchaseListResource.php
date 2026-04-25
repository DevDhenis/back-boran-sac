<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{
    public function toArray($request)
    {
        $person = $this->customer?->person;

        return [
            'id' => $this->id,
            'sale_date' => $this->sale_date,
            'total' => $this->total,
            'status' => $this->status,
            'client' => $person
                ? trim(($person->nombres ?? '') . ' ' . ($person->apellido_paterno ?? ''))
                : null,
            'payment_methods' => $this->payments->pluck('method')->unique()->values(),
            'employee' => $this->employee?->person?->nombres ?? null,
        ];
    }
}
