<?php

namespace App\Http\Requests\SaleReturn;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => 'required|integer|exists:sales,id',
            'reason' => 'required|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.sales_item_id' => 'required|integer|exists:sales_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'Falta la venta a devolver.',
            'reason.required' => 'Debes indicar el motivo de la devolución.',
            'items.required' => 'Selecciona al menos un producto a devolver.',
            'items.min' => 'Selecciona al menos un producto a devolver.',
            'items.*.quantity.min' => 'La cantidad a devolver debe ser mayor a 0.',
        ];
    }
}
