<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Restaurant::updateOrCreate(
            ['name' => 'Arcade Kebab House'],
            [
                'email' => 'hello@arcadekebabhouse.test',
                'phone' => '03000000010',
                'address' => 'Address to be configured',
                'formatted_address' => 'Address to be configured',
                'short_description' => 'Fresh kebabs, grilled plates, pizzas, burgers, drinks, and desserts delivered with secure card payment.',
                'opening_time' => '12:00',
                'closing_time' => '23:00',
                'timezone' => 'Australia/Sydney',
                'latitude' => null,
                'longitude' => null,
                'delivery_fee' => 4.99,
                'minimum_order_amount' => 18.00,
                'logo' => null,
                'cover_image' => null,
                'is_open' => true,
            ]
        );
    }
}
