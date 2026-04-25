<?php

namespace App\Http\Requests\InvestmentManagement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
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
     * Reglas de validación para actualizar un movimiento de inventario.
     */
    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'tipo_movimiento' => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.001',
            'motivo' => 'nullable|string|max:255',
            'employee_id' => 'nullable|exists:employees,id',
            'stock_despues' => 'nullable|numeric|min:0', // requerido solo si tipo_movimiento == ajuste
        ];
    }

    /**
     * Validación condicional: stock_despues es obligatorio solo si el tipo es 'ajuste'.
     */
    public function withValidator($validator): void
    {
        $validator->sometimes('stock_despues', 'required|numeric|min:0', function ($input) {
            return $input->tipo_movimiento === 'ajuste';
        });
    }
}