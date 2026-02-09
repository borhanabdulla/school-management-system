<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;
use Faker\Factory;

class MonthlyGradeSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Seeding Monthly Grades...');

        $faker = Factory::create('ar_SA');

        // Get a teacher to be the grader
        $grader = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['teacher', 'admin']);
        })->first();

        // Get gradebook months
        $months = GradebookMonth::with('term')->get();

        if ($months->isEmpty()) {
            $this->command->warn('No gradebook months found. Run GradebookMonthSeeder first.');
            return;
        }

        // Get course offerings with their sections and students
        $courseOfferings = CourseOffering::with(['classSection.students'])->get();

        if ($courseOfferings->isEmpty()) {
            $this->command->warn('No course offerings found. Skipping monthly grade seeding.');
            return;
        }

        // Grade categories with their typical max scores
        $categories = [
            ['name' => 'تحريري', 'max_score' => 30, 'weight' => 0.5],
            ['name' => 'شفهي', 'max_score' => 10, 'weight' => 0.2],
            ['name' => 'واجبات', 'max_score' => 10, 'weight' => 0.15],
            ['name' => 'حضور', 'max_score' => 10, 'weight' => 0.1],
            ['name' => 'مشاركة', 'max_score' => 5, 'weight' => 0.05],
        ];

        $totalGrades = 0;

        foreach ($courseOfferings as $courseOffering) {
            $students = $courseOffering->classSection->students ?? collect();
            if ($students->isEmpty())
                continue;

            // Filter months to only those in the course offering's term
            // For simplicity, we'll use all months
            foreach ($months as $month) {
                foreach ($students as $student) {
                    // Generate grades for each category
                    foreach ($categories as $category) {
                        // Generate a realistic score based on student performance consistency
                        // Some students are consistently good, some average, some struggling
                        $studentPerformanceLevel = $this->getStudentPerformanceLevel($student->id);
                        $score = $this->generateScore($category['max_score'], $studentPerformanceLevel, $faker);

                        MonthlyGrade::firstOrCreate(
                            [
                                'student_id' => $student->id,
                                'course_offering_id' => $courseOffering->id,
                                'gradebook_month_id' => $month->id,
                                'category' => $category['name'],
                            ],
                            [
                                'score' => $score,
                                'max_score' => $category['max_score'],
                                'notes' => $faker->boolean(10) ? $this->getRandomNote($faker) : null,
                                'graded_by' => $grader?->id,
                            ]
                        );
                        $totalGrades++;
                    }
                }
            }
        }

        $this->command->info("✅ Created {$totalGrades} monthly grade records.");
    }

    /**
     * Generate a consistent performance level for each student
     * This makes the data more realistic - students tend to perform consistently
     */
    private function getStudentPerformanceLevel(int $studentId): string
    {
        // Use student ID to deterministically assign a performance level
        $hash = crc32((string) $studentId);
        $normalized = abs($hash % 100);

        if ($normalized < 20) {
            return 'excellent'; // Top 20%
        } elseif ($normalized < 60) {
            return 'good'; // Middle 40%
        } elseif ($normalized < 85) {
            return 'average'; // 25%
        } else {
            return 'struggling'; // Bottom 15%
        }
    }

    /**
     * Generate a score based on max score and performance level
     */
    private function generateScore(float $maxScore, string $performanceLevel, $faker): float
    {
        $minPercentage = match ($performanceLevel) {
            'excellent' => 0.85,
            'good' => 0.70,
            'average' => 0.50,
            'struggling' => 0.30,
            default => 0.50,
        };

        $maxPercentage = match ($performanceLevel) {
            'excellent' => 1.0,
            'good' => 0.89,
            'average' => 0.75,
            'struggling' => 0.55,
            default => 0.70,
        };

        $percentage = $faker->randomFloat(2, $minPercentage, $maxPercentage);
        return round($maxScore * $percentage, 2);
    }

    private function getRandomNote($faker): string
    {
        $notes = [
            'متفوق',
            'يحتاج متابعة',
            'تحسن ملحوظ',
            'غياب متكرر أثر على الدرجة',
            'مشارك بفعالية',
            'يحتاج تحسين في الواجبات',
        ];
        return $faker->randomElement($notes);
    }
}
