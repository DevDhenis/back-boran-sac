<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        $personId = optional($user)->person_id;
        $userId = optional($user)->id;

        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'second_last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'document_number' => 'nullable|string|unique:persons,document_number,'.$personId,
            'document_type_id' => 'nullable|exists:document_types,id',
            'username' => 'sometimes|string|max:255|unique:users,username,'.$userId,
            'email' => 'sometimes|email|max:255|unique:users,email,'.$userId,
        ];
    }

    public function messages(): array
    {
        return [
            'string' => 'El :attribute debe ser texto.',
            'email' => 'El :attribute debe ser un correo válido.',
            'max' => 'El :attribute no debe exceder :max caracteres.',
            'image' => 'El :attribute debe ser una imagen válida.',
            'mimes' => 'El :attribute debe ser un archivo de tipo: :values.',
            'unique' => 'El :attribute ya está registrado.',
            'exists' => 'El :attribute seleccionado no existe.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'nombres',
            'last_name' => 'apellido paterno',
            'second_last_name' => 'apellido materno',
            'address' => 'dirección',
            'image' => 'imagen',
            'document_number' => 'número de documento',
            'document_type_id' => 'tipo de documento',
            'username' => 'nombre de usuario',
            'email' => 'correo electrónico',
        ];
    }
}
