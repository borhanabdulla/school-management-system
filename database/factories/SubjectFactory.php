<?php

namespace Database\Factories;

use App\Domains\Academic\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $subjects = [
            'القرآن الكريم',
            'التربية الإسلامية',
            'اللغة العربية',
            'الرياضيات',
            'العلوم',
            'اللغة الإنجليزية',
            'الدراسات الاجتماعية',
            'التربية الفنية',
            'التربية البدنية',
            'الحاسب الآلي'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($subjects) . ' ' . $this->faker->numberBetween(1, 100),
            'code' => $this->faker->unique()->bothify('SUB###'),
            'type' => $this->faker->randomElement(['theory', 'practical', 'both']),
            'description' => $this->faker->sentence(),
        ];
    }
}
