<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('menu_item_sizes')) {
            Schema::create('menu_item_sizes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->decimal('price', 10, 2);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('menu_item_addons')) {
            Schema::create('menu_item_addons', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('type')->default('topping');
                $table->decimal('price', 10, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'size_name')) {
                $table->string('size_name')->nullable()->after('item_name');
            }

            if (! Schema::hasColumn('order_items', 'size_price')) {
                $table->decimal('size_price', 10, 2)->nullable()->after('size_name');
            }

            if (! Schema::hasColumn('order_items', 'addons_snapshot')) {
                $table->json('addons_snapshot')->nullable()->after('size_price');
            }

            if (! Schema::hasColumn('order_items', 'addons_total')) {
                $table->decimal('addons_total', 10, 2)->default(0)->after('addons_snapshot');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            foreach (['addons_total', 'addons_snapshot', 'size_price', 'size_name'] as $column) {
                if (Schema::hasColumn('order_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('menu_item_addons');
        Schema::dropIfExists('menu_item_sizes');
    }
};
