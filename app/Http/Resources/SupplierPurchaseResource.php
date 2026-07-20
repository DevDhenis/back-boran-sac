<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierPurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $person = $this->employee?->person;

        return [
            'id' => $this->id,
            'purchase_date' => $this->purchase_date,
            'document_number' => $this->document_number,
            'subtotal' => (float) $this->subtotal,
            'tax' => (float) $this->tax,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
                'ruc' => $this->supplier->ruc,
            ]),
            'employee' => $person ? trim("{$person->first_name} {$person->last_name}") : '—',
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => (float) $item->quantity,
                'unit_cost' => (float) $item->unit_cost,
                'subtotal' => (float) $item->subtotal,
                'product' => $item->relationLoaded('product') && $item->product ? [
                    'id' => $item->product->id,
                    'internal_code' => $item->product->internal_code,
                    'name' => $item->product->name,
                ] : null,
            ])),
        ];
    }
}
