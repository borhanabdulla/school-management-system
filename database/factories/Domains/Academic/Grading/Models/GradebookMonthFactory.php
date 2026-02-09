<?php

namespace Database\Factories\Domains\Academic\Grading\Models;

use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradebookMonthFactory extends Factory
{
    protected $model = GradebookMonth::class;

    public function definition(): array
    {
        $monthNames = ['سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو'];
        $startDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $endDate = (clone $startDate)->modify('+1 month');

        return [
            'term_id' => Term::factory(),
            'name' => $this->faker->randomElement($monthNames),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'order' => $this->faker->numberBetween(1, 5),
        ];
    }

    /**
     * لترم محدد
     */
    public function forTerm(Term $term): static
    {
        return $this->state(fn(array $attributes) => [
            'term_id' => $term->id,
        ]);
    }

    /**
     * لشهر محدد في السنة
     */
    public function forMonth(int $month, int $year): static
    {
        $arabicMonths = [
            1 => 'يناير',
            2 => 'فبراير',
            3 => 'مارس',
            4 => 'أبريل',
            5 => 'مايو',
            6 => 'يونيو',
            7 => 'يوليو',
            8 => 'أغسطس',
            9 => 'سبتمبر',
            10 => 'أكتوبر',
            11 => 'نوفمبر',
            12 => 'ديسمبر',
        ];

        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return $this->state(fn(array $attributes) => [
            'name' => $arabicMonths[$month],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * مع ترتيب محدد
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn(array $attributes) => [
            'order' => $order,
        ]);
    }
}
