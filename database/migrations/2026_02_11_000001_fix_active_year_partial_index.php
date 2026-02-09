<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('academic_years')) {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS academic_years_one_active_year');
        DB::statement("
            CREATE UNIQUE INDEX academic_years_one_active_year
            ON academic_years (status)
            WHERE status = 'active'
        ");
    }

    public function down(): void
    {
        if (!Schema::hasTable('academic_years')) {
            return;
        }

        DB::statement('DROP INDEX IF EXISTS academic_years_one_active_year');
    }
};
