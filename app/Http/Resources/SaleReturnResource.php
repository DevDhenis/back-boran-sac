<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'reason' => $this->reason,
            'refund_status' => $this->refund_status,
            'review_note' => $this->review_note,
            'resolved_at' => $this->resolved_at,
            'created_at' => $this->created_at,
            'sale' => $this->whenLoaded('sale', fn () => [
                'id' => $this->sale->id,
                'sale_date' => $this->sale->sale_date,
                'total' => (float) $this->sale->total,
                'status' => $this->sale->status,
            ]),
            'client' => $this->whenLoaded('client', fn () => $this->personName($this->client?->person)),
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn () => $this->personName($this->reviewedBy?->person)),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'quantity' => (float) $item->quantity,
                'product' => $item->relationLoaded('product') && $item->product ? [
                    'id' => $item->product->id,
                    'internal_code' => $item->product->internal_code,
                    'name' => $item->product->name,
                ] : null,
            ])),
        ];
    }

    private function personName($person): ?string
    {
        if (! $person) {
            return null;
        }
        $name = trim("{$person->first_name} {$person->last_name}");

        return $name !== '' ? $name : null;
    }
}
