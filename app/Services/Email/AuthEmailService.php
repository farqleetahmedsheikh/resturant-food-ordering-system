<?php

namespace App\Services\Email;

use App\Mail\PasswordResetOtpMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthEmailService
{
    public function sendPasswordResetOtp(User $user, string $otp, int $expiresInMinutes): void
    {
        try {
            Mail::to($user->email, $user->name)->send(
                new PasswordResetOtpMail($user, $otp, $expiresInMinutes),
            );
        } catch (\Throwable $exception) {
            Log::warning('Password reset OTP email could not be sent.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
