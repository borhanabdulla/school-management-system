<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * تحديث جدول الحصص:
     * - إضافة يوم الأسبوع day_of_week (Flattening Strategy)
     * - إضافة نوع الحصة type
     * - إعادة تسمية name إلى label
     */
    public function up(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            // يوم الأسبوع (0=الأحد ... 6=السبت)
            // استراتيجية التسطيح: كل يوم له سجلاته الخاصة
            $table->tinyInteger('day_of_week')->default(0)->after('template_id');
            
            // نوع الحصة: academic, break, assembly, activity, prayer
            $table->string('type', 20)->default('academic')->after('order_index');
            
            // إعادة تسمية name إلى label للوضوح
            $table->renameColumn('name', 'label');
            
            // فهرس مركب للاستعلام السريع
            $table->index(['template_id', 'day_of_week', 'order_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('time_slots', function (Blueprint $table) {
            $table->dropIndex(['template_id', 'day_of_week', 'order_index']);
            $table->renameColumn('label', 'name');
            $table->dropColumn(['day_of_week', 'type']);
        });
    }
};
