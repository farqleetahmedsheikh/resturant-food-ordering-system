<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Exceptions\BusinessRuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdminRiderStoreRequest;
use App\Http\Requests\Api\V1\AdminRiderUpdateRequest;
use App\Http\Resources\V1\RiderResource;
use App\Models\User;
use App\Services\Security\AuditLogger;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RiderController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): JsonResponse
    {
        $riders = User::query()
            ->where('role', 'rider')
            ->withCount(['assignedOrders', 'deliveredOrders'])
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy('name')
            ->paginate(min((int) $request->integer('per_page', 20), 75))
            ->withQueryString();

        return ApiResponse::success(
            RiderResource::collection($riders)->resolve(),
            meta: ApiResponse::paginationMeta($riders),
        );
    }

    public function store(AdminRiderStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $rider = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'rider',
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->auditLogger->record('rider.created', $request->user(), $rider, [], $rider->only(['id', 'name', 'email', 'phone', 'is_active']));

        return ApiResponse::success(new RiderResource($rider), 'Rider created successfully.', status: 201);
    }

    public function show(User $rider): JsonResponse
    {
        abort_unless($rider->role === 'rider', 404);

        $rider->loadCount(['assignedOrders', 'deliveredOrders']);

        return ApiResponse::success(new RiderResource($rider));
    }

    public function update(AdminRiderUpdateRequest $request, User $rider): JsonResponse
    {
        abort_unless($rider->role === 'rider', 404);

        $validated = $request->validated();
        $old = $rider->only(['name', 'email', 'phone', 'is_active']);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', $rider->is_active),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $rider->update($payload);

        $this->auditLogger->record('rider.updated', $request->user(), $rider, $old, $rider->only(['name', 'email', 'phone', 'is_active']));

        return ApiResponse::success(new RiderResource($rider->fresh()), 'Rider updated successfully.');
    }

    public function destroy(Request $request, User $rider): JsonResponse
    {
        abort_unless($rider->role === 'rider', 404);

        $hasActiveOrders = $rider->assignedOrders()
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->exists();

        if ($hasActiveOrders) {
            throw new BusinessRuleException('This rider has active assigned orders and cannot be deleted.');
        }

        $this->auditLogger->record('rider.deleted', $request->user(), $rider, $rider->only(['id', 'name', 'email']), []);
        $rider->delete();

        return ApiResponse::success(null, 'Rider deleted successfully.');
    }
}
