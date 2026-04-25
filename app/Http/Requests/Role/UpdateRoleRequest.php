<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'nombre')->ignore($this->route('role')->id),
            ],
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'El nombre del rol ya existe.',
            'nombre.max' => 'El nombre no debe superar 255 caracteres.',
            'descripcion.max' => 'La descripción no debe superar 255 caracteres.',
        ];
    }
}
