<?php
// app/Http/Resources/PurchaseResource.php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'sale_date' => $this->sale_date,
            'status' => $this->status,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'client' => new ClientResource($this->whenLoaded('customer')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'items' => SalesItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'status_histories' => $this->whenLoaded('statusHistories'),
        ];
    }
}
