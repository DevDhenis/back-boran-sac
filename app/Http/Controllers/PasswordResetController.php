<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AuthCodeTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResendResetCodeRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyResetCodeRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
        //
    }

    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->where('is_active', true)->first();
        if (is_null($user->email_verified_at)) {
            $this->authService->sendCode($user, AuthCodeTypeEnum::EMAIL_VERIFICATION);

            return response()->json([
                'message' => 'Cuenta no verificada. Por favor verifica tu correo electrónico.',
                'requires_verification' => true,
            ], 403);
        }
        $this->authService->sendCode($user, AuthCodeTypeEnum::RESET_PASSWORD);

        return response()->json([
            'success' => true,
            'message' => 'Se envió un código de recuperación a tu correo.',
        ], 200);
    }

    public function verifyResetCode(VerifyResetCodeRequest $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Código válido.',
        ], 200);
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente.',
        ], 200);
    }

    public function resendResetCode(ResendResetCodeRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->where('is_active', true)->first();

        if (is_null($user->email_verified_at)) {
            $this->authService->sendCode($user, AuthCodeTypeEnum::EMAIL_VERIFICATION);

            return response()->json([
                'message' => 'Cuenta no verificada. Por favor verifica tu correo electrónico.',
                'requires_verification' => true,
            ], 403);
        }

        $this->authService->sendCode($user, AuthCodeTypeEnum::RESET_PASSWORD);

        return response()->json([
            'success' => true,
            'message' => __('Código de recuperación reenviado correctamente'),
        ]);
    }
}
