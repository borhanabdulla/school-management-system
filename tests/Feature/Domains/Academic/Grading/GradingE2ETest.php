<?php

namespace Tests\Feature\Domains\Academic\Grading;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Models\Assessment;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Grading\Actions\CalculateTermGradesAction;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\TemplateCategory;
use App\Domains\Academic\Grading\Services\GradingCalculatorService;
use App\Domains\Academic\Homework\Models\Homework;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\StudentMark;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ════════════════════════════════════════════════════════════════════════════
 * اختبار E2E لنظام الدرجات - التدفق الكامل
 * ════════════════════════════════════════════════════════════════════════════
 * 
 * التدفق:
 *   المدير → إنشاء GradingTemplate + TemplateCategory
 *   الأستاذ → إنشاء Assessment + إدخال StudentMark
 *   النظام → حساب الدرجات + تحديد ناجح/راسب
 * ════════════════════════════════════════════════════════════════════════════
 */
class GradingE2ETest extends TestCase
{
    use RefreshDatabase;

    private AcademicYear $academicYear;
    private Term $term;
    private Grade $grade;
    private ClassSection $classSection;
    private Subject $subject;
    private CourseOffering $courseOffering;
    private GradingTemplate $template;
    private Student $passingStudent;
    private Student $failingStudent;

    private TemplateCategory $writtenCategory;
    private TemplateCategory $oralCategory;
    private TemplateCategory $examCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->log('═══════════════════════════════════════════════════');
        $this->log('بدء اختبار نظام الدرجات E2E');
        $this->log('═══════════════════════════════════════════════════');

