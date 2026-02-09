<?php

namespace Database\Factories\Domains\Academic\Attendance\Models;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Attendance\Enums\AttendanceStatus;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $yearFactory = AcademicYear::factory();

        return [
            'student_id' => Student::factory(),
            'academic_year_id' => $yearFactory,
            'class_section_id' => ClassSection::factory()->state([
                'academic_year_id' => $yearFactory,
            ]),
            'term_id' => Term::factory()->state([
                'academic_year_id' => $yearFactory,
            ]),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d'),
            'time_slot_id' => null, // يتم تعيينه عبر state أو hasExisting
            'status' => $this->faker->randomElement([
                AttendanceStatus::PRESENT->value,
                AttendanceStatus::PRESENT->value,
                AttendanceStatus::PRESENT->value, // 60% حضور
                AttendanceStatus::ABSENT->value,  // 20% غياب
                AttendanceStatus::LATE->value,    // 20% تأخير
            ]),
            'remarks' => $this->faker->optional(0.1)->sentence(),
            'delay_minutes' => 0,
            'recorded_by' => null,
        ];
    }

    /**
     * حالة الحضور الكامل
     */
    public function present(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AttendanceStatus::PRESENT->value,
            'delay_minutes' => 0,
        ]);
    }

    /**
     * حالة الغياب
     */
    public function absent(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AttendanceStatus::ABSENT->value,
            'delay_minutes' => 0,
        ]);
    }

    /**
     * حالة التأخير
     */
    public function late(int $minutes = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AttendanceStatus::LATE->value,
            'delay_minutes' => $minutes ?? $this->faker->numberBetween(5, 30),
        ]);
    }

    /**
     * حالة الإعذار
     */
    public function excused(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => AttendanceStatus::EXCUSED->value,
            'remarks' => $this->faker->sentence(),
        ]);
    }

    /**
     * مع مسجّل معين
     */
    public function recordedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'recorded_by' => $user->id,
        ]);
    }

    /**
     * لطالب محدد
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn(array $attributes) => [
            'student_id' => $student->id,
            'class_section_id' => $student->enrollments()->latest()->first()?->class_section_id
                ?? $attributes['class_section_id'],
        ]);
    }

    /**
     * لحصة محددة
     */
    public function forTimeSlot(TimeSlot $timeSlot): static
    {
        return $this->state(fn(array $attributes) => [
            'time_slot_id' => $timeSlot->id,
        ]);
    }

    /**
     * لشعبة محددة
     */
    public function forClassSection(ClassSection $classSection): static
    {
        return $this->state(fn(array $attributes) => [
            'class_section_id' => $classSection->id,
        ]);
    }

    /**
     * لتاريخ محدد
     */
    public function forDate(string $date): static
    {
        return $this->state(fn(array $attributes) => [
            'date' => $date,
        ]);
    }
}
