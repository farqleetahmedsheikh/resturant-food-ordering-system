<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetOtpController extends Controller
{
    private const OTP_EXPIRES_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    public function requestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower($validated['email']);
        $rateKey = 'password-reset-otp:'.sha1($email.'|'.(string) $request->ip());

        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            throw ValidationException::withMessages([
                'email' => 'Please wait before requesting another OTP.',
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

            Mail::raw(
                "Your Arcade Kebab House password reset OTP is {$otp}. It expires in ".self::OTP_EXPIRES_MINUTES.' minutes.',
                fn ($message) => $message
                    ->to($user->email, $user->name)
                    ->subject('Arcade Kebab House password reset OTP'),
            );
        }

        $request->session()->put('password_reset_email', $email);

        return redirect()
            ->route('password.otp')
            ->with('status', 'If this email exists, an OTP has been sent. Please check your inbox.');
    }

    public function verifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $record = PasswordResetOtp::query()->where('email', $email)->first();

        if (
            ! $record
            || $record->expires_at->isPast()
            || $record->attempts >= self::OTP_MAX_ATTEMPTS
        ) {
            throw ValidationException::withMessages([
                'otp' => 'This OTP is invalid or expired. Please request a new code.',
            ]);
        }

        if (! Hash::check($validated['otp'], $record->otp_hash)) {
            $record->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => 'The OTP you entered is incorrect.',
            ]);
        }

        $record->forceFill(['verified_at' => now()])->save();
        $request->session()->put('password_reset_verified_email', $email);

        return redirect()
            ->route('password.reset.form')
            ->with('status', 'OTP verified. Create your new password.');
    }

    public function resetForm(Request $request): View|RedirectResponse
    {
        if (! $this->hasVerifiedResetSession($request)) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        if (! $this->hasVerifiedResetSession($request)) {
            return redirect()->route('password.request');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $email = $request->session()->get('password_reset_verified_email');
        $user = User::query()
            ->where('email', $email)
            ->where('is_active', true)
            ->firstOrFail();

        $user->forceFill([
            'password' => $validated['password'],
        ])->save();

        PasswordResetOtp::query()->where('email', $email)->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_verified_email']);
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been updated. Please login with the new password.');
    }

    private function hasVerifiedResetSession(Request $request): bool
    {
        $email = $request->session()->get('password_reset_verified_email');

        if (! $email) {
            return false;
        }

        return PasswordResetOtp::query()
            ->where('email', $email)
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();
    }
}
