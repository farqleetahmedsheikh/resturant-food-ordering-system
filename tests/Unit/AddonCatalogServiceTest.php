<?php

namespace Tests\Unit;

use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\AddonCatalogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddonCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_options_are_selected_without_an_extra_charge(): void
    {
        [$item, $group, $included, $paid] = $this->catalog();

        $result = app(AddonCatalogService::class)->validateGroupedSelections($item, []);

        $this->assertSame([$included->id => 1], $result['addon_quantities']);
        $this->assertSame(0.0, $result['addon_total']);
        $this->assertSame($included->name, $result['customization_snapshot'][0]['selected'][0]['name']);
        $this->assertEmpty($result['customization_snapshot'][0]['removed']);
    }

    public function test_customer_can_remove_a_default_option_and_snapshot_the_exclusion(): void
    {
        [$item, $group, $included] = $this->catalog();

        $result = app(AddonCatalogService::class)->validateGroupedSelections($item, [
            $group->id => [$included->id => 0],
        ]);

        $this->assertSame([], $result['addon_quantities']);
        $this->assertSame($included->name, $result['customization_snapshot'][0]['removed'][0]['name']);
    }

    public function test_paid_options_are_still_priced_while_defaults_remain_free(): void
    {
        [$item, $group, $included, $paid] = $this->catalog();

        $result = app(AddonCatalogService::class)->validateGroupedSelections($item, [
            $group->id => [
                $included->id => 1,
                $paid->id => 1,
            ],
        ]);

        $this->assertSame(3.5, $result['addon_total']);
        $selected = collect($result['customization_snapshot'][0]['selected'])->keyBy('id');
        $this->assertSame(0.0, $selected[$included->id]['price']);
        $this->assertSame(3.5, $selected[$paid->id]['price']);
    }

    /** @return array{MenuItem, AddonGroup, Addon, Addon} */
    private function catalog(): array
    {
        $restaurant = Restaurant::factory()->create();
        $category = Category::factory()->create(['restaurant_id' => $restaurant->id]);
        $item = MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
            'category_id' => $category->id,
        ]);
        $included = Addon::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Lettuce',
            'type' => 'customization',
            'price' => 2,
            'is_available' => true,
            'is_active' => true,
        ]);
        $paid = Addon::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Extra Meat',
            'type' => 'customization',
            'price' => 3.5,
            'is_available' => true,
            'is_active' => true,
        ]);
        $group = AddonGroup::create([
            'restaurant_id' => $restaurant->id,
            'name' => 'Included ingredients',
            'selection_type' => 'multi',
            'is_required' => false,
            'min_selections' => 0,
            'quantity_limit' => 1,
            'is_active' => true,
        ]);

        $group->addons()->attach([
            $included->id => ['is_default' => true, 'sort_order' => 0],
            $paid->id => ['is_default' => false, 'sort_order' => 1],
        ]);
        $item->addonGroups()->attach($group->id, ['is_active' => true, 'sort_order' => 0]);

        return [$item->fresh(), $group->fresh(), $included, $paid];
    }
}
