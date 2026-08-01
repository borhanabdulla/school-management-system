<?php

namespace App\Domains\Academic\Promotion\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\Student\Services\StudentEnrollmentQueryService;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentEnrollment;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use App\Domains\Academic\Student\Enums\EnrollmentType;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\Promotion\Exceptions\NoTargetYearException;
use App\Domains\Academic\Promotion\Exceptions\SchoolNotReadyException;
use App\Domains\Academic\Promotion\Exceptions\StudentCannotBePromotedException;
use App\Domains\Academic\Promotion\Exceptions\PromotionAlreadyRevertedException;
use App\Domains\Academic\Promotion\Exceptions\NoSectionsAvailableException;
use App\Domains\Academic\Promotion\Exceptions\IncompletePromotionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use App\Domains\Academic\Student\Services\StudentLookupService;

use App\Domains\Finance\Services\FinanceLookupService;
use App\Domains\Academic\Results\Enums\ResultDecision;
use App\Domains\Academic\Promotion\Enums\PromotionType;
use App\Domains\Finance\Enums\InvoiceStatus;

class PromotionService
{
    public function __construct(
        protected ActivateAcademicYearAction $activateAcademicYearAction,
        protected CloseAcademicYearAction $closeAcademicYearAction,
        protected StudentLookupService $studentLookup,
        protected FinanceLookupService $financeLookup,
        protected StudentEnrollmentQueryService $enrollmentQueryService
    ) {
    }

    /**
     * جلب الطلاب المؤهلين للترحيل (لديهم نتائج ولم يتم ترحيلهم بعد)
     * 
     * ✅ هذه الدالة تنتمي لـ Promotion domain لأنها تستعلم من:
     * - Results domain (annualResults)
     * - Promotion domain (promotions)
     */
    public function studentsEligibleForPromotion(int $yearId): Builder
    {
        return Student::whereHas(
            'annualResults',
            fn($q) => $q->where('academic_year_id', $yearId)
                ->where('decision', '!=', 'pending')
        )->whereDoesntHave(
                'promotions',
                fn($q) => $q->where('academic_year_id', $yearId)
                    ->where('is_reverted', false)
            );
    }
    /**
     * التحقق من إمكانية الترحيل
     */
    public function canPromote(Student $student, AcademicYear $fromYear): array
    {
        // 1. التحقق من وجود نتيجة سنوية
        $annualResult = AnnualResult::where('student_id', $student->id)
            ->where('academic_year_id', $fromYear->id)
            ->first();

        if (!$annualResult) {
            return ['can' => false, 'reason' => 'لا توجد نتيجة سنوية لهذا الطالب'];
        }

        if ($annualResult->decision === ResultDecision::Pending) {
            return ['can' => false, 'reason' => 'النتيجة السنوية لم تُعالج بعد'];
        }

        // 2. التحقق من عدم وجود ترحيل سابق
        $existingPromotion = Promotion::where('student_id', $student->id)
            ->where('academic_year_id', $fromYear->id)
            ->where('is_reverted', false)
            ->first();

        if ($existingPromotion) {
            return ['can' => false, 'reason' => 'تم ترحيل هذا الطالب مسبقاً'];
        }

        // 3. التحقق من وجود سنة جديدة
        $newYear = $this->getNextAcademicYear($fromYear);
        if (!$newYear) {
            return ['can' => false, 'reason' => 'لا توجد سنة دراسية جديدة. يرجى إنشاء سنة جديدة أولاً'];
        }

        return ['can' => true, 'reason' => null, 'annual_result' => $annualResult, 'new_year' => $newYear];
    }

    /**
     * جلب السنة الدراسية التالية
     */
    public function getNextAcademicYear(AcademicYear $currentYear): ?AcademicYear
    {
        return AcademicYear::where('start_date', '>', $currentYear->end_date)
            ->orderBy('start_date')
            ->first();
    }

