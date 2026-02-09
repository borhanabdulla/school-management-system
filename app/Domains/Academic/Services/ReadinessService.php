<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Data\ReadinessItem;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Services\StudentEnrollmentQueryService;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use Illuminate\Support\Collection;

/**
 * Service to check academic year readiness for closing
 *
 * Provides readiness badges (blocking and warning) to help administrators
 * understand what needs to be completed before closing the year.
 */
class ReadinessService
{
    /**
     * Blocking badge keys
     */
    public const KEY_TERMS_NOT_COMPLETED = 'terms_not_completed';
    public const KEY_ANNUAL_RESULTS_PENDING = 'annual_results_pending';
    public const KEY_PROMOTION_INCOMPLETE = 'promotion_incomplete';

    /**
     * Warning badge keys
     */
    public const KEY_ATTENDANCE_MISSING = 'attendance_missing_sessions';
    public const KEY_MARKS_MISSING = 'marks_missing_entries';
    public const KEY_GRADING_CONFIG_ISSUES = 'grading_config_issues';
    public const KEY_ATTENDANCE_MODE_UNSUPPORTED = 'attendance_mode_unsupported';

    public function __construct(
        protected StudentEnrollmentQueryService $studentService
    ) {}

    /**
     * Get all readiness items for an academic year
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getReadinessItems(AcademicYear $year): Collection
    {
        return collect([
            // Blocking items
            $this->checkTermsCompleted($year),
            $this->checkAnnualResultsPending($year),
            $this->checkPromotionIncomplete($year),
            // Warning items
            $this->checkGradingConfigIssues($year),
            $this->checkAttendanceMissing($year),
            $this->checkAttendanceModeSupport($year),
            $this->checkMarksMissing($year),
        ]);
    }

    /**
     * Get only blocking items (that prevent year closing)
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getBlockingItems(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year)
            ->filter(fn(ReadinessItem $item) => $item->isBlocking());
    }

    /**
     * Get only warning items
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getWarningItems(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year)
            ->filter(fn(ReadinessItem $item) => $item->isWarning());
    }

    /**
     * Check if year can be closed (no blocking issues)
     */
    public function canClose(AcademicYear $year): bool
    {
        return $this->getBlockingItems($year)->every(
            fn(ReadinessItem $item) => !$item->hasIssues()
        );
    }

    /**
     * Get summary for the year
     */
    public function getSummary(AcademicYear $year): array
    {
        $items = $this->getReadinessItems($year);

        return [
            'year_id' => $year->id,
            'year_name' => $year->name,
            'can_close' => $this->canClose($year),
            'blocking_count' => $this->getBlockingItems($year)->filter->hasIssues()->count(),
            'warning_count' => $this->getWarningItems($year)->filter->hasIssues()->count(),
            'items' => $items->map(fn($item) => $item->toArray())->toArray(),
        ];
    }

    /**
     * Get teacher-specific readiness (warnings only)
     * Used for weekly reminders to teachers
     *
     * @param AcademicYear $year
     * @param int|null $teacherId Specific teacher ID, or null for all teachers
     * @return Collection<int, ReadinessItem>
     */
    public function getTeacherReadiness(AcademicYear $year, ?int $teacherId = null): Collection
    {
        // Teachers only see warnings, not blocking items
        $warnings = $this->getWarningItems($year);

        // Filter by teacher if specified
        if ($teacherId !== null) {
            // Add teacher-specific filtering here if needed
            // For now, return all warnings
        }

        return $warnings;
    }

