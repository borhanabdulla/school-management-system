<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Actions\Admin;

use App\Domains\Academic\Grading\Data\TemplateData;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Services\GradingLookupService;
use App\Domains\Academic\Grading\Exceptions\GradingException;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Services\AcademicWriteGuard;
use Illuminate\Support\Facades\Auth;

/**
 * SaveGradingTemplateAction - حفظ قالب التقييم
 * 
 * @responsibility إنشاء أو تحديث قالب التقييم مع التحقق من اتساق الترم والسنة
 */
class SaveGradingTemplateAction
{
    public function __construct(
        private readonly GradingLookupService $lookupService
    ) {
    }

    /**
     * تنفيذ Action لحفظ قالب التقييم
     * 
     * @param int|null $templateId معرف القالب (null للإنشاء الجديد)
     * @param TemplateData $data بيانات القالب
     * @return GradingTemplate
     * @throws GradingException
     */
    public function execute(?int $templateId, TemplateData $data): GradingTemplate
    {
        $this->authorize();
        $term = $this->validateTermYearConsistency($data);
        $this->assertWritable($data, $term);

        if ($templateId) {
            // تحديث قالب موجود
            $template = GradingTemplate::findOrFail($templateId);
            $template->update([
                'name' => $data->name,
                'grade_id' => $data->gradeId,
                'academic_year_id' => $data->academicYearId,
                'term_id' => $data->termId,
            ]);
        } else {
            // إنشاء قالب جديد
            $template = GradingTemplate::create([
                'name' => $data->name,
                'grade_id' => $data->gradeId,
                'academic_year_id' => $data->academicYearId,
                'term_id' => $data->termId,
            ]);
        }

        // إبطال cache القوالب
        $this->lookupService->invalidateTemplatesCache();

        return $template->fresh();
    }

    /**
     * التحقق من الصلاحيات
     */
    protected function authorize(): void
    {
        if (!Auth::check()) {
            throw new GradingException('يجب تسجيل الدخول أولاً.');
        }

        if (!Auth::user()->can('grading.manage_templates')) {
            throw new GradingException('ليس لديك صلاحية إدارة قوالب التقييم.');
        }
    }

    /**
     * التحقق من اتساق الترم مع السنة الدراسية
     * 
     * Invariant: إذا كان termId موجوداً، يجب أن ينتمي للـ academicYearId
     * 
     * @param TemplateData $data
     * @return Term|null
     * @throws GradingException
     */
    protected function validateTermYearConsistency(TemplateData $data): ?Term
    {
        // إذا لم يكن هناك termId، فهو قالب على مستوى السنة (year-level) - مقبول
        if (!$data->termId) {
            return null;
        }

        // التحقق من أن الترم ينتمي للسنة الدراسية المحددة
        $term = Term::find($data->termId);

        if (!$term) {
            throw new GradingException("الفصل الدراسي #{$data->termId} غير موجود.");
        }

        if ($term->academic_year_id !== $data->academicYearId) {
            throw new GradingException(
                "الفصل الدراسي المحدد (#{$data->termId}) لا ينتمي للسنة الدراسية المحددة (#{$data->academicYearId}). " .
                "الفصل ينتمي للسنة #{$term->academic_year_id}"
            );
        }

        return $term;
    }

    private function assertWritable(TemplateData $data, ?Term $term): void
    {
        if ($term) {
            app(AcademicWriteGuard::class)->assertWritable($term->academic_year_id, $term->id);
            return;
        }

        if ($data->academicYearId) {
            app(AcademicWriteGuard::class)->assertYearNotClosed($data->academicYearId);
        }
    }
}
