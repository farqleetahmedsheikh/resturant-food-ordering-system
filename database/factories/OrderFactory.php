<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 15, 120);
        $deliveryFee = fake()->randomFloat(2, 2, 8);

        return [
            'user_id' => User::factory(),
            'rider_id' => null,
            'restaurant_id' => Restaurant::factory(),
            'order_number' => 'ORD-'.fake()->unique()->numerify('######'),
            'customer_name' => fake()->name(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_email' => fake()->optional()->safeEmail(),
            'delivery_address' => fake()->address(),
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total' => $subtotal + $deliveryFee,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ];
    }
}
