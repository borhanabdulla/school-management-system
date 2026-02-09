<?php

namespace Tests\Feature\Domains\Academic\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ════════════════════════════════════════════════════════════════════════════
 * 🎓 اختبار شامل لنظام الدرجات - من البداية إلى النهاية
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * الأدوار:
 *   👨‍💼 المدير → إنشاء GradingTemplate + TemplateCategory
 *   👨‍🏫 الأستاذ → إنشاء Assessment + إدخال StudentMark
 *   🖥️ النظام → حساب الدرجات + تحديد ناجح/راسب
 * 
 * ════════════════════════════════════════════════════════════════════════════
 */
class GradingSystemTracingTest extends TestCase
{
    use RefreshDatabase;

    // Academic Structure
    private AcademicYear $academicYear;
    private Term $term;
    private Grade $grade;
    private ClassSection $classSection;
    private Subject $subject;
    private CourseOffering $courseOffering;

    // Grading Structure
    private GradingTemplate $template;
    private TemplateCategory $courseworkCategory;
    private TemplateCategory $writtenCategory;
    private TemplateCategory $oralCategory;
    private TemplateCategory $finalExamCategory;

    // Students
    private Student $excellentStudent;
    private Student $failingStudent;

    // Assessments
    private Assessment $writtenAssessment;
    private Assessment $oralAssessment;
    private Assessment $finalExamAssessment;

    protected function setUp(): void
    {
        parent::setUp();
    }

    private function out(string $msg): void
    {
        fwrite(STDOUT, $msg . "\n");
    }

    private function header(string $title): void
    {
        $this->out('');
        $this->out('═══════════════════════════════════════════════════════════════════════════════');
        $this->out("  {$title}");
        $this->out('═══════════════════════════════════════════════════════════════════════════════');
    }

    private function step(string $actor, string $step): void
    {
        $icons = [
            'المدير' => '👨‍💼',
            'الأستاذ' => '👨‍🏫',
            'النظام' => '🖥️',
        ];
        $icon = $icons[$actor] ?? '▶';
        $this->out('');
        $this->out("【{$icon} {$actor}】 {$step}");
    }

    private function item(string $msg, int $indent = 1): void
    {
        $prefix = str_repeat('  ', $indent) . '├── ';
        $this->out($prefix . $msg);
    }

    private function lastItem(string $msg, int $indent = 1): void
    {
        $prefix = str_repeat('  ', $indent) . '└── ';
        $this->out($prefix . $msg);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الخطوة 1: إنشاء البنية الأكاديمية
    // ═══════════════════════════════════════════════════════════════════════════

    private function setupAcademicStructure(): void
    {
        $this->step('المدير', 'الخطوة 1: إنشاء البنية الأكاديمية');

        // السنة الدراسية
        $this->academicYear = AcademicYear::factory()->active()->create([
            'name' => '2024-2025',
        ]);
        $this->item("✓ السنة الدراسية: {$this->academicYear->name}");

        // الفصل الدراسي
        $this->term = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
            'name' => 'الفصل الأول',
        ]);
        $this->item("✓ الفصل: {$this->term->name}");

        // الصف
        $this->grade = Grade::factory()->create([
            'name' => 'الصف الأول',
        ]);
        $this->item("✓ الصف: {$this->grade->name}");

