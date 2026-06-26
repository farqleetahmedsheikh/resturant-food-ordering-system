<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table): void {
            if (! Schema::hasColumn('restaurants', 'formatted_address')) {
                $table->text('formatted_address')->nullable()->after('address');
            }

            if (! Schema::hasColumn('restaurants', 'timezone')) {
                $table->string('timezone')->nullable()->after('closing_time');
            }

            if (! Schema::hasColumn('restaurants', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('restaurants', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address');
            }

            if (! Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }
        });

        if (Schema::hasColumn('restaurants', 'slug')) {
            try {
                Schema::table('restaurants', function (Blueprint $table): void {
                    $table->dropUnique('restaurants_slug_unique');
                });
            } catch (Throwable) {
                //
            }

            Schema::table('restaurants', function (Blueprint $table): void {
                $table->dropColumn('slug');
            });
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach (['delivery_longitude', 'delivery_latitude'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('restaurants', function (Blueprint $table): void {
            foreach (['longitude', 'latitude', 'timezone', 'formatted_address'] as $column) {
                if (Schema::hasColumn('restaurants', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (! Schema::hasColumn('restaurants', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
        });
    }
};
