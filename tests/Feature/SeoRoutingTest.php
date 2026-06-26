<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_restaurant_slug_urls_redirect_to_simple_public_routes(): void
    {
        $this->get('/restaurant/arcade-kebab-house')
            ->assertMovedPermanently()
            ->assertRedirect(route('home'));

        $this->get('/restaurant/arcade-kebab-house/menu')
            ->assertMovedPermanently()
            ->assertRedirect(route('menu'));
    }

    public function test_sitemap_contains_only_public_indexable_urls(): void
    {
        $restaurant = Restaurant::create([
            'name' => 'Arcade Kebab House',
            'timezone' => 'Australia/Sydney',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 0,
            'is_open' => true,
        ]);

        $category = Category::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Kebabs',
            'slug' => 'kebabs',
            'is_active' => true,
        ]);

        $item = MenuItem::create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
            'name' => 'Chicken Kebab',
            'slug' => 'chicken-kebab',
            'price' => 14.50,
            'is_available' => true,
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('content-type', 'application/xml')
            ->assertSee(route('home'), false)
            ->assertSee(route('menu'), false)
            ->assertSee(route('contact'), false)
            ->assertSee(route('menu.show', $item), false)
            ->assertDontSee('/cart', false)
            ->assertDontSee('/checkout', false)
            ->assertDontSee('/admin', false)
            ->assertDontSee('/customer', false)
            ->assertDontSee('/rider', false);
    }
}
