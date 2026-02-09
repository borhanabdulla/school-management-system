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
                ->nullable() // nullable للتدرج في التحديث (backfill)
                ->after('name')
                ->constrained('academic_years')
                ->restrictOnDelete();
        });

        // Backfill: محاولة ملء العمود بناءً على تاريخ بداية الفترة
        // نفترض أن تاريخ البداية يقع ضمن نطاق سنة أكاديمية ما
        // هذا إجراء SQL مباشر للتسهيل
        $batches = \Illuminate\Support\Facades\DB::table('payroll_batches')->get();
        foreach ($batches as $batch) {
            $yearId = \Illuminate\Support\Facades\DB::table('academic_years')
                ->where('start_date', '<=', $batch->period_start)
                ->where('end_date', '>=', $batch->period_start)
                ->value('id');

            if ($yearId) {
                \Illuminate\Support\Facades\DB::table('payroll_batches')
                    ->where('id', $batch->id)
                    ->update(['academic_year_id' => $yearId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
