<?php

namespace Database\Factories\Domains\Academic\Homework\Models;

use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Student\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeworkSubmissionFactory extends Factory
{
    protected $model = HomeworkSubmission::class;

    public function definition(): array
    {
        return [
            'homework_id' => Homework::factory(),
            'student_id' => Student::factory(),
            'status' => SubmissionStatus::SUBMITTED->value,
            'submitted_at' => now(),
            'file_path' => null,
            'score' => null,
            'feedback' => null,
        ];
    }

    /**
     * تم التسليم
     */
    public function submitted(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SubmissionStatus::SUBMITTED->value,
            'submitted_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * قيد الانتظار
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SubmissionStatus::PENDING->value,
            'submitted_at' => null,
        ]);
    }

    /**
     * تسليم متأخر
     */
    public function late(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SubmissionStatus::LATE->value,
            'submitted_at' => $this->faker->dateTimeBetween('-3 days', 'now'),
        ]);
    }

    /**
     * تم الرصد
     */
    public function graded(float $score = null, string $feedback = null): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => SubmissionStatus::GRADED->value,
            'score' => $score ?? $this->faker->randomFloat(2, 5, 10),
            'feedback' => $feedback ?? $this->faker->optional(0.5)->sentence(),
        ]);
    }

    /**
     * درجة ممتازة
     */
    public function excellentGrade(): static
    {
        return $this->graded()->state(fn(array $attributes) => [
            'score' => $this->faker->randomFloat(2, 9, 10),
            'feedback' => $this->faker->randomElement(['ممتاز!', 'عمل رائع!', 'أحسنت!']),
        ]);
    }

    /**
     * درجة ضعيفة
     */
    public function failingGrade(): static
    {
        return $this->graded()->state(fn(array $attributes) => [
            'score' => $this->faker->randomFloat(2, 0, 4),
            'feedback' => 'يحتاج إلى تحسين',
        ]);
    }

    /**
     * لواجب محدد
     */
    public function forHomework(Homework $homework): static
    {
        return $this->state(fn(array $attributes) => [
            'homework_id' => $homework->id,
        ]);
    }

    /**
     * لطالب محدد
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn(array $attributes) => [
            'student_id' => $student->id,
        ]);
    }
}
