<?php

declare(strict_types=1);

namespace App\Domains\Finance\Services;

use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Enums\InvoiceStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * FinanceLookupService - خدمة جلب البيانات المالية
 * 
 * مسؤولة عن جميع عمليات القراءة المتعلقة بالفواتير والمدفوعات
 * مع تفعيل التكييش (Caching) لتحسين الأداء
 * 
 * سياسة التكييش:
 * - فواتير الطالب: 5 دقائق
 * - إحصائيات السنة: 10 دقائق
 * - آخر الدفعات: 2 دقيقة
 * 
 * يتم إبطال الكاش عبر Events:
 * - InvoiceCreated
 * - PaymentReceived
 * - DiscountApplied
 */
class FinanceLookupService
{
    // ═══════════════════════════════════════════════════════════════
    // TTL Constants (بالثواني)
    // ═══════════════════════════════════════════════════════════════
    public const CACHE_TTL_INVOICES = 300;  // 5 دقائق
    public const CACHE_TTL_STATS = 600;     // 10 دقائق
    public const CACHE_TTL_RECENT = 120;    // 2 دقيقة

    // Cache Tag for bulk invalidation
    public const CACHE_TAG = 'finance';

    // ═══════════════════════════════════════════════════════════════
    // Unified Cache Access (The Fix)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Helper to get the cache store, using tags if available.
     * This ensures 'forget' works on the same store that 'remember' used.
     */
    private function cache()
    {
        // Check if the default store supports tags
        $repository = Cache::store();
        if (method_exists($repository, 'tags')) {
            return $repository->tags([self::CACHE_TAG]);
        }

        return $repository;
    }

    // ═══════════════════════════════════════════════════════════════
    // Query Methods (Year-Aware)
    // ═══════════════════════════════════════════════════════════════

    /**
     * جلب فواتير الطالب لسنة محددة (لأغراض الترحيل والشهادات)
     */
    public function getStudentInvoicesForYear(int $studentId, int $academicYearId): Collection
    {
        $key = $this->studentInvoicesKey($studentId, $academicYearId);

        return $this->cache()->remember(
            $key,
            self::CACHE_TTL_INVOICES,
            function () use ($studentId, $academicYearId) {
                return Invoice::where('student_id', $studentId)
                    ->where('academic_year_id', $academicYearId)
                    ->with('items', 'payments')
                    ->orderBy('issue_date', 'desc')
                    ->get();
            }
        );
    }

    /**
     * جلب فواتير الطالب لكل السنوات (لملف الطالب المالي الشامل)
     */
    public function getStudentInvoicesAnyYear(int $studentId): Collection
    {
        $key = $this->studentInvoicesKey($studentId, null);

        return $this->cache()->remember(
            $key,
            self::CACHE_TTL_INVOICES,
            function () use ($studentId) {
                return Invoice::where('student_id', $studentId)
                    ->with('items', 'payments') // Optimization: Removed 'academicYear' eager load unless needed
                    ->orderBy('issue_date', 'desc')
                    ->get();
            }
        );
    }

    /**
     * Legacy Wrapper (Deprecated) - سيتم إزالته تدريجياً
     * @deprecated Use explicit getStudentInvoicesForYear or getStudentInvoicesAnyYear
     */
    public function getStudentInvoices(int $studentId, ?int $academicYearId = null): Collection
    {
        if ($academicYearId) {
            return $this->getStudentInvoicesForYear($studentId, $academicYearId);
        }
        return $this->getStudentInvoicesAnyYear($studentId);
    }

    /**
     * جلب الإحصائيات المالية (دائماً لسنة محددة)
     */
    public function getFinanceStatistics(int $yearId): array
    {
        $key = $this->statsKey($yearId);

        return $this->cache()->remember(
            $key,
            self::CACHE_TTL_STATS,
            function () use ($yearId) {
                $invoicesQuery = Invoice::query()->where('academic_year_id', $yearId);

                $totalAmount = (clone $invoicesQuery)->sum('total_amount');
                $paidAmount = (clone $invoicesQuery)->sum('paid_amount');

                return [
                    'total_invoices' => (clone $invoicesQuery)->count(),
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'unpaid_amount' => $totalAmount - $paidAmount,
                    'unpaid_count' => (clone $invoicesQuery)->notPaid()->count(),
                    'overdue_count' => (clone $invoicesQuery)
                        ->notPaid()
                        ->where('due_date', '<', now())
                        ->count(),
                ];
            }
        );
    }

