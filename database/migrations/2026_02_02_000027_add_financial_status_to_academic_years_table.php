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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->enum('financial_status', ['open', 'closed'])->default('open')->after('status');
            $table->timestamp('financial_closed_at')->nullable()->after('financial_status');
            $table->foreignId('financial_closed_by')->nullable()->constrained('users')->nullOnDelete()->after('financial_closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropForeign(['financial_closed_by']);
            $table->dropColumn(['financial_status', 'financial_closed_at', 'financial_closed_by']);
        });
    }
};
