<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use Illuminate\Support\Collection;

class StudentPerformanceService
{
    public function __construct(
        protected GradingCalculatorService $calculator
    ) {
    }

    /**
     * Get overall performance summary.
     */
    public function getPerformanceSummary(Student $student)
    {
        $courses = $this->getDetailedCourses($student);

        if (empty($courses)) {
            return [
                'overall_average' => 0,
                'best_performing' => null,
                'needs_attention' => null,
                'total_assessments' => 0,
            ];
        }

        $collection = collect($courses);
        $overallAverage = $collection->avg('score');
        $best = $collection->sortByDesc('score')->first();
        $worst = $collection->sortBy('score')->first();
        $totalAssessments = $collection->sum('assessments_count');

        return [
            'overall_average' => round($overallAverage, 1),
            'best_performing' => $best,
            'needs_attention' => $worst,
            'total_assessments' => $totalAssessments,
        ];
    }

    /**
     * Get detailed course list with ratings.
     */
    public function getDetailedCourses(Student $student)
    {
        $currentSection = $student->currentClassSection;

        if (!$currentSection) {
            return [];
        }

        // Fetch courses for the student's section
        $courses = CourseOffering::where('class_section_id', $currentSection->id)
            ->with(['subject', 'academicYear'])
            ->get();

        $results = [];

        foreach ($courses as $course) {
            // Find Grading Config
            $config = SubjectGradingConfig::where('subject_id', $course->subject_id)
                ->where('grade_id', $currentSection->grade_id)
                ->with('template.categories')
                ->first();

            if (!$config || !$config->template) {
                continue;
            }

            // Fetch Marks
            $marks = StudentMark::where('student_id', $student->id)
                ->where('course_offering_id', $course->id)
                ->with(['assessment.category', 'category'])
                ->get();

            // Calculate Grade
            try {
                $gradeData = $this->calculator->calculateStudentGrade(
                    $student,
                    $course,
                    $config->template,
                    $marks,
                    (float) $config->pass_score
                );

                $percentage = $gradeData['percentage'];
                $rating = $this->calculator->resolveGradeLabel($percentage);
                $ratingColor = $this->getRatingColor($percentage);

                $results[] = (object) [
                    'id' => $course->id,
                    'name' => $course->subject->name,
                    'score' => round($percentage, 1),
                    'rating' => $rating,
                    'rating_color' => $ratingColor,
                    'assessments_count' => $marks->count(),
                    'is_passing' => $gradeData['passed'],
                    'total_max' => $gradeData['max'],
                ];

            } catch (\Exception $e) {
                // Should log error but for now skip or add partial
                // \Log::error("Grade calculation error for student {$student->id} course {$course->id}: " . $e->getMessage());
                continue;
            }
        }

        return $results;
    }

    /**
     * Generate automated recommendations.
     */
    public function getRecommendations(Student $student)
    {
        $courses = collect($this->getDetailedCourses($student));
        $recommendations = [];

        if ($courses->isEmpty()) {
            return ['يرجى إكمال تقييمات المواد للحصول على توصيات دقيقة.'];
        }

        // Academic Performance Recommendations
        $lowPerformance = $courses->where('score', '<', 60);
        $highPerformance = $courses->where('score', '>=', 90);

        if ($highPerformance->isNotEmpty()) {
            $subjects = $highPerformance->pluck('name')->implode(' و');
            $recommendations[] = "أداء متميز في {$subjects}، ينصح بالمشاركة في المسابقات المدرسية والتحديات المتقدمة.";
        }

        if ($lowPerformance->isNotEmpty()) {
            $subjects = $lowPerformance->pluck('name')->implode(' و');
            $recommendations[] = "يحتاج إلى دعم إضافي وخطط علاجية في {$subjects} لرفع مستوى التحصيل.";
        }

        // Attendance Recommendation (Mock for now or fetch real)
        // $attendanceRate = ...
        // if ($attendanceRate < 90) ...

        if (empty($recommendations)) {
            $recommendations[] = 'مستوى الطالب متوازن بشكل عام، ينصح بالاستمرار في المتابعة الدورية.';
        }

        return $recommendations;
    }

    private function getRatingColor(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'green',
            $percentage >= 80 => 'blue',
            $percentage >= 70 => 'indigo',
            $percentage >= 60 => 'yellow',
            default => 'red',
        };
    }
}
