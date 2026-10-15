<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_uses_live_categories_and_featured_menu_items(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Arcade Kebab House',
            'timezone' => 'Australia/Sydney',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 20,
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
            'name' => 'Chicken Kebab',
            'slug' => 'chicken-kebab',
            'price' => 14.50,
            'is_featured' => true,
            'is_available' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Authentic kebabs and meals,')
            ->assertSee('Explore our delicious menu')
            ->assertSee('Chicken Kebab')
            ->assertSee(route('menu', ['category' => 'kebabs']), false)
            ->assertSee(asset('images/home/kebab-hero.webp'), false)
            ->assertSee(asset('images/home/family-feast.webp'), false);
    }
}
