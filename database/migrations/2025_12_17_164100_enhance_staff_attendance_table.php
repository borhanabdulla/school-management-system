<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * تحسين جدول حضور الموظفين بإضافة التأخير والمصدر والتدقيق
     */
    public function up(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            // دقائق التأخير (محسوبة تلقائياً)
            $table->unsignedSmallInteger('delay_minutes')
                  ->default(0)
                  ->after('status');
            
            // دقائق الخروج المبكر
            $table->unsignedSmallInteger('early_leave_minutes')
                  ->default(0)
                  ->after('delay_minutes');
            
            // مصدر التسجيل
            $table->enum('source', ['manual', 'auto', 'correction'])
                  ->default('manual')
                  ->after('early_leave_minutes');
            
            // من سجّل الحضور
            $table->foreignId('recorded_by')
                  ->nullable()
                  ->after('source')
                  ->constrained('users')
                  ->nullOnDelete();
            
            // ملاحظات
            $table->text('remarks')
                  ->nullable()
                  ->after('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dropForeign(['recorded_by']);
            $table->dropColumn([
                'delay_minutes',
                'early_leave_minutes',
                'source',
                'recorded_by',
                'remarks'
            ]);
        });
    }
};
