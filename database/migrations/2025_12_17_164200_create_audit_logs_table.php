<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول سجل التدقيق (Audit Log) - لا حذف ولا تعديل
     * يسجّل كل تغيير في أي جدول حساس
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // الجدول والسجل المتأثر
            $table->string('auditable_type');        // App\Models\StaffAttendance
            $table->unsignedBigInteger('auditable_id');
            
            // نوع العملية
            $table->enum('action', ['created', 'updated', 'deleted']);
            
            // القيم قبل وبعد (JSON)
            $table->json('old_values')->nullable();   // القيم القديمة
            $table->json('new_values')->nullable();   // القيم الجديدة
            
            // من قام بالتغيير
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            
            // السبب (اختياري - للتصحيحات)
            $table->string('reason')->nullable();
            
            // معلومات إضافية
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            // فهارس للبحث السريع
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
