<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (! Schema::hasColumn('orders', 'currency')) {
                $table->string('currency', 3)->default('AUD')->after('total');
            }

            if (! Schema::hasColumn('orders', 'stripe_checkout_session_id')) {
                $table->string('stripe_checkout_session_id')->nullable()->unique()->after('currency');
            }

            if (! Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->index()->after('stripe_checkout_session_id');
            }

            if (! Schema::hasColumn('orders', 'stripe_payment_status')) {
                $table->string('stripe_payment_status')->nullable()->after('stripe_payment_intent_id');
            }

            if (! Schema::hasColumn('orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('stripe_payment_status');
            }

            if (! Schema::hasColumn('orders', 'payment_failed_at')) {
                $table->timestamp('payment_failed_at')->nullable()->after('paid_at');
            }

            if (! Schema::hasColumn('orders', 'payment_cancelled_at')) {
                $table->timestamp('payment_cancelled_at')->nullable()->after('payment_failed_at');
            }

            if (! Schema::hasColumn('orders', 'payment_failure_reason')) {
                $table->text('payment_failure_reason')->nullable()->after('payment_cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            foreach ([
                'payment_failure_reason',
                'payment_cancelled_at',
                'payment_failed_at',
                'paid_at',
                'stripe_payment_status',
                'stripe_payment_intent_id',
                'stripe_checkout_session_id',
                'currency',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
