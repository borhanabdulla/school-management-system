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
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->foreignId('paid_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            $table->string('payout_method')->nullable()->after('paid_by');
            $table->string('payout_reference')->nullable()->after('payout_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['paid_by', 'payout_method', 'payout_reference']);
        });
    }
};
