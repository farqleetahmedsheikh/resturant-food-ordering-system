<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::current();
        $customers = User::where('role', 'customer')->orderBy('email')->get();
        $riders = User::where('role', 'rider')->orderBy('email')->get();
        $menuItems = MenuItem::where('is_available', true)->orderBy('id')->take(8)->get();

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
                'items' => [0, 3],
                'address' => 'House 12, Demo Street, City Center',
            ],
            [
                'number' => 'ORD-DEMO-0002',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'accepted',
                'payment_status' => 'pending',
                'rider' => null,
                'delivery_status' => null,
                'items' => [1, 9],
                'address' => 'Apartment 5B, Food Avenue, City Center',
            ],
            [
                'number' => 'ORD-DEMO-0003',
                'customer' => $customers->get(0),
                'status' => 'preparing',
                'payment_status' => 'pending',
                'rider' => null,
                'delivery_status' => null,
                'items' => [2, 10],
                'address' => 'Office 4, Business Road, City Center',
            ],
            [
                'number' => 'ORD-DEMO-0004',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'assigned_to_rider',
                'payment_status' => 'pending',
                'rider' => $riders->get(0),
                'delivery_status' => 'assigned',
                'items' => [4, 11],
                'address' => 'Villa 7, Green Block, City Center',
            ],
            [
                'number' => 'ORD-DEMO-0005',
                'customer' => $customers->get(0),
                'status' => 'out_for_delivery',
                'payment_status' => 'pending',
                'rider' => $riders->get(1) ?? $riders->get(0),
                'delivery_status' => 'out_for_delivery',
                'items' => [5, 12],
                'address' => 'Shop 3, Market Lane, City Center',
                'picked_up' => true,
            ],
            [
                'number' => 'ORD-DEMO-0006',
                'customer' => $customers->get(1) ?? $customers->get(0),
                'status' => 'delivered',
                'payment_status' => 'paid',
                'rider' => $riders->get(2) ?? $riders->get(0),
                'delivery_status' => 'delivered',
                'items' => [6, 13],
                'address' => 'Flat 22, Sunset Apartments, City Center',
                'picked_up' => true,
                'delivered' => true,
            ],
        ];

        foreach ($orders as $index => $demo) {
            $selectedItems = collect($demo['items'])
                ->map(fn (int $itemIndex) => $menuItems->get($itemIndex % $menuItems->count()))
                ->filter();

            $subtotal = $selectedItems->sum(fn (MenuItem $item): float => (float) $item->price);
            $deliveryFee = (float) ($restaurant->delivery_fee ?? 4.99);
            $assignedAt = $demo['rider'] ? now()->subHours(6 - $index) : null;
            $pickedUpAt = ($demo['picked_up'] ?? false) ? now()->subHours(4 - min($index, 3)) : null;
            $deliveredAt = ($demo['delivered'] ?? false) ? now()->subHour() : null;

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
                    'order_notes' => 'Demo order for client walkthrough.',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'total' => $subtotal + $deliveryFee,
                    'payment_method' => 'cod',
                    'payment_status' => $demo['payment_status'],
                    'order_status' => $demo['status'],
                    'assigned_at' => $assignedAt,
                    'picked_up_at' => $pickedUpAt,
                    'delivered_at' => $deliveredAt,
                ],
            );

            $order->items()->delete();

            foreach ($selectedItems as $item) {
                $order->items()->create([
                    'menu_item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => 1,
                    'price' => $item->price,
                    'total' => $item->price,
                ]);
            }

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
}
