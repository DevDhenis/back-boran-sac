<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\VerifyRecoveryCodeRequest;
use App\Http\Resources\PersonResource;
use App\Mail\CodigoRecuperacionEmail;
use App\Mail\CodigoVerificacionEmail;
use App\Models\Client;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        $user = Auth::user();

        $role = $user->role()->with('accesses')->first();

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'token'   => $token,
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $role->nombre,
                'role_id'  => $role->id,
                'accesses' => $role->accesses,
                'person'   => new PersonResource($user->person),
            ],
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $persona = Person::create($request->validated());

        $code = strtoupper(Str::random(8));
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => 2,
            'person_id' => $persona->id,
            'codigo_verificacion' => $code,
        ]);

        Client::create([
            'person_id' => $persona->id,
            'cantidad_compras' => 0,
            'cantidad_compras_aceptadas' => 0,
            'cantidad_compras_rechazadas' => 0,
            'cantidad_compras_devueltas' => 0,
        ]);

        $token = JWTAuth::fromUser($user);

        Mail::to($user->email)->send(new CodigoVerificacionEmail($code, $persona));

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado con éxito',
            'token' => $token
        ], 201);
    }
    public function verifyEmail(VerifyEmailRequest $request)
    {
        // Aceptar tanto 'code' como 'codigo_verificacion'
        $codigo = $request->codigo_verificacion ?? $request->code;

        if (!$codigo) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación requerido.'
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('codigo_verificacion', strtoupper($codigo))
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Código o email inválido/expirado.'
            ], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'codigo_verificacion' => null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correo verificado correctamente',
            'data' => new PersonResource($user->person)
        ]);
    }

    public function verifyRecoveryCode(VerifyRecoveryCodeRequest $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Código de recuperación verificado correctamente'
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $code = strtoupper(Str::random(8));
        $user->update(['codigo_recuperacion' => $code]);

        Mail::to($user->email)->send(new CodigoRecuperacionEmail($code, $user->person));

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado un código de recuperación a tu correo electrónico.'
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)
            ->where('codigo_recuperacion', $request->code)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Código de recuperación inválido o expirado.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'codigo_recuperacion' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.'
        ]);
    }

    public function me()
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado',
            ], 401);
        }


        $role = $user->role()->with('accesses.children')->first();

        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $role->nombre,
                'role_id'  => $role->id,
                'accesses' => $role->accesses,
                'person'   => new PersonResource($user->person),
            ],
        ]);
    }
}
