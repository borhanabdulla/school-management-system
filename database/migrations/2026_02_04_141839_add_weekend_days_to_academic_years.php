<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->json('weekend_days')->nullable()->after('status');
        });

        // Backfill with default Friday (5) and Saturday (6)
        // using raw update for SQLite compatibility (json_encode)
        \Illuminate\Support\Facades\DB::table('academic_years')
            ->update(['weekend_days' => '[5,6]']);

        Schema::table('academic_years', function (Blueprint $table) {
            $table->json('weekend_days')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropColumn('weekend_days');
        });
    }
};
