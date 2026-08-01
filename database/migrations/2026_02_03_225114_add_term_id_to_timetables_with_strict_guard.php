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
        // 1. Add term_id column (Nullable)
        Schema::table('timetables', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->after('class_section_id')->constrained()->cascadeOnDelete();
            $table->index(['class_section_id', 'term_id']);
        });

        // 2. Update Constraints
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropUnique('unique_section_slot');
            $table->unique(['class_section_id', 'time_slot_id', 'term_id'], 'unique_section_slot_term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropUnique('unique_section_slot_term');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropIndex('timetables_class_section_id_term_id_index');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropForeign(['term_id']);
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropColumn('term_id');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->unique(['class_section_id', 'time_slot_id'], 'unique_section_slot');
        });
    }
};
