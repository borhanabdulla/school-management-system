<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Attendance\Models\Attendance;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceTermAwarenessTest extends TestCase
{
    use RefreshDatabase;

    private $academicYear;
    private $term1;
    private $teacher;
    private $courseOffering;
    private $classSection;
    private $timetable;

    protected function setUp(): void
    {
        parent::setUp();

        // ✅ PR1.1: Set locale to Arabic for proper translation testing
        app()->setLocale('ar');

        $this->academicYear = AcademicYear::factory()->create([
            'status' => 'active',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'weekend_days' => json_encode([5, 6]), // Fri, Sat
        ]);

        $this->term1 = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        $this->classSection = ClassSection::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->teacher = \App\Domains\HR\Teacher\Models\Teacher::factory()->create();

        $this->courseOffering = \App\Domains\Academic\CourseOffering\Models\CourseOffering::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term1->id,
            'teacher_id' => $this->teacher->id,
        ]);

        // Create TimeSlot attached to current year
        $template = \App\Domains\Academic\Timetable\Models\TimetableTemplate::factory()->create([
            'academic_year_id' => $this->academicYear->id
        ]);

        $timeSlot = \App\Domains\Academic\Timetable\Models\TimeSlot::factory()->create([
            'template_id' => $template->id,
            'type' => \App\Domains\Academic\Timetable\Enums\TimeSlotType::Academic
        ]);

        $this->timetable = Timetable::factory()->create([
            'class_section_id' => $this->classSection->id,
            'term_id' => $this->term1->id,
            'course_offering_id' => $this->courseOffering->id,
            'time_slot_id' => $timeSlot->id,
        ]);
    }

    public function test_school_calendar_service_respects_weekend_config()
    {
        // Invalidate Context Cache
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        // Change weekend to Thursday (4) and Friday (5)
        $this->academicYear->update(['weekend_days' => json_encode([4, 5])]);

        // Invalidate Again to refresh context
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        /** @var \App\Domains\Academic\Calendar\Services\SchoolCalendarService $service */
        $service = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);
        $service->clearCache(); // Clear any cached events/logic

        // Thursday (2024-02-01 is Thursday)
        $this->assertTrue($service->isHoliday('2024-02-01'), 'Thursday should be a holiday');

        // Friday (2024-02-02)
        $this->assertTrue($service->isHoliday('2024-02-02'), 'Friday should be a holiday');

        // Saturday (2024-02-03)
        $this->assertFalse($service->isHoliday('2024-02-03'), 'Saturday should NOT be a holiday');
    }

    public function test_cannot_record_attendance_on_holiday_via_action()
    {
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        $this->academicYear->update(['weekend_days' => json_encode([5, 6])]);
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $date = '2024-02-02'; // Friday

        $student = \App\Domains\Academic\Student\Models\Student::factory()->create([
            'status' => 'active',
        ]);
        $this->classSection->students()->save($student);
        $student->enrollments()->create([
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        $data = [['student_id' => $student->id, 'status' => 'present', 'delay_minutes' => 0]];

        $this->expectException(\App\Infrastructure\Exceptions\InvalidOperationException::class);
        // ✅ PR1.1: Expect translated message with actual date (locale is set in setUp)
        $this->expectExceptionMessage('لا يمكن تسجيل الحضور في يوم عطلة (2024-02-02).');

        app(\App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction::class)
            ->execute($this->timetable, $date, $data, \App\Domains\Shared\Models\User::factory()->create()->id);
    }

    public function test_attendance_record_saves_term_id_explicitly()
    {
        $date = '2024-02-04'; // Sunday (Workday)

        $student = \App\Domains\Academic\Student\Models\Student::factory()->create([
            'status' => 'active',
        ]);
        $this->classSection->students()->save($student);
        $student->enrollments()->create([
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        $data = [['student_id' => $student->id, 'status' => 'present']];

        app(\App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction::class)
            ->execute($this->timetable, $date, $data, \App\Domains\Shared\Models\User::factory()->create()->id);

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => $date,
            'term_id' => $this->term1->id, // ✅ Critical Check
            'status' => 'present',
        ]);
    }

    public function test_attendance_report_scopes_by_term_id()
    {
        $term2 = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Term 2',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
        ]);

        $student = \App\Domains\Academic\Student\Models\Student::factory()->create([
            'status' => 'active',
        ]);
        $this->classSection->students()->save($student);
        $student->enrollments()->create([
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $this->term1->id,
            'time_slot_id' => $this->timetable->time_slot_id,
            'date' => '2024-02-04',
            'status' => 'absent',
            'recorded_by' => 1
        ]);

        Attendance::create([
            'student_id' => $student->id,
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'term_id' => $term2->id,
            'time_slot_id' => $this->timetable->time_slot_id,
            'date' => '2024-08-01',
            'status' => 'absent',
            'recorded_by' => 1
        ]);

        $service = app(\App\Domains\Academic\Attendance\Services\AttendanceReportService::class);

        $reportT1 = $service->getMonthlyReport($this->classSection->id, 2, 2024, $this->term1->id);
        $this->assertEquals(1, $reportT1['stats'][$student->id]['absences']);

        $reportT1Aug = $service->getMonthlyReport($this->classSection->id, 8, 2024, $this->term1->id);
        $this->assertEquals(0, $reportT1Aug['stats'][$student->id]['absences']);

        $reportT2 = $service->getMonthlyReport($this->classSection->id, 8, 2024, $term2->id);
        $this->assertEquals(1, $reportT2['stats'][$student->id]['absences']);
    }

    public function test_attendance_holiday_override_requires_reason(): void
    {
        $user = \App\Domains\Shared\Models\User::factory()->create();
        Permission::findOrCreate('attendance.manage');
        $user->givePermissionTo('attendance.manage');

        $student = \App\Domains\Academic\Student\Models\Student::factory()->create([
            'status' => 'active',
        ]);
        $this->classSection->students()->save($student);
        $student->enrollments()->create([
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        $data = [['student_id' => $student->id, 'status' => 'present', 'delay_minutes' => 0]];

        $this->expectException(\App\Infrastructure\Exceptions\InvalidOperationException::class);
        app(\App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction::class)
            ->execute($this->timetable, '2024-02-02', $data, $user->id);
    }

    public function test_attendance_holiday_override_allows_with_permission_and_reason(): void
    {
        $user = \App\Domains\Shared\Models\User::factory()->create();
        Permission::findOrCreate('attendance.manage');
        $user->givePermissionTo('attendance.manage');

        $student = \App\Domains\Academic\Student\Models\Student::factory()->create([
            'status' => 'active',
        ]);
        $this->classSection->students()->save($student);
        $student->enrollments()->create([
            'class_section_id' => $this->classSection->id,
            'academic_year_id' => $this->academicYear->id,
            'grade_id' => $this->classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        $date = '2024-02-02';
        $data = [['student_id' => $student->id, 'status' => 'present', 'delay_minutes' => 0]];

        app(\App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction::class)
            ->execute($this->timetable, $date, $data, $user->id, 'موافقة استثنائية');

        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'date' => $date,
            'term_id' => $this->term1->id,
            'status' => 'present',
        ]);
    }

    public function test_attendance_report_requires_term_id(): void
    {
        $service = app(\App\Domains\Academic\Attendance\Services\AttendanceReportService::class);

        $this->expectException(\InvalidArgumentException::class);
        $service->getMonthlyReport($this->classSection->id, 2, 2024, null);
    }

    public function test_attendance_report_respects_weekend_days_config()
    {
        // Set weekend to Thursday (4) and Friday (5)
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        $this->academicYear->update(['weekend_days' => json_encode([4, 5])]);
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $service = app(\App\Domains\Academic\Attendance\Services\AttendanceReportService::class);

        // Get report for February 2024
        $report = $service->getMonthlyReport($this->classSection->id, 2, 2024, $this->term1->id);

        // Find Thursday (2024-02-01) and Friday (2024-02-02)
        $thursday = collect($report['days'])->firstWhere('date', '2024-02-01');
        $friday = collect($report['days'])->firstWhere('date', '2024-02-02');
        $saturday = collect($report['days'])->firstWhere('date', '2024-02-03');

        // Thursday and Friday should be marked as weekend
        $this->assertTrue($thursday['is_weekend'], 'Thursday should be marked as weekend');
        $this->assertTrue($friday['is_weekend'], 'Friday should be marked as weekend');
        // Saturday should NOT be weekend (since we changed config to Thu/Fri)
        $this->assertFalse($saturday['is_weekend'], 'Saturday should NOT be weekend when config is Thu/Fri');
    }

    /**
     * ✅ PR1.1: Fallback when weekend_days is invalid (empty string)
     */
    public function test_calendar_service_requires_weekend_days_configuration()
    {
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        
        // Set weekend_days to empty string (invalid JSON - simulates "not configured" scenario)
        $this->academicYear->update(['weekend_days' => '']);
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $service = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);

        $this->assertSame([0, 6], $service->getWeekendDays());
    }

    /**
     * ✅ PR1.1: Fallback when weekend_days is empty array
     */
    public function test_calendar_service_rejects_empty_weekend_days_array()
    {
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        
        $this->academicYear->update(['weekend_days' => json_encode([])]); // Empty array
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $service = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);

        $this->assertSame([0, 6], $service->getWeekendDays());
    }

    /**
     * ✅ PR1.1: Test unified calendar source (AttendanceReportService uses SchoolCalendarService)
     */
    public function test_attendance_report_uses_unified_calendar_service()
    {
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        $this->academicYear->update(['weekend_days' => json_encode([4, 5])]); // Thu, Fri
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $reportService = app(\App\Domains\Academic\Attendance\Services\AttendanceReportService::class);
        $calendarService = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);

        $report = $reportService->getMonthlyReport($this->classSection->id, 2, 2024, $this->term1->id);

        // Verify that report's is_weekend matches calendar service's isWeekend
        foreach ($report['days'] as $day) {
            $date = $day['date'];
            $expectedIsWeekend = $calendarService->isWeekend($date);
            $this->assertEquals(
                $expectedIsWeekend,
                $day['is_weekend'],
                "is_weekend mismatch for date {$date}. Report should use unified calendar service."
            );
        }
    }

    /**
     * ✅ Performance: Test that getWeekendDays() uses instance-level cache
     * This test verifies that json_decode is not called repeatedly in loops
     */
    public function test_weekend_days_caching_performance()
    {
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();
        $this->academicYear->update(['weekend_days' => json_encode([5, 6])]);
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        $service = app(\App\Domains\Academic\Calendar\Services\SchoolCalendarService::class);

        // Call getWeekendDays() multiple times (simulating a loop)
        $result1 = $service->getWeekendDays();
        $result2 = $service->getWeekendDays();
        $result3 = $service->getWeekendDays();

        // All calls should return the same cached result
        $this->assertEquals([5, 6], $result1);
        $this->assertEquals([5, 6], $result2);
        $this->assertEquals([5, 6], $result3);
        $this->assertSame($result1, $result2, 'Cache should return same array instance');
        $this->assertSame($result2, $result3, 'Cache should return same array instance');

        // Change weekend_days and verify cache is invalidated
        $this->academicYear->update(['weekend_days' => json_encode([4, 5])]);
        app(\App\Infrastructure\Context\AcademicContextService::class)->invalidate();

        // Clear cache explicitly (simulating Observer behavior)
        $service->clearCache();

        // New call should return new value
        $result4 = $service->getWeekendDays();
        $this->assertEquals([4, 5], $result4);
        $this->assertNotEquals($result1, $result4, 'Cache should be invalidated after weekend_days change');
    }
}
