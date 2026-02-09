<?php

namespace Tests\Feature\HR;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TeacherAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_available_at_excludes_busy_teacher(): void
    {
        $year = AcademicYear::factory()->active()->create();
        $term = Term::factory()->active()->create(['academic_year_id' => $year->id]);
        $section = ClassSection::factory()->create(['academic_year_id' => $year->id]);
        $template = TimetableTemplate::factory()->create(['academic_year_id' => $year->id]);

        $timeSlot = TimeSlot::factory()->create([
            'template_id' => $template->id,
        ]);

        $busyTeacher = Teacher::factory()->create();
        $freeTeacher = Teacher::factory()->create();

        $offering = CourseOffering::factory()->create([
            'teacher_id' => $busyTeacher->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
            'class_section_id' => $section->id,
        ]);

        Timetable::create([
            'class_section_id' => $section->id,
            'time_slot_id' => $timeSlot->id,
            'course_offering_id' => $offering->id,
            'term_id' => $term->id,
        ]);

        $availableIds = Teacher::query()
            ->availableAt(Carbon::today(), $timeSlot->id)
            ->pluck('id');

        $this->assertFalse($availableIds->contains($busyTeacher->id));
        $this->assertTrue($availableIds->contains($freeTeacher->id));
    }
}
