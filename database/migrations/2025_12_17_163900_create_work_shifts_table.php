<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * جدول الورديات (Work Shifts) لتحديد أوقات الدوام
     */
    public function up(): void
    {
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                          // "الدوام الصيفي - إداري"
            $table->enum('season', ['summer', 'winter', 'all'])->default('all'); // الموسم
            $table->time('start_time');                                       // 07:00
            $table->time('end_time');                                         // 14:00
            $table->unsignedTinyInteger('grace_period_minutes')->default(15); // فترة السماح
            $table->json('working_days');                                     // ["sun","mon","tue","wed","thu"]
            $table->boolean('works_on_holidays')->default(false);             // للحراس والأمن
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
