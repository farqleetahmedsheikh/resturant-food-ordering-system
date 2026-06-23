<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class SecurityRateLimiter
{
    private const IP_MAX_ATTEMPTS = 180;
    private const USER_MAX_ATTEMPTS = 300;
    private const DECAY_SECONDS = 60;
    private const BLOCK_SECONDS = 600;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up')) {
            return $next($request);
        }

        $ip = (string) $request->ip();
        $blockedKey = $this->blockedKey($ip);
        $blockedUntil = Cache::get($blockedKey);

        if ($blockedUntil) {
            return $this->blockedResponse($request, max(1, (int) $blockedUntil - now()->timestamp));
        }

        $limits = [
            [$this->ipKey($ip), self::IP_MAX_ATTEMPTS],
        ];

        if ($request->user()) {
            $limits[] = [$this->userKey((int) $request->user()->id), self::USER_MAX_ATTEMPTS];
        }

        foreach ($limits as [$key, $maxAttempts]) {
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                return $this->blockIp($request, $ip);
            }

            RateLimiter::hit($key, self::DECAY_SECONDS);

            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                return $this->blockIp($request, $ip);
            }
        }

        return $next($request);
    }

    private function blockIp(Request $request, string $ip): Response
    {
        $blockedUntil = now()->addSeconds(self::BLOCK_SECONDS)->timestamp;

        Cache::put($this->blockedKey($ip), $blockedUntil, self::BLOCK_SECONDS);

        return $this->blockedResponse($request, self::BLOCK_SECONDS);
    }

    private function blockedResponse(Request $request, int $retryAfter): Response
    {
        $message = 'Too many requests were sent from your network. For security, access is temporarily paused for 10 minutes.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
                'retry_after_seconds' => $retryAfter,
            ], 429)->header('Retry-After', (string) $retryAfter);
        }

        return response()
            ->view('errors.429', [
                'message' => $message,
                'retryAfter' => $retryAfter,
            ], 429)
            ->header('Retry-After', (string) $retryAfter);
    }

    private function ipKey(string $ip): string
    {
        return 'security:requests:ip:'.sha1($ip);
    }

    private function userKey(int $userId): string
    {
        return 'security:requests:user:'.$userId;
    }

    private function blockedKey(string $ip): string
    {
        return 'security:blocked-ip:'.sha1($ip);
    }
}
