<?php

namespace App\Domains\HR\Substitution\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Domains\Academic\Timetable\Models\Timetable;

class Substitution extends Model
{
    use HasModelLabels;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\SubstitutionFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'الإحلال';
    protected static string $modelPluralLabel = 'الإحلالات';

    protected $fillable = [
        'date',
        'timetable_id',
        'original_teacher_id',
        'substitute_teacher_id',
        'leave_request_id',
        'status',
        'is_paid',
        'notification_sent_at',
        'acceptance_status',
        'rejection_reason',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'is_paid' => 'boolean',
        'notification_sent_at' => 'datetime',
    ];

    // ==================== Relationships ====================

    public function timetable(): BelongsTo
    {
        return $this->belongsTo(Timetable::class);
    }

    public function originalTeacher(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\Teacher\Models\Teacher::class, 'original_teacher_id');
    }

    public function substituteTeacher(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\Teacher\Models\Teacher::class, 'substitute_teacher_id');
    }

    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\Leave\Models\LeaveRequest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForTeacher($query, int $teacherId)
    {
        return $query->where('substitute_teacher_id', $teacherId);
    }

    // ==================== Accessors ====================

    public function getIsAcceptedAttribute(): bool
    {
        return $this->acceptance_status === 'accepted';
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    // ==================== Methods ====================

    public function confirm(): bool
    {
        $this->update([
            'status' => 'confirmed',
            'acceptance_status' => 'accepted',
        ]);
        return true;
    }

    public function reject(?string $reason = null): bool
    {
        $this->update([
            'status' => 'rejected',
            'acceptance_status' => 'declined',
            'rejection_reason' => $reason,
        ]);
        return true;
    }

    public function markComplete(): bool
    {
        $this->update(['status' => 'completed']);
        return true;
    }
}
