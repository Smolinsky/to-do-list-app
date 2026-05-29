<?php

namespace App\Http\Controllers\Api\Auth;

use App\DTO\Auth\ForgotPasswordData;
use App\DTO\Auth\ResetPasswordData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResetPasswordController extends Controller
{
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        /** @var ForgotPasswordData $forgotPasswordData */
        $forgotPasswordData = $request->getDTO();

        $status = Password::sendResetLink(
            $forgotPasswordData->toCredentials()
        );

        $statusCode = $status === Password::RESET_LINK_SENT
            ? Response::HTTP_OK
            : Response::HTTP_BAD_REQUEST;

        return response()->json([
            'message' => __($status),
        ], $statusCode);
    }

    public function reset(ResetPasswordRequest $request)
    {
        /** @var ResetPasswordData $resetPasswordData */
        $resetPasswordData = $request->getDTO();

        $status = Password::reset(
            $resetPasswordData->toCredentials(),
            function (User $user) use ($resetPasswordData) {
                $user->forceFill([
                    'password' => Hash::make($resetPasswordData->password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        $statusCode = $status === Password::PASSWORD_RESET
            ? Response::HTTP_OK
            : Response::HTTP_BAD_REQUEST;

        return response()->json([
            'message' => __($status),
        ], $statusCode);
    }
}