    /**
     * Get readiness for admin dashboard (all items)
     *
     * @param AcademicYear $year
     * @return Collection<int, ReadinessItem>
     */
    public function getAdminReadiness(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year);
    }

    /**
     * Check if there are any warnings for a year
     *
     * @param AcademicYear $year
     * @return bool
     */
    public function hasWarnings(AcademicYear $year): bool
    {
        return $this->getWarningItems($year)->filter->hasIssues()->isNotEmpty();
    }

    /**
     * Check if all terms are completed (Blocking)
     */
    protected function checkTermsCompleted(AcademicYear $year): ReadinessItem
    {
        $incompleteCount = $year->terms()
            ->where('status', '!=', TermStatus::Completed->value)
            ->count();

        return ReadinessItem::blocking(
            key: self::KEY_TERMS_NOT_COMPLETED,
            label: 'ترمات غير مكتملة',
            message: $incompleteCount === 0
                ? 'جميع الترمات مكتملة'
                : "توجد {$incompleteCount} ترمات غير مكتملة",
            count: $incompleteCount,
            route: 'terms.index',
            routeParams: ['year_id' => $year->id]
        );
    }

    /**
     * Check if all annual results are calculated (Blocking)
     */
    protected function checkAnnualResultsPending(AcademicYear $year): ReadinessItem
    {
        $pendingCount = AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', 'pending')
            ->count();

        return ReadinessItem::blocking(
            key: self::KEY_ANNUAL_RESULTS_PENDING,
            label: 'نتائج سنوية معلقة',
            message: $pendingCount === 0
                ? 'جميع النتائج السنوية محسوبة'
                : "توجد {$pendingCount} نتيجة سنوية معلقة",
            count: $pendingCount,
            route: 'promotion.annual-results',
            routeParams: []
        );
    }

    /**
     * Check if all eligible students are promoted (Blocking)
     */
    protected function checkPromotionIncomplete(AcademicYear $year): ReadinessItem
    {
        $eligibleCount = $this->studentService->eligibleForClosureCount($year);

        if ($eligibleCount === 0) {
            return ReadinessItem::blocking(
                key: self::KEY_PROMOTION_INCOMPLETE,
                label: 'ترحيلات غير مكتملة',
                message: 'لا يوجد طلاب مؤهلين للترحيل',
                count: 0
            );
        }

        $promotedCount = Promotion::where('academic_year_id', $year->id)
            ->where('is_reverted', false)
            ->count();

        $remaining = max(0, $eligibleCount - $promotedCount);

        return ReadinessItem::blocking(
            key: self::KEY_PROMOTION_INCOMPLETE,
            label: 'ترحيلات غير مكتملة',
            message: $remaining === 0
                ? 'جميع الطلاب تم ترحيلهم'
                : "بقي {$remaining} طالب لم يتم ترحيلهم",
            count: $remaining,
            route: 'promotion.manage',
            routeParams: []
        );
    }

    /**
     * Check for missing attendance sessions (Warning)
     */
    protected function checkAttendanceMissing(AcademicYear $year): ReadinessItem
    {
        // Get active terms for the year
        $activeTerms = $year->terms()
            ->whereIn('status', [TermStatus::Active->value, TermStatus::Completed->value])
            ->get();

        $missingCount = 0;

        foreach ($activeTerms as $term) {
            // Count classes that should have had attendance but don't
            // This is a simplified check - adjust based on actual requirements
            $missingCount += $this->countMissingAttendance($year, $term);
        }

        return ReadinessItem::warning(
            key: self::KEY_ATTENDANCE_MISSING,
            label: 'غياب في الحضور',
            message: $missingCount === 0
                ? 'لا توجد حصص حضور ناقصة'
                : "توجد {$missingCount} حصة حضور ناقصة",
            count: $missingCount,
            route: 'teacher.attendance.report',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check if attendance mode is supported by grading sync (Warning)
     */
    protected function checkAttendanceModeSupport(AcademicYear $year): ReadinessItem
    {
        $setting = AttendanceSetting::where('academic_year_id', $year->id)->first();

        if (! $setting) {
            return ReadinessItem::warning(
                key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
                label: 'نمط الحضور',
                message: 'لا توجد إعدادات حضور لهذه السنة',
                count: 1,
                route: 'attendance.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        if ($setting->mode === AttendanceMode::Daily) {
            return ReadinessItem::warning(
                key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
                label: 'نمط الحضور',
                message: 'نمط الحضور اليومي غير مدعوم حالياً في مزامنة الدرجات',
                count: 1,
                route: 'attendance.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        return ReadinessItem::warning(
            key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
            label: 'نمط الحضور',
            message: 'نمط الحضور مدعوم',
            count: 0,
            route: 'attendance.settings',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check grading configuration health (Warning)
     */
    protected function checkGradingConfigIssues(AcademicYear $year): ReadinessItem
    {
        $terms = $year->terms()
            ->whereIn('status', [TermStatus::Active->value, TermStatus::Completed->value])
            ->get();

        if ($terms->isEmpty()) {
            return ReadinessItem::warning(
                key: self::KEY_GRADING_CONFIG_ISSUES,
                label: 'إعدادات الدرجات',
                message: 'لا توجد ترمات للفحص',
                count: 0,
                route: 'grading.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        $checker = app(GradingConfigHealthChecker::class);
        $missing = 0;
        $invalid = 0;

        foreach ($terms as $term) {
            $report = $checker->checkTerm($term)->toArray();
            $missing += count($report['missing']);
            $invalid += count($report['invalid']);
        }

        $count = $missing + $invalid;
        $message = $count === 0
            ? 'لا توجد مشاكل في إعدادات الدرجات'
            : "توجد {$count} مشكلة في إعدادات الدرجات (Missing {$missing} / Invalid {$invalid})";

        return ReadinessItem::warning(
            key: self::KEY_GRADING_CONFIG_ISSUES,
            label: 'إعدادات الدرجات',
            message: $message,
            count: $count,
            route: 'grading.settings',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check for missing marks entries (Warning)
     */
    protected function checkMarksMissing(AcademicYear $year): ReadinessItem
    {
        // Get completed terms for the year
        $completedTerms = $year->terms()
            ->where('status', TermStatus::Completed->value)
            ->get();

        $missingCount = 0;

        foreach ($completedTerms as $term) {
            $missingCount += $this->countMissingMarks($year, $term);
        }

        return ReadinessItem::warning(
            key: self::KEY_MARKS_MISSING,
            label: 'درجات ناقصة',
            message: $missingCount === 0
                ? 'لا توجد درجات ناقصة'
                : "توجد {$missingCount} درجة ناقصة",
            count: $missingCount,
            route: 'grading.gradebooks',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Count missing attendance for a term
     */
    protected function countMissingAttendance(AcademicYear $year, Term $term): int
    {
        // Simplified: Count attendance records that are missing
        // Adjust based on actual business logic
        return Attendance::where('academic_year_id', $year->id)
            ->where('term_id', $term->id)
            ->whereNull('status')
            ->count();
    }

    /**
     * Count missing marks for a term
     */
    protected function countMissingMarks(AcademicYear $year, Term $term): int
    {
        // Safe query: count explicit rows with missing scores for this term.
        return MonthlyGrade::query()
            ->whereNull('score')
            ->whereHas('gradebookMonth', function ($query) use ($term, $year) {
                $query->where('term_id', $term->id)
                    ->where('academic_year_id', $year->id);
            })
            ->whereHas('courseOffering', fn($query) => $query->where('academic_year_id', $year->id))
            ->count();
    }
}
