<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->index('users', ['role', 'is_active'], 'users_role_active_idx');
        $this->index('users', ['is_active'], 'users_active_idx');

        $this->index('orders', ['user_id', 'created_at'], 'orders_user_created_idx');
        $this->index('orders', ['rider_id', 'order_status'], 'orders_rider_status_idx');
        $this->index('orders', ['order_status', 'created_at'], 'orders_status_created_idx');
        $this->index('orders', ['payment_method', 'payment_status', 'order_status'], 'orders_payment_status_idx');

        $this->index('deliveries', ['rider_id', 'status'], 'deliveries_rider_status_idx');
        $this->index('deliveries', ['status', 'updated_at'], 'deliveries_status_updated_idx');

        $this->index('categories', ['is_active', 'sort_order', 'name'], 'categories_active_sort_idx');
        $this->index('categories', ['restaurant_id', 'is_active'], 'categories_restaurant_active_idx');

        $this->index('menu_items', ['category_id', 'is_available', 'sort_order', 'name'], 'menu_items_cat_available_sort_idx');
        $this->index('menu_items', ['is_featured', 'is_available', 'sort_order'], 'menu_items_featured_available_idx');
        $this->index('menu_items', ['restaurant_id', 'is_available'], 'menu_items_restaurant_available_idx');

        $this->index('order_items', ['menu_item_id', 'created_at'], 'order_items_menu_created_idx');

        if (Schema::hasTable('menu_item_sizes')) {
            $this->index('menu_item_sizes', ['menu_item_id', 'is_active', 'sort_order'], 'sizes_item_active_sort_idx');
        }

        if (Schema::hasTable('menu_item_addons')) {
            $this->index('menu_item_addons', ['menu_item_id', 'is_active', 'sort_order'], 'addons_item_active_sort_idx');
        }
    }

    public function down(): void
    {
        $this->dropIndex('menu_item_addons', 'addons_item_active_sort_idx');
        $this->dropIndex('menu_item_sizes', 'sizes_item_active_sort_idx');
        $this->dropIndex('order_items', 'order_items_menu_created_idx');
        $this->dropIndex('menu_items', 'menu_items_restaurant_available_idx');
        $this->dropIndex('menu_items', 'menu_items_featured_available_idx');
        $this->dropIndex('menu_items', 'menu_items_cat_available_sort_idx');
        $this->dropIndex('categories', 'categories_restaurant_active_idx');
        $this->dropIndex('categories', 'categories_active_sort_idx');
        $this->dropIndex('deliveries', 'deliveries_status_updated_idx');
        $this->dropIndex('deliveries', 'deliveries_rider_status_idx');
        $this->dropIndex('orders', 'orders_payment_status_idx');
        $this->dropIndex('orders', 'orders_status_created_idx');
        $this->dropIndex('orders', 'orders_rider_status_idx');
        $this->dropIndex('orders', 'orders_user_created_idx');
        $this->dropIndex('users', 'users_active_idx');
        $this->dropIndex('users', 'users_role_active_idx');
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function index(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasIndex($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name): void {
            $blueprint->dropIndex($name);
        });
    }
};
