<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'code' => [
                'required',
                Rule::exists('users', 'verification_code')
                    ->where(fn ($query) => $query->where('email', $this->email)),
            ],
        ];
    }

    public function messages()
    {
        return [
            'required' => 'El campo :attribute es requerido.',
            'email.exists' => 'No existe ningún usuario con el correo proporcionado.',
            'exists' => 'El valor del campo :attribute es inválido para el correo.',
        ];
    }

    public function attributes()
    {
        return [
            'email' => 'correo electrónico',
            'code' => 'código',
        ];
    }
}
