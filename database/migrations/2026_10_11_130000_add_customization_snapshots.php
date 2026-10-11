<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->json('customizations_snapshot')->nullable()->after('item_notes');
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->json('customizations_snapshot')->nullable()->after('addons_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropColumn('customizations_snapshot');
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropColumn('customizations_snapshot');
        });
    }
};
