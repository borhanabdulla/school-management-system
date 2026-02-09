<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;

class AcademicYearSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('name', '2025/2026')->first();

        if (!$year) {
            $year = AcademicYear::factory()
                ->active()
                ->has(
                    Term::factory()
                        ->firstTerm()
                        ->active(),
                    'terms'
                )
                ->has(
                    Term::factory()
                        ->secondTerm(),
                    'terms'
                )
                ->create([
                    'name' => '2025/2026',
                ]);
        }

        if ($year->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active) {
            $year->update(['status' => \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active]);
        }
    }
}
