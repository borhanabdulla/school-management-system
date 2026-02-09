<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_applications', function (Blueprint $table) {
            $table->id();
            
            // البند المخصوم عليه
            $table->foreignId('invoice_item_id')
                ->constrained('invoice_items')
                ->cascadeOnDelete(); // إذا حذف البند يحذف تطبيق الخصم

            // الخصم المطبق
            $table->foreignId('discount_id')
                ->constrained('discounts')
                ->restrictOnDelete(); // يمنع حذف الخصم إذا كان مستخدماً في تدقيق مالي

            // قيمة الخصم الفعلية
            $table->decimal('applied_amount', 10, 2);

            // التدقيق
            $table->foreignId('applied_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
                
            $table->datetime('applied_at');
            $table->string('reason')->nullable();
            
            $table->timestamps();

            // منع تكرار الخصم لنفس البند (تطبيق سياسة Single Discount مبدئياً)
            // يمكن استبداله بـ composite unique index على (invoice_item_id, discount_id) 
            // لكن لسياسة Single Discount:
            // $table->unique('invoice_item_id'); <-- هذا حازم جداً، ربما في المستقبل نحتاج أكثر من خصم
            // لذا سأكتفي بالمنطق في الكود حالياً، أو أضع index للبحث السريع
            $table->index('invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_applications');
    }
};
