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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained(); // السنة المنتهية
            $table->foreignId('annual_result_id')->nullable()->constrained()->nullOnDelete();

            // من وإلى
            $table->foreignId('from_grade_id')->constrained('grades');
            $table->foreignId('to_grade_id')->nullable()->constrained('grades');
            $table->foreignId('to_class_section_id')->nullable()->constrained('class_sections');

            // نوع الترحيل
            $table->enum('type', ['promoted', 'repeated', 'graduated', 'transferred', 'withdrawn']);

            // الربط المالي
            $table->boolean('has_financial_clearance')->default(false);
            $table->boolean('certificate_blocked')->default(false);

            // التراجع
            $table->boolean('is_reverted')->default(false);
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();
            $table->text('revert_reason')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamp('processed_at');
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
