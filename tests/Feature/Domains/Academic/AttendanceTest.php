<?php

namespace Tests\Feature\Domains\Academic;

use App\Domains\Academic\Attendance\Actions\RecordStudentAttendanceAction;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Services\AttendanceLookupService;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Shared\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    // ============================================
    // اختبارات رصد الحضور
    // ============================================

    /** @test */
    public function can_record_attendance_for_session(): void
    {
        // Arrange: إعداد البيانات
        $user = User::factory()->create();
        $classSection = ClassSection::factory()->create();
        $students = Student::factory()->count(3)->create([
            'current_class_section_id' => $classSection->id,
            'current_grade_id' => $classSection->grade_id,
        ]);

        // إنشاء enrollment للطلاب
        foreach ($students as $student) {
            $student->enrollments()->create([
                'class_section_id' => $classSection->id,
                'academic_year_id' => $classSection->academic_year_id,
                'grade_id' => $classSection->grade_id,
                'status' => 'active',
                'enrollment_date' => now(),
                'enrollment_type' => 'new',
            ]);
        }

        $term = Term::factory()->create(['academic_year_id' => $classSection->academic_year_id]);
        $timeSlot = TimeSlot::factory()->create();
        $timetable = Timetable::factory()->create([
            'class_section_id' => $classSection->id,
            'time_slot_id' => $timeSlot->id,
            'term_id' => $term->id,
        ]);

        $date = now()->format('Y-m-d');
        $attendanceData = [
            ['student_id' => $students[0]->id, 'status' => 'present', 'delay_minutes' => 0, 'remarks' => null],
            ['student_id' => $students[1]->id, 'status' => 'absent', 'delay_minutes' => 0, 'remarks' => 'مريض'],
            ['student_id' => $students[2]->id, 'status' => 'late', 'delay_minutes' => 10, 'remarks' => null],
        ];

        // Act: تنفيذ الإجراء
        $action = new RecordStudentAttendanceAction();
        $action->execute($timetable, $date, $attendanceData, $user->id);

        // Assert: التحقق من النتائج
        $this->assertDatabaseCount('attendances', 3);

        // التحقق من الطالب الحاضر
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[0]->id,
            'status' => 'present',
            'delay_minutes' => 0,
            'academic_year_id' => $classSection->academic_year_id,
        ]);

        // التحقق من الطالب الغائب
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[1]->id,
            'status' => 'absent',
            'remarks' => 'مريض',
            'academic_year_id' => $classSection->academic_year_id,
        ]);

        // التحقق من الطالب المتأخر
        $this->assertDatabaseHas('attendances', [
            'student_id' => $students[2]->id,
            'status' => 'late',
            'delay_minutes' => 10,
            'academic_year_id' => $classSection->academic_year_id,
        ]);
    }

    /** @test */
    public function attendance_status_values_are_valid(): void
    {
        $validStatuses = ['present', 'absent', 'late', 'excused', 'pending', 'escaped'];

        foreach (AttendanceStatus::cases() as $status) {
            $this->assertContains(
                $status->value,
                $validStatuses,
                "Invalid status: {$status->value}"
            );
        }
    }

    /** @test */
    public function can_update_existing_attendance_record(): void
    {
        $user = User::factory()->create();
        $classSection = ClassSection::factory()->create();
        $student = Student::factory()->create();
        $term = Term::factory()->create(['academic_year_id' => $classSection->academic_year_id]);
        $timeSlot = TimeSlot::factory()->create();
        $timetable = Timetable::factory()->create([
            'class_section_id' => $classSection->id,
            'time_slot_id' => $timeSlot->id,
        ]);

        $date = now()->format('Y-m-d');

        // إنشاء سجل حضور أولي
        Attendance::create([
            'student_id' => $student->id,
            'class_section_id' => $classSection->id,
            'academic_year_id' => $classSection->academic_year_id,
            'term_id' => $term->id,
            'date' => $date,
            'timetable_id' => $timetable->id,
            'time_slot_id' => $timeSlot->id,
            'status' => 'present',
            'recorded_by' => $user->id,
        ]);

        // تحديث السجل
        $attendanceData = [
            ['student_id' => $student->id, 'status' => 'absent', 'delay_minutes' => 0, 'remarks' => 'تحديث'],
        ];

        $action = new RecordStudentAttendanceAction();
        $action->execute($timetable, $date, $attendanceData, $user->id);

        // التحقق من التحديث
        $this->assertDatabaseCount('attendances', 1);
        $this->assertDatabaseHas('attendances', [
            'student_id' => $student->id,
            'status' => 'absent',
            'remarks' => 'تحديث',
            'academic_year_id' => $classSection->academic_year_id,
        ]);
    }

    /** @test */
    public function can_get_attendance_sheet_data(): void
    {
        $classSection = ClassSection::factory()->create();
        $students = Student::factory()->count(5)->create([
            'current_class_section_id' => $classSection->id,
            'current_grade_id' => $classSection->grade_id,
        ]);

        foreach ($students as $student) {
            $student->enrollments()->create([
                'class_section_id' => $classSection->id,
                'academic_year_id' => $classSection->academic_year_id,
                'grade_id' => $classSection->grade_id,
                'status' => 'active',
                'enrollment_date' => now(),
                'enrollment_type' => 'new',
            ]);
        }

        $timeSlot = TimeSlot::factory()->create();
        $timetable = Timetable::factory()->create([
            'class_section_id' => $classSection->id,
            'time_slot_id' => $timeSlot->id,
        ]);

        $service = new AttendanceLookupService();
        $sheetData = $service->getAttendanceSheetData($timetable, now()->format('Y-m-d'));

        $this->assertCount(5, $sheetData);
        $this->assertTrue($sheetData->every(fn($item) => isset($item['student_id'])));
        $this->assertTrue($sheetData->every(fn($item) => isset($item['status'])));
    }

    /** @test */
    public function inherited_absence_logic_works_correctly(): void
    {
        $user = User::factory()->create();
        $classSection = ClassSection::factory()->create();
        $student = Student::factory()->create([
            'current_class_section_id' => $classSection->id,
            'current_grade_id' => $classSection->grade_id,
        ]);
        $term = Term::factory()->create(['academic_year_id' => $classSection->academic_year_id]);

        $student->enrollments()->create([
            'class_section_id' => $classSection->id,
            'academic_year_id' => $classSection->academic_year_id,
            'grade_id' => $classSection->grade_id,
            'status' => 'active',
            'enrollment_date' => now(),
            'enrollment_type' => 'new',
        ]);

        $timeSlot1 = TimeSlot::factory()->create(['order_index' => 1]);
        $timeSlot2 = TimeSlot::factory()->create(['order_index' => 2]);

        $date = now()->format('Y-m-d');

        // رصد غياب في الحصة الأولى
        Attendance::create([
            'student_id' => $student->id,
            'class_section_id' => $classSection->id,
            'academic_year_id' => $classSection->academic_year_id,
            'term_id' => $term->id,
            'date' => $date,
            'time_slot_id' => $timeSlot1->id,
            'status' => 'absent',
            'recorded_by' => $user->id,
        ]);

        // التحقق من وراثة الغياب في الحصة الثانية
        $timetable2 = Timetable::factory()->create([
            'class_section_id' => $classSection->id,
            'time_slot_id' => $timeSlot2->id,
            'term_id' => $term->id,
        ]);

        $service = new AttendanceLookupService();
        $sheetData = $service->getAttendanceSheetData($timetable2, $date);

        $studentData = $sheetData->firstWhere('student_id', $student->id);
        $this->assertEquals('absent', $studentData['status']);
    }

    /** @test */
    public function attendance_factory_creates_valid_records(): void
    {
        $attendance = Attendance::factory()->present()->create();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'present',
        ]);
    }

    /** @test */
    public function attendance_late_state_includes_delay_minutes(): void
    {
        $attendance = Attendance::factory()->late(15)->create();

        $this->assertEquals('late', $attendance->status);
        $this->assertEquals(15, $attendance->delay_minutes);
    }
}
