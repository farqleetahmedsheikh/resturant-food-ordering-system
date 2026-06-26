<?php

namespace Database\Factories;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company().' Kitchen';

        return [
            'name' => $name,
            'email' => fake()->optional()->companyEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'timezone' => fake()->randomElement(array_keys(config('restaurant.timezones'))),
            'delivery_fee' => fake()->randomFloat(2, 0, 9),
            'minimum_order_amount' => fake()->randomFloat(2, 0, 25),
            'logo' => null,
            'is_open' => true,
        ];
    }
}
