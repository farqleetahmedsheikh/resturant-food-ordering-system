<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::current();

        $categories = [
            ['Kebabs', 'Signature wraps and kebabs grilled fresh with house sauces.'],
            ['Kebab Plates', 'Loaded rice, salad, chips, and grilled protein plates.'],
            ['Burgers', 'Chargrilled burgers with Arcade sauces and fresh toppings.'],
            ['Sides & Dips', 'Chips, wings, breads, dips, and shareable extras.'],
            ['Drinks', 'Cold drinks and fresh refreshers for every order.'],
            ['Desserts', 'Sweet finishes for family meals and late-night cravings.'],
        ];

        Category::whereNotIn('slug', collect($categories)->map(fn (array $category) => Str::slug($category[0]))->all())
            ->update(['is_active' => false]);

        collect($categories)->each(function (array $category, int $index) use ($restaurant): void {
            [$name, $description] = $category;

            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'restaurant_id' => $restaurant?->id,
                    'name' => $name,
                    'description' => $description,
                    'image' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        });
    }
}
