<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProfileUpdateRequest;
use App\Http\Resources\V1\UserResource;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return ApiResponse::success(new UserResource($request->user()));
    }

    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return ApiResponse::success(new UserResource($request->user()->fresh()), 'Profile updated successfully.');
    }
}