        // الشعبة
        $this->classSection = ClassSection::factory()->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'أ',
        ]);
        $this->item("✓ الشعبة: {$this->classSection->name}");

        // المادة
        $this->subject = Subject::factory()->create([
            'name' => 'الرياضيات',
        ]);
        $this->item("✓ المادة: {$this->subject->name}");

        // ربط المادة بالشعبة
        $this->courseOffering = CourseOffering::factory()->create([
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'term_id' => $this->term->id,
        ]);
        $this->lastItem("✓ ربط المادة بالشعبة: CourseOffering #{$this->courseOffering->id}");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الخطوة 2: إنشاء قالب التقييم
    // ═══════════════════════════════════════════════════════════════════════════

    private function setupGradingTemplate(): void
    {
        $this->step('المدير', 'الخطوة 2: إنشاء قالب التقييم');

        // القالب الرئيسي
        $this->template = GradingTemplate::factory()->create([
            'name' => 'قالب التقييم الأساسي',
            'total_max_score' => 100,
            'pass_score' => 50,
            'academic_year_id' => $this->academicYear->id,
        ]);

        $this->item("✓ القالب: {$this->template->name}");
        $this->item("  • الدرجة القصوى: {$this->template->total_max_score}", 1);
        $this->item("  • درجة النجاح: {$this->template->pass_score}", 1);

        // فئة: أعمال السنة (40%)
        $this->courseworkCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'name' => 'أعمال السنة',
            'weight' => 40,
            'parent_id' => null,
        ]);
        $this->item("✓ فئة رئيسية: أعمال السنة (40%)");

        // تحريري (60% من أعمال السنة)
        $this->writtenCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'parent_id' => $this->courseworkCategory->id,
            'name' => 'تحريري',
            'weight' => 60,
        ]);
        $this->item("  └── تحريري (60% من أعمال السنة)", 1);

        // شفهي (40% من أعمال السنة)
        $this->oralCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'parent_id' => $this->courseworkCategory->id,
            'name' => 'شفهي',
            'weight' => 40,
        ]);
        $this->item("  └── شفهي (40% من أعمال السنة)", 1);

        // فئة: اختبار نهائي (60%)
        $this->finalExamCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'name' => 'اختبار نهائي',
            'weight' => 60,
            'parent_id' => null,
        ]);
        $this->item("✓ فئة رئيسية: اختبار نهائي (60%)");

        // Note: We're testing calculateStudentGrade() directly, which accepts the template as a parameter
        // So we don't need SubjectGradingConfig which is only used by generateReportCard()
        $this->lastItem("✓ القالب جاهز للاستخدام");

        // عرض ملخص هيكل التقييم
        $this->out('');
        $this->out('  ┌──────────────────────────────────────────────┐');
        $this->out('  │         هيكل توزيع الدرجات                  │');
        $this->out('  ├──────────────────────────────────────────────┤');
        $this->out('  │ أعمال السنة ─────────────────────── 40%     │');
        $this->out('  │   ├── تحريري ────────────────────── 60%     │');
        $this->out('  │   └── شفهي ──────────────────────── 40%     │');
        $this->out('  │ اختبار نهائي ────────────────────── 60%     │');
        $this->out('  ├──────────────────────────────────────────────┤');
        $this->out('  │ المجموع ─────────────────────────── 100%    │');
        $this->out('  │ درجة النجاح ─────────────────────── 50%     │');
        $this->out('  └──────────────────────────────────────────────┘');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الخطوة 3: إنشاء الطلاب
    // ═══════════════════════════════════════════════════════════════════════════

    private function setupStudents(): void
    {
        $this->step('المدير', 'الخطوة 3: تسجيل الطلاب');

        $this->excellentStudent = Student::factory()->create([
            'first_name_ar' => 'أحمد',
            'family_name_ar' => 'محمد',
            'current_class_section_id' => $this->classSection->id,
        ]);
        $this->item("✓ الطالب 1: {$this->excellentStudent->first_name_ar} {$this->excellentStudent->family_name_ar} (متفوق)");

        $this->failingStudent = Student::factory()->create([
            'first_name_ar' => 'خالد',
            'family_name_ar' => 'علي',
            'current_class_section_id' => $this->classSection->id,
        ]);
        $this->lastItem("✓ الطالب 2: {$this->failingStudent->first_name_ar} {$this->failingStudent->family_name_ar} (ضعيف)");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الخطوة 4: إنشاء الاختبارات
    // ═══════════════════════════════════════════════════════════════════════════

    private function setupAssessments(): void
    {
        $this->step('الأستاذ', 'الخطوة 4: إنشاء الاختبارات');

        $this->writtenAssessment = Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->writtenCategory->id,
            'title' => 'اختبار تحريري شهري',
            'max_score' => 100,
        ]);
        $this->item("✓ اختبار تحريري (100 درجة)");

        $this->oralAssessment = Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->oralCategory->id,
            'title' => 'اختبار شفهي',
            'max_score' => 100,
        ]);
        $this->item("✓ اختبار شفهي (100 درجة)");

        $this->finalExamAssessment = Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->finalExamCategory->id,
            'title' => 'الاختبار النهائي',
            'max_score' => 100,
        ]);
        $this->lastItem("✓ اختبار نهائي (100 درجة)");
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الخطوة 5: إدخال الدرجات
    // ═══════════════════════════════════════════════════════════════════════════

    private function enterGrades(): void
    {
        $this->step('الأستاذ', 'الخطوة 5: إدخال درجات الطلاب');

        // الطالب المتفوق - درجات عالية
        $this->out('');
        $this->item("📝 درجات الطالب: {$this->excellentStudent->first_name_ar}");

        StudentMark::factory()->create([
            'student_id' => $this->excellentStudent->id,
            'assessment_id' => $this->writtenAssessment->id,
            'raw_score' => 85,
        ]);
        $this->item("  تحريري: 85/100", 1);

        StudentMark::factory()->create([
            'student_id' => $this->excellentStudent->id,
            'assessment_id' => $this->oralAssessment->id,
            'raw_score' => 90,
        ]);
        $this->item("  شفهي: 90/100", 1);

        StudentMark::factory()->create([
            'student_id' => $this->excellentStudent->id,
            'assessment_id' => $this->finalExamAssessment->id,
            'raw_score' => 88,
        ]);
        $this->item("  نهائي: 88/100", 1);

        // الطالب الضعيف - درجات منخفضة
        $this->out('');
        $this->item("📝 درجات الطالب: {$this->failingStudent->first_name_ar}");

        StudentMark::factory()->create([
            'student_id' => $this->failingStudent->id,
            'assessment_id' => $this->writtenAssessment->id,
            'raw_score' => 25,
        ]);
        $this->item("  تحريري: 25/100", 1);

        StudentMark::factory()->create([
            'student_id' => $this->failingStudent->id,
            'assessment_id' => $this->oralAssessment->id,
            'raw_score' => 30,
        ]);
        $this->item("  شفهي: 30/100", 1);

        StudentMark::factory()->create([
            'student_id' => $this->failingStudent->id,
            'assessment_id' => $this->finalExamAssessment->id,
            'raw_score' => 35,
        ]);
        $this->lastItem("  نهائي: 35/100", 1);
    }

    private function marksForStudent(Student $student)
    {
        return StudentMark::where('student_id', $student->id)
            ->where(function ($query) {
                $query->where('course_offering_id', $this->courseOffering->id)
                    ->orWhereHas('assessment', fn($q) => $q->where('course_offering_id', $this->courseOffering->id));
            })
            ->with(['category', 'assessment.category'])
            ->get();
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // الاختبار الرئيسي
    // ═══════════════════════════════════════════════════════════════════════════

    #[Test]
    public function complete_grading_flow_from_template_to_result(): void
    {
        $this->header('🎓 اختبار شامل لنظام الدرجات');

        // === الخطوات 1-5: إعداد البيانات ===
        $this->setupAcademicStructure();
        $this->setupGradingTemplate();
        $this->setupStudents();
        $this->setupAssessments();
        $this->enterGrades();

        // === الخطوة 6: النظام يحسب الدرجات ===
        $this->step('النظام', 'الخطوة 6: حساب الدرجات النهائية');

        $calculator = app(GradingCalculatorService::class);
        $this->template->load(['categories' => fn($q) => $q->with('children')]);

        // حساب درجة الطالب المتفوق
        $this->out('');
        $this->item("🔄 حساب درجة: {$this->excellentStudent->first_name_ar}");

        $excellentResult = $calculator->calculateStudentGrade(
            $this->excellentStudent,
            $this->courseOffering,
            $this->template,
            $this->marksForStudent($this->excellentStudent)
        );

        $this->out('');
        $this->out('  ┌──────────────────────────────────────────────────────────────┐');
        $this->out("  │ 📊 تفصيل درجة: {$this->excellentStudent->first_name_ar}                                   │");
        $this->out('  ├──────────────────────────────────────────────────────────────┤');

        // تفصيل أعمال السنة
        $courseworkResult = $excellentResult['categories'][$this->courseworkCategory->id] ?? null;
        if ($courseworkResult) {
            $this->out(sprintf('  │ أعمال السنة (40%%):                                         │'));

            // تحريري
            $writtenResult = $courseworkResult['children'][$this->writtenCategory->id] ?? null;
            if ($writtenResult) {
                $this->out(sprintf(
                    '  │   ├── تحريري: %d%% × 60%% = %.1f%%                            │',
                    $writtenResult['percentage'],
                    $writtenResult['percentage'] * 0.6
                ));
            }

            // شفهي
            $oralResult = $courseworkResult['children'][$this->oralCategory->id] ?? null;
            if ($oralResult) {
                $this->out(sprintf(
                    '  │   └── شفهي: %d%% × 40%% = %.1f%%                              │',
                    $oralResult['percentage'],
                    $oralResult['percentage'] * 0.4
                ));
            }

            $this->out(sprintf(
                '  │   ══> إجمالي أعمال السنة: %.1f%% × 40%% = %.1f%%              │',
                $courseworkResult['percentage'],
                $courseworkResult['percentage'] * 0.4
            ));
        }

        // تفصيل الاختبار النهائي
        $finalResult = $excellentResult['categories'][$this->finalExamCategory->id] ?? null;
        if ($finalResult) {
            $this->out(sprintf(
                '  │ اختبار نهائي (60%%): %d%% × 60%% = %.1f%%                     │',
                $finalResult['percentage'],
                $finalResult['percentage'] * 0.6
            ));
        }

        $this->out('  ├──────────────────────────────────────────────────────────────┤');
        $this->out(sprintf('  │ ⭐ المجموع النهائي: %.1f / 100                              │', $excellentResult['total']));
        $this->out(sprintf('  │ 📈 النسبة المئوية: %.1f%%                                    │', $excellentResult['percentage']));
        $this->out(sprintf(
            '  │ %s النتيجة: %s                                          │',
            $excellentResult['passed'] ? '✅' : '❌',
            $excellentResult['passed'] ? 'ناجح' : 'راسب'
        ));
        $this->out('  └──────────────────────────────────────────────────────────────┘');

        // حساب درجة الطالب الضعيف
        $this->out('');
        $this->item("🔄 حساب درجة: {$this->failingStudent->first_name_ar}");

        $failingResult = $calculator->calculateStudentGrade(
            $this->failingStudent,
            $this->courseOffering,
            $this->template,
            $this->marksForStudent($this->failingStudent)
        );

        $this->out('');
        $this->out('  ┌──────────────────────────────────────────────────────────────┐');
        $this->out("  │ 📊 تفصيل درجة: {$this->failingStudent->first_name_ar}                                   │");
        $this->out('  ├──────────────────────────────────────────────────────────────┤');

        // تفصيل أعمال السنة
        $courseworkResult = $failingResult['categories'][$this->courseworkCategory->id] ?? null;
        if ($courseworkResult) {
            $this->out(sprintf('  │ أعمال السنة (40%%):                                         │'));

            $writtenResult = $courseworkResult['children'][$this->writtenCategory->id] ?? null;
            if ($writtenResult) {
                $this->out(sprintf(
                    '  │   ├── تحريري: %d%% × 60%% = %.1f%%                            │',
                    $writtenResult['percentage'],
                    $writtenResult['percentage'] * 0.6
                ));
            }

            $oralResult = $courseworkResult['children'][$this->oralCategory->id] ?? null;
            if ($oralResult) {
                $this->out(sprintf(
                    '  │   └── شفهي: %d%% × 40%% = %.1f%%                              │',
                    $oralResult['percentage'],
                    $oralResult['percentage'] * 0.4
                ));
            }

            $this->out(sprintf(
                '  │   ══> إجمالي أعمال السنة: %.1f%% × 40%% = %.1f%%              │',
                $courseworkResult['percentage'],
                $courseworkResult['percentage'] * 0.4
            ));
        }

        // تفصيل الاختبار النهائي
        $finalResult = $failingResult['categories'][$this->finalExamCategory->id] ?? null;
        if ($finalResult) {
            $this->out(sprintf(
                '  │ اختبار نهائي (60%%): %d%% × 60%% = %.1f%%                      │',
                $finalResult['percentage'],
                $finalResult['percentage'] * 0.6
            ));
        }

        $this->out('  ├──────────────────────────────────────────────────────────────┤');
        $this->out(sprintf('  │ ⭐ المجموع النهائي: %.1f / 100                               │', $failingResult['total']));
        $this->out(sprintf('  │ 📈 النسبة المئوية: %.1f%%                                    │', $failingResult['percentage']));
        $this->out(sprintf(
            '  │ %s النتيجة: %s                                          │',
            $failingResult['passed'] ? '✅' : '❌',
            $failingResult['passed'] ? 'ناجح' : 'راسب'
        ));
        $this->out('  └──────────────────────────────────────────────────────────────┘');

        // === الخطوة 7: ملخص النتائج ===
        $this->step('النظام', 'الخطوة 7: كشف النتائج النهائي');

        $this->out('');
        $this->out('  ╔════════════════════════════════════════════════════════════════════╗');
        $this->out('  ║                     📋 كشف درجات المادة                            ║');
        $this->out('  ║                        الرياضيات                                   ║');
        $this->out('  ╠════════════════════════════════════════════════════════════════════╣');
        $this->out('  ║  الطالب           │  الدرجة  │  النسبة  │  النتيجة                 ║');
        $this->out('  ╠════════════════════════════════════════════════════════════════════╣');
        $this->out(sprintf(
            '  ║  %-16s │  %5.1f   │  %5.1f%%  │  %s                        ║',
            $this->excellentStudent->first_name_ar,
            $excellentResult['total'],
            $excellentResult['percentage'],
            $excellentResult['passed'] ? '✅ ناجح' : '❌ راسب'
        ));
        $this->out(sprintf(
            '  ║  %-16s │  %5.1f   │  %5.1f%%  │  %s                        ║',
            $this->failingStudent->first_name_ar,
            $failingResult['total'],
            $failingResult['percentage'],
            $failingResult['passed'] ? '✅ ناجح' : '❌ راسب'
        ));
        $this->out('  ╚════════════════════════════════════════════════════════════════════╝');

        // === التحقق ===
        $this->header('✅ التحقق من النتائج');

        // الطالب المتفوق يجب أن يكون ناجحاً
        $this->assertTrue($excellentResult['passed'], 'الطالب المتفوق يجب أن يكون ناجحاً');
        $this->assertGreaterThanOrEqual(50, $excellentResult['total'], 'درجة الطالب المتفوق >= 50');
        $this->item("✓ الطالب {$this->excellentStudent->first_name_ar}: ناجح بدرجة {$excellentResult['total']}");

        // الطالب الضعيف يجب أن يكون راسباً
        $this->assertFalse($failingResult['passed'], 'الطالب الضعيف يجب أن يكون راسباً');
        $this->assertLessThan(50, $failingResult['total'], 'درجة الطالب الضعيف < 50');
        $this->lastItem("✓ الطالب {$this->failingStudent->first_name_ar}: راسب بدرجة {$failingResult['total']}");

        $this->header('🎉 اكتمل الاختبار بنجاح');
    }
}
