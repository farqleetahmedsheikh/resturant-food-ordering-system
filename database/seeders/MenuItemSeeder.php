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
        $restaurant = Restaurant::where('slug', 'freshbite-restaurant')->first();

        $items = [
            ['Pizza', 'Margherita Pizza', 'Classic tomato sauce, mozzarella, basil, and olive oil on a crisp base.', 1099.00, 1299.00, 18, 780, true, true],
            ['Pizza', 'Chicken Tikka Pizza', 'Spiced chicken tikka, onions, peppers, mozzarella, and house sauce.', 1399.00, 1599.00, 22, 940, true, true],
            ['Pizza', 'Pepperoni Feast Pizza', 'Loaded pepperoni, mozzarella, oregano, and rich tomato sauce.', 1499.00, 1699.00, 20, 1020, false, true],
            ['Pizza', 'Veggie Supreme Pizza', 'Mushrooms, olives, sweet corn, onions, capsicum, and mozzarella.', 1249.00, null, 19, 820, false, true],
            ['Burgers', 'Classic Beef Burger', 'Juicy beef patty, cheddar, lettuce, tomato, pickles, and burger sauce.', 899.00, 999.00, 15, 690, true, true],
            ['Burgers', 'Crispy Chicken Burger', 'Crunchy chicken fillet with slaw, lettuce, and creamy mayo.', 849.00, 949.00, 14, 640, true, true],
            ['Burgers', 'Smoky BBQ Burger', 'Beef patty with BBQ sauce, caramelized onions, and melted cheese.', 999.00, 1199.00, 16, 740, false, true],
            ['Burgers', 'Spicy Jalapeno Burger', 'Grilled chicken, jalapenos, pepper jack cheese, and spicy mayo.', 929.00, null, 15, 680, false, true],
            ['Pasta', 'Creamy Alfredo Pasta', 'Fettuccine tossed in parmesan cream sauce with grilled chicken.', 1199.00, 1399.00, 20, 760, true, true],
            ['Pasta', 'Spicy Arrabbiata Pasta', 'Penne in a chili tomato sauce with garlic, parsley, and parmesan.', 999.00, null, 18, 610, false, true],
            ['Pasta', 'Mushroom Penne Pasta', 'Penne with mushrooms, herbs, cream sauce, and parmesan.', 1099.00, 1299.00, 19, 700, false, true],
            ['Pasta', 'Chicken Lasagna', 'Layered pasta with chicken mince, tomato sauce, bechamel, and cheese.', 1299.00, null, 24, 880, true, true],
            ['Drinks', 'Mint Lemonade', 'Fresh lemon, mint, and chilled soda served over ice.', 299.00, null, 5, 140, true, true],
            ['Drinks', 'Iced Peach Tea', 'Cold brewed tea with peach syrup and lemon.', 349.00, null, 5, 160, false, true],
            ['Drinks', 'Soft Drink', 'Chilled canned soft drink.', 199.00, null, 2, 130, false, true],
            ['Drinks', 'Mango Smoothie', 'Creamy mango smoothie blended with chilled milk and yogurt.', 449.00, null, 7, 260, false, true],
            ['Desserts', 'Chocolate Lava Cake', 'Warm chocolate cake with a molten center.', 599.00, 699.00, 12, 520, true, true],
            ['Desserts', 'New York Cheesecake', 'Creamy cheesecake slice with berry topping.', 649.00, null, 5, 480, false, true],
            ['Desserts', 'Fudge Brownie', 'Dense chocolate brownie with a glossy fudge top.', 449.00, null, 5, 390, false, true],
            ['Desserts', 'Classic Tiramisu', 'Coffee-soaked layers with mascarpone cream and cocoa.', 699.00, null, 6, 430, false, false],
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
