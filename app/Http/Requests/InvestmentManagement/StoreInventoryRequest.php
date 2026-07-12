<?php

namespace App\Http\Requests\InvestmentManagement;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para realizar esta solicitud.
     * Puedes ajustar esto con policies o middleware según el rol.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para almacenar un movimiento de inventario.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'movement_type' => 'required|in:inbound,outbound,adjustment',
            'quantity' => 'required|numeric|min:0.001',
            'reason' => 'nullable|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'stock_after' => 'nullable|numeric|min:0', // requerido solo si movement_type == adjustment
        ];
    }

    /**
     * Validación condicional: stock_after es obligatorio solo si el tipo es 'adjustment'.
     */
    public function withValidator($validator): void
    {
        $validator->sometimes('stock_after', 'required|numeric|min:0', function ($input) {
            return $input->movement_type === 'adjustment';
        });
    }
}
