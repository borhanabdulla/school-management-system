<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->integer('installments_count'); // Number of months
            $table->decimal('monthly_installment', 10, 2);
            $table->string('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, completed
            $table->date('start_date'); // When deduction starts
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->date('due_date'); // The month this installment belongs to
            $table->string('status')->default('pending'); // pending, paid, skipped
            $table->foreignId('payroll_batch_id')->nullable()->constrained('payroll_batches'); // Linked when paid via payroll
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
        Schema::dropIfExists('loans');
    }
};
