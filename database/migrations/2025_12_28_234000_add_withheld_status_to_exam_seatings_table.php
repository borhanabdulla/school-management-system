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
        Schema::table('exam_seatings', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_seatings', 'is_withheld')) {
                $table->boolean('is_withheld')->default(false);
            }
            if (!Schema::hasColumn('exam_seatings', 'withhold_reason')) {
                $table->string('withhold_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_seatings', function (Blueprint $table) {
            $table->dropColumn(['is_withheld', 'withhold_reason']);
        });
    }
};
