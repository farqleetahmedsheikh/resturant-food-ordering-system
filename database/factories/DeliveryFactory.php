<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'rider_id' => User::factory()->state(['role' => 'rider']),
            'pickup_time' => null,
            'delivered_time' => null,
            'status' => 'pending',
            'notes' => null,
        ];
    }
}
