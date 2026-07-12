<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Http\Requests\Auth\VerifyRecoveryCodeRequest;
use App\Http\Resources\PersonResource;
use App\Mail\RecoveryCodeMail;
use App\Mail\VerificationCodeMail;
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

        if (! $token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        $user = Auth::user();

        $role = $user->role()->with('accesses')->first();

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role->name,
                'role_id' => $role->id,
                'accesses' => $role->accesses,
                'person' => new PersonResource($user->person),
            ],
        ]);
    }

    public function register(RegisterRequest $request)
    {
        $person = Person::create($request->validated());

        $code = strtoupper(Str::random(8));
        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'role_id' => 2,
            'person_id' => $person->id,
            'verification_code' => $code,
        ]);

        Client::create([
            'person_id' => $person->id,
            'total_purchases' => 0,
            'accepted_purchases' => 0,
            'rejected_purchases' => 0,
            'returned_purchases' => 0,
        ]);

        $token = JWTAuth::fromUser($user);

        Mail::to($user->email)->send(new VerificationCodeMail($code, $person));

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado con éxito',
            'token' => $token,
        ], 201);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        // Aceptar tanto 'code' como 'verification_code'
        $code = $request->verification_code ?? $request->code;

        if (! $code) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación requerido.',
            ], 400);
        }

        $user = User::where('email', $request->email)
            ->where('verification_code', strtoupper($code))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Código o email inválido/expirado.',
            ], 400);
        }

        $user->update([
            'email_verified_at' => now(),
            'verification_code' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correo verificado correctamente',
            'data' => new PersonResource($user->person),
        ]);
    }

    public function verifyRecoveryCode(VerifyRecoveryCodeRequest $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Código de recuperación verificado correctamente',
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        $code = strtoupper(Str::random(8));
        $user->update(['recovery_code' => $code]);

        Mail::to($user->email)->send(new RecoveryCodeMail($code, $user->person));

        return response()->json([
            'success' => true,
            'message' => 'Se ha enviado un código de recuperación a tu correo electrónico.',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)
            ->where('recovery_code', $request->code)
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Código de recuperación inválido o expirado.',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'recovery_code' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function me()
    {
        $user = auth()->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado',
            ], 401);
        }

        $role = $user->role()->with('accesses.children')->first();

        return response()->json([
            'success' => true,
            'message' => 'Usuario autenticado',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $role->name,
                'role_id' => $role->id,
                'accesses' => $role->accesses,
                'person' => new PersonResource($user->person),
            ],
        ]);
    }
}
