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
        if (Schema::hasTable('job_positions')) {
            Schema::table('job_positions', function (Blueprint $table) {
                // Check if column exists before dropping to avoid errors
                if (Schema::hasColumn('job_positions', 'department_id')) {
                    // Drop foreign key first if it exists (naming convention usually table_column_foreign)
                    $table->dropForeign(['department_id']);
                    $table->dropColumn('department_id');
                }
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
