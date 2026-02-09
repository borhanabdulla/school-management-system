<?php

namespace Database\Factories\Domains\Academic\Student\Models;

use App\Domains\Academic\Student\Models\Guardian;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuardianFactory extends Factory
{
    protected $model = Guardian::class;

    public function definition(): array
    {
        return [
            'first_name' => fake('ar_SA')->firstName(),
            'last_name' => fake('ar_SA')->lastName(),
            'phone' => fake()->unique()->numerify('05########'),
            'national_id' => fake()->unique()->numerify('##########'),
            'employer' => fake()->company(),
            'work_phone' => fake()->numerify('01########'),
            'user_id' => null,
            'nationality_id' => null,
        ];
    }

    /**
     * ولي أمر مسؤول مالي
     */
    public function financialSponsor(): static
    {
        return $this->state(fn(array $attributes) => [
            // يُستخدم عند ربط الولي بالطالب عبر pivot
        ]);
    }
}
