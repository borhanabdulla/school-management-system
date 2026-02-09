<?php

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Grading\Actions\AggregateGradebookToTemplateMarksAction;
use Illuminate\Support\Facades\DB;

// Force clean state for test
echo "Starting PR2 Verification Test...\n";

DB::transaction(function () {
    // 1. Create Dummy Data
    $student = Student::factory()->create();
    $courseOffering = CourseOffering::factory()->create();
    $term = Term::findOrFail($courseOffering->term_id);
    $academicYearId = $term->academic_year_id;
    $gradeId = $courseOffering->classSection?->grade_id;
    $subjectId = $courseOffering->subject_id;

    if (! $gradeId || ! $subjectId) {
        throw new RuntimeException('Missing grade_id or subject_id on course offering.');
    }

    $config = SubjectGradingConfig::query()
        ->where('subject_id', $subjectId)
        ->where('grade_id', $gradeId)
        ->where('term_id', $term->id)
        ->firstOrFail();

    $templateCategory = TemplateCategory::query()
        ->where('grading_template_id', $config->grading_template_id)
        ->orderBy('order')
        ->firstOrFail();

    $categoryKey = 'homework';
    $categoryLabel = 'Homework';

    GradebookSettings::updateOrCreate(
        ['academic_year_id' => $academicYearId],
        [
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories([
                ['key' => $categoryKey, 'label' => $categoryLabel, 'max_score' => 10],
            ]),
            'attendance_deduct_after' => 3,
            'attendance_deduct_per_absence' => 0.5,
            'attendance_max_score' => 5,
            'allow_custom_categories' => true,
        ]
    );

    // Create 3 Months
    $months = [];
    for ($i = 1; $i <= 3; $i++) {
        $months[] = GradebookMonth::forceCreate([
            'term_id' => $term->id,
            'academic_year_id' => $term->academic_year_id,
            'name' => "Month $i",
            'start_date' => now()->addMonths($i),
            'end_date' => now()->addMonths($i)->addDays(20),
            'order' => $i
        ]);
    }

    MonthlyCategoryMapping::create([
        'academic_year_id' => $academicYearId,
        'term_id' => $term->id,
        'grade_id' => $gradeId,
        'subject_id' => $subjectId,
        'category_key' => $categoryKey,
        'template_category_id' => $templateCategory->id,
        'aggregation_rule' => 'sum',
        'missing_months_policy' => 'ignore',
    ]);

    echo "Created Student: {$student->id}, Term: {$term->id}, TemplateCategory: {$templateCategory->id}\n";

    // 2. Insert Monthly Grades
    // Month 1: 8/10
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $months[0]->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 8,
        'max_score' => 10,
    ]);

    // Month 2: 7/10
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $months[1]->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 7,
        'max_score' => 10,
    ]);

    // Month 3: 9/10
    MonthlyGrade::create([
        'student_id' => $student->id,
        'course_offering_id' => $courseOffering->id,
        'gradebook_month_id' => $months[2]->id,
        'category_key' => $categoryKey,
        'category' => $categoryLabel,
        'score' => 9,
        'max_score' => 10,
    ]);

    echo "Inserted 3 Monthly Grades.\n";

    // 3. Run Aggregation Action
    app(AggregateGradebookToTemplateMarksAction::class)->execute(
        offering: $courseOffering,
        studentId: $student->id,
        termId: $term->id
    );

    // 4. Verify StudentMark
    $mark = StudentMark::where('student_id', $student->id)
        ->where('course_offering_id', $courseOffering->id)
        ->where('template_category_id', $templateCategory->id)
        ->where('term_id', $term->id)
        ->first();

    if ($mark) {
        echo "✅ StudentMark Found!\n";

        $expectedRaw = 24.0;
        $expectedMax = 30.0;
        $expectedScaled = ($expectedRaw / $expectedMax) * (float) $templateCategory->weight;

        echo "Raw Score: " . $mark->raw_score . " (Expected {$expectedRaw})\n";
        echo "Scaled Score: " . $mark->scaled_score . " (Expected ~{$expectedScaled})\n";

        if (abs($mark->raw_score - $expectedRaw) < 0.1) {
            echo "✅ Calculation Correct!\n";
        } else {
            echo "❌ Calculation Mismatch!\n";
        }
    } else {
        echo "❌ StudentMark NOT Found!\n";
    }

    // Rollback to clean up
    // DB::rollBack(); // Uncomment to keep DB clean, but for now we might want to see data
    // For this test script, we'll let it persist or rollback based on preference. 
    // Let's rollback to be safe.
    throw new Exception("Test Complete - Rolling back");
});
