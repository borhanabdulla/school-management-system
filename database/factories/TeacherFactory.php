<?php

namespace Database\Factories;

use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\HR\Staff\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $faker = \Faker\Factory::create('ar_SA');

        return [
            'staff_id' => Staff::factory(),
            'specialization' => $faker->randomElement(['رياضيات', 'علوم', 'لغة عربية', 'لغة إنجليزية', 'تاريخ', 'جغرافيا', 'فيزياء', 'كيمياء']),
        ];
    }
}
