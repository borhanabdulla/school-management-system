<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Grade\Models\Grade;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Define standard subjects
        $subjectsList = [
            ['name' => 'القرآن الكريم', 'code' => 'QUR', 'type' => 'theory'],
            ['name' => 'التربية الإسلامية', 'code' => 'ISL', 'type' => 'theory'],
            ['name' => 'اللغة العربية', 'code' => 'ARB', 'type' => 'theory'],
            ['name' => 'الرياضيات', 'code' => 'MATH', 'type' => 'theory'],
            ['name' => 'العلوم', 'code' => 'SCI', 'type' => 'both'],
            ['name' => 'اللغة الإنجليزية', 'code' => 'ENG', 'type' => 'theory'],
            ['name' => 'الدراسات الاجتماعية', 'code' => 'SOC', 'type' => 'theory'],
            ['name' => 'التربية الفنية', 'code' => 'ART', 'type' => 'practical'],
            ['name' => 'التربية البدنية', 'code' => 'PE', 'type' => 'practical'],
        ];

        // Create Subjects in the library
        $createdSubjects = [];
        foreach ($subjectsList as $subjectData) {
            $createdSubjects[] = Subject::firstOrCreate(
                ['name' => $subjectData['name']],
                [
                    'code' => $subjectData['code'],
                    'type' => $subjectData['type'],
                    'description' => 'مادة أساسية في المنهج الدراسي',
                ]
            );
        }

        // Assign Subjects to All Grades
        $grades = Grade::all();

        foreach ($grades as $grade) {
            foreach ($createdSubjects as $subject) {
                // Check if already assigned to avoid duplicates
                $exists = $grade->subjects()
                    ->where('subject_id', $subject->id)
                    ->wherePivot('term_type', 'full_year')
                    ->exists();

                if (!$exists) {
                    $grade->subjects()->attach($subject->id, [
                        'credit_hours' => 4, // Default credit hours
                        'max_grade' => 100,
                        'pass_grade' => 50,
                        'term_type' => 'full_year',
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
