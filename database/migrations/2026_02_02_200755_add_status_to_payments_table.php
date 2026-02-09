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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', array_column(\App\Domains\Finance\Enums\PaymentStatus::cases(), 'value'))
                ->default(\App\Domains\Finance\Enums\PaymentStatus::Posted->value)
                ->after('method');
            $table->string('cancel_reason')->nullable()->after('status');
            $table->foreignId('cancelled_by')->nullable()->after('cancel_reason')->constrained('users');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['status', 'cancel_reason', 'cancelled_by', 'cancelled_at']);
        });
    }
};
