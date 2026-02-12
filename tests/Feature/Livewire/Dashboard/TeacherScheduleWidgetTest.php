<?php

namespace Tests\Feature\Livewire\Dashboard;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Attendance\Enums\AttendanceResponsibility;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Infrastructure\Context\AcademicContextService;
use App\Livewire\Dashboard\TeacherScheduleWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class TeacherScheduleWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_only_active_term_schedule(): void
    {
        $year = AcademicYear::factory()->create([
            'status' => AcademicYearStatus::Active,
        ]);

        $activeTerm = Term::factory()->active()->create([
            'academic_year_id' => $year->id,
        ]);

        $pendingTerm = Term::factory()->create([
            'academic_year_id' => $year->id,
            'status' => TermStatus::Pending,
        ]);

        Cache::forget(AcademicContextService::CACHE_KEY_YEAR);
        Cache::forget(AcademicContextService::CACHE_KEY_TERM);

        AttendanceSetting::create([
            'academic_year_id' => $year->id,
            'mode' => AttendanceMode::Checkpoints,
            'responsible_role' => AttendanceResponsibility::SubjectTeacher,
            'late_tolerance' => 0,
        ]);

        $user = User::factory()->create();
        $staff = Staff::factory()->create(['user_id' => $user->id]);
        $teacher = Teacher::factory()->create([
            'staff_id' => $staff->id,
            'user_id' => $user->id,
        ]);

        $grade = Grade::factory()->create();
        $section = ClassSection::factory()->create([
            'grade_id' => $grade->id,
            'academic_year_id' => $year->id,
        ]);

        $subject = Subject::factory()->create();
        $template = TimetableTemplate::factory()->create([
            'academic_year_id' => $year->id,
        ]);

        $slot = TimeSlot::factory()->create([
            'template_id' => $template->id,
            'day_of_week' => now()->dayOfWeek,
            'order_index' => 0,
            'is_attendance_checkpoint' => true,
        ]);

        $activeOffering = CourseOffering::create([
            'academic_year_id' => $year->id,
            'term_id' => $activeTerm->id,
            'class_section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        $pendingOffering = CourseOffering::create([
            'academic_year_id' => $year->id,
            'term_id' => $pendingTerm->id,
            'class_section_id' => $section->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
        ]);

        $activeTimetable = Timetable::create([
            'class_section_id' => $section->id,
            'time_slot_id' => $slot->id,
            'course_offering_id' => $activeOffering->id,
            'term_id' => $activeTerm->id,
        ]);

        Timetable::create([
            'class_section_id' => $section->id,
            'time_slot_id' => $slot->id,
            'course_offering_id' => $pendingOffering->id,
            'term_id' => $pendingTerm->id,
        ]);

        $component = Livewire::actingAs($user)->test(TeacherScheduleWidget::class);
        $timetables = collect($component->get('timetables'));

        $this->assertCount(1, $timetables);
        $this->assertSame($activeTimetable->id, $timetables->first()->id);
    }
}
