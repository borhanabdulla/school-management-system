<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable'); // (student_id/guardian_id/user_id)
            $table->string('city');
            $table->string('district'); // الحي
            $table->string('street_name');
            $table->string('building_number')->nullable();
            $table->string('national_address_code')->nullable(); // العنوان الوطني
            $table->decimal('latitude', 10, 8)->nullable(); // للموقع الجغرافي (هام للباصات)
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_primary')->default(true); // هل هو سكنه الرئيسي؟
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
