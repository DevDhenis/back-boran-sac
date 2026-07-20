<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'customer' => new ClientResource($this->whenLoaded('customer')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'sale_date' => $this->sale_date,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'shipping_address' => $this->shipping_address,
            'items' => SalesItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
