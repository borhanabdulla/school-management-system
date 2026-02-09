<?php

namespace App\Domains\HR\Leave\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffLeaveBalance extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    protected static function newFactory()
    {
        return \Database\Factories\StaffLeaveBalanceFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'رصيد إجازة';
    protected static string $modelPluralLabel = 'أرصدة الإجازات';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'year',
        'remaining_days',
    ];

    /**
     * الموظف
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * نوع الإجازة
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
