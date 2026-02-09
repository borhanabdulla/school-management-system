<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->unsignedBigInteger('amended_by')->nullable()->after('graded_by_user_id');
            $table->timestamp('amended_at')->nullable()->after('amended_by');
            $table->text('amendment_reason')->nullable()->after('amended_at');
            $table->foreign('amended_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->unsignedBigInteger('amended_by')->nullable()->after('graded_by');
            $table->timestamp('amended_at')->nullable()->after('amended_by');
            $table->text('amendment_reason')->nullable()->after('amended_at');
            $table->foreign('amended_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropForeign(['amended_by']);
            $table->dropColumn(['amended_by', 'amended_at', 'amendment_reason']);
        });

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropForeign(['amended_by']);
            $table->dropColumn(['amended_by', 'amended_at', 'amendment_reason']);
        });
    }
};
