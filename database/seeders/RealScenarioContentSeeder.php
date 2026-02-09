<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Domains\Academic\Subject\Actions\CreateSubjectAction;
use App\Domains\Academic\Subject\Data\SubjectData;
use App\Domains\Academic\Subject\Actions\AssignSubjectToGradeAction;
use App\Domains\Academic\Subject\Data\SubjectAssignmentData;
use App\Domains\HR\Teacher\Actions\CreateTeacherAction;
use App\Domains\HR\Teacher\Data\TeacherOnboardingData;
use App\Domains\Academic\CourseOffering\Actions\AssignTeacherToCourseAction;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;

/**
 * RealScenarioContentSeeder - تعبئة المحتوى الأكاديمي (مواد، معلمين، جداول)
 * 
 * هذا الـ Seeder يُكمل السيناريو الحقيقي بإنشاء:
 * 1. المواد الدراسية (باستخدام CreateSubjectAction)
 * 2. ربط المواد بالصفوف (باستخدام AssignSubjectToGradeAction)
 * 3. إنشاء المعلمين (باستخدام CreateTeacherAction)
 * 4. توزيع المعلمين على المواد والشعب (باستخدام AssignTeacherToCourseAction)
 * 
 * @example php artisan db:seed --class=RealScenarioContentSeeder
 */
class RealScenarioContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📚 بدء تعبئة المحتوى الأكاديمي...');

        // ═══════════════════════════════════════════════════════════════
        // 1️⃣ إنشاء المواد الدراسية
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('📝 إنشاء المواد الدراسية...');

        /** @var CreateSubjectAction $createSubjectAction */
        $createSubjectAction = app(CreateSubjectAction::class);

        $subjectsList = [
            ['name' => 'القرآن الكريم', 'code' => 'QUR', 'type' => 'theory'],
            ['name' => 'التربية الإسلامية', 'code' => 'ISL', 'type' => 'theory'],
            ['name' => 'اللغة العربية', 'code' => 'ARB', 'type' => 'theory'],
            ['name' => 'الرياضيات', 'code' => 'MATH', 'type' => 'theory'],
            ['name' => 'العلوم', 'code' => 'SCI', 'type' => 'both'],
            ['name' => 'اللغة الإنجليزية', 'code' => 'ENG', 'type' => 'theory'],
            ['name' => 'الدراسات الاجتماعية', 'code' => 'SOC', 'type' => 'theory'],
        ];

        $subjects = [];

        foreach ($subjectsList as $data) {
            // Check if exists
            $existing = \App\Domains\Academic\Subject\Models\Subject::where('code', $data['code'])->first();
            if ($existing) {
                $subjects[$data['code']] = $existing;
                continue;
            }

            $subjectData = SubjectData::fromArray([
                'name' => $data['name'],
                'code' => $data['code'],
                'type' => $data['type'],
                'description' => 'مادة أساسية',
                'credit_hours' => 4,
                'is_active' => true,
            ]);

            $subject = $createSubjectAction->execute($subjectData);
            $subjects[$data['code']] = $subject;
            $this->command->info("   ✅ {$subject->name} ({$subject->code})");
        }

        // ═══════════════════════════════════════════════════════════════
        // 2️⃣ ربط المواد بالصفوف
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('🔗 ربط المواد بالصفوف...');

        /** @var AssignSubjectToGradeAction $assignSubjectAction */
        $assignSubjectAction = app(AssignSubjectToGradeAction::class);
        $grades = Grade::all();

        foreach ($grades as $grade) {
            foreach ($subjects as $code => $subject) {
                // Skip logic if needed (e.g. Science only for higher grades)

                // Check if already assigned
                $exists = $grade->subjects()->where('subject_id', $subject->id)->exists();
                if ($exists) {
                    continue;
                }

                $assignmentData = SubjectAssignmentData::fromArray([
                    'subject_id' => $subject->id,
                    'credit_hours' => 4,
                    'max_grade' => 100,
                    'pass_grade' => 50,
                    'term_type' => 'full_year',
                    'is_active' => true,
                ]);

                $assignSubjectAction->execute($grade, $assignmentData);
            }
            $this->command->info("   ✅ تم ربط المواد للصف: {$grade->name}");
        }

        // ═══════════════════════════════════════════════════════════════
        // 3️⃣ إنشاء المعلمين
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('👨‍🏫 إنشاء المعلمين...');

        /** @var CreateTeacherAction $createTeacherAction */
        $createTeacherAction = app(CreateTeacherAction::class);

        $teachersList = [
            ['first' => 'أحمد', 'last' => 'محمد', 'spec' => 'الرياضيات', 'email' => 'math@school.com'],
            ['first' => 'خالد', 'last' => 'علي', 'spec' => 'العلوم', 'email' => 'science@school.com'],
            ['first' => 'سعيد', 'last' => 'عمر', 'spec' => 'اللغة العربية', 'email' => 'arabic@school.com'],
            ['first' => 'محمد', 'last' => 'حسن', 'spec' => 'التربية الإسلامية', 'email' => 'islamic@school.com'],
            ['first' => 'يوسف', 'last' => 'إبراهيم', 'spec' => 'اللغة الإنجليزية', 'email' => 'english@school.com'],
        ];

        $teachers = [];

        foreach ($teachersList as $t) {
            // Check if exists
            $existingUser = \App\Domains\Shared\Models\User::where('email', $t['email'])->first();
            if ($existingUser) {
                $teacher = \App\Domains\HR\Teacher\Models\Teacher::whereHas('staff', function ($q) use ($existingUser) {
                    $q->where('user_id', $existingUser->id);
                })->first();

                if ($teacher) {
                    $teachers[$t['spec']] = $teacher;
                    continue;
                }
            }

            $teacherData = new TeacherOnboardingData(
                first_name: $t['first'],
                last_name: $t['last'],
                email: $t['email'],
                password: 'password123',
                specialization: $t['spec'],
                max_weekly_classes: 24,
                hire_date: now()->format('Y-m-d'),
                phone: '05' . rand(10000000, 99999999)
            );

            $teacher = $createTeacherAction->execute($teacherData);
            $teachers[$t['spec']] = $teacher;
            $this->command->info("   ✅ المعلم: {$t['first']} {$t['last']} - {$t['spec']}");
        }

        // ═══════════════════════════════════════════════════════════════
        // 4️⃣ توزيع المعلمين على المواد (Course Offerings)
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('📅 توزيع المعلمين على المواد (Course Offerings)...');

        /** @var AssignTeacherToCourseAction $assignTeacherAction */
        $assignTeacherAction = app(AssignTeacherToCourseAction::class);

        $activeYearId = school()->activeYearId();
        $sections = ClassSection::where('academic_year_id', $activeYearId)->get();

        $assignmentsCount = 0;

        foreach ($sections as $section) {
            // Get subjects for this section's grade
            $gradeSubjects = $section->grade->subjects;

            foreach ($gradeSubjects as $subject) {
                // Find suitable teacher
                $teacher = null;

                // Simple matching logic
                if ($subject->name === 'الرياضيات')
                    $teacher = $teachers['الرياضيات'] ?? null;
                elseif ($subject->name === 'العلوم')
                    $teacher = $teachers['العلوم'] ?? null;
                elseif ($subject->name === 'اللغة العربية')
                    $teacher = $teachers['اللغة العربية'] ?? null;
                elseif (in_array($subject->name, ['القرآن الكريم', 'التربية الإسلامية']))
                    $teacher = $teachers['التربية الإسلامية'] ?? null;
                elseif ($subject->name === 'اللغة الإنجليزية')
                    $teacher = $teachers['اللغة الإنجليزية'] ?? null;
                else
                    $teacher = collect($teachers)->first(); // Fallback

                if ($teacher) {
                    $assignTeacherAction->execute($section, $subject, $teacher);
                    $assignmentsCount++;
                }
            }
        }

        $this->command->info("   ✅ تم إنشاء {$assignmentsCount} مقرر دراسي (Course Offerings)");
        $this->command->newLine();
        $this->command->info('🎉 تم اكتمال إعداد المحتوى الأكاديمي!');
    }
}
