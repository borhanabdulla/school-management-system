<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Originally added missing columns to grading_templates and template_categories.
 * These columns are now included in the create migration (create_unified_grading_tables).
 * This migration is kept as a no-op for migration history consistency.
 */
return new class extends Migration {
    public function up(): void
    {
        // No-op: All columns (total_max_score, pass_score, rounding_rule, rounding_precision
        // on grading_templates; parent_id, max_raw_score, calculation_type, is_dynamic_weight,
        // is_locked, pass_required, pass_threshold, order on template_categories)
        // are now defined in 2025_12_26_143746_create_unified_grading_tables.php
    }

    public function down(): void
    {
        // No-op
    }
};
