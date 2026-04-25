<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class AssignUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'username'    => 'required|string|unique:users,username',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:6',
        ];
    }

    public function attributes(): array
    {
        return [
            'employee_id' => 'empleado',
            'username'    => 'usuario',
            'email'       => 'correo',
            'password'    => 'contraseña',
        ];
    }
}
