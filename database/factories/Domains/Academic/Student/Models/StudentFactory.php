<?php

namespace Database\Factories\Domains\Academic\Student\Models;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'admission_number' => fake()->unique()->numberBetween(10000, 99999),
            'first_name_ar' => fake('ar_SA')->name(),
            'family_name_ar' => fake('ar_SA')->name(),
            'first_name_en' => fake()->name(),
            'family_name_en' => fake()->name(),
            'date_of_birth' => fake()->date(),
            'gender' => fake()->randomElement(['male', 'female']),
            'national_id' => fake()->unique()->numerify('##########'),
            'status' => 'active',
        ];
    }
}
