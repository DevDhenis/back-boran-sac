<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $person = $this->employee?->person;
        $employeeName = $person
            ? trim("{$person->first_name} {$person->last_name}")
            : null;

        return [
            'id' => $this->id,
            'movement_type' => $this->movement_type,
            'origin' => $this->origin,
            'quantity' => (float) $this->quantity,
            'reason' => $this->reason,
            'stock_before' => (float) $this->stock_before,
            'stock_after' => (float) $this->stock_after,
            'movement_date' => $this->movement_date,
            'status' => $this->status,
            'sale_id' => $this->sale_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'internal_code' => $this->product->internal_code,
                'name' => $this->product->name,
            ]),
            'employee' => $employeeName ?: '—',
        ];
    }
}
