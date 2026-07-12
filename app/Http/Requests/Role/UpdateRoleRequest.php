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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->route('role')->id),
            ],
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El name del rol es obligatorio.',
            'name.unique' => 'El name del rol ya existe.',
            'name.max' => 'El name no debe superar 255 caracteres.',
            'description.max' => 'La descripción no debe superar 255 caracteres.',
        ];
    }
}
