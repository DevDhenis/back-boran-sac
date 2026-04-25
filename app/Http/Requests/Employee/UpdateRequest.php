<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $personId = optional($this->employee)->person_id;

        return [
            'nombres' => 'sometimes|string|max:255',
            'apellido_paterno' => 'nullable|string|max:255',
            'apellido_materno' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:255',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'numero_documento' => 'nullable|string|unique:persons,numero_documento,' . $personId,
            'document_type_id' => 'nullable|exists:document_types,id',
            'horario_laboral' => 'sometimes|string|max:255',
            'sueldo' => 'sometimes|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
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
            'nombres' => 'nombres',
            'apellido_paterno' => 'apellido paterno',
            'apellido_materno' => 'apellido materno',
            'direccion' => 'dirección',
            'imagen' => 'imagen',
            'numero_documento' => 'número de documento',
            'document_type_id' => 'tipo de documento',
            'horario_laboral' => 'horario laboral',
            'sueldo' => 'sueldo',
        ];
    }
}
