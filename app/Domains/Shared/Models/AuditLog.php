<?php

namespace App\Domains\Shared\Models;

use App\Domains\Shared\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    /**
     * لا يوجد updated_at - هذا الجدول للقراءة فقط
     */
    public $timestamps = false;

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'reason',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * العنصر المُدقق (Polymorphic)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * المستخدم الذي قام بالتغيير
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * اسم العملية بالعربية
     */
    public function getActionNameAttribute(): string
    {
        return match ($this->action) {
            'created' => 'إنشاء',
            'updated' => 'تعديل',
            'deleted' => 'حذف',
            default => $this->action,
        };
    }

    /**
     * لون العملية للواجهة
     */
    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'emerald',
            'updated' => 'amber',
            'deleted' => 'red',
            default => 'gray',
        };
    }

    /**
     * اسم النموذج بالعربية
     */
    public function getModelNameAttribute(): string
    {
        $modelNames = [
            'App\Domains\HR\Staff\Models\StaffAttendance' => 'حضور موظف',
            'App\Domains\HR\Staff\Models\Staff' => 'موظف',
            'App\Models\WorkShift' => 'وردية',
            'App\Domains\Academic\Student\Models\Student' => 'طالب',
        ];

        return $modelNames[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    /**
     * جلب التغييرات بشكل مقروء
     */
    public function getChangesAttribute(): array
    {
        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        // للإنشاء: عرض القيم الجديدة فقط
        if ($this->action === 'created') {
            foreach ($new as $key => $value) {
                $changes[$key] = ['old' => null, 'new' => $value];
            }
            return $changes;
        }

        // للتعديل: عرض الفروقات فقط
        if ($this->action === 'updated') {
            foreach ($new as $key => $value) {
                if (($old[$key] ?? null) !== $value) {
                    $changes[$key] = ['old' => $old[$key] ?? null, 'new' => $value];
                }
            }
            return $changes;
        }

        // للحذف: عرض القيم القديمة
        if ($this->action === 'deleted') {
            foreach ($old as $key => $value) {
                $changes[$key] = ['old' => $value, 'new' => null];
            }
            return $changes;
        }

        return $changes;
    }

    /**
     * Scope: لنموذج معين
     */
    public function scopeForModel($query, string $modelClass, ?int $modelId = null)
    {
        $query->where('auditable_type', $modelClass);

        if ($modelId) {
            $query->where('auditable_id', $modelId);
        }

        return $query;
    }

    /**
     * Scope: لمستخدم معين
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: في فترة معينة
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Boot method to set created_at automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }
}