    /**
     * التحقق من وجود سنة جديدة وإلا منع الترحيل
     */
    public function requireNextYear(AcademicYear $currentYear): AcademicYear
    {
        $nextYear = $this->getNextAcademicYear($currentYear);

        if (!$nextYear) {
            throw new NoTargetYearException();
        }

        return $nextYear;
    }

    /**
     * فحص جاهزية المدرسة للترحيل الكامل
     */
    public function validateSchoolReadyForPromotion(AcademicYear $year): array
    {
        $issues = [];
        $stats = [];

        // 1. إجمالي الطلاب المسجلين
        $totalStudents = $this->studentLookup->studentsActiveEnrolledInYear($year->id)->count();
        $stats['total_students'] = $totalStudents;

        // 2. الطلاب الذين لديهم نتائج سنوية
        $studentsWithResults = AnnualResult::where('academic_year_id', $year->id)
            ->distinct('student_id')
            ->count('student_id');
        $studentsWithoutResults = $totalStudents - $studentsWithResults;
        $stats['with_results'] = $studentsWithResults;
        $stats['without_results'] = $studentsWithoutResults;

        if ($studentsWithoutResults > 0) {
            $issues[] = [
                'type' => 'missing_results',
                'severity' => 'critical',
                'message' => "يوجد {$studentsWithoutResults} طالب بدون نتائج سنوية",
                'action' => 'يجب تجميع النتائج أولاً',
                'link' => 'promotion.annual-results'
            ];
        }

        // 3. القرارات المعلقة
        $pendingDecisions = AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', ResultDecision::Pending)
            ->count();
        $stats['pending_decisions'] = $pendingDecisions;

        if ($pendingDecisions > 0) {
            $issues[] = [
                'type' => 'pending_decisions',
                'severity' => 'critical',
                'message' => "يوجد {$pendingDecisions} طالب بقرار معلق",
                'action' => 'يجب حساب القرارات أولاً'
            ];
        }

        // 4. السنة الجديدة
        $nextYear = $this->getNextAcademicYear($year);
        $stats['next_year'] = $nextYear?->name;

        if (!$nextYear) {
            $issues[] = [
                'type' => 'no_next_year',
                'severity' => 'critical',
                'message' => 'لا توجد سنة دراسية جديدة',
                'action' => 'يجب إنشاء سنة جديدة',
                'link' => 'academic-years.index'
            ];
        } else {
            // 5. الشعب للسنة الجديدة
            $sectionsCount = ClassSection::where('academic_year_id', $nextYear->id)->count();
            $stats['new_year_sections'] = $sectionsCount;

            if ($sectionsCount === 0) {
                $issues[] = [
                    'type' => 'no_sections',
                    'severity' => 'warning',
                    'message' => 'لا توجد شعب للسنة الجديدة',
                    'action' => 'يجب إنشاء الشعب للتوزيع',
                    'link' => 'class-sections.index'
                ];
            }
        }

        // 6. إحصائيات الترحيل المتوقعة
        $stats['expected_promoted'] = AnnualResult::where('academic_year_id', $year->id)
            ->whereIn('decision', [ResultDecision::Pass, ResultDecision::Conditional])->count();
        $stats['expected_repeated'] = AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', ResultDecision::Fail)->count();

        // 7. الطلاب المُرحَّلون بالفعل
        $alreadyPromoted = Promotion::where('academic_year_id', $year->id)
            ->where('is_reverted', false)
            ->distinct('student_id')
            ->count('student_id');
        $stats['already_promoted'] = $alreadyPromoted;
        $stats['remaining'] = max(0, $totalStudents - $alreadyPromoted);

        $hasCritical = !empty(array_filter($issues, fn($i) => $i['severity'] === 'critical'));

        return [
            'ready' => !$hasCritical && $studentsWithResults > 0,
            'issues' => $issues,
            'stats' => $stats,
            'next_year' => $nextYear
        ];
    }

