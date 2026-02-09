<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grading\Data\GradeScoreData;
use App\Domains\Academic\Grading\Data\SyncResultData;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Grading\Actions\AggregateGradebookToTemplateMarksAction;
use Illuminate\Support\Facades\Log;

/**
 * GradeSyncService - خدمة مزامنة الدرجات
 *
 * تقوم بمزامنة الدرجات من مصادر مختلفة (شهري، حضور، واجبات) إلى StudentMark
 * باستخدام معادلة AGS للمعايرة (Normalization).
 * 
 * المعادلة: normalized = (raw / max) × weight
 */
class GradeSyncService
{
    public function __construct(
        private GradingCalculatorService $calculator
    ) {
    }

    // ═══════════════════════════════════════════════════════════════
    // المزامنة من الدرجات الشهرية
    // ═══════════════════════════════════════════════════════════════

    /**
     * مزامنة درجة شهرية → StudentMark
     */
    public function syncFromMonthlyGrade(MonthlyGrade $grade): SyncResultData
    {
        $termId = $grade->gradebookMonth?->term_id ?? $grade->courseOffering->term_id;
        if ($termId) {
            app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);
        }

        $courseOffering = $grade->courseOffering ?? CourseOffering::find($grade->course_offering_id);
        if (!$courseOffering) {
            return SyncResultData::failed(
                studentId: $grade->student_id,
                categoryId: 0,
                error: 'لا توجد مادة مرتبطة بالدرجة الشهرية',
                source: 'monthly'
            );
        }

        app(AggregateGradebookToTemplateMarksAction::class)
            ->execute($courseOffering, $grade->student_id, $termId);

        return SyncResultData::aggregated(
            studentId: $grade->student_id,
            categoryId: 0,
            aggregatedScore: (float) ($grade->score ?? 0),
            source: 'monthly'
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // المزامنة من الحضور
    // ═══════════════════════════════════════════════════════════════

    /**
     * مزامنة درجة الحضور من سجل الغياب
     */
    public function syncAttendanceGrade(
        Student $student,
        ClassSection $classSection,
        GradebookMonth $month
    ): SyncResultData {
        if (!$month->term) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'الترم غير مرتبط بالشهر',
                source: 'attendance'
            );
        }

        app(AcademicWriteGuard::class)->assertTermNotCompleted($month->term_id);

        // جلب إعدادات الـ Gradebook (مع fallback الإنشائي)
        $settings = GradebookSettings::getForYear($month->term->academic_year_id);

