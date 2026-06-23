<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemOptionSeeder extends Seeder
{
    public function run(): void
    {
        $pizzaSlugs = [
            'margherita-pizza',
            'chicken-tikka-pizza',
            'pepperoni-feast-pizza',
            'veggie-supreme-pizza',
        ];

        $burgerSlugs = [
            'classic-beef-burger',
            'crispy-chicken-burger',
            'smoky-bbq-burger',
            'spicy-jalapeno-burger',
        ];

        $pastaSlugs = [
            'creamy-alfredo-pasta',
            'spicy-arrabbiata-pasta',
            'mushroom-penne-pasta',
            'chicken-lasagna',
        ];

        MenuItem::whereIn('slug', [...$pizzaSlugs, ...$burgerSlugs, ...$pastaSlugs])
            ->with(['sizes', 'addons'])
            ->get()
            ->each(function (MenuItem $item) use ($pizzaSlugs, $burgerSlugs, $pastaSlugs): void {
                $item->sizes()->delete();
                $item->addons()->delete();

                if (in_array($item->slug, $pizzaSlugs, true)) {
                    $base = (float) $item->price;

                    $item->sizes()->createMany([
                        ['name' => 'Small', 'price' => round($base * 0.72), 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Medium', 'price' => $base, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Large', 'price' => round($base * 1.28), 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Party Size', 'price' => round($base * 1.85), 'sort_order' => 4, 'is_active' => true],
                    ]);

                    $item->addons()->createMany([
                        ['name' => 'Extra Cheese', 'type' => 'extra', 'price' => 199, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Extra Chicken', 'type' => 'topping', 'price' => 249, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Mushrooms', 'type' => 'topping', 'price' => 149, 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Olives', 'type' => 'topping', 'price' => 129, 'sort_order' => 4, 'is_active' => true],
                        ['name' => 'Garlic Mayo Dip', 'type' => 'dip', 'price' => 99, 'sort_order' => 5, 'is_active' => true],
                        ['name' => 'Ranch Dip', 'type' => 'dip', 'price' => 99, 'sort_order' => 6, 'is_active' => true],
                    ]);
                }

                if (in_array($item->slug, $burgerSlugs, true)) {
                    $item->addons()->createMany([
                        ['name' => 'Extra Cheese Slice', 'type' => 'extra', 'price' => 99, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Extra Patty', 'type' => 'extra', 'price' => 299, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Jalapenos', 'type' => 'topping', 'price' => 79, 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Garlic Mayo Dip', 'type' => 'dip', 'price' => 99, 'sort_order' => 4, 'is_active' => true],
                    ]);
                }

                if (in_array($item->slug, $pastaSlugs, true)) {
                    $item->addons()->createMany([
                        ['name' => 'Extra Chicken', 'type' => 'extra', 'price' => 199, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Parmesan Cheese', 'type' => 'extra', 'price' => 149, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Garlic Bread Dip', 'type' => 'dip', 'price' => 99, 'sort_order' => 3, 'is_active' => true],
                    ]);
                }
            });
    }
}
