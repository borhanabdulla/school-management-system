<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PR-B1: إضافة عمود is_active للخصومات
 * 
 * يسمح بتعطيل الخصومات بدون حذفها (soft-disable)
 * مما يحل مشكلة "انفجار UI" عند وجود خصومات كثيرة
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('value');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('discounts', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
