<?php

namespace App\Domains\HR\Leave\Models;

use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Leave\Enums\LeaveRequestStatus;
use App\Models\User;

class LeaveRequest extends Model
{
    use HasFactory, HasModelLabels;

    protected static function newFactory()
    {
        return \Database\Factories\LeaveRequestFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'طلب الإجازة';
    protected static string $modelPluralLabel = 'طلبات الإجازات';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'attachment',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => \App\Domains\HR\Leave\Enums\LeaveRequestStatus::class,
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', LeaveRequestStatus::Pending);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', LeaveRequestStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', LeaveRequestStatus::Rejected);
    }


}
