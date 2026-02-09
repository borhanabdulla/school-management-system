<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('nationality_id')->nullable()->constrained('countries');
            $table->string('national_id')->unique()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->unique();
            // $table->string('email')->nullable();
            $table->string('employer')->nullable(); // جهة العمل (هام للمالية)
            $table->string('work_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
