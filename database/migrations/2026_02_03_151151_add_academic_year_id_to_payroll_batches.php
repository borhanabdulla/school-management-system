<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PR-C1: ربط مسيرات الرواتب بالسنة الأكاديمية
 * 
 * يتيح تقارير دقيقة حسب سنة الاستحقاق (Accrual Year)
 * بدلاً من الاعتماد على تاريخ الدفع فقط
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable()
                ->after('name')
                ->constrained('academic_years')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
