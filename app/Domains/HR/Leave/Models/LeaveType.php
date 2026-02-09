<?php

namespace App\Domains\HR\Leave\Models;

use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use HasFactory, HandlesSafeDelete, HasModelLabels;

    protected static function newFactory()
    {
        return \Database\Factories\LeaveTypeFactory::new();
    }

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'نوع الإجازة';
    protected static string $modelPluralLabel = 'أنواع الإجازات';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'leaveRequests' => 'طلبات الإجازات',
    ];

    protected $fillable = [
        'name',
        'days_per_year',
        'excludes_holidays',
        'requires_proof',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'excludes_holidays' => 'boolean',
        'requires_proof' => 'boolean',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
