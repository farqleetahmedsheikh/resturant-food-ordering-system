<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'last_known_latitude')) {
                $table->decimal('last_known_latitude', 10, 7)->nullable()->after('phone');
            }

            if (! Schema::hasColumn('users', 'last_known_longitude')) {
                $table->decimal('last_known_longitude', 10, 7)->nullable()->after('last_known_latitude');
            }

            if (! Schema::hasColumn('users', 'last_location_updated_at')) {
                $table->timestamp('last_location_updated_at')->nullable()->after('last_known_longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            foreach (['last_location_updated_at', 'last_known_longitude', 'last_known_latitude'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
