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
        Schema::create('payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);

            // Calculation Settings
            $table->enum('month_days_type', ['30_fixed', 'actual'])->default('30_fixed');
            $table->decimal('substitution_rate', 8, 2)->default(75.00); // Per class

            // Penalty Settings (JSON)
            // Structure: [{"min": 0, "max": 15, "deduction_factor": 0}, {"min": 16, "max": 30, "deduction_factor": 0.25}]
            $table->json('lateness_brackets')->nullable();

            // Absence Settings
            $table->decimal('absence_deduction_factor', 4, 2)->default(1.00); // 1.0 = deduct 1 day per 1 day absence

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_policies');
    }
};
