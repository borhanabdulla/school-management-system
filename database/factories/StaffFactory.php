<?php

namespace Database\Factories;

use App\Domains\HR\Staff\Models\Staff;
use App\Domains\Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        $faker = \Faker\Factory::create('ar_SA');

        return [
            'user_id' => User::factory(),
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'employee_number' => $this->faker->unique()->numberBetween(1000, 9999),
            'phone' => '05' . $this->faker->unique()->numerify('########'),
            'joining_date' => $this->faker->date(),
            'employment_type' => 'full_time',
            'job_title' => $faker->jobTitle,
            'status' => 'active',
        ];
    }
}
