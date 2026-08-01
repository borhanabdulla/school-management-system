<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clean up legacy departments table and related columns.
 * 
 * Note: The departments table and department_id column on job_positions
 * were created outside of migrations (manually or via a deleted migration).
 * This migration safely cleans them up if they exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_positions') && Schema::hasColumn('job_positions', 'department_id')) {
            Schema::table('job_positions', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            });
        }

        Schema::dropIfExists('departments');
    }

    public function down(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        if (Schema::hasTable('job_positions')) {
            Schema::table('job_positions', function (Blueprint $table) {
                $table->foreignId('department_id')->nullable()->constrained();
            });
        }
    }
};
