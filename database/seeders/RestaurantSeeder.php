<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        Restaurant::where('slug', '!=', 'freshbite-restaurant')->update([
            'is_active' => false,
        ]);

        Restaurant::updateOrCreate(
            ['slug' => 'freshbite-restaurant'],
            [
                'name' => 'FreshBite Restaurant',
                'email' => 'hello@freshbite.test',
                'phone' => '03000000010',
                'address' => 'Main Food Street, City Center',
                'short_description' => 'Fresh pizzas, burgers, pasta, drinks, and desserts delivered with a simple cash-on-delivery flow.',
                'opening_time' => '12:00',
                'closing_time' => '23:00',
                'delivery_fee' => 199.00,
                'minimum_order_amount' => 799.00,
                'logo' => null,
                'cover_image' => null,
                'is_open' => true,
                'is_active' => true,
            ]
        );
    }
}
