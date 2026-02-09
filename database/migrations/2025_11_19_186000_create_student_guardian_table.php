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
        Schema::create('student_guardian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained()->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'brother', 'uncle', 'other']);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('is_financial_sponsor')->default(false); // المسؤول المالي
            $table->boolean('lives_with')->default(true); // هل يسكن معه؟
            $table->boolean('has_portal_access')->default(true);
            $table->unique(['student_id', 'guardian_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_guardian');
    }
};