    /**
     * ترحيل جميع طلاب المدرسة دفعة واحدة
     */
    public function promoteEntireSchool(AcademicYear $fromYear, ?User $processedBy = null): array
    {
        $readiness = $this->validateSchoolReadyForPromotion($fromYear);

        if (!$readiness['ready']) {
            throw new SchoolNotReadyException($readiness['issues'][0]['message'] ?? 'خطأ غير معروف');
        }

        $results = ['success' => 0, 'failed' => 0, 'errors' => []];

        // جلب الطلاب الذين لم يتم ترحيلهم بعد
        $students = $this->studentsEligibleForPromotion($fromYear->id)->get();

        foreach ($students as $student) {
            try {
                $this->promote($student, $fromYear, null, $processedBy);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name_ar,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * معاينة تكوين الصفوف للسنة الجديدة
     */
    public function getNewYearComposition(AcademicYear $fromYear): array
    {
        $nextYear = $this->getNextAcademicYear($fromYear);
        if (!$nextYear) {
            return [];
        }

        $grades = Grade::with('stage')->get()->sortBy('stage.rank');
        $composition = [];

        foreach ($grades as $grade) {
            // القادمون من الصف الأدنى (الناجحون)
            $previousGrade = Grade::where('next_grade_id', $grade->id)->first();
            $promotedCount = 0;

            if ($previousGrade) {
                $promotedCount = AnnualResult::where('academic_year_id', $fromYear->id)
                    ->where('grade_id', $previousGrade->id)
                    ->whereIn('decision', [ResultDecision::Pass, ResultDecision::Conditional])
                    ->count();
            }

            // الباقون (الراسبون من نفس الصف)
            $repeatersCount = AnnualResult::where('academic_year_id', $fromYear->id)
                ->where('grade_id', $grade->id)
                ->where('decision', ResultDecision::Fail)
                ->count();

            // سعة الشعب المتاحة للسنة الجديدة
            $sections = ClassSection::where('academic_year_id', $nextYear->id)
                ->where('grade_id', $grade->id)
                ->get();
            $availableCapacity = $sections->sum('max_capacity');
            $sectionsCount = $sections->count();

            $totalExpected = $promotedCount + $repeatersCount;

            $composition[] = [
                'grade_id' => $grade->id,
                'grade_name' => $grade->name,
                'promoted' => $promotedCount,
                'repeaters' => $repeatersCount,
                'total' => $totalExpected,
                'sections_count' => $sectionsCount,
                'capacity' => $availableCapacity,
                'overflow' => max(0, $totalExpected - $availableCapacity),
                'status' => $totalExpected <= $availableCapacity ? 'ok' : 'overflow'
            ];
        }

        return $composition;
    }

    /**
     * تنفيذ ترحيل طالب واحد
     */
    public function promote(
        Student $student,
        AcademicYear $fromYear,
        ?ClassSection $toSection = null,
        ?User $processedBy = null
    ): Promotion {
        $check = $this->canPromote($student, $fromYear);
        if (!$check['can']) {
            throw new StudentCannotBePromotedException($check['reason']);
        }

        $annualResult = $check['annual_result'];
        $newYear = $check['new_year'];

        // تحديد الصف الجديد
        $fromGrade = $student->currentGrade;
        $toGrade = $this->determineNextGrade($student, $annualResult);

        // تحديد نوع الترحيل
        $type = $this->determinePromotionType($annualResult, $fromGrade, $toGrade);

        // التحقق من المستحقات المالية (Year-Aware Check)
        // نتحقق فقط من مستحقات السنة التي يتم الترحيل منها
        $financialStatus = $this->financeLookup->getStudentFinancialStatusForYear($student->id, $fromYear->id);
        $hasFinancialClearance = $financialStatus['status'] === InvoiceStatus::Paid->value || $financialStatus['status'] === 'paid';
        $certificateBlocked = !$hasFinancialClearance && SystemSetting::get('promotion.require_financial_clearance_for_certificate', true);

        return DB::transaction(function () use ($student, $fromYear, $newYear, $annualResult, $fromGrade, $toGrade, $toSection, $type, $hasFinancialClearance, $certificateBlocked, $processedBy) {
            // 1. إنشاء سجل الترحيل
            $promotion = Promotion::create([
                'student_id' => $student->id,
                'academic_year_id' => $fromYear->id,
                'annual_result_id' => $annualResult->id,
                'from_grade_id' => $fromGrade->id,
                'to_grade_id' => $toGrade?->id,
                'to_class_section_id' => $toSection?->id,
                'type' => $type,
                'has_financial_clearance' => $hasFinancialClearance,
                'certificate_blocked' => $certificateBlocked,
                'processed_by' => $processedBy?->id ?? auth()->id(),
                'processed_at' => now(),
            ]);

            // 2. تحديث بيانات الطالب (إذا لم يتخرج أو ينسحب)
            if (in_array($type, [PromotionType::Promoted, PromotionType::Repeated])) {
                $student->update([
                    'current_grade_id' => $toGrade?->id ?? $fromGrade->id,
                    'current_class_section_id' => $toSection?->id,
                ]);

                // 3. إنشاء سجل تسجيل جديد
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $newYear->id,
                    'grade_id' => $toGrade?->id ?? $fromGrade->id,
                    'class_section_id' => $toSection?->id,
                    'enrollment_date' => now(),
                    'enrollment_type' => $type === PromotionType::Repeated
                        ? EnrollmentType::Returning
                        : EnrollmentType::New,
                    'status' => EnrollmentStatus::Active,
                ]);
            }

            // 4. تحديث حالة التسجيل القديم
            $oldEnrollmentStatus = match ($type) {
                PromotionType::Repeated => EnrollmentStatus::Failed,
                PromotionType::Withdrawn => EnrollmentStatus::Withdrawn,
                default => EnrollmentStatus::Completed,
            };
            StudentEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $fromYear->id)
                ->update(['status' => $oldEnrollmentStatus]);

            return $promotion;
        });
    }

