<?php

namespace Database\Factories\Domains\Academic\Timetable\Models;

use App\Domains\Academic\Timetable\Enums\TimeSlotType;
use App\Domains\Academic\Timetable\Models\TimeSlot;
use App\Domains\Academic\Timetable\Models\TimetableTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        $hour = $this->faker->numberBetween(7, 14);
        return [
            'template_id' => TimetableTemplate::factory(),
            'day_of_week' => $this->faker->numberBetween(1, 5), // Sunday-Thursday
            'label' => 'الحصة ' . $this->faker->numberBetween(1, 8),
            'order_index' => $this->faker->numberBetween(1, 8),
            'start_time' => sprintf('%02d:00', $hour),
            'end_time' => sprintf('%02d:45', $hour),
            'type' => TimeSlotType::Academic,
            'is_attendance_checkpoint' => false,
        ];
    }

    public function academic(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => TimeSlotType::Academic,
        ]);
    }

    public function break(): static
    {
        return $this->state(fn(array $attributes) => [
            'type' => TimeSlotType::Break ,
            'label' => 'استراحة',
        ]);
    }

    public function forTemplate(TimetableTemplate $template): static
    {
        return $this->state(fn(array $attributes) => [
            'template_id' => $template->id,
        ]);
    }

    public function forDay(int $dayOfWeek): static
    {
        return $this->state(fn(array $attributes) => [
            'day_of_week' => $dayOfWeek,
        ]);
    }

    public function withOrder(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'order_index' => $order,
            'order' => $order, // for compatibility
        ]);
    }
}
