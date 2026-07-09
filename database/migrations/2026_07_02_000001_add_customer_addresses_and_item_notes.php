<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customer_addresses')) {
            Schema::create('customer_addresses', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('label', 80)->nullable();
                $table->string('recipient_name', 191)->nullable();
                $table->string('phone', 40)->nullable();
                $table->text('address');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->text('delivery_notes')->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'is_default']);
                $table->index('created_at');
            });
        }

        Schema::table('cart_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('cart_items', 'item_notes')) {
                $table->text('item_notes')->nullable()->after('quantity');
            }
        });

        Schema::table('order_items', function (Blueprint $table): void {
            if (! Schema::hasColumn('order_items', 'item_notes')) {
                $table->text('item_notes')->nullable()->after('item_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            if (Schema::hasColumn('order_items', 'item_notes')) {
                $table->dropColumn('item_notes');
            }
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            if (Schema::hasColumn('cart_items', 'item_notes')) {
                $table->dropColumn('item_notes');
            }
        });

        Schema::dropIfExists('customer_addresses');
    }
};
