<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إضافة الوردية ونوع التوظيف للموظفين
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('work_shift_id')
                  ->nullable()
                  ->after('joining_date')
                  ->constrained('work_shifts')
                  ->nullOnDelete();
            
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor'])
                  ->default('full_time')
                  ->after('work_shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['work_shift_id']);
            $table->dropColumn(['work_shift_id', 'employment_type']);
        });
    }
};
