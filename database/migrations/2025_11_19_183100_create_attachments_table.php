<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable'); // يربط بالطالب أو الولي
            $table->string('document_type'); // birth_certificate, passport, id_card, medical_report
            $table->string('file_path');
            $table->string('mime_type'); // pdf, jpg
            $table->string('original_name');
            $table->date('expiry_date')->nullable(); // تاريخ انتهاء الوثيقة (للتنبيهات)
            $table->boolean('is_verified')->default(false); // هل طابقها الموظف بالأصل؟
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
