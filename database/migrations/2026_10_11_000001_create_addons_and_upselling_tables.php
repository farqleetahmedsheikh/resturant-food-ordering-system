<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type')->default('customization')->index();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_available')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('archived_at')->nullable()->index();
            $table->timestamps();

            $table->index(['restaurant_id', 'type']);
            $table->index(['restaurant_id', 'is_active', 'is_available']);
        });

        Schema::create('addon_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('selection_type')->default('multi')->index();
            $table->boolean('is_required')->default(false)->index();
            $table->unsignedInteger('min_selections')->default(0);
            $table->unsignedInteger('max_selections')->nullable();
            $table->unsignedInteger('quantity_limit')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });

        Schema::create('addon_group_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('addon_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['addon_group_id', 'addon_id']);
            $table->index(['addon_group_id', 'sort_order']);
        });

        Schema::create('product_addon_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['menu_item_id', 'addon_group_id']);
        });

        Schema::create('category_addon_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('addon_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['category_id', 'addon_group_id']);
        });

        Schema::create('global_addon_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('addon_group_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['restaurant_id', 'addon_group_id']);
        });

        Schema::create('upsell_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('trigger_type')->default('global')->index();
            $table->json('trigger_config')->nullable();
            $table->string('placement')->default('cart')->index();
            $table->unsignedInteger('priority')->default(0);
            $table->unsignedInteger('max_recommendations')->nullable();
            $table->boolean('allow_duplicates')->default(false);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['restaurant_id', 'placement', 'is_active']);
            $table->index(['trigger_type', 'priority']);
        });

        Schema::create('upsell_rule_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('upsell_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['upsell_rule_id', 'menu_item_id']);
            $table->index(['upsell_rule_id', 'sort_order']);
        });

        $this->seedReusableGroupsFromExistingMenuItemAddons();
    }

    public function down(): void
    {
        Schema::dropIfExists('upsell_rule_products');
        Schema::dropIfExists('upsell_rules');
        Schema::dropIfExists('global_addon_groups');
        Schema::dropIfExists('category_addon_groups');
        Schema::dropIfExists('product_addon_groups');
        Schema::dropIfExists('addon_group_items');
        Schema::dropIfExists('addon_groups');
        Schema::dropIfExists('addons');
    }

    private function seedReusableGroupsFromExistingMenuItemAddons(): void
    {
        if (! Schema::hasTable('menu_item_addons')) {
            return;
        }

        DB::table('menu_items')
            ->join('menu_item_addons', 'menu_items.id', '=', 'menu_item_addons.menu_item_id')
            ->select([
                'menu_items.id as menu_item_id',
                'menu_items.restaurant_id',
                'menu_item_addons.name',
                'menu_item_addons.type',
                'menu_item_addons.price',
                'menu_item_addons.sort_order',
                'menu_item_addons.is_active',
            ])
            ->orderBy('menu_items.id')
            ->chunk(100, function ($rows): void {
                foreach ($rows as $row) {
                    $addonId = DB::table('addons')->insertGetId([
                        'restaurant_id' => $row->restaurant_id,
                        'name' => $row->name,
                        'type' => 'customization',
                        'price' => $row->price,
                        'sort_order' => $row->sort_order,
                        'is_available' => (bool) $row->is_active,
                        'is_active' => (bool) $row->is_active,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $groupId = DB::table('addon_groups')->insertGetId([
                        'restaurant_id' => $row->restaurant_id,
                        'name' => 'Extras',
                        'description' => 'Reusable extras migrated from product options.',
                        'selection_type' => 'multi',
                        'is_required' => false,
                        'min_selections' => 0,
                        'max_selections' => null,
                        'quantity_limit' => 1,
                        'sort_order' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('addon_group_items')->insert([
                        'addon_group_id' => $groupId,
                        'addon_id' => $addonId,
                        'sort_order' => $row->sort_order,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    DB::table('product_addon_groups')->insert([
                        'menu_item_id' => $row->menu_item_id,
                        'addon_group_id' => $groupId,
                        'sort_order' => 0,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};
