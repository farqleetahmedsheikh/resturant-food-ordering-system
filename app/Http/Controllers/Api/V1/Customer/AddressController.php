<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CustomerAddressRequest;
use App\Http\Resources\V1\CustomerAddressResource;
use App\Models\CustomerAddress;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()
            ->addresses()
            ->latest('is_default')
            ->latest()
            ->get();

        return ApiResponse::success(CustomerAddressResource::collection($addresses));
    }

    public function store(CustomerAddressRequest $request): JsonResponse
    {
        $address = DB::transaction(function () use ($request): CustomerAddress {
            $payload = $request->validated();
            $payload['is_default'] = $request->boolean('is_default') || ! $request->user()->addresses()->exists();

            if ($payload['is_default']) {
                $request->user()->addresses()->update(['is_default' => false]);
            }

            return $request->user()->addresses()->create($payload);
        });

        return ApiResponse::success(new CustomerAddressResource($address), 'Address saved successfully.', status: 201);
    }

    public function update(CustomerAddressRequest $request, CustomerAddress $address): JsonResponse
    {
        $this->ensureOwnedByUser($request, $address);

        $address = DB::transaction(function () use ($request, $address): CustomerAddress {
            $payload = $request->validated();
            $payload['is_default'] = $request->boolean('is_default');

            if ($payload['is_default']) {
                $request->user()->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
            }

            $address->update($payload);

            if (! $request->user()->addresses()->where('is_default', true)->exists()) {
                $address->update(['is_default' => true]);
            }

            return $address->fresh();
        });

        return ApiResponse::success(new CustomerAddressResource($address), 'Address updated successfully.');
    }

    public function destroy(Request $request, CustomerAddress $address): JsonResponse
    {
        $this->ensureOwnedByUser($request, $address);

        DB::transaction(function () use ($request, $address): void {
            $wasDefault = (bool) $address->is_default;
            $address->delete();

            if ($wasDefault) {
                $request->user()
                    ->addresses()
                    ->latest()
                    ->first()
                    ?->update(['is_default' => true]);
            }
        });

        return ApiResponse::success(null, 'Address deleted successfully.');
    }

    private function ensureOwnedByUser(Request $request, CustomerAddress $address): void
    {
        if ($address->user_id !== $request->user()->id) {
            throw new BusinessRuleException('You are not allowed to access this address.', 403);
        }
    }
}