    /**
     * جلب المدفوعات الأخيرة
     */
    public function getRecentPayments(int $limit = 10, ?int $academicYearId = null): Collection
    {
        $key = $this->recentPaymentsKey($limit, $academicYearId);

        return $this->cache()->remember(
            $key,
            self::CACHE_TTL_RECENT,
            function () use ($limit, $academicYearId) {
                $query = Payment::with('invoice.student')
                    ->orderBy('paid_at', 'desc')
                    ->limit($limit);

                if ($academicYearId) {
                    $query->whereHas('invoice', fn($q) => $q->where('academic_year_id', $academicYearId));
                }

                return $query->get();
            }
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // Financial Status Logic (Year-Aware)
    // ═══════════════════════════════════════════════════════════════

    /**
     * الحالة المالية لسنة محددة (تستخدم للترحيل وحجب الشهادات)
     */
    public function getStudentFinancialStatusForYear(int $studentId, int $yearId): array
    {
        $invoices = $this->getStudentInvoicesForYear($studentId, $yearId);

        return $this->calculateStatusFromInvoices($invoices);
    }

    /**
     * الحالة المالية العامة (تستخدم للعرض العام)
     */
    public function getStudentFinancialStatusAnyYear(int $studentId): array
    {
        $invoices = $this->getStudentInvoicesAnyYear($studentId);

        return $this->calculateStatusFromInvoices($invoices);
    }

    /**
     * Legacy Wrapper (Deprecated)
     * @deprecated Use getStudentFinancialStatusForYear or getStudentFinancialStatusAnyYear
     */
    public function getStudentFinancialStatus(int $studentId): array
    {
        // Default to global status (as per old behavior)
        return $this->getStudentFinancialStatusAnyYear($studentId);
    }

    private function calculateStatusFromInvoices(Collection $invoices): array
    {
        $unpaidAmount = $invoices
            ->where('status', '!=', InvoiceStatus::Paid)
            ->sum(fn($invoice) => $invoice->total_amount - $invoice->paid_amount);

        if ($unpaidAmount > 0) {
            return [
                'status' => InvoiceStatus::Unpaid->value,
                'label' => 'عليه مستحقات',
                'amount' => $unpaidAmount,
                'color' => 'red'
            ];
        }

        return [
            'status' => InvoiceStatus::Paid->value,
            'label' => 'خالص',
            'amount' => 0,
            'color' => 'green'
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // Invalidation Methods (Robust & Tag-Safe)
    // ═══════════════════════════════════════════════════════════════

    /**
     * مسح كاش فواتير طالب محدد
     */
    public function invalidateForStudent(int $studentId, ?int $academicYearId = null): void
    {
        // 1. Invalidate "All Invoices" key
        $this->cache()->forget($this->studentInvoicesKey($studentId, null));

        // 2. Invalidate specific year key if provided
        if ($academicYearId) {
            $this->cache()->forget($this->studentInvoicesKey($studentId, $academicYearId));
        }
    }

    /**
     * مسح كاش إحصائيات سنة محددة
     */
    public function invalidateStats(int $yearId): void
    {
        $this->cache()->forget($this->statsKey($yearId));
    }

    /**
     * مسح كاش آخر الدفعات
     */
    public function invalidateRecentPayments(?int $academicYearId = null): void
    {
        // Invalidate global list
        $this->cache()->forget($this->recentPaymentsKey(10, null));

        // Invalidate year specific list
        if ($academicYearId) {
            $this->cache()->forget($this->recentPaymentsKey(10, $academicYearId));
        }
    }

    /**
     * مسح كل الكاش المالي (للطوارئ)
     */
    public function invalidateAll(): void
    {
        $this->cache()->flush();
    }

    // ═══════════════════════════════════════════════════════════════
    // Private Key Generators
    // ═══════════════════════════════════════════════════════════════

    private function studentInvoicesKey(int $studentId, ?int $academicYearId = null): string
    {
        if ($academicYearId) {
            return "finance:student:{$studentId}:year:{$academicYearId}:invoices";
        }
        return "finance:student:{$studentId}:invoices:all";
    }

    private function statsKey(int $yearId): string
    {
        return "finance:year:{$yearId}:stats";
    }

    private function recentPaymentsKey(int $limit = 10, ?int $academicYearId = null): string
    {
        if ($academicYearId) {
            return "finance:year:{$academicYearId}:recent_payments:limit:{$limit}";
        }
        return "finance:recent_payments:limit:{$limit}";
    }

    // ═══════════════════════════════════════════════════════════════
    // Observer Support - Cache Invalidation Methods
    // ═══════════════════════════════════════════════════════════════

    /**
     * إبطال كاش فاتورة محددة (للاستخدام من Observer)
     */
    public function invalidateInvoice(int $invoiceId): void
    {
        $this->cache()->forget("invoice:{$invoiceId}");
        $this->cache()->forget("invoice:{$invoiceId}:totals");
        $this->cache()->forget("invoice:{$invoiceId}:details");
    }

    /**
     * إبطال كاش طالب محدد (للاستخدام من Observer)
     */
    public function invalidateStudent(int $studentId, ?int $yearId = null): void
    {
        if ($yearId) {
            $this->cache()->forget($this->studentInvoicesKey($studentId, $yearId));
            $this->cache()->forget("finance:student:{$studentId}:year:{$yearId}:balance");
        } else {
            // إبطال جميع السنوات للطالب
            $this->cache()->forget($this->studentInvoicesKey($studentId, null));
        }
    }

    /**
     * إبطال كاش سنة أكاديمية (للاستخدام من Observer)
     */
    public function invalidateAcademicYear(int $yearId): void
    {
        $this->cache()->forget($this->statsKey($yearId));
        $this->cache()->forget("finance:year:{$yearId}:receivables");
        $this->cache()->forget("finance:year:{$yearId}:outstanding");
    }
}
