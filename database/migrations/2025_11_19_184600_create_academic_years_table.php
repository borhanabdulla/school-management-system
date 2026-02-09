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
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            // Index: Unique لضمان عدم التكرار وتسريع البحث بالاسم
            $table->string('name')->unique(); 
            // Index: لأننا سنقوم بالترتيب والبحث بالنطاق الزمني كثيراً
            $table->date('start_date')->index(); 
            $table->date('end_date');
            // Index: حيوي جداً لأننا دائماً نبحث عن السنة 'active'
            $table->string('status')->index(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
