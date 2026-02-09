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
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            // تاريخ القيد (من المصدر، ليس now())
            $table->timestamp('entry_date');

            // الاتجاه: دخل أم خرج
            $table->enum('direction', ['in', 'out']);

            // المبلغ (دقة عالية للمحاسبة)
            $table->decimal('amount', 14, 2);

            // العملة (اختياري، افتراضي SAR)
            $table->string('currency', 3)->default('SAR');

            // التصنيف
            $table->enum('category', [
                'student_payment',
                'payroll_payout',
                'expense',
                'adjustment'
            ]);

            // المصدر (Polymorphic)
            $table->nullableMorphs('source');

            // الحالة
            $table->enum('status', ['posted', 'cancelled'])->default('posted');

            // من أنشأ القيد
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // بيانات الإلغاء (في حال الإلغاء)
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cancel_reason')->nullable();

            // ملاحظات
            $table->text('notes')->nullable();

            // مفتاح خارجي فريد لمنع التكرار (Idempotency)
            // مثال: "payment:123" أو "payroll_batch:45"
            $table->string('external_key')->unique();

            $table->timestamps();

            // فهارس للتقارير السريعة
            $table->index('entry_date');
            $table->index(['direction', 'status']);
            $table->index(['category', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
