<?php

namespace Database\Factories\Domains\Finance\Models;

use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'guardian_id' => Guardian::factory(),
            'amount' => $this->faker->randomFloat(2, 50, 500),
            'method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Posted,
            'paid_at' => now(),
            'transaction_reference' => $this->faker->uuid(),
        ];
    }
}
