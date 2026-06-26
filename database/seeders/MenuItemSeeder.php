<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = Restaurant::current();

        $items = [
            ['Pizza', 'Margherita Pizza', 'Classic tomato sauce, mozzarella, basil, and olive oil on a crisp base.', 13.90, 15.90, 18, 780, true, true],
            ['Pizza', 'Chicken Tikka Pizza', 'Spiced chicken tikka, onions, peppers, mozzarella, and house sauce.', 16.90, 18.90, 22, 940, true, true],
            ['Pizza', 'Pepperoni Feast Pizza', 'Loaded pepperoni, mozzarella, oregano, and rich tomato sauce.', 17.90, 19.90, 20, 1020, false, true],
            ['Pizza', 'Veggie Supreme Pizza', 'Mushrooms, olives, sweet corn, onions, capsicum, and mozzarella.', 15.90, null, 19, 820, false, true],
            ['Burgers', 'Classic Beef Burger', 'Juicy beef patty, cheddar, lettuce, tomato, pickles, and burger sauce.', 12.90, 14.90, 15, 690, true, true],
            ['Burgers', 'Crispy Chicken Burger', 'Crunchy chicken fillet with slaw, lettuce, and creamy mayo.', 12.50, 14.50, 14, 640, true, true],
            ['Burgers', 'Smoky BBQ Burger', 'Beef patty with BBQ sauce, caramelized onions, and melted cheese.', 14.90, 16.90, 16, 740, false, true],
            ['Burgers', 'Spicy Jalapeno Burger', 'Grilled chicken, jalapenos, pepper jack cheese, and spicy mayo.', 13.90, null, 15, 680, false, true],
            ['Pasta', 'Creamy Alfredo Pasta', 'Fettuccine tossed in parmesan cream sauce with grilled chicken.', 15.90, 17.90, 20, 760, true, true],
            ['Pasta', 'Spicy Arrabbiata Pasta', 'Penne in a chili tomato sauce with garlic, parsley, and parmesan.', 13.90, null, 18, 610, false, true],
            ['Pasta', 'Mushroom Penne Pasta', 'Penne with mushrooms, herbs, cream sauce, and parmesan.', 14.90, 16.90, 19, 700, false, true],
            ['Pasta', 'Chicken Lasagna', 'Layered pasta with chicken mince, tomato sauce, bechamel, and cheese.', 16.90, null, 24, 880, true, true],
            ['Drinks', 'Mint Lemonade', 'Fresh lemon, mint, and chilled soda served over ice.', 4.50, null, 5, 140, true, true],
            ['Drinks', 'Iced Peach Tea', 'Cold brewed tea with peach syrup and lemon.', 4.90, null, 5, 160, false, true],
            ['Drinks', 'Soft Drink', 'Chilled canned soft drink.', 3.50, null, 2, 130, false, true],
            ['Drinks', 'Mango Smoothie', 'Creamy mango smoothie blended with chilled milk and yogurt.', 6.90, null, 7, 260, false, true],
            ['Desserts', 'Chocolate Lava Cake', 'Warm chocolate cake with a molten center.', 7.90, 8.90, 12, 520, true, true],
            ['Desserts', 'New York Cheesecake', 'Creamy cheesecake slice with berry topping.', 8.50, null, 5, 480, false, true],
            ['Desserts', 'Fudge Brownie', 'Dense chocolate brownie with a glossy fudge top.', 6.50, null, 5, 390, false, true],
            ['Desserts', 'Classic Tiramisu', 'Coffee-soaked layers with mascarpone cream and cocoa.', 8.90, null, 6, 430, false, false],
        ];

        MenuItem::whereNotIn('slug', collect($items)->map(fn (array $item) => Str::slug($item[1]))->all())
            ->update(['is_available' => false]);

        foreach ($items as $index => [$categoryName, $name, $description, $price, $compareAtPrice, $preparationTime, $calories, $isFeatured, $isAvailable]) {
            $category = Category::where('slug', Str::slug($categoryName))->first();

            MenuItem::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'restaurant_id' => $restaurant?->id,
                    'category_id' => $category?->id,
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'compare_at_price' => $compareAtPrice,
                    'image' => null,
                    'preparation_time' => $preparationTime,
                    'calories' => $calories,
                    'is_featured' => $isFeatured,
                    'sort_order' => $index + 1,
                    'is_available' => $isAvailable,
                ]
            );
        }
    }
}
