<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use App\Domains\Academic\Grade\Services\GradeLookupService;
use App\Domains\Academic\Subject\Services\SubjectLookupService;
use App\Domains\Academic\ClassSection\Services\ClassSectionLookupService;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Support\Collection;

class AcademicDirectoryManager extends Component
{
    // Search & Filter (Livewire bound, but we might move to Alpine for speed)
    // We'll keep them here for now to support the "render" logic if we decide to filter server-side,
    // but the plan suggests client-side filtering. Let's pass all data and filter in Alpine.
    // Actually, for "Search", client-side is best for small datasets.

    public function render(
        GradeLookupService $gradeLookup,
        SubjectLookupService $subjectLookup,
        ClassSectionLookupService $sectionLookup
    ) {
        // 1. Fetch Data (Context-Aware & Cached)
        $grades = $gradeLookup->getGradesWithStats();

        // 2. Calculate Global Stats (Aggregated from Grades)
        // This avoids extra queries.
        $stats = [
            'grades_count' => $grades->count(),
            'sections_count' => $grades->sum('sections_count'),
            'subjects_count' => $grades->sum('subjects_count'),
            'students_count' => $grades->sum('students_count'),
            'teachers_count' => $grades->sum('teachers_count'),
        ];

        // 3. Get Stages for Filter Pills
        // We can get unique stages from the grades collection to ensure we only show relevant stages
        $stages = $grades->pluck('stage')->unique('id')->sortBy('rank')->values();

        return view('livewire.academic.academic-directory-manager', [
            'grades' => $grades,
            'stats' => $stats,
            'stages' => $stages,
        ]);
    }
}

