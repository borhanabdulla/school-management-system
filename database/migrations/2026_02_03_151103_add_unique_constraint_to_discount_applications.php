<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PR-B3: إضافة قيد عدم التكرار على تطبيقات الخصم
 * 
 * يمنع تطبيق أكثر من خصم على نفس البند (Single Discount Policy)
 * كما تم الاتفاق عليه في الخطة
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('discount_applications', function (Blueprint $table) {
            // حذف الـ index العادي القديم إن وجد لتحويله لـ unique
            $table->dropIndex(['invoice_item_id']);

            // إضافة unique index
            $table->unique('invoice_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('discount_applications', function (Blueprint $table) {
            $table->dropUnique(['invoice_item_id']);
            $table->index('invoice_item_id');
        });
    }
};
