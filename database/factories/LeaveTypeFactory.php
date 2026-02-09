<?php

namespace Database\Factories;

use App\Domains\HR\Leave\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'days_per_year' => 30,
            'requires_proof' => false,
            'is_paid' => true,
            'is_active' => true,
        ];
    }
}
