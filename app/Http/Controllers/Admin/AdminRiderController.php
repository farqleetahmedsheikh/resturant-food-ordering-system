<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRiderController extends Controller
{
    public function index(): View
    {
        $riders = User::query()
            ->where('role', 'rider')
            ->withCount([
                'assignedOrders as assigned_orders_count',
                'assignedOrders as delivered_orders_count' => fn ($query) => $query->where('order_status', 'delivered'),
            ])
            ->latest()
            ->paginate(12);

        return view('admin.riders', compact('riders'));
    }

    public function create(): View
    {
        return view('admin.rider-form', [
            'rider' => new User(['is_active' => true]),
            'mode' => 'create',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'role' => 'rider',
            'is_active' => $request->boolean('is_active'),
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.riders.index')->with('status', 'Rider created successfully.');
    }

    public function edit(User $rider): View
    {
        $this->ensureRider($rider);

        return view('admin.rider-form', [
            'rider' => $rider,
            'mode' => 'edit',
        ]);
    }

    public function update(Request $request, User $rider): RedirectResponse
    {
        $this->ensureRider($rider);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($rider->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = $validated['password'];
        }

        $rider->update($payload);

        return redirect()->route('admin.riders.index')->with('status', 'Rider updated successfully.');
    }

    public function destroy(User $rider): RedirectResponse
    {
        $this->ensureRider($rider);

        $activeAssignedOrders = Order::query()
            ->where('rider_id', $rider->id)
            ->whereNotIn('order_status', ['delivered', 'cancelled'])
            ->count();

        if ($activeAssignedOrders > 0) {
            return back()->with('status', 'This rider has active assigned orders and cannot be deleted.');
        }

        $rider->delete();

        return redirect()->route('admin.riders.index')->with('status', 'Rider deleted successfully.');
    }

    private function ensureRider(User $rider): void
    {
        abort_unless($rider->role === 'rider', 404);
    }
}
