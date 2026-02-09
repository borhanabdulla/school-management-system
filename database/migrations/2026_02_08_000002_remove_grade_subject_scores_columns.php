<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grade_subjects', function (Blueprint $table) {
            if (Schema::hasColumn('grade_subjects', 'max_grade')) {
                $table->dropColumn('max_grade');
            }
            if (Schema::hasColumn('grade_subjects', 'pass_grade')) {
                $table->dropColumn('pass_grade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grade_subjects', function (Blueprint $table) {
            if (! Schema::hasColumn('grade_subjects', 'max_grade')) {
                $table->integer('max_grade')->default(100);
            }
            if (! Schema::hasColumn('grade_subjects', 'pass_grade')) {
                $table->integer('pass_grade')->default(50);
            }
        });
    }
};
