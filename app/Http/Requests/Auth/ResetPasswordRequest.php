<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'email' => 'required|email|exists:users,email',
      'code' => 'required|string',
      'password' => 'required|string|min:6|confirmed',
    ];
  }

  public function messages(): array
  {
    return [
      'email.required' => 'El campo email es obligatorio.',
      'email.email' => 'El email debe tener un formato válido.',
      'email.exists' => 'El correo no está registrado.',
      'code.required' => 'El código de recuperación es obligatorio.',
      'password.required' => 'La nueva contraseña es obligatoria.',
      'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
      'password.confirmed' => 'Las contraseñas no coinciden.',
    ];
  }
}