        $this->setupAcademicStructure();
        $this->setupGradingTemplate();
        $this->setupAssessments();
        $this->setupStudents();
    }

    private function log(string $msg): void
    {
        fwrite(STDOUT, "\n✓ {$msg}\n");
    }

    // ═══════════════════════════════════════════════════════════════
    // الخطوة 1: البنية الأكاديمية (يقوم بها المدير)
    // ═══════════════════════════════════════════════════════════════

    private function setupAcademicStructure(): void
    {
        $this->log('【المدير】 الخطوة 1: إنشاء البنية الأكاديمية');

        // السنة الدراسية - استخدام active() state
        $this->academicYear = AcademicYear::factory()->active()->create();
        $this->log("  ↳ السنة: {$this->academicYear->name}");

        // الفصل الدراسي
        $this->term = Term::factory()->create([
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->log("  ↳ الفصل: {$this->term->name}");

        // الصف - factory تنشئ EducationalStage تلقائياً
        $this->grade = Grade::factory()->create();
        $this->log("  ↳ الصف: {$this->grade->name}");

        // الشعبة
        $this->classSection = ClassSection::factory()->create([
            'grade_id' => $this->grade->id,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->log("  ↳ الشعبة: {$this->classSection->name}");

        // المادة
        $this->subject = Subject::factory()->create();
        $this->log("  ↳ المادة: {$this->subject->name}");

        // ربط المادة بالشعبة (CourseOffering)
        $this->courseOffering = CourseOffering::factory()->create([
            'class_section_id' => $this->classSection->id,
            'subject_id' => $this->subject->id,
            'term_id' => $this->term->id,
        ]);
        $this->log("  ↳ ربط المادة بالشعبة ✓");
    }

    // ═══════════════════════════════════════════════════════════════
    // الخطوة 2: قالب التقييم (يقوم به المدير)
    // ═══════════════════════════════════════════════════════════════

    private function setupGradingTemplate(): void
    {
        $this->log('【المدير】 الخطوة 2: إنشاء قالب التقييم');

        // القالب الرئيسي: 100 درجة، النجاح 50
        $this->template = GradingTemplate::factory()->create([
            'total_max_score' => 100,
            'pass_score' => 50,
            'academic_year_id' => $this->academicYear->id,
        ]);
        $this->log("  ↳ القالب: {$this->template->name}");
        $this->log("    • الدرجة القصوى: {$this->template->total_max_score}");
        $this->log("    • درجة النجاح: {$this->template->pass_score}");

        // فئة: أعمال السنة (40%)
        $coursework = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'name' => 'أعمال السنة',
            'weight' => 40,
            'parent_id' => null,
        ]);
        $this->log("  ↳ فئة: أعمال السنة (40%)");

        // تحريري (50% من 40% = 20%)
        $this->writtenCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'parent_id' => $coursework->id,
            'name' => 'تحريري',
            'weight' => 50,
        ]);
        $this->log("    • تحريري (50%)");

        // شفهي (50% من 40% = 20%)
        $this->oralCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'parent_id' => $coursework->id,
            'name' => 'شفهي',
            'weight' => 50,
        ]);
        $this->log("    • شفهي (50%)");

        // فئة: اختبار نهائي (60%)
        $this->examCategory = TemplateCategory::factory()->create([
            'grading_template_id' => $this->template->id,
            'name' => 'اختبار نهائي',
            'weight' => 60,
            'parent_id' => null,
        ]);
        $this->log("  ↳ فئة: اختبار نهائي (60%)");

        // ربط القالب بالمادة
        SubjectGradingConfig::updateOrCreate([
            'subject_id' => $this->subject->id,
            'grade_id' => $this->grade->id,
            'term_id' => $this->term->id,
        ], [
            'grading_template_id' => $this->template->id,
            'max_score' => 100,
            'pass_score' => 50,
            'counts_in_gpa' => true,
        ]);
        $this->log("  ↳ ربط القالب بالمادة ✓");
    }

    // ═══════════════════════════════════════════════════════════════
    // الخطوة 3: الاختبارات (يقوم بها الأستاذ)
    // ═══════════════════════════════════════════════════════════════

    private function setupAssessments(): void
    {
        $this->log('【الأستاذ】 الخطوة 3: إنشاء الاختبارات');

        Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->writtenCategory->id,
            'title' => 'اختبار تحريري',
            'max_score' => 100,
        ]);
        $this->log("  ↳ اختبار تحريري (100)");

        Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->oralCategory->id,
            'title' => 'اختبار شفهي',
            'max_score' => 100,
        ]);
        $this->log("  ↳ اختبار شفهي (100)");

        Assessment::factory()->create([
            'course_offering_id' => $this->courseOffering->id,
            'template_category_id' => $this->examCategory->id,
            'title' => 'اختبار نهائي',
            'max_score' => 100,
        ]);
        $this->log("  ↳ اختبار نهائي (100)");
    }

    // ═══════════════════════════════════════════════════════════════
    // الخطوة 4: الطلاب
    // ═══════════════════════════════════════════════════════════════

    private function setupStudents(): void
    {
        $this->log('الخطوة 4: إنشاء الطلاب');

        $this->passingStudent = Student::factory()->create([
            'current_class_section_id' => $this->classSection->id,
        ]);
        $this->log("  ↳ طالب 1: {$this->passingStudent->first_name_ar}");

        $this->failingStudent = Student::factory()->create([
            'current_class_section_id' => $this->classSection->id,
        ]);
        $this->log("  ↳ طالب 2: {$this->failingStudent->first_name_ar}");
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

    // ═══════════════════════════════════════════════════════════════
    // الاختبارات
    // ═══════════════════════════════════════════════════════════════

    #[Test]
    public function passing_student_flow(): void
    {
        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log('【الأستاذ】 إدخال درجات الطالب الناجح');
        $this->log('═══════════════════════════════════════════════════');

        // الأستاذ يدخل الدرجات
        $assessments = Assessment::where('course_offering_id', $this->courseOffering->id)->get();

        foreach ($assessments as $assessment) {
            StudentMark::factory()->create([
                'student_id' => $this->passingStudent->id,
                'assessment_id' => $assessment->id,
                'raw_score' => 85, // درجة عالية
            ]);
            $this->log("  ↳ {$assessment->title}: 85/100");
        }

        // النظام يحسب النتيجة
        $this->log('');
        $this->log('【النظام】 حساب الدرجات');

        $calculator = app(GradingCalculatorService::class);
        $this->template->load(['categories' => fn($q) => $q->with('children')]);

        $marks = $this->marksForStudent($this->passingStudent);
        $result = $calculator->calculateStudentGrade(
            $this->passingStudent,
            $this->courseOffering,
            $this->template,
            $marks
        );

        $this->log("  ↳ الدرجة: {$result['total']}/{$result['max']}");
        $this->log("  ↳ النسبة: {$result['percentage']}%");
        $this->log("  ↳ النتيجة: " . ($result['passed'] ? '✓ ناجح' : '✗ راسب'));

        // التحقق
        $this->assertTrue($result['passed']);
        $this->assertGreaterThanOrEqual(50, $result['total']);

        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log("الطالب {$this->passingStudent->first_name_ar}: ناجح ✓");
        $this->log('═══════════════════════════════════════════════════');
    }

    #[Test]
    public function failing_student_flow(): void
    {
        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log('【الأستاذ】 إدخال درجات الطالب الراسب');
        $this->log('═══════════════════════════════════════════════════');

        $assessments = Assessment::where('course_offering_id', $this->courseOffering->id)->get();

        foreach ($assessments as $assessment) {
            StudentMark::factory()->create([
                'student_id' => $this->failingStudent->id,
                'assessment_id' => $assessment->id,
                'raw_score' => 30, // درجة منخفضة
            ]);
            $this->log("  ↳ {$assessment->title}: 30/100");
        }

        $this->log('');
        $this->log('【النظام】 حساب الدرجات');

        $calculator = app(GradingCalculatorService::class);
        $this->template->load(['categories' => fn($q) => $q->with('children')]);

        $marks = $this->marksForStudent($this->failingStudent);
        $result = $calculator->calculateStudentGrade(
            $this->failingStudent,
            $this->courseOffering,
            $this->template,
            $marks
        );

        $this->log("  ↳ الدرجة: {$result['total']}/{$result['max']}");
        $this->log("  ↳ النسبة: {$result['percentage']}%");
        $this->log("  ↳ النتيجة: " . ($result['passed'] ? '✓ ناجح' : '✗ راسب'));

        $this->assertFalse($result['passed']);
        $this->assertLessThan(50, $result['total']);

        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log("الطالب {$this->failingStudent->first_name_ar}: راسب ✗");
        $this->log('═══════════════════════════════════════════════════');
    }

    #[Test]
    public function complete_results_summary(): void
    {
        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log('كشف الدرجات النهائي');
        $this->log('═══════════════════════════════════════════════════');

        $assessments = Assessment::where('course_offering_id', $this->courseOffering->id)->get();

        // طالب ناجح: درجات مختلفة
        $passingScores = [85, 90, 75];
        // طالب راسب: درجات منخفضة
        $failingScores = [30, 25, 35];

        $i = 0;
        foreach ($assessments as $assessment) {
            StudentMark::factory()->create([
                'student_id' => $this->passingStudent->id,
                'assessment_id' => $assessment->id,
                'raw_score' => $passingScores[$i] ?? 80,
            ]);

            StudentMark::factory()->create([
                'student_id' => $this->failingStudent->id,
                'assessment_id' => $assessment->id,
                'raw_score' => $failingScores[$i] ?? 30,
            ]);
            $i++;
        }

        $calculator = app(GradingCalculatorService::class);
        $this->template->load(['categories' => fn($q) => $q->with('children')]);

        $r1 = $calculator->calculateStudentGrade(
            $this->passingStudent,
            $this->courseOffering,
            $this->template,
            $this->marksForStudent($this->passingStudent)
        );
        $r2 = $calculator->calculateStudentGrade(
            $this->failingStudent,
            $this->courseOffering,
            $this->template,
            $this->marksForStudent($this->failingStudent)
        );

        $this->log('');
        $this->log('┌─────────────────────────────────────────────────┐');
        $this->log('│              كشف درجات المادة                   │');
        $this->log('├────────────────┬─────────┬──────────┬───────────┤');
        $this->log('│ الطالب         │ الدرجة  │ النسبة   │ النتيجة   │');
        $this->log('├────────────────┼─────────┼──────────┼───────────┤');
        $this->log(sprintf(
            '│ %-14s │ %5.1f   │ %5.1f%%   │ %-9s │',
            mb_substr($this->passingStudent->first_name_ar, 0, 10),
            $r1['total'],
            $r1['percentage'],
            $r1['passed'] ? '✓ ناجح' : '✗ راسب'
        ));
        $this->log(sprintf(
            '│ %-14s │ %5.1f   │ %5.1f%%   │ %-9s │',
            mb_substr($this->failingStudent->first_name_ar, 0, 10),
            $r2['total'],
            $r2['percentage'],
            $r2['passed'] ? '✓ ناجح' : '✗ راسب'
        ));
        $this->log('└────────────────┴─────────┴──────────┴───────────┘');

        $this->assertTrue($r1['passed']);
        $this->assertFalse($r2['passed']);

        $this->log('');
        $this->log('═══════════════════════════════════════════════════');
        $this->log('✓ اكتمل الاختبار بنجاح');
        $this->log('═══════════════════════════════════════════════════');
    }
}
