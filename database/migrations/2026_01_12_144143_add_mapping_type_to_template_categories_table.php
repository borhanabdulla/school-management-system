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
        Schema::table('template_categories', function (Blueprint $table) {
            // نوع مصدر الدرجة: يدوي، متوسط شهري، حضور، واجبات
            $table->enum('mapping_type', [
                'manual',           // إدخال يدوي
                'monthly_average',  // متوسط MonthlyGrade
                'attendance',       // من سجل الحضور
                'homework',         // من HomeworkSubmission
            ])->default('manual')->after('order');

            // هل هذه الفئة للقراءة فقط (محسوبة برمجياً)
            $table->boolean('is_readonly')->default(false)->after('mapping_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_categories', function (Blueprint $table) {
            $table->dropColumn(['mapping_type', 'is_readonly']);
        });
    }
};
