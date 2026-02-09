<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * تحديث جدول قوالب الدوام:
     * - إضافة أيام العمل working_days
     * - إضافة الحالة status
     * - إضافة ربط بالمرحلة الدراسية (اختياري)
     */
    public function up(): void
    {
        Schema::table('timetable_templates', function (Blueprint $table) {
            // الأيام العاملة (مصفوفة JSON: ["sun","mon","tue","wed","thu"])
            $table->json('working_days')->default('["sun","mon","tue","wed","thu"]')->after('description');
            
            // حالة القالب: draft, active, archived
            $table->string('status', 20)->default('draft')->after('is_default');
            
            // ربط اختياري بالمرحلة الدراسية (لتخصيص قوالب مختلفة لكل مرحلة)
            $table->foreignId('educational_stage_id')
                  ->nullable()
                  ->after('academic_year_id')
                  ->constrained()
                  ->nullOnDelete();
            
            // فهرس للبحث السريع
            $table->index(['academic_year_id', 'status']);
            $table->index('educational_stage_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable_templates', function (Blueprint $table) {
            $table->dropIndex('timetable_templates_academic_year_id_status_index');
            $table->dropIndex('timetable_templates_educational_stage_id_index');
            $table->dropForeign(['educational_stage_id']);
            $table->dropColumn(['working_days', 'status', 'educational_stage_id']);
        });
    }
};
