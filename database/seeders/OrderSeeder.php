<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::current();
        $customers = User::where('role', 'customer')->orderBy('email')->get();
        $riders = User::where('role', 'rider')->orderBy('email')->get();
        $menuItems = MenuItem::query()
            ->with(['activeSizes', 'activeAddons'])
            ->where('is_available', true)
            ->get()
            ->keyBy('slug');

        if (! $restaurant || $customers->isEmpty() || $menuItems->isEmpty()) {
            return;
        }

        $orders = [
            [
                'number' => 'ORD-DEMO-0001',
                'customer' => $customers->get(0),
                'status' => 'pending',
                'payment_status' => 'pending',
                'rider' => null,
                'delivery_status' => null,
                'items' => [
                    ['slug' => 'mixed-meat-kebab', 'quantity' => 1, 'size' => 'Large', 'addons' => ['Extra Cheese', 'Garlic Sauce']],
                    ['slug' => 'coca-cola-can', 'quantity' => 2],
                ],
                'address' => 'House 12, Demo Street, City Center',
                'notes' => 'Please add extra napkins.',
                'placed_at' => now()->subMinutes(18),
            ],
            [
                'number' => 'ORD-DEMO-0002',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'accepted',
                'payment_status' => 'pending',
                'rider' => null,
                'delivery_status' => null,
                'items' => [
                    ['slug' => 'chicken-doner-kebab', 'quantity' => 2, 'size' => 'Regular', 'addons' => ['Garlic Sauce']],
                    ['slug' => 'regular-chips', 'quantity' => 1, 'addons' => ['Cheese Sauce']],
                    ['slug' => 'sprite-can', 'quantity' => 2],
                ],
                'address' => 'Apartment 5B, Food Avenue, City Center',
                'notes' => 'Call when outside.',
                'placed_at' => now()->subMinutes(42),
            ],
            [
                'number' => 'ORD-DEMO-0003',
                'customer' => $customers->get(0),
                'status' => 'preparing',
                'payment_status' => 'pending',
                'rider' => null,
                'delivery_status' => null,
                'items' => [
                    ['slug' => 'chicken-hsp-plate', 'quantity' => 1, 'size' => 'Large', 'addons' => ['Extra Meat', 'Peri Sauce']],
                    ['slug' => 'mint-lemonade', 'quantity' => 1],
                ],
                'address' => 'Office 4, Business Road, City Center',
                'notes' => 'Mild chilli only.',
                'placed_at' => now()->subHour(),
            ],
            [
                'number' => 'ORD-DEMO-0004',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'assigned_to_rider',
                'payment_status' => 'pending',
                'rider' => $riders->get(0),
                'delivery_status' => 'assigned',
                'items' => [
                    ['slug' => 'mixed-meat-hsp-plate', 'quantity' => 1, 'size' => 'Regular', 'addons' => ['Extra Cheese']],
                    ['slug' => 'baklava-box', 'quantity' => 1],
                ],
                'address' => 'Villa 7, Green Block, City Center',
                'notes' => 'No onion.',
                'placed_at' => now()->subHours(2),
            ],
            [
                'number' => 'ORD-DEMO-0005',
                'customer' => $customers->get(0),
                'status' => 'out_for_delivery',
                'payment_status' => 'pending',
                'rider' => $riders->get(1) ?? $riders->get(0),
                'delivery_status' => 'out_for_delivery',
                'items' => [
                    ['slug' => 'arcade-beef-burger', 'quantity' => 1, 'addons' => ['Extra Cheese Slice', 'Jalapenos']],
                    ['slug' => 'loaded-kebab-fries', 'quantity' => 1, 'addons' => ['Garlic Sauce']],
                    ['slug' => 'mango-lassi', 'quantity' => 1],
                ],
                'address' => 'Shop 3, Market Lane, City Center',
                'notes' => 'Deliver to reception.',
                'picked_up' => true,
                'placed_at' => now()->subHours(3),
            ],
            [
                'number' => 'ORD-DEMO-0006',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'rider' => $riders->get(2) ?? $riders->get(0),
                'delivery_status' => 'delivered',
                'items' => [
                    ['slug' => 'charcoal-chicken-plate', 'quantity' => 1, 'size' => 'Family Tray', 'addons' => ['Extra Rice', 'Garlic Sauce']],
                    ['slug' => 'kunafa-cup', 'quantity' => 2],
                    ['slug' => 'coca-cola-can', 'quantity' => 4],
                ],
                'address' => 'Flat 22, Sunset Apartments, City Center',
                'notes' => 'Family dinner order.',
                'picked_up' => true,
                'delivered' => true,
                'placed_at' => now()->subDay(),
            ],
            [
                'number' => 'ORD-DEMO-0007',
                'customer' => $customers->get(0),
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'rider' => null,
                'delivery_status' => null,
                'items' => [
                    ['slug' => 'falafel-kebab', 'quantity' => 1, 'size' => 'Regular', 'addons' => ['Pickles']],
                    ['slug' => 'chilli-sauce-dip', 'quantity' => 1],
                ],
                'address' => 'Cancelled demo address',
                'notes' => 'Demo cancelled order.',
                'placed_at' => now()->subDays(2),
            ],
        ];

        foreach ($orders as $index => $demo) {
            $selectedItems = $this->buildOrderItems($menuItems, $demo['items']);

            if ($selectedItems->isEmpty()) {
                continue;
            }

            $subtotal = $selectedItems->sum('total');
            $deliveryFee = (float) ($restaurant->delivery_fee ?? 4.99);
            $assignedAt = $demo['rider'] ? $demo['placed_at']->copy()->addMinutes(25) : null;
            $pickedUpAt = ($demo['picked_up'] ?? false) ? $demo['placed_at']->copy()->addMinutes(45) : null;
            $deliveredAt = ($demo['delivered'] ?? false) ? $demo['placed_at']->copy()->addMinutes(70) : null;

            $order = Order::updateOrCreate(
                ['order_number' => $demo['number']],
                [
                    'user_id' => $demo['customer']?->id,
                    'rider_id' => $demo['rider']?->id,
                    'restaurant_id' => $restaurant->id,
                    'customer_name' => $demo['customer']?->name ?? 'Demo Customer',
                    'customer_phone' => $demo['customer']?->phone ?? '03000000000',
                    'customer_email' => $demo['customer']?->email,
                    'delivery_address' => $demo['address'],
                    'order_notes' => $demo['notes'],
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $subtotal + $deliveryFee,
                    'payment_method' => 'cod',
                    'payment_status' => $demo['payment_status'],
                    'order_status' => $demo['status'],
                    'assigned_at' => $assignedAt,
                    'picked_up_at' => $pickedUpAt,
                    'delivered_at' => $deliveredAt,
                    'created_at' => $demo['placed_at'],
                    'updated_at' => now()->subMinutes(max(1, 20 - $index)),
                ],
            );

            $order->items()->delete();

            foreach ($selectedItems as $item) {
                $order->items()->create($item);
            }

            $order->statusHistories()->delete();
            $this->seedStatusHistory($order, $demo['status'], $demo['placed_at'], $demo['rider']);

            if ($demo['rider']) {
                $order->delivery()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'rider_id' => $demo['rider']->id,
                        'status' => $demo['delivery_status'],
                        'pickup_time' => $pickedUpAt,
                        'delivered_time' => $deliveredAt,
                        'notes' => null,
                    ],
                );
            } else {
                $order->delivery()->delete();
            }
        }
    }

    /**
     * @param  Collection<string, MenuItem>  $menuItems
     * @param  array<int, array<string, mixed>>  $itemDefinitions
     * @return Collection<int, array<string, mixed>>
     */
    private function buildOrderItems(Collection $menuItems, array $itemDefinitions): Collection
    {
        return collect($itemDefinitions)
            ->map(function (array $definition) use ($menuItems): ?array {
                /** @var MenuItem|null $menuItem */
                $menuItem = $menuItems->get($definition['slug']);

                if (! $menuItem) {
                    return null;
                }

                $size = $menuItem->activeSizes->firstWhere('name', $definition['size'] ?? '');
                $addons = collect($definition['addons'] ?? [])
                    ->map(fn (string $name) => $menuItem->activeAddons->firstWhere('name', $name))
                    ->filter()
                    ->values();

                $quantity = (int) ($definition['quantity'] ?? 1);
                $unitPrice = (float) ($size?->price ?? $menuItem->price);
                $addonsTotal = $addons->sum(fn ($addon): float => (float) $addon->price);
                $lineTotal = ($unitPrice + $addonsTotal) * $quantity;

                return [
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'size_name' => $size?->name,
                    'size_price' => $size?->price,
                    'addons_snapshot' => $addons->map(fn ($addon): array => [
                        'id' => $addon->id,
                        'name' => $addon->name,
                        'type' => $addon->type,
                        'price' => (float) $addon->price,
                    ])->all(),
                    'addons_total' => $addonsTotal,
                    'quantity' => $quantity,
                    'price' => $unitPrice,
                    'total' => $lineTotal,
                ];
            })
            ->filter()
            ->values();
    }

    private function seedStatusHistory(Order $order, string $status, $placedAt, ?User $rider): void
    {
        $admin = User::where('role', 'admin')->first();

        $order->statusHistories()->create([
            'previous_status' => null,
            'new_status' => 'pending',
            'changed_by_user_id' => $order->user_id,
            'changed_by_role' => 'customer',
            'reason' => 'Demo order placed',
            'metadata' => ['source' => 'seeder'],
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);

        $timeline = [
            'accepted' => ['accepted'],
            'preparing' => ['accepted', 'preparing'],
            'assigned_to_rider' => ['accepted', 'preparing', 'assigned_to_rider'],
            'out_for_delivery' => ['accepted', 'preparing', 'assigned_to_rider', 'out_for_delivery'],
            'delivered' => ['accepted', 'preparing', 'assigned_to_rider', 'out_for_delivery', 'delivered'],
            'cancelled' => ['cancelled'],
        ][$status] ?? [];

        $previous = 'pending';

        foreach ($timeline as $stepIndex => $step) {
            $actor = in_array($step, ['out_for_delivery', 'delivered'], true) ? $rider : $admin;

            $order->statusHistories()->create([
                'previous_status' => $previous,
                'new_status' => $step,
                'changed_by_user_id' => $actor?->id,
                'changed_by_role' => $actor?->role ?? 'admin',
                'reason' => 'Demo '.$step.' status',
                'metadata' => ['source' => 'seeder'],
                'created_at' => $placedAt->copy()->addMinutes(12 * ($stepIndex + 1)),
                'updated_at' => $placedAt->copy()->addMinutes(12 * ($stepIndex + 1)),
            ]);

            $previous = $step;
        }
    }
}
