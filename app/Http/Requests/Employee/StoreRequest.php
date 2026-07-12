<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'second_last_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'document_number' => 'nullable|string|unique:persons,document_number',
            'document_type_id' => 'nullable|exists:document_types,id',
            'work_schedule' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El :attribute es obligatorio.',
            'string' => 'El :attribute debe ser texto.',
            'max' => 'El :attribute no debe exceder :max caracteres.',
            'numeric' => 'El :attribute debe ser un número válido.',
            'min' => 'El :attribute debe ser al menos :min.',
            'unique' => 'El :attribute ya está registrado.',
            'exists' => 'El :attribute seleccionado no existe.',
            'in' => 'El :attribute debe ser uno de los valores permitidos.',
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
            'work_schedule' => 'horario laboral',
            'salary' => 'sueldo',
        ];
    }
}
