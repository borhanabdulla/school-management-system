<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('grading_templates', 'total_max_score')) {
                $table->decimal('total_max_score', 8, 2)->default(100);
            }
            if (!Schema::hasColumn('grading_templates', 'pass_score')) {
                $table->decimal('pass_score', 8, 2)->default(50);
            }
            if (!Schema::hasColumn('grading_templates', 'rounding_rule')) {
                $table->enum('rounding_rule', ['none', 'nearest_integer', 'up', 'down'])->default('none');
            }
            if (!Schema::hasColumn('grading_templates', 'rounding_precision')) {
                $table->integer('rounding_precision')->default(0);
            }
        });

        Schema::table('template_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('template_categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->constrained('template_categories')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('template_categories', 'max_raw_score')) {
                $table->decimal('max_raw_score', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('template_categories', 'calculation_type')) {
                $table->enum('calculation_type', ['sum', 'average', 'weighted_average'])->default('sum');
            }
            if (!Schema::hasColumn('template_categories', 'is_dynamic_weight')) {
                $table->boolean('is_dynamic_weight')->default(false);
            }
            if (!Schema::hasColumn('template_categories', 'is_locked')) {
                $table->boolean('is_locked')->default(false);
            }
            if (!Schema::hasColumn('template_categories', 'pass_required')) {
                $table->boolean('pass_required')->default(false);
            }
            if (!Schema::hasColumn('template_categories', 'pass_threshold')) {
                $table->decimal('pass_threshold', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('template_categories', 'order')) {
                $table->integer('order')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            $table->dropColumn(['total_max_score', 'pass_score', 'rounding_rule', 'rounding_precision']);
        });
    }
};
