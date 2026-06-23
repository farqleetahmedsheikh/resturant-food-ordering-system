<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurants', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('logo');
            }

            if (! Schema::hasColumn('restaurants', 'short_description')) {
                $table->text('short_description')->nullable()->after('address');
            }

            if (! Schema::hasColumn('restaurants', 'opening_time')) {
                $table->time('opening_time')->nullable()->after('short_description');
            }

            if (! Schema::hasColumn('restaurants', 'closing_time')) {
                $table->time('closing_time')->nullable()->after('opening_time');
            }

            if (! Schema::hasColumn('restaurants', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('closing_time');
            }

            if (! Schema::hasColumn('restaurants', 'minimum_order_amount')) {
                $table->decimal('minimum_order_amount', 10, 2)->default(0)->after('delivery_fee');
            }

            if (! Schema::hasColumn('restaurants', 'is_open')) {
                $table->boolean('is_open')->default(true)->after('minimum_order_amount');
            }
        });

        Schema::table('categories', function (Blueprint $table): void {
            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }

            if (! Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('description');
            }

            if (! Schema::hasColumn('categories', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('image');
            }
        });

        Schema::table('menu_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('menu_items', 'compare_at_price')) {
                $table->decimal('compare_at_price', 10, 2)->nullable()->after('price');
            }

            if (! Schema::hasColumn('menu_items', 'preparation_time')) {
                $table->unsignedInteger('preparation_time')->nullable()->after('image');
            }

            if (! Schema::hasColumn('menu_items', 'calories')) {
                $table->unsignedInteger('calories')->nullable()->after('preparation_time');
            }

            if (! Schema::hasColumn('menu_items', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('calories');
            }

            if (! Schema::hasColumn('menu_items', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_featured');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table): void {
            foreach (['sort_order', 'is_featured', 'calories', 'preparation_time', 'compare_at_price'] as $column) {
                if (Schema::hasColumn('menu_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table): void {
            foreach (['sort_order', 'image', 'description'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('restaurants', function (Blueprint $table): void {
            foreach (['is_open', 'minimum_order_amount', 'delivery_fee', 'closing_time', 'opening_time', 'short_description', 'cover_image'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
