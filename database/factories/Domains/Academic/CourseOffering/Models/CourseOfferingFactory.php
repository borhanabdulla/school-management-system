<?php

namespace Database\Factories\Domains\Academic\CourseOffering\Models;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\HR\Teacher\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseOfferingFactory extends Factory
{
    protected $model = CourseOffering::class;

    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'term_id' => Term::factory()->active(),
            'class_section_id' => ClassSection::factory(),
            'subject_id' => Subject::factory(),
            'teacher_id' => Teacher::factory(),
        ];
    }

    public function configure(): static
    {
        return parent::configure()->afterCreating(function (CourseOffering $offering) {
            $offering->loadMissing(['classSection', 'subject']);
            $gradeId = $offering->classSection?->grade_id;
            $academicYearId = $offering->classSection?->academic_year_id;

            if (! $gradeId || ! $academicYearId || ! $offering->term_id) {
                return;
            }

            $template = GradingTemplate::factory()->create([
                'grade_id' => $gradeId,
                'academic_year_id' => $academicYearId,
                'name' => 'Default template for ' . ($offering->subject?->name ?? 'subject'),
            ]);

            $template->categories()->createMany([
                [
                    'name' => 'أعمال السنة',
                    'weight' => 50,
                    'max_raw_score' => 100,
                    'calculation_type' => 'sum',
                    'is_dynamic_weight' => false,
                    'is_locked' => false,
                    'pass_required' => false,
                    'pass_threshold' => null,
                    'order' => 1,
                    'mapping_type' => 'manual',
                    'is_readonly' => false,
                    'is_final_exam' => false,
                ],
                [
                    'name' => 'الاختبار النهائي',
                    'weight' => 50,
                    'max_raw_score' => 100,
                    'calculation_type' => 'sum',
                    'is_dynamic_weight' => false,
                    'is_locked' => false,
                    'pass_required' => true,
                    'pass_threshold' => 50,
                    'order' => 2,
                    'mapping_type' => 'manual',
                    'is_readonly' => false,
                    'is_final_exam' => true,
                ],
            ]);

            SubjectGradingConfig::create([
                'subject_id' => $offering->subject_id,
                'grade_id' => $gradeId,
                'term_id' => $offering->term_id,
                'grading_template_id' => $template->id,
                'max_score' => 100,
                'pass_score' => 50,
                'is_continuous' => true,
                'counts_in_gpa' => true,
            ]);
        });
    }
}
