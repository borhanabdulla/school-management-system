<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Homework\Enums\HomeworkStatus;
use App\Domains\Academic\Homework\Enums\SubmissionStatus;
use App\Domains\Academic\Homework\Enums\SubmissionType;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Homework\Models\HomeworkSubmission;
use App\Domains\Academic\Student\Models\Student;
use Carbon\Carbon;
use Faker\Factory;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📚 Seeding Homework...');

        $faker = Factory::create('ar_SA');

        // Get all course offerings
        $courseOfferings = CourseOffering::with(['classSection.students', 'subject'])->get();

        if ($courseOfferings->isEmpty()) {
            $this->command->warn('No course offerings found. Skipping homework seeding.');
            return;
        }

        $homeworkTitles = [
            'حل تمارين الوحدة',
            'مراجعة الدرس',
            'بحث قصير عن',
            'تلخيص الفصل',
            'حل أسئلة الكتاب',
            'ورقة عمل',
            'نشاط منزلي',
            'مشروع صغير',
        ];

        $totalHomework = 0;
        $totalSubmissions = 0;

        foreach ($courseOfferings as $courseOffering) {
            // Create 3-5 homework assignments per course offering
            $homeworkCount = rand(3, 5);
            $students = $courseOffering->classSection->students ?? collect();

            for ($i = 1; $i <= $homeworkCount; $i++) {
                $dueDate = Carbon::now()->addDays(rand(-14, 14));
                $isPast = $dueDate->isPast();

                $status = $isPast
                    ? ($faker->boolean(80) ? HomeworkStatus::ARCHIVED->value : HomeworkStatus::PUBLISHED->value)
                    : HomeworkStatus::PUBLISHED->value;

                $homework = Homework::create([
                    'course_offering_id' => $courseOffering->id,
                    'title' => $faker->randomElement($homeworkTitles) . ' ' . $i . ' - ' . ($courseOffering->subject->name ?? 'مادة'),
                    'description' => $faker->paragraph(2),
                    'submission_type' => $faker->randomElement([
                        SubmissionType::ONLINE->value,
                        SubmissionType::OFFLINE->value,
                    ]),
                    'status' => $status,
                    'due_date' => $dueDate,
                    'allow_late' => $faker->boolean(30),
                    'max_score' => $faker->randomElement([10, 20, 50]),
                ]);

                $totalHomework++;

                // Create submissions for past homeworks
                if ($isPast && $students->isNotEmpty()) {
                    foreach ($students as $student) {
                        // 70% of students submit
                        if ($faker->boolean(70)) {
                            $submittedAt = $dueDate->copy()->subDays(rand(0, 5));
                            $isLate = $submittedAt->gt($dueDate);

                            // 80% of submissions are graded for past homework
                            $isGraded = $faker->boolean(80);

                            $submissionStatus = $isGraded
                                ? SubmissionStatus::GRADED->value
                                : ($isLate ? SubmissionStatus::LATE->value : SubmissionStatus::SUBMITTED->value);

                            HomeworkSubmission::create([
                                'homework_id' => $homework->id,
                                'student_id' => $student->id,
                                'status' => $submissionStatus,
                                'submitted_at' => $submittedAt,
                                'score' => $isGraded ? $faker->randomFloat(2, $homework->max_score * 0.5, $homework->max_score) : null,
                                'feedback' => $isGraded && $faker->boolean(40) ? $this->getRandomFeedback($faker) : null,
                            ]);

                            $totalSubmissions++;
                        }
                    }
                }
            }
        }

        $this->command->info("✅ Created {$totalHomework} homework assignments with {$totalSubmissions} submissions.");
    }

    private function getRandomFeedback($faker): string
    {
        $feedbacks = [
            'ممتاز! استمر على هذا المستوى.',
            'عمل جيد.',
            'أحسنت!',
            'يحتاج تحسين في بعض النقاط.',
            'جهد رائع!',
            'يرجى مراجعة الأخطاء.',
        ];
        return $faker->randomElement($feedbacks);
    }
}
