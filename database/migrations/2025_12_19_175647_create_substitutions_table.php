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
        Schema::create('substitutions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('timetable_id')->constrained()->onDelete('cascade');
            $table->foreignId('original_teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('substitute_teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('leave_request_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'completed'])->default('pending');
            $table->boolean('is_paid')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->enum('acceptance_status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for common queries
            $table->index(['date', 'status']);
            $table->index(['substitute_teacher_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitutions');
    }
};

