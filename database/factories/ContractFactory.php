<?php

namespace Database\Factories;

use App\Domains\HR\Payroll\Models\Contract;
use App\Domains\HR\Payroll\Enums\ContractStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_id' => \App\Domains\HR\Staff\Models\Staff::factory(),
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'basic_salary' => $this->faker->numberBetween(3000, 10000),
            'status' => 'active', // String for now, or enum
            'is_locked' => false,
        ];
    }
}
