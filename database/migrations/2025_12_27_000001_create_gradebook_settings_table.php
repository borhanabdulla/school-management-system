<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // إعدادات دفتر الدرجات العام
        Schema::create('gradebook_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            // التقسيمات الشهرية الافتراضية
            // [{name: "تحريري", max_score: 10, is_default: true}, ...]
            $table->json('monthly_categories')->nullable();

            // إعدادات خصم الحضور
            $table->integer('attendance_deduct_after')->default(3); // بعد كم غياب يبدأ الخصم
            $table->decimal('attendance_deduct_per_absence', 4, 2)->default(0.5); // كم درجة تُخصم لكل غياب
            $table->decimal('attendance_max_score', 4, 2)->default(5); // الدرجة العظمى للحضور

            // هل يُسمح للمعلم بإضافة أقسام مخصصة؟
            $table->boolean('allow_custom_categories')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gradebook_settings');
    }
};
