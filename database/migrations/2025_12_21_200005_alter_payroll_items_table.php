<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ترقية جدول payroll_items لدعم النظام الجديد
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            // تغيير اسم العمود من payroll_id إلى payroll_record_id
            // (هذا يتطلب حذف البيانات القديمة أو تحويلها)
            
            // إضافة الأعمدة الجديدة إذا لم تكن موجودة
            if (!Schema::hasColumn('payroll_items', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('payroll_items', 'description')) {
                $table->string('description')->nullable()->after('category');
            }
            
            if (!Schema::hasColumn('payroll_items', 'is_manual_override')) {
                $table->boolean('is_manual_override')->default(false)->after('amount');
            }
            
            if (!Schema::hasColumn('payroll_items', 'original_amount')) {
                $table->decimal('original_amount', 10, 2)->nullable()->after('is_manual_override');
            }
            
            // تحديث نوع العمود type ليشمل القيم الجديدة
            // ملاحظة: SQLite لا يدعم تعديل ENUM مباشرة
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'is_manual_override', 'original_amount']);
        });
    }
};
