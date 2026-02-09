<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * إنشاء جدول المدفوعات
     * 
     * يربط الدفعة بالفاتورة والولي المالي (guardian_id)
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guardian_id')->constrained(); // الدافع الحقيقي
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'manual_transfer'])->default('cash');
            $table->string('transaction_reference')->nullable()->unique(); // رقم إيصال/مرجع تحويل
            $table->datetime('paid_at');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users'); // المحاسب/الموظف
            $table->timestamps();

            // فهرس للبحث السريع
            $table->index(['invoice_id', 'paid_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
