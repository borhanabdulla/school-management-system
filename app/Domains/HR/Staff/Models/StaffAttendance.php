<?php

namespace App\Domains\HR\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class StaffAttendance extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return \Database\Factories\StaffAttendanceFactory::new();
    }

    protected $table = 'staff_attendance';

    protected $fillable = [
        'staff_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'delay_minutes',
        'early_leave_minutes',
        'source',
        'recorded_by',
        'remarks',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'string',
        'check_out' => 'string',
        'status' => \App\Domains\HR\Staff\Enums\StaffAttendanceStatus::class,
    ];

    /**
     * الموظف صاحب السجل
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * من سجّل الحضور
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }

    /**
     * Alias for recorded_by
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }



    /**
     * وقت الدخول مُنسّق
     */
    public function getCheckInDisplayAttribute(): ?string
    {
        return $this->check_in
            ? Carbon::parse($this->check_in)->format('h:i A')
            : null;
    }

    /**
     * وقت الخروج مُنسّق
     */
    public function getCheckOutDisplayAttribute(): ?string
    {
        return $this->check_out
            ? Carbon::parse($this->check_out)->format('h:i A')
            : null;
    }

    /**
     * Scope: لتاريخ معين
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('date', Carbon::parse($date)->toDateString());
    }

    /**
     * Scope: لوردية معينة
     */
    public function scopeForShift($query, int $shiftId)
    {
        return $query->whereHas('staff', fn($q) => $q->where('work_shift_id', $shiftId));
    }

    /**
     * Scope: الغياب فقط
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', \App\Domains\HR\Staff\Enums\StaffAttendanceStatus::Absent);
    }

    /**
     * Scope: التأخيرات فقط
     */
    public function scopeLate($query)
    {
        return $query->where('status', \App\Domains\HR\Staff\Enums\StaffAttendanceStatus::Late);
    }
}
