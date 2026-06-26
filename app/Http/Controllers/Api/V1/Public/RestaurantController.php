<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RestaurantResource;
use App\Models\Restaurant;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    public function show(): JsonResponse
    {
        $restaurant = Restaurant::query()
            ->oldest('id')
            ->firstOrFail();

        return ApiResponse::success(new RestaurantResource($restaurant));
    }
}