        if (!$settings) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'لا توجد إعدادات للدفتر',
                source: 'attendance'
            );
        }

        $normalizedCategories = GradebookSettings::normalizeMonthlyCategories(
            $settings->monthly_categories ?? []
        );
        $attendanceCategory = collect($normalizedCategories)
            ->firstWhere('is_attendance', true)
            ?? collect(GradebookSettings::getDefaultCategories())->firstWhere('is_attendance', true);

        if (!$attendanceCategory) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'لا توجد فئة مواظبة في الإعدادات',
                source: 'attendance'
            );
        }

        $attendanceMaxScore = (float) ($attendanceCategory['max_score'] ?? $settings->attendance_max_score);
        $attendanceCategoryLabel = (string) ($attendanceCategory['label'] ?? '');
        $attendanceCategoryKey = (string) ($attendanceCategory['key'] ?? '');

        $courseOfferings = CourseOffering::where('class_section_id', $classSection->id)
            ->where('term_id', $month->term_id)
            ->get();

        if ($courseOfferings->isEmpty()) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'لا توجد مواد مرتبطة بهذه الشعبة في هذا الترم',
                source: 'attendance'
            );
        }

        $synced = 0;
        $firstCategoryId = 0;
        $firstNormalizedScore = null;

        $attendanceMode = $this->resolveAttendanceMode($classSection->academic_year_id);
        if ($attendanceMode === null) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'نمط الحضور غير مدعوم حالياً لمزامنة الدرجات',
                source: 'attendance'
            );
        }
        $slotMap = null;

        if ($attendanceMode === 'per_subject') {
            $slotMap = Timetable::where('class_section_id', $classSection->id)
                ->whereIn('course_offering_id', $courseOfferings->pluck('id'))
                ->get(['course_offering_id', 'time_slot_id'])
                ->groupBy('course_offering_id')
                ->map(fn($rows) => $rows->pluck('time_slot_id')->unique()->values()->all());
        }

        foreach ($courseOfferings as $courseOffering) {
            $category = $this->findCategoryForCourseOffering(
                courseOffering: $courseOffering,
                mappingType: 'attendance',
                categoryName: $attendanceCategoryLabel ?: null
            );

            if (!$category) {
                Log::warning('Attendance sync skipped: no attendance category', [
                    'student_id' => $student->id,
                    'course_offering_id' => $courseOffering->id,
                ]);
                continue;
            }

            if ($attendanceMode === 'per_subject') {
                $slotIds = $slotMap?->get($courseOffering->id, []);
                if (empty($slotIds)) {
                    Log::warning('Attendance sync skipped: no timetable slots for course offering', [
                        'student_id' => $student->id,
                        'course_offering_id' => $courseOffering->id,
                    ]);
                    continue;
                }

                $absenceCount = Attendance::where('student_id', $student->id)
                    ->where('class_section_id', $classSection->id)
                    ->whereIn('time_slot_id', $slotIds)
                    ->whereBetween('date', [$month->start_date, $month->end_date])
                    ->where('status', AttendanceStatus::ABSENT->value)
                    ->count();
            } else {
                $absenceCount = Attendance::where('student_id', $student->id)
                    ->where('class_section_id', $classSection->id)
                    ->whereBetween('date', [$month->start_date, $month->end_date])
                    ->where('status', AttendanceStatus::ABSENT->value)
                    ->count();
            }

            // حساب الدرجة باستخدام Service
            $score = $this->calculator->calculateAttendanceScore(
                absenceCount: $absenceCount,
                settings: $settings
            );

            $normalizedScore = $this->calculator->normalize(
                rawScore: $score,
                maxScore: $attendanceMaxScore,
                weight: (float) $category->weight
            );

            MonthlyGrade::withoutEvents(function () use ($student, $courseOffering, $month, $attendanceCategoryLabel, $attendanceCategoryKey, $score, $attendanceMaxScore): void {
                $grade = MonthlyGrade::query()
                    ->where([
                        'student_id' => $student->id,
                        'course_offering_id' => $courseOffering->id,
                        'gradebook_month_id' => $month->id,
                    ])
                    ->where(function ($query) use ($attendanceCategoryKey, $attendanceCategoryLabel) {
                        $query->where('category_key', $attendanceCategoryKey)
                            ->orWhere('category', $attendanceCategoryLabel);
                    })
                    ->first();

                if (!$grade) {
                    $grade = new MonthlyGrade([
                        'student_id' => $student->id,
                        'course_offering_id' => $courseOffering->id,
                        'gradebook_month_id' => $month->id,
                    ]);
                }

                $grade->fill([
                    'category_key' => $attendanceCategoryKey,
                    'category' => $attendanceCategoryLabel,
                    'score' => $score,
                    'max_score' => $attendanceMaxScore,
                    'graded_by' => null,
                ]);

                $grade->save();
            });

            app(AggregateGradebookToTemplateMarksAction::class)
                ->execute($courseOffering, $student->id, $courseOffering->term_id);

            $synced++;
            $firstCategoryId = $firstCategoryId ?: $category->id;
            $firstNormalizedScore = $firstNormalizedScore ?? $normalizedScore;
        }

        if ($synced === 0) {
            return SyncResultData::failed(
                studentId: $student->id,
                categoryId: 0,
                error: 'لا توجد فئة حضور قابلة للمزامنة',
                source: 'attendance'
            );
        }

        return SyncResultData::success(
            studentId: $student->id,
            categoryId: $firstCategoryId,
            rawScore: $score ?? 0,
            normalizedScore: $firstNormalizedScore ?? 0,
            source: 'attendance'
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // المزامنة من الواجبات
    // ═══════════════════════════════════════════════════════════════

    /**
     * مزامنة درجة الواجبات من HomeworkSubmission
     */
    public function syncHomeworkGrade(HomeworkSubmission $submission): void
    {
        $homework = $submission->homework;

        if (!$homework->assessment_id) {
            Log::warning('Homework sync skipped: missing assessment_id', [
                'homework_id' => $homework->id,
                'submission_id' => $submission->id,
            ]);
            return;
        }

        $assessment = $homework->assessment;
        $studentId = $submission->student_id;
        $linkedHomeworks = $assessment->homeworks;

        if ($linkedHomeworks->isEmpty()) {
            Log::warning('Homework sync skipped: assessment has no linked homeworks', [
                'assessment_id' => $assessment->id,
                'submission_id' => $submission->id,
            ]);
            return;
        }

        $totalObtainedScore = 0;
        $totalMaxScore = 0;

        foreach ($linkedHomeworks as $hw) {
            $sub = $hw->submissions()->where('student_id', $studentId)->first();
            if ($sub && $sub->score !== null) {
                $totalObtainedScore += $sub->score;
            }
            $totalMaxScore += $hw->max_score;
        }

        if ($totalMaxScore == 0) {
            return;
        }

        // AGS Normalization via Service
        $weight = (float) ($assessment->weight ?? 0);
        if ($weight <= 0) {
            $weight = (float) $assessment->max_score;
        }

        $normalizedScore = $this->calculator->normalize(
            rawScore: (float) $totalObtainedScore,
            maxScore: (float) $totalMaxScore,
            weight: $weight
        );

        // GUARD: منع الكتابة بعد إغلاق الترم
        $termId = $assessment->courseOffering?->term_id;
        if ($termId) {
            app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);
        }

        StudentMark::updateOrCreate(
            [
                'student_id' => $studentId,
                'course_offering_id' => $assessment->course_offering_id,
                'assessment_id' => $assessment->id,
            ],
            [
                'raw_score' => (float) $totalObtainedScore,
                'scaled_score' => $normalizedScore,
                'graded_by_user_id' => auth()->id(),
                'academic_year_id' => $assessment->courseOffering?->academic_year_id,
                'term_id' => $assessment->courseOffering?->term_id,
            ]
        );

        Log::info("تمت مزامنه درجة الواجب المنزلي للطالب {$studentId}");
    }

    // ═══════════════════════════════════════════════════════════════
    // Helper Methods
    // ═══════════════════════════════════════════════════════════════

    private function getTemplateCategories(CourseOffering $courseOffering)
    {
        if (!$courseOffering->classSection) {
            return collect();
        }

        $config = SubjectGradingConfig::where('subject_id', $courseOffering->subject_id)
            ->where('grade_id', $courseOffering->classSection->grade_id)
            ->where('term_id', $courseOffering->term_id)
            ->first();

        $template = $config?->template;
        if (!$template) {
            return collect();
        }

        return $template->categories()->get();
    }

    private function findCategoryForCourseOffering(
        CourseOffering $courseOffering,
        string $mappingType,
        ?string $categoryName = null
    ): ?TemplateCategory {
        $categories = $this->getTemplateCategories($courseOffering);
        if ($categories->isEmpty()) {
            return null;
        }

        $filtered = $categories->where('mapping_type', $mappingType);
        if ($filtered->isEmpty()) {
            return null;
        }

        if ($categoryName) {
            $matched = $filtered->firstWhere('name', $categoryName);
            if ($matched) {
                return $matched;
            }
        }

        return $filtered->first();
    }

    private function resolveAttendanceMode(int $academicYearId): ?string
    {
        $settings = AttendanceSetting::where('academic_year_id', $academicYearId)->first();

        if (!$settings) {
            return 'per_section';
        }

        return match ($settings->mode) {
            AttendanceMode::PerPeriod => 'per_subject',
            AttendanceMode::Checkpoints => 'per_section',
            AttendanceMode::Daily => null,
        };
    }
}
