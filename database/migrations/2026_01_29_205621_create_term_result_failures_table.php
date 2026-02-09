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
        Schema::create('term_result_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_category_id')->constrained('template_categories')->cascadeOnDelete();
            $table->string('reason');
            $table->decimal('required_min', 6, 2);
            $table->decimal('actual_percentage', 6, 2);
            $table->timestamps();

            $table->unique(['term_result_id', 'template_category_id', 'reason']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('term_result_failures');
    }
};
