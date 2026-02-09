<?php

namespace Database\Factories\Domains\Academic\Homework\Models;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Homework\Models\Homework;
use Illuminate\Database\Eloquent\Factories\Factory;

class HomeworkFactory extends Factory
{
    protected $model = Homework::class;

    public function definition(): array
    {
        return [
            'course_offering_id' => CourseOffering::factory(),
            'assessment_id' => null,
            'title' => $this->faker->randomElement([
                'حل تمارين الوحدة',
                'مراجعة الدرس',
                'بحث قصير',
                'تلخيص الفصل',
                'حل أسئلة الكتاب',
            ]) . ' ' . $this->faker->numberBetween(1, 10),
            'description' => $this->faker->paragraph,
            'submission_type' => $this->faker->randomElement([
                SubmissionType::ONLINE->value,
                SubmissionType::OFFLINE->value,
            ]),
            'status' => HomeworkStatus::PUBLISHED->value,
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+2 weeks'),
            'allow_late' => $this->faker->boolean(30),
            'max_score' => $this->faker->randomElement([10, 20, 50, 100]),
        ];
    }

    /**
     * واجب منشور
     */
    public function published(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => HomeworkStatus::PUBLISHED->value,
        ]);
    }

    /**
     * واجب مسودة
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => HomeworkStatus::DRAFT->value,
        ]);
    }

    /**
     * واجب مؤرشف
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => HomeworkStatus::ARCHIVED->value,
        ]);
    }

    /**
     * تسليم أونلاين
     */
    public function online(): static
    {
        return $this->state(fn(array $attributes) => [
            'submission_type' => SubmissionType::ONLINE->value,
        ]);
    }

    /**
     * تسليم ورقي
     */
    public function offline(): static
    {
        return $this->state(fn(array $attributes) => [
            'submission_type' => SubmissionType::OFFLINE->value,
        ]);
    }

    /**
     * يسمح بالتأخير
     */
    public function allowsLate(): static
    {
        return $this->state(fn(array $attributes) => [
            'allow_late' => true,
        ]);
    }

    /**
     * منتهي الصلاحية
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-2 weeks', '-1 day'),
        ]);
    }

    /**
     * لعرض مقرر محدد
     */
    public function forCourseOffering(CourseOffering $courseOffering): static
    {
        return $this->state(fn(array $attributes) => [
            'course_offering_id' => $courseOffering->id,
        ]);
    }
}
