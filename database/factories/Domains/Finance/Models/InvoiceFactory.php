<?php

namespace Database\Factories\Domains\Finance\Models;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_number' => $this->faker->unique()->numerify('INV-####'),
            'student_id' => Student::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'issue_date' => $this->faker->date(),
            'due_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomFloat(2, 100, 1000),
            'paid_amount' => 0,
            'status' => 'unpaid',
        ];
    }
}
