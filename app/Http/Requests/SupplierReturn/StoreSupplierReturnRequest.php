<?php

namespace App\Http\Requests\SupplierReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Selecciona el proveedor.',
            'reason.required' => 'Indica el motivo de la devolución.',
            'items.required' => 'Agrega al menos un producto a devolver.',
            'items.min' => 'Agrega al menos un producto a devolver.',
        ];
    }
}