    /**
     * تحديد الصف التالي
     */
    protected function determineNextGrade(Student $student, AnnualResult $result): ?Grade
    {
        $currentGrade = $student->currentGrade;

        // إذا راسب، يبقى في نفس الصف
        if ($result->decision === ResultDecision::Fail) {
            return $currentGrade;
        }

        // إذا ناجح أو مُكمِّل، ينتقل للصف التالي
        return $currentGrade->nextGrade ?? $currentGrade;
    }

    /**
     * تحديد نوع الترحيل
     */
    protected function determinePromotionType(AnnualResult $result, Grade $fromGrade, ?Grade $toGrade): PromotionType
    {
        // تخرّج إذا لم يكن هناك صف تالي
        if (!$toGrade || !$fromGrade->nextGrade) {
            return PromotionType::Graduated;
        }

        // راسب
        if ($result->decision === ResultDecision::Fail) {
            return PromotionType::Repeated;
        }

        // ناجح أو مُكمِّل
        return PromotionType::Promoted;
    }

    /**
     * ترحيل جماعي
     */
    public function bulkPromote(
        Collection $students,
        AcademicYear $fromYear,
        ?User $processedBy = null
    ): array {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($students as $student) {
            try {
                $this->promote($student, $fromYear, null, $processedBy);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'student_id' => $student->id,
                    'name' => $student->full_name_ar,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * التراجع عن الترحيل
     */
    public function revert(Promotion $promotion, string $reason, ?User $user = null): bool
    {
        if ($promotion->is_reverted) {
            throw new PromotionAlreadyRevertedException();
        }

        return DB::transaction(function () use ($promotion, $reason, $user) {
            $student = $promotion->student;
            $fromYear = $promotion->academicYear;

            // 1. إعادة الطالب للصف السابق
            $student->update([
                'current_grade_id' => $promotion->from_grade_id,
                'current_class_section_id' => null,
            ]);

            // 2. حذف سجل التسجيل الجديد
            $newYear = $this->getNextAcademicYear($fromYear);
            if ($newYear) {
                StudentEnrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $newYear->id)
                    ->delete();
            }

            // 3. إعادة حالة التسجيل القديم
            StudentEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $fromYear->id)
                ->update(['status' => EnrollmentStatus::Active]);

            // 4. تحديث سجل الترحيل
            $promotion->update([
                'is_reverted' => true,
                'reverted_by' => $user?->id ?? auth()->id(),
                'reverted_at' => now(),
                'revert_reason' => $reason,
            ]);

            return true;
        });
    }

    /**
     * توزيع الطلاب على الشعب تلقائياً
     */
    public function autoDistributeToSections(Grade $grade, AcademicYear $year): int
    {
        $sections = ClassSection::where('grade_id', $grade->id)
            ->where('academic_year_id', $year->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        if ($sections->isEmpty()) {
            throw new NoSectionsAvailableException($grade->id, $year->id);
        }

        $students = $this->studentLookup->studentsInGradeWithoutSection($grade->id);

        $counts = $this->studentLookup->countStudentsInSections($sections->pluck('id'));
        $count = 0;
        $sectionIndex = 0;
        $sectionCount = $sections->count();

        foreach ($students as $student) {
            $section = $sections[$sectionIndex % $sectionCount];

            // التحقق من السعة
            $currentCount = (int) ($counts[$section->id] ?? 0);
            if ($currentCount < $section->max_capacity) {
                $student->update(['current_class_section_id' => $section->id]);

                // تحديث سجل التسجيل أيضاً
                StudentEnrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $year->id)
                    ->update(['class_section_id' => $section->id]);

                $counts[$section->id] = $currentCount + 1;
                $count++;
            }

            $sectionIndex++;
        }

        return $count;
    }

    /**
     * إغلاق السنة الدراسية وتفعيل الجديدة
     */
    public function closeYearAndActivateNext(AcademicYear $currentYear): AcademicYear
    {
        $nextYear = $this->requireNextYear($currentYear);

        // ✅ التحقق من اكتمال الترحيل باستخدام Enrollments (ثابت)
        // بدلاً من students() الديناميكي الذي يعتمد على current_class_section_id
        $eligibleCount = $this->enrollmentQueryService->eligibleForClosureCount($currentYear);

        $eligibleStudentIdsSubquery = $this->enrollmentQueryService->eligibleStudentIdsSubquery($currentYear);

        $promotionsCount = Promotion::where('academic_year_id', $currentYear->id)
            ->where('is_reverted', false)
            ->whereIn('student_id', $eligibleStudentIdsSubquery)
            ->count();

        if ($promotionsCount < $eligibleCount) {
            throw new IncompletePromotionException($promotionsCount, $eligibleCount);
        }

        $this->closeAcademicYearAction->execute($currentYear);
        $this->activateAcademicYearAction->execute($nextYear);

        return $nextYear->fresh();
    }

    /**
     * إحصائيات الترحيل
     */
    public function getStatistics(AcademicYear $year): array
    {
        $base = Promotion::where('academic_year_id', $year->id)->active();

        return [
            'total' => (clone $base)->count(),
            'promoted' => (clone $base)->where('type', PromotionType::Promoted)->count(),
            'repeated' => (clone $base)->where('type', PromotionType::Repeated)->count(),
            'graduated' => (clone $base)->where('type', PromotionType::Graduated)->count(),
            'transferred' => (clone $base)->where('type', PromotionType::Transferred)->count(),
            'withdrawn' => (clone $base)->where('type', PromotionType::Withdrawn)->count(),
            'certificate_blocked' => (clone $base)->where('certificate_blocked', true)->count(),
        ];
    }
}
