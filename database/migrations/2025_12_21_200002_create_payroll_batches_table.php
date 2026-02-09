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
        Schema::create('payroll_batches', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // مثال: "مسير أكتوبر 2025"
            
            // الفترة المحاسبية
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            
            // سير العمل الصارم
            $table->enum('status', ['draft', 'frozen', 'approved', 'paid'])->default('draft');
            
            // تواريخ المراحل
            $table->timestamp('frozen_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // المسؤولون
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('frozen_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            
            // الإجماليات (للعرض السريع)
            $table->decimal('total_gross', 14, 2)->default(0);
            $table->decimal('total_deductions', 14, 2)->default(0);
            $table->decimal('total_net', 14, 2)->default(0);
            $table->unsignedInteger('employees_count')->default(0);
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // فهرسة
            $table->unique(['year', 'month']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_batches');
    }
};
