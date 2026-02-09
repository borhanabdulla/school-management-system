<?php

namespace Database\Factories\Domains\Academic\Grading\Models;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyGradeFactory extends Factory
{
    protected $model = MonthlyGrade::class;

    public function definition(): array
    {
        $categories = ['تحريري', 'شفهي', 'واجبات', 'حضور', 'مشاركة'];
        $maxScore = 10;
        $category = $this->faker->randomElement($categories);

        return [
            'student_id' => Student::factory(),
            'course_offering_id' => CourseOffering::factory(),
            'gradebook_month_id' => GradebookMonth::factory(),
            'category_key' => GradebookSettings::generateCategoryKey($category),
            'category' => $category,
            'score' => $this->faker->randomFloat(2, 0, $maxScore),
            'max_score' => $maxScore,
            'notes' => $this->faker->optional(0.2)->sentence(),
            'graded_by' => null,
        ];
    }

    // ============================================
    // حالات الدرجات حسب الفئة
    // ============================================

    /**
     * درجة تحريري
     */
    public function written(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'تحريري',
            'category_key' => GradebookSettings::generateCategoryKey('تحريري'),
            'max_score' => 30,
            'score' => $this->faker->randomFloat(2, 15, 30),
        ]);
    }

    /**
     * درجة شفهي
     */
    public function oral(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'شفهي',
            'category_key' => GradebookSettings::generateCategoryKey('شفهي'),
            'max_score' => 10,
            'score' => $this->faker->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * درجة واجبات
     */
    public function homework(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'واجبات',
            'category_key' => GradebookSettings::generateCategoryKey('واجبات'),
            'max_score' => 10,
            'score' => $this->faker->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * درجة حضور
     */
    public function attendance(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'حضور',
            'category_key' => GradebookSettings::generateCategoryKey('حضور'),
            'max_score' => 10,
            'score' => $this->faker->randomFloat(2, 7, 10),
        ]);
    }

    /**
     * درجة مشاركة
     */
    public function participation(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'مشاركة',
            'category_key' => GradebookSettings::generateCategoryKey('مشاركة'),
            'max_score' => 5,
            'score' => $this->faker->randomFloat(2, 2, 5),
        ]);
    }

    // ============================================
    // حالات مستوى الأداء
    // ============================================

    /**
     * درجة ممتازة (90%+)
     */
    public function excellent(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $attributes['max_score'] * $this->faker->randomFloat(2, 0.9, 1.0),
        ]);
    }

    /**
     * درجة جيدة (70-89%)
     */
    public function good(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $attributes['max_score'] * $this->faker->randomFloat(2, 0.7, 0.89),
        ]);
    }

    /**
     * درجة ضعيفة (أقل من 50%)
     */
    public function failing(): static
    {
        return $this->state(fn(array $attributes) => [
            'score' => $attributes['max_score'] * $this->faker->randomFloat(2, 0, 0.49),
        ]);
    }

    // ============================================
    // العلاقات
    // ============================================

    /**
     * لطالب محدد
     */
    public function forStudent(Student $student): static
    {
        return $this->state(fn(array $attributes) => [
            'student_id' => $student->id,
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

    /**
     * لشهر محدد
     */
    public function forMonth(GradebookMonth $month): static
    {
        return $this->state(fn(array $attributes) => [
            'gradebook_month_id' => $month->id,
        ]);
    }

    /**
     * مع راصد محدد
     */
    public function gradedBy(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'graded_by' => $user->id,
        ]);
    }
}
