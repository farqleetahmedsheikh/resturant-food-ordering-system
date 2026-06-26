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
            'Pizza',
            'Burgers',
            'Pasta',
            'Drinks',
            'Desserts',
        ];

        Category::whereNotIn('slug', collect($categories)->map(fn (string $name) => Str::slug($name))->all())
            ->update(['is_active' => false]);

        collect($categories)->each(function (string $name, int $index) use ($restaurant): void {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'restaurant_id' => $restaurant?->id,
                    'name' => $name,
                    'description' => "Arcade Kebab House {$name} prepared fresh for delivery.",
                    'image' => null,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        });
    }
}
