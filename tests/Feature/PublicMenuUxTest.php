<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMenuUxTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_can_search_and_sort_items(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Arcade Kebab House',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 18,
            'is_open' => true,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Doner Kebab',
            'slug' => 'chicken-doner-kebab',
            'description' => 'Fresh chicken doner wrap.',
            'price' => 13.90,
            'is_available' => true,
        ]);

        MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Baklava Box',
            'slug' => 'baklava-box',
            'description' => 'Sweet pastry dessert.',
            'price' => 7.90,
            'is_available' => true,
        ]);

        $this->get(route('menu', ['q' => 'chicken', 'sort' => 'price_desc']))
            ->assertOk()
            ->assertSee('Chicken Doner Kebab')
            ->assertDontSee('Baklava Box')
            ->assertSee('Search: "chicken"', false)
            ->assertSee('Price: high to low');
    }
}
