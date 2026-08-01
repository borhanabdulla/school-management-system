<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('grading_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('term_id');
        });
    }
};
