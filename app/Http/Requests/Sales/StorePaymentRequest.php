<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'sale_id' => 'required|exists:sales,id',
            'method' => 'required|in:efectivo,tarjeta,transferencia',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'status' => 'required|in:pendiente,confirmado,fallido',
        ];
    }
}