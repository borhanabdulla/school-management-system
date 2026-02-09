<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;

class GradingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Create a Standard Template
        $template = GradingTemplate::firstOrCreate(
            ['name' => 'القالب القياسي (عام)'],
            [
                'total_max_score' => 100,
                'pass_score' => 50,
                'rounding_rule' => 'nearest_integer',
                'rounding_precision' => 0,
                'academic_year_id' => school()->activeYearId(),
            ]
        );

        // Clear existing categories if re-seeding (optional, but good for idempotency)
        // $template->categories()->delete(); 

        if ($template->categories()->count() === 0) {
            // 1. Coursework (40%)
            $coursework = TemplateCategory::create([
                'grading_template_id' => $template->id,
                'name' => 'أعمال السنة',
                'weight' => 40,
                'max_raw_score' => 40,
                'calculation_type' => 'sum',
                'order' => 1,
            ]);

            // Sub-categories for Coursework
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'parent_id' => $coursework->id,
                'name' => 'واجبات',
                'weight' => 10,
                'max_raw_score' => 10,
                'order' => 1,
            ]);
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'parent_id' => $coursework->id,
                'name' => 'اختبارات قصيرة',
                'weight' => 10,
                'max_raw_score' => 10,
                'order' => 2,
            ]);
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'parent_id' => $coursework->id,
                'name' => 'مشاركة',
                'weight' => 10,
                'max_raw_score' => 10,
                'order' => 3,
            ]);
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'parent_id' => $coursework->id,
                'name' => 'مشاريع',
                'weight' => 10,
                'max_raw_score' => 10,
                'order' => 4,
            ]);

            // 2. Midterm (20%)
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'name' => 'اختبار نصفي',
                'weight' => 20,
                'max_raw_score' => 20,
                'calculation_type' => 'sum',
                'order' => 2,
            ]);

            // 3. Final Exam (40%)
            TemplateCategory::create([
                'grading_template_id' => $template->id,
                'name' => 'اختبار نهائي',
                'weight' => 40,
                'max_raw_score' => 40,
                'calculation_type' => 'sum',
                'order' => 3,
                'is_final_exam' => true,
            ]);
        }

        $this->command->info('Grading Template seeded successfully.');
    }
}
