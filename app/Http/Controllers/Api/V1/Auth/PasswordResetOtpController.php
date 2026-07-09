<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Services\Email\AuthEmailService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class PasswordResetOtpController extends Controller
{
    private const OTP_EXPIRES_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    public function send(Request $request, AuthEmailService $authEmailService): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($validated['email']);
        $rateKey = 'api-password-reset-otp:'.sha1($email.'|'.(string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages([
                'email' => ['Please wait before requesting another OTP.'],
            ]);
        }

        RateLimiter::hit($rateKey, 300);

        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->first();

        if ($user) {
            $otp = (string) random_int(100000, 999999);

            PasswordResetOtp::query()->updateOrCreate(
                ['email' => $email],
                [
                    'otp_hash' => Hash::make($otp),
                    'attempts' => 0,
                    'expires_at' => now()->addMinutes(self::OTP_EXPIRES_MINUTES),
                    'verified_at' => null,
                ],
            );

            $authEmailService->sendPasswordResetOtp($user, $otp, self::OTP_EXPIRES_MINUTES);
        }

        return ApiResponse::success([
            'expires_in_minutes' => self::OTP_EXPIRES_MINUTES,
        ], 'If this email exists, an OTP has been sent.');
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
        ]);

        $record = $this->validOtpRecord(strtolower($validated['email']), $validated['otp']);
        $record->forceFill(['verified_at' => now()])->save();

        return ApiResponse::success([
            'verified' => true,
            'expires_at' => $record->expires_at?->toISOString(),
        ], 'OTP verified successfully.');
    }

    public function reset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $email = strtolower($validated['email']);
        $this->validOtpRecord($email, $validated['otp']);

        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->firstOrFail();

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        $user->tokens()->delete();
        PasswordResetOtp::query()->where('email', $email)->delete();

        return ApiResponse::success(null, 'Password reset successfully. Please login with your new password.');
    }

    private function validOtpRecord(string $email, string $otp): PasswordResetOtp
    {
        $record = PasswordResetOtp::query()->where('email', $email)->first();

        if (
            ! $record
            || $record->expires_at->isPast()
            || $record->attempts >= self::OTP_MAX_ATTEMPTS
        ) {
            throw ValidationException::withMessages([
                'otp' => ['This OTP is invalid or expired. Please request a new code.'],
            ]);
        }

        if (! Hash::check($otp, $record->otp_hash)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => ['The OTP you entered is incorrect.'],
            ]);
        }

        return $record;
    }
}
