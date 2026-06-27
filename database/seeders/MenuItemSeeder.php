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
            ['Kebabs', 'Chicken Doner Kebab', 'Fresh pita loaded with chicken doner, salad, pickles, and garlic sauce.', 13.90, 15.90, 12, 720, true, true],
            ['Kebabs', 'Lamb Doner Kebab', 'Tender lamb doner with lettuce, onion, tomato, and chilli garlic sauce.', 14.90, 16.90, 12, 780, true, true],
            ['Kebabs', 'Mixed Meat Kebab', 'Chicken and lamb doner wrapped with fresh salad and two house sauces.', 15.90, 17.90, 13, 850, true, true],
            ['Kebabs', 'Falafel Kebab', 'Crispy falafel, tabbouleh, pickles, lettuce, and tahini garlic sauce.', 12.90, null, 11, 620, false, true],
            ['Kebabs', 'Peri Chicken Kebab', 'Charred peri chicken strips with slaw, jalapenos, and creamy peri sauce.', 15.50, null, 14, 760, true, true],
            ['Kebab Plates', 'Chicken HSP Plate', 'Hot chips topped with chicken doner, cheese, garlic, BBQ, and chilli sauce.', 16.90, 18.90, 15, 980, true, true],
            ['Kebab Plates', 'Mixed Meat HSP Plate', 'Chicken and lamb over chips with melted cheese and signature sauces.', 18.50, 20.50, 16, 1080, true, true],
            ['Kebab Plates', 'Lamb Rice Plate', 'Lamb doner served with spiced rice, salad, hummus, and warm bread.', 19.90, null, 18, 930, false, true],
            ['Kebab Plates', 'Charcoal Chicken Plate', 'Charcoal chicken with rice, salad, chips, garlic dip, and bread.', 21.90, 24.90, 20, 1010, true, true],
            ['Kebab Plates', 'Falafel Rice Plate', 'Falafel, spiced rice, hummus, tabbouleh, pickles, and tahini sauce.', 16.50, null, 15, 760, false, true],
            ['Burgers', 'Arcade Beef Burger', 'Beef patty, cheddar, lettuce, tomato, pickles, onion, and Arcade burger sauce.', 13.90, 15.90, 14, 720, true, true],
            ['Burgers', 'Crispy Chicken Burger', 'Crispy chicken fillet with slaw, lettuce, cheese, and garlic mayo.', 13.50, 15.50, 13, 690, true, true],
            ['Burgers', 'Peri Chicken Burger', 'Grilled peri chicken, jalapenos, lettuce, tomato, and peri mayo.', 14.50, null, 14, 670, false, true],
            ['Burgers', 'Double Smash Burger', 'Two smashed beef patties, double cheese, pickles, and house sauce.', 17.90, 19.90, 15, 930, true, true],
            ['Sides & Dips', 'Loaded Kebab Fries', 'Crispy fries topped with doner meat, cheese, garlic, chilli, and BBQ sauce.', 11.90, 13.90, 10, 740, true, true],
            ['Sides & Dips', 'Regular Chips', 'Golden crispy chips seasoned with chicken salt.', 5.90, null, 7, 420, false, true],
            ['Sides & Dips', 'Garlic Bread', 'Toasted bread brushed with garlic butter and herbs.', 5.50, null, 6, 360, false, true],
            ['Sides & Dips', 'Chicken Wings', 'Crispy wings tossed in BBQ, peri, or chilli garlic sauce.', 10.90, 12.90, 12, 620, true, true],
            ['Sides & Dips', 'Garlic Sauce Dip', 'Creamy house garlic sauce, perfect with kebabs and chips.', 1.50, null, 2, 120, false, true],
            ['Sides & Dips', 'Chilli Sauce Dip', 'House chilli sauce with a bold heat finish.', 1.50, null, 2, 80, false, true],
            ['Drinks', 'Coca-Cola Can', 'Chilled Coca-Cola can.', 3.50, null, 2, 140, false, true],
            ['Drinks', 'Sprite Can', 'Chilled Sprite can.', 3.50, null, 2, 130, false, true],
            ['Drinks', 'Mango Lassi', 'Creamy mango yogurt drink served chilled.', 5.90, null, 4, 260, true, true],
            ['Drinks', 'Mint Lemonade', 'Fresh lemon, mint, and chilled soda over ice.', 4.90, null, 4, 150, true, true],
            ['Desserts', 'Baklava Box', 'Layered pastry with nuts, honey syrup, and pistachio.', 7.90, 8.90, 4, 430, true, true],
            ['Desserts', 'Chocolate Brownie', 'Fudgy chocolate brownie slice with a rich cocoa finish.', 6.90, null, 4, 390, false, true],
            ['Desserts', 'Kunafa Cup', 'Crispy kataifi, sweet cheese cream, syrup, and pistachio.', 8.90, null, 6, 520, true, true],
            ['Desserts', 'Rice Pudding', 'Creamy rice pudding with cinnamon and crushed pistachio.', 6.50, null, 5, 320, false, false],
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
