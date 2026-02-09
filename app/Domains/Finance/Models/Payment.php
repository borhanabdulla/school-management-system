<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Academic\Student\Models\Guardian;
use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HandlesSafeDelete, HasModelLabels, HasFactory;

    protected $fillable = [
        'invoice_id',
        'guardian_id',
        'amount',
        'method',
        'transaction_reference',
        'paid_at',
        'notes',
        'created_by',
        'status',
        'cancel_reason',
        'cancelled_by',
        'cancelled_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'method' => \App\Domains\Finance\Enums\PaymentMethod::class,
        'status' => \App\Domains\Finance\Enums\PaymentStatus::class,
    ];

    protected static string $modelLabel = 'دفعة مالية';
    protected static string $modelPluralLabel = 'دفعات مالية';

    protected array $protectedRelations = [];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ═══════════════════════════════════════════════════════════════
    // Scopes (PR-M1: Query Readability)
    // ═══════════════════════════════════════════════════════════════

    /**
     * الدفعات النشطة (غير الملغاة)
     */
    public function scopeActive($query)
    {
        return $query->where('status', \App\Domains\Finance\Enums\PaymentStatus::Active);
    }

    /**
     * الدفعات الملغاة
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', \App\Domains\Finance\Enums\PaymentStatus::Cancelled);
    }

    /**
     * دفعات طالب محدد
     */
    public function scopeByStudent($query, int $studentId)
    {
        return $query->whereHas('invoice', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        });
    }

    /**
     * دفعات سنة أكاديمية محددة
     */
    public function scopeByYear($query, int $yearId)
    {
        return $query->whereHas('invoice', function ($q) use ($yearId) {
            $q->where('academic_year_id', $yearId);
        });
    }

    /**
     * آخر الدفعات
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->latest('paid_at')->limit($limit);
    }
}
