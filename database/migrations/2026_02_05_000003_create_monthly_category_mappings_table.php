<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_year_id');
            $table->unsignedBigInteger('term_id');
            $table->unsignedBigInteger('grade_id');
            $table->unsignedBigInteger('subject_id');
            $table->string('category_key');
            $table->unsignedBigInteger('template_category_id');
            $table->string('aggregation_rule')->default('sum');
            $table->string('missing_months_policy')->default('ignore');
            $table->timestamps();

            $table->unique(
                ['academic_year_id', 'term_id', 'grade_id', 'subject_id', 'category_key'],
                'monthly_category_mapping_unique'
            );

            $table->index(['template_category_id'], 'monthly_category_mapping_template_idx');

            $table->foreign('academic_year_id')
                ->references('id')
                ->on('academic_years')
                ->cascadeOnDelete();
            $table->foreign('term_id')
                ->references('id')
                ->on('terms')
                ->cascadeOnDelete();
            $table->foreign('grade_id')
                ->references('id')
                ->on('grades')
                ->cascadeOnDelete();
            $table->foreign('subject_id')
                ->references('id')
                ->on('subjects')
                ->cascadeOnDelete();
            $table->foreign('template_category_id')
                ->references('id')
                ->on('template_categories')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_category_mappings');
    }
};
