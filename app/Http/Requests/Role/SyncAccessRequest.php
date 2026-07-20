<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class SyncAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Podés meter aquí lógica de permisos si lo deseas
        return true;
    }

    public function rules(): array
    {
        return [
            'access_ids' => 'required|array',
            'access_ids.*' => 'exists:accesses,id',
        ];
    }

    public function messages(): array
    {
        return [
            'access_ids.required' => 'Debe enviar al menos un acceso.',
            'access_ids.array' => 'El campo accesos debe ser un arreglo.',
            'access_ids.*.exists' => 'Uno de los accesos seleccionados no existe en el sistema.',
        ];
    }

    public function attributes(): array
    {
        return [
            'access_ids' => 'accesos',
        ];
    }
}
