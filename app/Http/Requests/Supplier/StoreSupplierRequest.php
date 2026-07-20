<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'ruc' => 'nullable|digits:11|unique:suppliers,ruc',
            'contact_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'status' => 'nullable|in:A,I',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proveedor es obligatorio.',
            'ruc.digits' => 'El RUC debe tener 11 dígitos.',
            'ruc.unique' => 'Ya existe un proveedor con ese RUC.',
            'email.email' => 'El correo debe tener un formato válido.',
        ];
    }
}
