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
        Schema::create('substitution_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained();
            $table->date('date');
            $table->foreignId('substitute_teacher_id')->constrained('teachers', 'id');
            $table->enum('status', ['assigned', 'completed'])->default('assigned');
            $table->timestamps();
            $table->unique(['timetable_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitution_classes');
    }
};
