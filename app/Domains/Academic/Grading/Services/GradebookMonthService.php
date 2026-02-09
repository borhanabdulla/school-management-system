<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\Log;

class GradebookMonthService
{
    /**
     * Generate or update GradebookMonth records for a given Term.
     *
     * @param Term $term
     * @return void
     */
    public function generateForTerm(Term $term): void
    {
        if (!$term->start_date || !$term->end_date) {
            return;
        }

        app(AcademicWriteGuard::class)->assertTermNotCompleted($term->id);

        $start = $term->start_date->copy();
        $end = $term->end_date->copy();
        $order = 1;

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
            12 => 'ديسمبر'
        ];

        $desired = [];
        // Loop through months from start date to end date
        while ($start->lte($end)) {
            $monthEnd = $start->copy()->endOfMonth();
            if ($monthEnd->gt($end)) {
                $monthEnd = $end->copy();
            }

            $desired[] = [
                'name' => $arabicMonths[$start->month],
                'start_date' => $start->copy(),
                'end_date' => $monthEnd,
                'order' => $order++,
            ];

            // Move to start of next month
            $start->addMonth()->startOfMonth();
        }

        $existing = GradebookMonth::where('term_id', $term->id)->get()->keyBy('name');
        $desiredNames = collect($desired)->pluck('name')->all();

        // Create or Update
        foreach ($desired as $data) {
            $month = $existing->get($data['name']);
            if ($month) {
                $month->update([
                    'academic_year_id' => $term->academic_year_id,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'order' => $data['order'],
                ]);
            } else {
                GradebookMonth::create([
                    'term_id' => $term->id,
                    'academic_year_id' => $term->academic_year_id,
                    'name' => $data['name'],
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'order' => $data['order'],
                ]);
            }
        }

        // Handle Stale Months
        $staleMonths = $existing->reject(function ($month) use ($desiredNames) {
            return in_array($month->name, $desiredNames, true);
        });

        foreach ($staleMonths as $month) {
            if ($month->grades()->exists()) {
                Log::warning("Skipping deletion of GradebookMonth '{$month->name}' (ID: {$month->id}) because it has associated grades.");
                continue;
            }

            $month->delete();
            Log::info("Deleted stale GradebookMonth '{$month->name}' (ID: {$month->id}).");
        }
    }
}
