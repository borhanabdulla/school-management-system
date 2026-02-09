<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * توسيع حالات الفاتورة لتطابق الكود
     * 
     * SQLite لا يدعم ENUM مباشرة، لذا نستخدم إعادة إنشاء الجدول
     * القيم الجديدة: unpaid, partially_paid, paid, cancelled, overdue
     */
    public function up(): void
    {
        // SQLite: إنشاء جدول مؤقت ونقل البيانات
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status_new')->default('unpaid')->after('status');
        });

        // نقل البيانات مع تحويل partial إلى partially_paid
        DB::table('invoices')->update([
            'status_new' => DB::raw("CASE 
                WHEN status = 'partial' THEN 'partially_paid'
                ELSE status 
            END")
        ]);

        // حذف العمود القديم وإعادة تسمية الجديد
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status_old')->default('unpaid')->after('status');
        });

        // تحويل partially_paid إلى partial
        DB::table('invoices')->update([
            'status_old' => DB::raw("CASE 
                WHEN status = 'partially_paid' THEN 'partial'
                ELSE status 
            END")
        ]);

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};
