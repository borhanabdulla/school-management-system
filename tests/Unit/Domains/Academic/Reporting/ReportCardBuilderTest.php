<?php

namespace Tests\Unit\Domains\Academic\Reporting;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Reporting\Support\GradeLabelResolver;
use App\Domains\Academic\Reporting\Services\ReportCardBuilder;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Subject\Models\GradeSubject;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReportCardBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_uses_stored_grade_letter_when_available(): void
    {
        [$student, $term] = $this->seedResultWithGradeLetter('B');

        $report = app(ReportCardBuilder::class)->build($student->id, $term->id);

        $this->assertSame(1, count($report['subjects']));
        $subjectRow = $report['subjects'][0];
        $this->assertEquals(70, $subjectRow['total_score']);
        $this->assertEquals(100, $subjectRow['max_score']);
        $this->assertEquals(70, $subjectRow['percentage']);
        $this->assertEquals('B', $subjectRow['grade_label']);
        $this->assertTrue($subjectRow['is_passed']);
        $this->assertEquals(50, $subjectRow['pass_score']);

        $this->assertEquals(70, $report['totals']['total_score']);
        $this->assertEquals(100, $report['totals']['max_score']);
        $this->assertEquals(70, $report['totals']['percentage']);
        $this->assertEquals(1, $report['totals']['passed_count']);
        $this->assertEquals(0, $report['totals']['failed_count']);
        $expectedTotalLabel = GradeLabelResolver::forPercentage(70);
        $this->assertEquals($expectedTotalLabel, $report['totals']['grade_label']);

        $this->assertEquals($student->id, $report['meta']['student']['id']);
        $this->assertEquals($term->id, $report['meta']['term']['id']);
    }

    public function test_build_resolves_grade_letter_when_missing(): void
    {
        [$student, $term] = $this->seedResultWithGradeLetter(null);

        $report = app(ReportCardBuilder::class)->build($student->id, $term->id);

        $subjectRow = $report['subjects'][0];
        $expectedLabel = GradeLabelResolver::forPercentage(70);

        $this->assertEquals($expectedLabel, $subjectRow['grade_label']);
    }

    private function seedResultWithGradeLetter(?string $gradeLetter): array
    {
        $classSection = ClassSection::factory()->create();
        $subject = Subject::factory()->create();
        $grade = $classSection->grade;
        $term = Term::factory()->create(['academic_year_id' => $classSection->academic_year_id]);

        CourseOffering::factory()->create([
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
            'term_id' => $term->id,
        ]);

        GradeSubject::create([
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'term_type' => 'full_year',
        ]);

        $student = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
        ]);

        $courseOffering = CourseOffering::query()
            ->where('class_section_id', $classSection->id)
            ->where('subject_id', $subject->id)
            ->firstOrFail();

        TermResult::create([
            'student_id' => $student->id,
            'course_offering_id' => $courseOffering->id,
            'term_id' => $term->id,
            'coursework_score' => 30,
            'exam_score' => 40,
            'total_score' => 70,
            'max_score' => 100,
            'percentage' => 70,
            'grade_letter' => $gradeLetter,
            'is_passed' => true,
        ]);

        return [$student, $term];
    }
}
