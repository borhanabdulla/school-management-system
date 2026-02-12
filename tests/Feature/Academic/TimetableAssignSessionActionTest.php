<?php

namespace Tests\Feature\Academic;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Actions\AssignSessionAction;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimetableAssignSessionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_assigning_on_non_assignable_slot(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);

        $term = Term::factory()->active()->create([
            'academic_year_id' => $year->id,
        ]);

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $subject = Subject::factory()->create();
        $teacher = Teacher::factory()->create();
        $template = TimetableTemplate::factory()->create([
            'academic_year_id' => $year->id,
        ]);
        $slot = TimeSlot::factory()->break()->create([
            'template_id' => $template->id,
        ]);

        $this->expectException(InvalidOperationException::class);

        app(AssignSessionAction::class)->execute(
            $year->id,
            $term->id,
            $section->id,
            $subject->id,
            $teacher->id,
            $slot->id
        );
    }
}
