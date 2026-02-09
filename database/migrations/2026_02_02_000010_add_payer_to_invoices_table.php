<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * إضافة حقول تثبيت الدافع على جدول الفواتير
     * 
     * PR1: يثبت الولي المالي وقت إنشاء الفاتورة
     * لضمان عدم تأثر السجلات القديمة بتغيير is_financial_sponsor
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // الدافع المثبت (الولي المالي وقت الإنشاء)
            $table->foreignId('payer_guardian_id')
                ->nullable()
                ->after('academic_year_id')
                ->constrained('guardians')
                ->nullOnDelete();

            // وقت تثبيت الدافع
            $table->datetime('payer_set_at')
                ->nullable()
                ->after('payer_guardian_id');

            // من ثبّت الدافع (للتدقيق)
            $table->foreignId('payer_set_by')
                ->nullable()
                ->after('payer_set_at')
                ->constrained('users')
                ->nullOnDelete();

            // فهرس مركب لكشف حساب ولي الأمر
            $table->index(['payer_guardian_id', 'academic_year_id'], 'idx_invoices_payer_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_payer_year');
            $table->dropConstrainedForeignId('payer_set_by');
            $table->dropColumn('payer_set_at');
            $table->dropConstrainedForeignId('payer_guardian_id');
        });
    }
};
