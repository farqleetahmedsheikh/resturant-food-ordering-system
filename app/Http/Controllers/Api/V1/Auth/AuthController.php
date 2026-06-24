<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        return ApiResponse::success(
            $this->tokenPayload($user, $validated['device_name'] ?? null),
            'Registration successful.',
            status: 201,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->is_active) {
            return ApiResponse::error('Your account is inactive. Please contact support.', 403);
        }

        return ApiResponse::success(
            $this->tokenPayload($user, $validated['device_name'] ?? null),
            'Login successful.',
        );
    }

    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return ApiResponse::success(null, 'Logged out from all devices successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function tokenPayload(User $user, ?string $deviceName): array
    {
        $abilities = $this->abilitiesForRole($user->role);
        $expiration = config('sanctum.expiration');
        $expiresAt = $expiration ? now()->addMinutes((int) $expiration) : null;
        $token = $user->createToken($deviceName ?: 'mobile-app', $abilities, $expiresAt);

        return [
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt?->toISOString(),
            'abilities' => $abilities,
            'user' => new UserResource($user),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function abilitiesForRole(string $role): array
    {
        return match ($role) {
            'admin' => ['admin', 'customer:read', 'rider:read'],
            'rider' => ['rider'],
            default => ['customer'],
        };
    }
}
