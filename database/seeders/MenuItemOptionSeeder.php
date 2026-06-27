<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuItemOptionSeeder extends Seeder
{
    public function run(): void
    {
        $kebabSlugs = [
            'chicken-doner-kebab',
            'lamb-doner-kebab',
            'mixed-meat-kebab',
            'falafel-kebab',
            'peri-chicken-kebab',
        ];

        $plateSlugs = [
            'chicken-hsp-plate',
            'mixed-meat-hsp-plate',
            'lamb-rice-plate',
            'charcoal-chicken-plate',
            'falafel-rice-plate',
        ];

        $burgerSlugs = [
            'arcade-beef-burger',
            'crispy-chicken-burger',
            'peri-chicken-burger',
            'double-smash-burger',
        ];

        $sideSlugs = [
            'loaded-kebab-fries',
            'regular-chips',
            'chicken-wings',
        ];

        MenuItem::whereIn('slug', [...$kebabSlugs, ...$plateSlugs, ...$burgerSlugs, ...$sideSlugs])
            ->with(['sizes', 'addons'])
            ->get()
            ->each(function (MenuItem $item) use ($kebabSlugs, $plateSlugs, $burgerSlugs, $sideSlugs): void {
                $item->sizes()->delete();
                $item->addons()->delete();

                if (in_array($item->slug, $kebabSlugs, true)) {
                    $base = (float) $item->price;

                    $item->sizes()->createMany([
                        ['name' => 'Regular', 'price' => $base, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Large', 'price' => round($base + 3.50, 2), 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Meal Combo', 'price' => round($base + 7.00, 2), 'sort_order' => 3, 'is_active' => true],
                    ]);

                    $item->addons()->createMany([
                        ['name' => 'Extra Meat', 'type' => 'extra', 'price' => 4.00, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Extra Cheese', 'type' => 'extra', 'price' => 2.00, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Jalapenos', 'type' => 'topping', 'price' => 1.00, 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Pickles', 'type' => 'topping', 'price' => 1.00, 'sort_order' => 4, 'is_active' => true],
                        ['name' => 'Garlic Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 5, 'is_active' => true],
                        ['name' => 'Chilli Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 6, 'is_active' => true],
                    ]);
                }

                if (in_array($item->slug, $plateSlugs, true)) {
                    $base = (float) $item->price;

                    $item->sizes()->createMany([
                        ['name' => 'Regular', 'price' => $base, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Large', 'price' => round($base + 5.00, 2), 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Family Tray', 'price' => round($base * 2.35, 2), 'sort_order' => 3, 'is_active' => true],
                    ]);

                    $item->addons()->createMany([
                        ['name' => 'Extra Meat', 'type' => 'extra', 'price' => 4.50, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Extra Cheese', 'type' => 'extra', 'price' => 2.00, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Extra Rice', 'type' => 'extra', 'price' => 3.00, 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Garlic Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 4, 'is_active' => true],
                        ['name' => 'Peri Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 5, 'is_active' => true],
                    ]);
                }

                if (in_array($item->slug, $burgerSlugs, true)) {
                    $item->addons()->createMany([
                        ['name' => 'Extra Cheese Slice', 'type' => 'extra', 'price' => 1.50, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Extra Patty', 'type' => 'extra', 'price' => 4.50, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Jalapenos', 'type' => 'topping', 'price' => 1.00, 'sort_order' => 3, 'is_active' => true],
                        ['name' => 'Garlic Mayo', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 4, 'is_active' => true],
                    ]);
                }

                if (in_array($item->slug, $sideSlugs, true)) {
                    $item->addons()->createMany([
                        ['name' => 'Cheese Sauce', 'type' => 'extra', 'price' => 2.00, 'sort_order' => 1, 'is_active' => true],
                        ['name' => 'Garlic Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 2, 'is_active' => true],
                        ['name' => 'Chilli Sauce', 'type' => 'dip', 'price' => 1.50, 'sort_order' => 3, 'is_active' => true],
                    ]);
                }
            });
    }
}
