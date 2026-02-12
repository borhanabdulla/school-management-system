<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\SubjectConfigData;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Support\Facades\Auth;

/**
 * SaveSubjectGradingConfigAction - حفظ تكوين التقييم للمادة
 * 
 * @responsibility ربط المادة بقالب تقييم مع التحقق من التطابق مع الترم
 */
class SaveSubjectGradingConfigAction
{
    /**
     * تنفيذ Action لحفظ تكوين المادة
     * 
     * @param SubjectConfigData $data بيانات التكوين
     * @return SubjectGradingConfig
     * @throws GradingException
     */
    public function execute(SubjectConfigData $data): SubjectGradingConfig
    {
        $this->authorize();
        $term = $this->validateTemplateMatchesTerm($data);
        $this->assertWritable($term);

        // إنشاء أو تحديث التكوين
        $config = SubjectGradingConfig::updateOrCreate(
            [
                'subject_id' => $data->subjectId,
                'grade_id' => $data->gradeId,
                'term_id' => $data->termId,
            ],
            [
                'grading_template_id' => $data->templateId,
            ]
        );

        return $config->fresh();
    }

    /**
     * التحقق من الصلاحيات
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        if (!Auth::user()->can('grading.manage_settings')) {
            throw new GradingException('ليس لديك صلاحية إدارة تكوينات المواد.');
        }
    }

    /**
     * التحقق من أن القالب المختار يطابق الترم
     * 
     * Invariant: القالب يجب أن يكون إما:
     * 1. term-specific للترم المحدد
     * 2. year-level للسنة التي ينتمي لها الترم
     * 
     * @param SubjectConfigData $data
     * @return Term
     * @throws GradingException
     */
    protected function validateTemplateMatchesTerm(SubjectConfigData $data): Term
    {
        $template = GradingTemplate::find($data->templateId);

        if (!$template) {
            throw new GradingException("قالب التقييم #{$data->templateId} غير موجود.");
        }

        // التحقق من أن القالب للـ grade الصحيح
        if ($template->grade_id !== $data->gradeId) {
            throw new GradingException(
                "قالب التقييم المحدد لا يطابق الصف الدراسي. " .
                "القالب للصف #{$template->grade_id} بينما التكوين للصف #{$data->gradeId}"
            );
        }

        // التحقق من أن القالب يطابق الترم أو السنة
        $term = Term::find($data->termId);

        if (!$term) {
            throw new GradingException("الفصل الدراسي #{$data->termId} غير موجود.");
        }

        if ($template->matchesTerm($term)) {
            return $term;
        }

        // أي حالة أخرى - مرفوض
        throw new GradingException(
            "قالب التقييم المحدد لا يطابق الفصل الدراسي. " .
            "القالب للفصل #{$template->term_id} بينما التكوين للفصل #{$data->termId}"
        );
    }

    private function assertWritable(Term $term): void
    {
        app(AcademicWriteGuard::class)->assertWritable($term->academic_year_id, $term->id);
    }
}
