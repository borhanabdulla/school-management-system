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
        // 1. Add term_id (Nullable)
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('academic_year_id')->constrained()->cascadeOnDelete();
        });

        // 2. Backfill Logic (Date-based Mapping)
        $dates = \Illuminate\Support\Facades\DB::table('attendances')
            ->select('date')
            ->distinct()
            ->whereNull('term_id')
            ->pluck('date');

        foreach ($dates as $date) {
            // Find Term that encloses this date
            $termId = \Illuminate\Support\Facades\DB::table('terms')
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->value('id');

            if ($termId) {
                \Illuminate\Support\Facades\DB::table('attendances')
                    ->where('date', $date)
                    ->update(['term_id' => $termId]);
            }
        }

        // 3. Strict Guard (Abort if Orphans)
        $orphans = \Illuminate\Support\Facades\DB::table('attendances')->whereNull('term_id')->count();

        if ($orphans > 0) {
            // Rollback changes
            Schema::table('attendances', function (Blueprint $table) {
                $table->dropForeign(['term_id']);
                $table->dropColumn('term_id');
            });

            throw new \RuntimeException(
                "Migration Aborted: Found {$orphans} attendance records that could not be mapped to a Term. " .
                "Please manually fix dates or ensure Terms cover all attendance dates."
            );
        }

        // 4. Enforce Not Null
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
            $table->dropColumn('term_id');
        });
    }
};
