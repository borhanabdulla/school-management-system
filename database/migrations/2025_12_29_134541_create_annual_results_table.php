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
        Schema::create('annual_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('grade_id')->constrained();

            // التجميع من الترمين
            $table->decimal('term1_total', 6, 2)->default(0);
            $table->decimal('term1_max', 6, 2)->default(0);
            $table->decimal('term2_total', 6, 2)->default(0);
            $table->decimal('term2_max', 6, 2)->default(0);
            $table->decimal('annual_total', 6, 2)->default(0);
            $table->decimal('annual_max', 6, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);

            // المواد الراسب فيها
            $table->json('failed_subjects')->nullable();
            $table->integer('failed_count')->default(0);

            // القرار
            $table->enum('decision', ['pending', 'pass', 'conditional', 'fail'])->default('pending');
            $table->string('grade_label')->nullable();
            $table->integer('rank')->nullable();

            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annual_results');
    }
};
