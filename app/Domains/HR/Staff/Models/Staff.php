<?php

namespace App\Domains\HR\Staff\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\HR\Staff\Enums\StaffStatus;

class Staff extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;

    protected static function newFactory()
    {
        return \Database\Factories\StaffFactory::new();
    }

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'موظف';
    protected static string $modelPluralLabel = 'موظفين';

    /**
     * العلاقات المحمية من الحذف
     */
    protected array $protectedRelations = [
        'staffAttendances' => 'سجلات حضور',
        'contracts' => 'عقود',
        'payrollRecords' => 'سجلات رواتب',
        'loans' => 'سلف',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['staff', 'hr'];

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'employee_number',
        'phone',
        'joining_date',
        'work_shift_id',
        'employment_type',
        'job_title',
        'status',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'status' => \App\Domains\HR\Staff\Enums\StaffStatus::class,
    ];
    /**
     * الاسم الكامل للموظف
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * علاقة بالحساب (اختياري، قد يكون الموظف ليس له حساب دخول)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Shared\Models\User::class);
    }

    /**
     * هل هذا الموظف معلم؟
     */
    public function teacher(): HasOne
    {
        return $this->hasOne(\App\Domains\HR\Teacher\Models\Teacher::class);
    }

    /**
     * الوردية المرتبطة بالموظف
     */
    public function workShift(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\HR\WorkShift\Models\WorkShift::class);
    }

    /**
     * سجلات الحضور
     */
    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Alias for attendance relation used across legacy paths.
     */
    public function attendances(): HasMany
    {
        return $this->staffAttendances();
    }

    /**
     * أرصدة الإجازات
     */
    public function staffLeaveBalances(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Leave\Models\StaffLeaveBalance::class);
    }

    /**
     * Alias for leave balances (used across HR leave services).
     */
    public function leaveBalances(): HasMany
    {
        return $this->staffLeaveBalances();
    }

    /**
     * طلبات الإجازة
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Leave\Models\LeaveRequest::class);
    }

    /**
     * هل هذا الموظف معلم؟ (Helper)
     */
    public function isTeacher(): bool
    {
        return $this->teacher()->exists();
    }

    /**
     * نوع التوظيف بالعربية
     */
    public function getEmploymentTypeNameAttribute(): string
    {
        return __('hr.employment_types.' . $this->employment_type) ?? $this->employment_type;
    }

    /**
     * Scope: الموظفون النشطون
     */
    public function scopeActive($query)
    {
        return $query->where('status', StaffStatus::Active);
    }


    /**
     * Scope: الموظفون القابلون للإسناد (نشط أو في إجازة)
     */
    public function scopeEmployed($query)
    {
        return $query->whereIn('status', [StaffStatus::Active, StaffStatus::OnLeave]);
    }

    /**
     * Scope: الموظفون النشطون (لديهم وردية)
     */
    public function scopeWithShift($query)
    {
        return $query->whereNotNull('work_shift_id');
    }

    /**
     * Scope: حسب الوردية
     */
    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('work_shift_id', $shiftId);
    }

    /**
     * عقود الموظف
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Payroll\Models\Contract::class);
    }

    /**
     * العقد النشط الحالي
     */
    public function activeContract()
    {
        return $this->hasOne(\App\Domains\HR\Payroll\Models\Contract::class)
            // استخدام Enum للعقود
            ->where('status', \App\Domains\HR\Payroll\Enums\ContractStatus::Active)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest('start_date');
    }

    /**
     * سجلات الرواتب
     */
    public function payrollRecords(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Payroll\Models\PayrollRecord::class);
    }

    /**
     * السلف والقروض
     */
    public function loans(): HasMany
    {
        return $this->hasMany(\App\Domains\HR\Payroll\Models\Loan::class);
    }
}
