<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Grading\Actions\CreateGradingTemplateAction;
use App\Domains\Academic\Grading\Actions\ApplyTemplateToGradeAction;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Facades\Log;

/**
 * GradingConfigurationSeeder - إعداد نظام الدرجات
 * 
 * يقوم بإنشاء قوالب التقييم وتطبيقها على الصفوف والمواد.
 * هذا ضروري لتمكين المعلمين من إدخال الدرجات.
 * 
 * @example php artisan db:seed --class=GradingConfigurationSeeder
 */
class GradingConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 بدء إعداد نظام الدرجات...');

        /** @var CreateGradingTemplateAction $createTemplateAction */
        $createTemplateAction = app(CreateGradingTemplateAction::class);

        /** @var ApplyTemplateToGradeAction $applyTemplateAction */
        $applyTemplateAction = app(ApplyTemplateToGradeAction::class);

        $activeYearId = school()->activeYearId();
        $activeTerm = school()->activeTerm();

        if (!$activeYearId || !$activeTerm) {
            $this->command->error('❌ لا توجد سنة أو فصل دراسي نشط!');
            return;
        }

        // 1. إنشاء القالب القياسي (Standard Template)
        $this->command->info('📝 إنشاء القالب القياسي...');

        $templateData = [
            'name' => 'التقييم القياسي (100 درجة)',
            'total_max_score' => 100,
            'pass_score' => 50,
            'academic_year_id' => $activeYearId,
        ];

        $categories = [
            [
                'name' => 'أعمال السنة',
                'weight' => 60,
                'children' => [
                    ['name' => 'الواجبات', 'weight' => 20],
                    ['name' => 'المشاركة', 'weight' => 20],
                    ['name' => 'الاختبارات القصيرة', 'weight' => 20],
                ]
            ],
            [
                'name' => 'الاختبار النهائي',
                'weight' => 40,
                'is_locked' => true,
            ]
        ];

        $template = $createTemplateAction->execute($templateData, $categories);
        $this->command->info("   ✅ تم إنشاء القالب: {$template->name}");

        // 2. تطبيق القالب على جميع الصفوف
        $this->command->newLine();
        $this->command->info('🔗 تطبيق القالب على الصفوف والمواد...');

        $grades = Grade::all();
        $totalApplied = 0;

        foreach ($grades as $grade) {
            $count = $applyTemplateAction->execute($template, $grade, $activeTerm);
            $this->command->info("   ✅ {$grade->name}: تم تفعيل التقييم لـ {$count} مادة");
            $totalApplied += $count;
        }

        $this->command->newLine();
        $this->command->info("🎉 تم إعداد نظام الدرجات بنجاح! ({$totalApplied} إعداد مادة)");
    }
}
