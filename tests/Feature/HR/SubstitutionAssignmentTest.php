<?php

namespace Tests\Feature\HR;

use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\HR\Substitution\Services\SubstitutionService;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SubstitutionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_substitution_twice_for_same_slot_fails(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_year_id' => $year->id]);
        $section = ClassSection::factory()->create(['academic_year_id' => $year->id]);
        $template = TimetableTemplate::factory()->create(['academic_year_id' => $year->id]);
        $timeSlot = TimeSlot::factory()->create(['template_id' => $template->id]);

        $originalTeacher = Teacher::factory()->create();
        $substituteTeacher = Teacher::factory()->create();
        $offering = CourseOffering::factory()->create([
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $section->id,
        ]);

        $timetable = Timetable::create([
            'class_section_id' => $section->id,
            'time_slot_id' => $timeSlot->id,
            'course_offering_id' => $offering->id,
            'term_id' => $term->id,
        ]);

        $createdBy = User::factory()->create();

        $service = app(SubstitutionService::class);
        $date = Carbon::today();

        $service->assignSubstitute(
            $timetable->id,
            $substituteTeacher->id,
            $originalTeacher->id,
            $date,
            null,
            false,
            $createdBy->id
        );

        $this->expectException(\DomainException::class);

        $service->assignSubstitute(
            $timetable->id,
            $substituteTeacher->id,
            $originalTeacher->id,
            $date,
            null,
            false,
            $createdBy->id
        );
    }
}
