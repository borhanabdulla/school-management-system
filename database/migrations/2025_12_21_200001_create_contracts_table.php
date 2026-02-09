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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            
            // التواريخ - مصدر الحقيقة
            $table->date('start_date');
            $table->date('end_date');
            
            // الراتب والبدلات
            $table->decimal('basic_salary', 10, 2);
            $table->json('allowances')->nullable(); // [{name: 'سكن', amount: 500}, ...]
            
            // الحالة والقفل
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            
            // الربط الأكاديمي (اختياري - للتقارير فقط)
            $table->foreignId('academic_year_id')->nullable()->constrained()->nullOnDelete();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // فهرسة للبحث السريع
            $table->index(['staff_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
