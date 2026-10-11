<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_item_addons', function (Blueprint $table): void {
            $table->unsignedSmallInteger('quantity')->default(1)->after('menu_item_addon_id');
        });
    }

    public function down(): void
    {
        Schema::table('cart_item_addons', function (Blueprint $table): void {
            $table->dropColumn('quantity');
        });
    }
};
