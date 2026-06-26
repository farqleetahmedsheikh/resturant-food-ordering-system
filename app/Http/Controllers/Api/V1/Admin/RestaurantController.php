<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminRestaurantRequest;
use App\Http\Resources\V1\RestaurantResource;
use App\Models\Restaurant;
use App\Services\Security\AuditLogger;
use App\Support\Api\ApiResponse;
use App\Support\ImageUpload;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function show(): JsonResponse
    {
        $restaurant = Restaurant::query()->oldest('id')->firstOrFail();

        return ApiResponse::success(new RestaurantResource($restaurant));
    }

    public function update(AdminRestaurantRequest $request): JsonResponse
    {
        $restaurant = Restaurant::query()->oldest('id')->firstOrFail();
        $old = $restaurant->toArray();
        $payload = $request->validated();

        $payload['is_open'] = $request->boolean('is_open', $restaurant->is_open);
        $payload['formatted_address'] = $payload['formatted_address'] ?? $payload['address'] ?? null;

        if ($request->hasFile('logo')) {
            $payload['logo'] = ImageUpload::store($request->file('logo'), 'restaurant/logos', $restaurant->logo);
        }

        if ($request->hasFile('cover_image')) {
            $payload['cover_image'] = ImageUpload::store($request->file('cover_image'), 'restaurant/covers', $restaurant->cover_image);
        }

        $restaurant->update($payload);
        $restaurant = $restaurant->fresh();

        $this->auditLogger->record('restaurant.updated', $request->user(), $restaurant, $old, $restaurant->toArray());

        return ApiResponse::success(new RestaurantResource($restaurant), 'Restaurant settings updated successfully.');
    }
}
