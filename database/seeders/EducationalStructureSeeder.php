<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Grade\Actions\CreateGradeAction;
use App\Domains\Academic\Grade\Data\GradeData;
use App\Domains\Academic\ClassSection\Actions\CreateClassSectionAction;
use App\Domains\Academic\ClassSection\Data\ClassSectionData;
use App\Domains\Academic\ClassSection\Enums\SectionGenderType;
use Illuminate\Support\Facades\Log;

/**
 * EducationalStructureSeeder - إنشاء الهيكل التعليمي
 * 
 * يُنشئ المراحل والصفوف والشعب باستخدام Actions الحقيقية
 * بنفس الطريقة التي تعمل بها الواجهة
 * 
 * الهيكل المُنشأ:
 * - المرحلة الأساسية (الصف الأول → الرابع) - شعبتين لكل صف
 * - المرحلة المتوسطة (الصف الخامس → السادس) - شعبتين لكل صف
 * 
 * @example php artisan db:seed --class=EducationalStructureSeeder
 */
class EducationalStructureSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🏫 بدء إنشاء الهيكل التعليمي...');
        $this->command->newLine();

        // ═══════════════════════════════════════════════════════════════
        // 1️⃣ إنشاء المراحل التعليمية
        // ═══════════════════════════════════════════════════════════════
        $this->command->info('📚 إنشاء المراحل التعليمية...');

        // المرحلة الأساسية
        $primaryStage = EducationalStage::firstOrCreate(
            ['name' => 'المرحلة الأساسية'],
            [
                'rank' => 2,  // rank 1 مُستخدم من 'ابتدائي'
                'min_passing_percentage' => 50,
                'grading_system' => 'standard',
            ]
        );
        $this->command->info("   ✅ {$primaryStage->name} (ID: {$primaryStage->id})");

        // المرحلة المتوسطة
        $middleStage = EducationalStage::firstOrCreate(
            ['name' => 'المرحلة المتوسطة'],
            [
                'rank' => 3,
                'min_passing_percentage' => 50,
                'grading_system' => 'standard',
            ]
        );
        $this->command->info("   ✅ {$middleStage->name} (ID: {$middleStage->id})");

        // ═══════════════════════════════════════════════════════════════
        // 2️⃣ إنشاء الصفوف باستخدام CreateGradeAction
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('📖 إنشاء الصفوف الدراسية...');

        /** @var CreateGradeAction $createGradeAction */
        $createGradeAction = app(CreateGradeAction::class);

        $grades = [];

        // صفوف المرحلة الأساسية (1-4)
        $primaryGrades = [
            ['name' => 'الصف الأول', 'level_order' => 1],
            ['name' => 'الصف الثاني', 'level_order' => 2],
            ['name' => 'الصف الثالث', 'level_order' => 3],
            ['name' => 'الصف الرابع', 'level_order' => 4],
        ];

        foreach ($primaryGrades as $gradeInfo) {
            $existingGrade = \App\Domains\Academic\Grade\Models\Grade::where('name', $gradeInfo['name'])
                ->where('educational_stage_id', $primaryStage->id)
                ->first();

            if ($existingGrade) {
                $grades[$gradeInfo['name']] = $existingGrade;
                $this->command->warn("   ⚠️ {$gradeInfo['name']} موجود مسبقاً (ID: {$existingGrade->id})");
                continue;
            }

            $gradeData = GradeData::fromArray([
                'name' => $gradeInfo['name'],
                'level_order' => $gradeInfo['level_order'],
                'educational_stage_id' => $primaryStage->id,
                'next_grade_id' => null, // سنُحدّث لاحقاً
            ]);

            $grade = $createGradeAction->execute($gradeData);
            $grades[$gradeInfo['name']] = $grade;
            $this->command->info("   ✅ {$grade->name} (ID: {$grade->id}) - {$primaryStage->name}");
        }

        // صفوف المرحلة المتوسطة (5-6)
        $middleGrades = [
            ['name' => 'الصف الخامس', 'level_order' => 5],
            ['name' => 'الصف السادس', 'level_order' => 6],
        ];

        foreach ($middleGrades as $gradeInfo) {
            $existingGrade = \App\Domains\Academic\Grade\Models\Grade::where('name', $gradeInfo['name'])
                ->where('educational_stage_id', $middleStage->id)
                ->first();

            if ($existingGrade) {
                $grades[$gradeInfo['name']] = $existingGrade;
                $this->command->warn("   ⚠️ {$gradeInfo['name']} موجود مسبقاً (ID: {$existingGrade->id})");
                continue;
            }

            $gradeData = GradeData::fromArray([
                'name' => $gradeInfo['name'],
                'level_order' => $gradeInfo['level_order'],
                'educational_stage_id' => $middleStage->id,
                'next_grade_id' => null,
            ]);

            $grade = $createGradeAction->execute($gradeData);
            $grades[$gradeInfo['name']] = $grade;
            $this->command->info("   ✅ {$grade->name} (ID: {$grade->id}) - {$middleStage->name}");
        }

        // ═══════════════════════════════════════════════════════════════
        // 3️⃣ إنشاء الشعب باستخدام CreateClassSectionAction
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('🏠 إنشاء الشعب الدراسية (شعبتين لكل صف)...');

        /** @var CreateClassSectionAction $createSectionAction */
        $createSectionAction = app(CreateClassSectionAction::class);

        // الحصول على السنة النشطة
        $activeYearId = school()->activeYearId();

        if (!$activeYearId) {
            $this->command->error('❌ لا توجد سنة دراسية نشطة! شغّل RealScenarioSeeder أولاً.');
            return;
        }

        $this->command->info("   📅 السنة النشطة: " . school()->activeYear()->name);

        $sectionNames = ['أ', 'ب']; // شعبتين لكل صف
        $sectionsCreated = 0;

        foreach ($grades as $gradeName => $grade) {
            foreach ($sectionNames as $sectionName) {
                // التحقق من عدم وجود الشعبة
                $existingSection = \App\Domains\Academic\ClassSection\Models\ClassSection::where('grade_id', $grade->id)
                    ->where('academic_year_id', $activeYearId)
                    ->where('name', $sectionName)
                    ->first();

                if ($existingSection) {
                    $this->command->warn("   ⚠️ {$gradeName} - شعبة ({$sectionName}) موجودة");
                    continue;
                }

                $sectionData = ClassSectionData::fromArray([
                    'name' => $sectionName,
                    'grade_id' => $grade->id,
                    'academic_year_id' => $activeYearId,
                    'max_capacity' => 30,
                    'gender_type' => SectionGenderType::Mixed->value,
                    'is_active' => true,
                ]);

                $section = $createSectionAction->execute($sectionData);
                $sectionsCreated++;
                $this->command->info("   ✅ {$gradeName} - شعبة ({$section->name}) ID: {$section->id}");
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // 4️⃣ عرض الملخص
        // ═══════════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('📊 ملخص الهيكل التعليمي:');

        $summary = [
            ['المراحل التعليمية', EducationalStage::count()],
            ['الصفوف', \App\Domains\Academic\Grade\Models\Grade::count()],
            ['الشعب (هذه السنة)', \App\Domains\Academic\ClassSection\Models\ClassSection::where('academic_year_id', $activeYearId)->count()],
        ];

        $this->command->table(['البند', 'العدد'], $summary);

        // عرض الهيكل الكامل
        $this->command->newLine();
        $this->command->info('🏫 الهيكل الكامل:');

        foreach (EducationalStage::with('grades.sections')->get() as $stage) {
            $this->command->info("📚 {$stage->name}");
            foreach ($stage->grades as $grade) {
                $sections = $grade->sections->where('academic_year_id', $activeYearId);
                $sectionsList = $sections->pluck('name')->join('، ');
                $this->command->info("   ├── {$grade->name} → [{$sectionsList}]");
            }
        }

        $this->command->newLine();
        $this->command->info('🎉 تم إنشاء الهيكل التعليمي بنجاح!');

        Log::info('EducationalStructureSeeder completed', [
            'stages' => EducationalStage::count(),
            'grades' => count($grades),
            'sections_created' => $sectionsCreated,
        ]);
    }
}
