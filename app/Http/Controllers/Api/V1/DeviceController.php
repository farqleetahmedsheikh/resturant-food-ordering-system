<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeviceStoreRequest;
use App\Http\Resources\V1\UserDeviceResource;
use App\Models\UserDevice;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function store(DeviceStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $hash = hash('sha256', $validated['push_token']);

        $device = UserDevice::query()->updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'push_token_hash' => $hash,
            ],
            [
                'device_uuid' => $validated['device_uuid'] ?? null,
                'device_name' => $validated['device_name'] ?? null,
                'platform' => $validated['platform'],
                'push_token' => $validated['push_token'],
                'app_version' => $validated['app_version'] ?? null,
                'last_seen_at' => now(),
                'revoked_at' => null,
            ],
        );

        return ApiResponse::success(new UserDeviceResource($device), 'Device registered successfully.');
    }

    public function destroy(Request $request, UserDevice $device): JsonResponse
    {
        if ($device->user_id !== $request->user()->id) {
            throw new BusinessRuleException('You are not allowed to access this device.', 403);
        }

        $device->update(['revoked_at' => now()]);

        return ApiResponse::success(null, 'Device removed successfully.');
    }
}
