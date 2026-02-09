<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PR-D1: إضافة academic_year_id لجدول القيود المالية (Ledger)
 * 
 * الهدف: السماح بإصدار تقارير دفتر الأستاذ (GL) مفلترة حسب السنة الأكاديمية
 * بدلاً من الاعتماد فقط على تاريخ القيد.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreignId('academic_year_id')
                ->nullable() // nullable للقيود العامة التي لا تخص سنة بعينها (مثل رأس المال، قروض عامة)
                ->after('id')
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->index('academic_year_id');
        });
    }

    public function down(): void
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['academic_year_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropColumn('academic_year_id');
        });
    }
};
