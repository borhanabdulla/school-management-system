<?php

namespace Database\Factories\Domains\Academic\Timetable\Models;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Timetable\Models\Timetable;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimetableFactory extends Factory
{
    protected $model = Timetable::class;

    public function definition(): array
    {
        return [
            'class_section_id' => ClassSection::factory(),
            'time_slot_id' => TimeSlot::factory(),
            'course_offering_id' => CourseOffering::factory(),
            'term_id' => function (array $attributes) {
                if (!empty($attributes['course_offering_id'])) {
                    return CourseOffering::query()->whereKey($attributes['course_offering_id'])->value('term_id');
                }

                return Term::factory()->active();
            },
        ];
    }

    public function forClassSection(ClassSection $classSection): static
    {
        return $this->state(fn(array $attributes) => [
            'class_section_id' => $classSection->id,
        ]);
    }

    public function forTimeSlot(TimeSlot $timeSlot): static
    {
        return $this->state(fn(array $attributes) => [
            'time_slot_id' => $timeSlot->id,
        ]);
    }

    public function forCourseOffering(CourseOffering $courseOffering): static
    {
        return $this->state(fn(array $attributes) => [
            'course_offering_id' => $courseOffering->id,
            'term_id' => $courseOffering->term_id,
        ]);
    }
}
