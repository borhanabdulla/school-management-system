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
        Schema::table('payroll_items', function (Blueprint $table) {
            // 1. Drop old FK if exists
            // We use array syntax to let Laravel guess the name or just try to drop by column
            // Based on checking schema, the FK exists on 'payroll_record_id' referencing 'payrolls' table

            // First, we need to know the constraint name or rely on Laravel's naming convention.
            // Usually: payroll_items_payroll_record_id_foreign
            // But since we saw it pointing to payrolls, checking if we can drop by array.

            $table->dropForeign(['payroll_record_id']);

            // 2. Add correct FK referencing payroll_records
            $table->foreign('payroll_record_id')
                ->references('id')
                ->on('payroll_records')
                ->cascadeOnDelete();

            // 3. Add Index (if not exists - usually Laravel adds index with FK, but explicit is better here)
            // Checking DB earlier showed no index on this column.
            $table->index('payroll_record_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropForeign(['payroll_record_id']);
            $table->dropIndex(['payroll_record_id']);

            // Revert to old incorrect FK? No, better to leave it without FK or point correctly.
            // But strict rollback would be restoring the "wrong" state. 
            // Let's assume we want to revert to "pointing to payrolls" if that was the state.

            $table->foreign('payroll_record_id')
                ->references('id')
                ->on('payrolls')
                ->cascadeOnDelete();
        });
    }
};
