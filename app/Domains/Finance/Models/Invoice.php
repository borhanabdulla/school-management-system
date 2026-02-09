<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use App\Infrastructure\Traits\HandlesSafeDelete;
use App\Infrastructure\Traits\HasModelLabels;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;

class Invoice extends Model
{
    use HandlesSafeDelete, HasModelLabels;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use \App\Infrastructure\Traits\HasAcademicScope;

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'items' => 'بنود الفاتورة',
        'payments' => 'المدفوعات',
    ];

    protected $fillable = [
        'invoice_number',
        'student_id',
        'academic_year_id',
        'issue_date',
        'due_date',
        'total_amount',
        'paid_amount',
        'status',
        'payer_guardian_id',
        'payer_set_at',
        'payer_set_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'status' => \App\Domains\Finance\Enums\InvoiceStatus::class,
    ];

    protected $dispatchesEvents = [
        'created' => \App\Domains\Finance\Events\InvoiceCreated::class,
    ];

    protected static string $modelLabel = 'فاتورة';
    protected static string $modelPluralLabel = 'فواتير';

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * الولي المسؤول مالياً (الدافع المثبت)
     */
    public function payerGuardian()
    {
        return $this->belongsTo(\App\Domains\Academic\Student\Models\Guardian::class, 'payer_guardian_id');
    }

    /**
     * المستخدم الذي قام تثبيت الدافع
     */
    public function payerSetter()
    {
        return $this->belongsTo(\App\Domains\Shared\Models\User::class, 'payer_set_by');
    }

    // ============================================
    // Scopes (للتخلص من Magic Strings)
    // ============================================

    public function scopePaid($query)
    {
        return $query->where('status', \App\Domains\Finance\Enums\InvoiceStatus::Paid);
    }

    public function scopeNotPaid($query)
    {
        return $query->where('status', '!=', \App\Domains\Finance\Enums\InvoiceStatus::Paid);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', \App\Domains\Finance\Enums\InvoiceStatus::Cancelled);
    }

    public function scopeOutstanding($query)
    {
        return $query->whereNotIn('status', [
            \App\Domains\Finance\Enums\InvoiceStatus::Paid,
            \App\Domains\Finance\Enums\InvoiceStatus::Cancelled
        ]);
    }
}
