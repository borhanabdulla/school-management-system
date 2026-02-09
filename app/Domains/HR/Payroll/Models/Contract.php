<?php

namespace App\Domains\HR\Payroll\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\HR\Payroll\Exceptions\ContractLockedException;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;

class Contract extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels;

    protected static function newFactory()
    {
        return \Database\Factories\ContractFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'العقد';
    protected static string $modelPluralLabel = 'العقود';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'payrollRecords' => 'سجلات الرواتب',
    ];

    protected $fillable = [
        'staff_id',
        'start_date',
        'end_date',
        'basic_salary',
        'bank_name',
        'iban',
        // 'allowances', // Removed in v2.2
        'status',
        'is_locked',
        'locked_at',
        'locked_by',
        'academic_year_id',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'basic_salary' => 'decimal:2',
        // 'allowances' => 'array', // Removed in v2.2
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'status' => \App\Domains\HR\Payroll\Enums\ContractStatus::class,
    ];

    // ==================== العلاقات ====================

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(\App\Domains\Academic\AcademicYear\Models\AcademicYear::class);
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function contractItems(): HasMany
    {
        return $this->hasMany(ContractItem::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('status', \App\Domains\HR\Payroll\Enums\ContractStatus::Active);
    }

    public function scopeForPeriod($query, $date)
    {
        return $query->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date);
    }

    // ==================== Accessors ====================

    /**
     * إجمالي البدلات الشهرية
     */
    public function getTotalAllowancesAttribute(): float
    {
        if ($this->relationLoaded('contractItems')) {
            return $this->contractItems
                ->filter(function ($item) {
                    return $item->type === 'allowance' &&
                        (!$item->is_one_time || is_null($item->consumed_at));
                })
                ->sum('amount');
        }

        return $this->contractItems()
            ->where('type', 'allowance')
            ->active()
            ->sum('amount');
    }

    /**
     * إجمالي الراتب الشهري (الأساسي + البدلات)
     */
    public function getGrossSalaryAttribute(): float
    {
        return $this->basic_salary + $this->total_allowances;
    }

    // ==================== Business Logic ====================

    /**
     * قفل العقد (بعد صدور أول راتب)
     */
    public function lock(int $userId): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId,
        ]);
    }

    /**
     * التحقق من إمكانية التعديل تحرير العقود بعد صدور أول راتب
     */
    public function ensureEditable(): void
    {
        if ($this->is_locked) {
            throw new ContractLockedException(); // يعمل هذا على تجنب التعديل على العقد بعد صدور أول راتب   
        }
    }
}
