<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grade_subjects', function (Blueprint $table) {
            $table->dropColumn('max_grade');
            $table->dropColumn('pass_grade');
        });
    }

    public function down(): void
    {
        Schema::table('grade_subjects', function (Blueprint $table) {
            $table->integer('max_grade')->default(100);
            $table->integer('pass_grade')->default(50);
        });
    }
};
