<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            
            // بيانات العقد وقت التوليد (Snapshot)
            $table->decimal('basic_salary', 10, 2);
            
            // أيام العمل
            $table->unsignedTinyInteger('working_days')->default(0); // أيام العمل المستحقة
            $table->unsignedTinyInteger('days_worked')->default(0); // أيام العمل الفعلية
            $table->unsignedTinyInteger('days_absent')->default(0);
            $table->unsignedTinyInteger('days_late')->default(0);
            
            // الإجماليات
            $table->decimal('gross_earnings', 10, 2)->default(0); // إجمالي الاستحقاقات
            $table->decimal('total_deductions', 10, 2)->default(0); // إجمالي الاستقطاعات
            $table->decimal('net_payable', 10, 2)->default(0); // صافي المستحق
            
            // التعديلات اليدوية
            $table->decimal('manual_adjustment', 10, 2)->default(0);
            $table->text('adjustment_reason')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // فهرسة
            $table->unique(['payroll_batch_id', 'staff_id']);
            $table->index('contract_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};
