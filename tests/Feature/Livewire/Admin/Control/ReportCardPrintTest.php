<?php

namespace Tests\Feature\Livewire\Admin\Control;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Results\Models\TermResult;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Subject\Models\GradeSubject;
use App\Domains\Academic\Subject\Models\Subject;
use App\Livewire\Admin\Control\ReportCardPrint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ReportCardPrintTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_card_print_renders_term_result_totals(): void
    {
        $session = ExamSession::factory()->create();
        $classSection = ClassSection::factory()->create([
            'academic_year_id' => $session->academic_year_id,
        ]);
        $subject = Subject::factory()->create();

        $courseOffering = CourseOffering::factory()->create([
            'academic_year_id' => $session->academic_year_id,
            'term_id' => $session->term_id,
            'class_section_id' => $classSection->id,
            'subject_id' => $subject->id,
        ]);

        GradeSubject::create([
            'grade_id' => $classSection->grade_id,
            'subject_id' => $subject->id,
            'term_type' => 'full_year',
        ]);

        $studentA = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
        ]);
        $studentB = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
        ]);

        ExamSeating::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $studentA->id,
        ]);
        ExamSeating::factory()->create([
            'exam_session_id' => $session->id,
            'student_id' => $studentB->id,
        ]);

        TermResult::create([
            'student_id' => $studentA->id,
            'course_offering_id' => $courseOffering->id,
            'term_id' => $session->term_id,
            'coursework_score' => 30,
            'exam_score' => 40,
            'total_score' => 70,
            'max_score' => 100,
            'percentage' => 70,
            'grade_letter' => 'جيد',
            'is_passed' => true,
        ]);

        TermResult::create([
            'student_id' => $studentB->id,
            'course_offering_id' => $courseOffering->id,
            'term_id' => $session->term_id,
            'coursework_score' => 35,
            'exam_score' => 45,
            'total_score' => 80,
            'max_score' => 100,
            'percentage' => 80,
            'grade_letter' => 'جيد جداً',
            'is_passed' => true,
        ]);

        Livewire::test(ReportCardPrint::class, ['sessionId' => $session->id])
            ->assertSee($studentA->full_name_ar)
            ->assertSee($studentB->full_name_ar)
            ->assertSee(number_format(70, 2))
            ->assertSee(number_format(80, 2))
            ->assertSee('جيد')
            ->assertSee('جيد جداً');
    }
}
