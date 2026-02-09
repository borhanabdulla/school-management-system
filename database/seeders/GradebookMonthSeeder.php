<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Term\Models\Term;

class GradebookMonthSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📅 Seeding Gradebook Months...');

        // Get active academic year and its terms
        $activeYear = AcademicYear::where('status', 'active')
            ->with('terms')
            ->first();

        if (!$activeYear) {
            $this->command->warn('No active academic year found. Skipping gradebook month seeding.');
            return;
        }

        $terms = $activeYear->terms;
        if ($terms->isEmpty()) {
            $this->command->warn('No terms found for active year. Skipping gradebook month seeding.');
            return;
        }

        $totalMonths = 0;

        foreach ($terms as $term) {
            if (!$term->start_date || !$term->end_date) {
                $this->command->warn("Term '{$term->name}' has no dates. Skipping...");
                continue;
            }

            // Use the model's built-in method
            GradebookMonth::generateForTerm($term);

            $count = GradebookMonth::where('term_id', $term->id)->count();
            $totalMonths += $count;

            $this->command->line("   ↳ Created {$count} months for term: {$term->name}");
        }

        $this->command->info("✅ Created {$totalMonths} gradebook months.");
    }
}
