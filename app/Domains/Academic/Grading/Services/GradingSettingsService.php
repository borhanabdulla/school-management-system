<?php

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Grading\Data\GeneralSettingsData;
use App\Domains\Academic\Grading\Data\GradeScaleData;
use App\Domains\Academic\Grading\Data\GradingSettingsInitData;
use App\Domains\Academic\Grading\Data\GradingSettingsViewData;
use App\Domains\Academic\Grading\Data\MonthlySettingsData;
use App\Domains\Academic\Grading\Data\SubjectConfigData;
use App\Domains\Academic\Grading\Data\TemplateData;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\GradingTemplate;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Actions\FinalizeTermCourseworkAction;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Services\TermLookupService;
use Illuminate\Support\Collection;

class GradingSettingsService
{
    public function __construct(
        private readonly TermLookupService $termLookup,
        private readonly GradeScaleValidator $scaleValidator,
        private readonly GradingLookupService $gradingLookup
    ) {
    }

    public function loadGeneralSettings(): GeneralSettingsData
    {
        $terms = $this->termLookup->getActiveTerms();
        $savedWeights = SystemSetting::get('grading.term_weights', []);
        $weights = [];

        foreach ($terms as $term) {
            $weights[$term->id] = $savedWeights[$term->id] ?? (100 / ($terms->count() ?: 1));
        }

        return new GeneralSettingsData(
            defaultPassScore: SystemSetting::get('grading.default_pass_score', 50),
            graceMarksLimit: SystemSetting::get('grading.grace_marks_limit', 2),
            termWeights: $weights
        );
    }

    public function buildInitialState(): GradingSettingsInitData
    {
        $generalSettings = $this->loadGeneralSettings();
        $gradeScale = $this->loadGradeScale();
        $defaultGradeId = $this->getDefaultGradeId();
        $activeTermId = $this->getActiveTermId();
        $firstTemplateId = $this->getFirstTemplateForTerm($activeTermId)?->id;
        $monthlySettings = $this->loadMonthlySettings();
        $subjectConfigs = [];

        if ($defaultGradeId && $activeTermId) {
            $subjectConfigs = $this->loadSubjectConfigs($defaultGradeId, $activeTermId);
        }

        return new GradingSettingsInitData(
            generalSettings: $generalSettings,
            gradeScale: $gradeScale,
            defaultGradeId: $defaultGradeId,
            activeTermId: $activeTermId,
            firstTemplateId: $firstTemplateId,
            monthlySettings: $monthlySettings,
            subjectConfigs: $subjectConfigs
        );
    }

    public function getActiveTermId(): ?int
    {
        return $this->termLookup->getActiveTerm()?->id;
    }

    public function getDefaultGradeId(): ?int
    {
        return Grade::query()->value('id');
    }

    public function getFirstTemplateForTerm(?int $termId): ?GradingTemplate
    {
        return GradingTemplate::query()
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->first();
    }

    public function getSubjectName(int $subjectId): string
    {
        return Subject::find($subjectId)?->name ?? '';
    }

    public function getTerm(int $termId): ?Term
    {
        return Term::find($termId);
    }

    public function getTermAcademicYearId(int $termId): ?int
    {
        return Term::find($termId)?->academic_year_id;
    }

    public function recomputeSubjectCoursework(int $termId, int $gradeId, int $subjectId): bool
    {
        $term = $this->getTerm($termId);
        if (! $term) {
            return false;
        }

        app(FinalizeTermCourseworkAction::class)->execute(
            term: $term,
            gradeId: $gradeId,
            subjectId: $subjectId
        );

        return true;
    }

    public function templateMatchesTerm(int $templateId, int $termId): bool
    {
        $template = GradingTemplate::find($templateId);
        if (! $template) {
            return false;
        }

        $term = Term::find($termId);
        if (! $term) {
            return false;
        }

        return $template->matchesTerm($term);
    }

    public function saveGeneralSettings(GeneralSettingsData $data): void
    {
        $totalWeight = array_sum($data->termWeights);
        if (abs($totalWeight - 100.0) > 0.01) {
            throw new \InvalidArgumentException('مجموع أوزان الفصول يجب أن يساوي 100%.');
        }

        $this->assertYearWritable(school()->activeYearId() ?? AcademicYear::first()?->id);

        SystemSetting::set('grading.default_pass_score', $data->defaultPassScore);
        SystemSetting::set('grading.grace_marks_limit', $data->graceMarksLimit);
        SystemSetting::set('grading.term_weights', $data->termWeights, 'grading', 'json');
    }

    public function loadGradeScale(): GradeScaleData
    {
        return new GradeScaleData(
            scale: SystemSetting::get('grading.scale', GradeScaleValidator::defaultScale())
        );
    }

    public function validateGradeScale(GradeScaleData $scale): array
    {
        return $this->scaleValidator->validate($scale->scale);
    }

    public function saveGradeScale(GradeScaleData $scale): void
    {
        $this->assertYearWritable(school()->activeYearId() ?? AcademicYear::first()?->id);
        SystemSetting::set('grading.scale', $scale->scale, 'grading', 'json');
        $this->gradingLookup->invalidateScaleCache();
    }

    public function loadTemplate(int $id): ?GradingTemplate
    {
        return GradingTemplate::with([
            'categories' => fn($q) => $q->whereNull('parent_id')->with('children'),
        ])->find($id);
    }

    public function saveTemplate(?int $id, TemplateData $payload): GradingTemplate
    {
        $term = $payload->termId ? Term::find($payload->termId) : null;
        if ($term) {
            app(AcademicWriteGuard::class)->assertWritable($term->academic_year_id, $term->id);
        } else {
            $this->assertYearWritable($payload->academicYearId);
        }

        return GradingTemplate::updateOrCreate(
            ['id' => $id],
            [
                'name' => $payload->name,
                'academic_year_id' => $payload->academicYearId ?: null,
                'grade_id' => $payload->gradeId ?: null,
                'term_id' => $payload->termId,
            ]
        );
    }

    public function getTemplatesForTerm(?int $termId): Collection
    {
        return GradingTemplate::query()
            ->when($termId, fn($q) => $q->where('term_id', $termId))
            ->get();
    }

    public function getRenderData(?int $templateTermId, ?int $subjectGradeId, ?int $subjectTermId): GradingSettingsViewData
    {
        $subjects = $subjectGradeId ? Grade::find($subjectGradeId)?->subjects ?? collect() : collect();
        $monthlyMappingStatus = $this->getMonthlyMappingStatus($subjectGradeId, $subjectTermId);
        $monthlyMappingSummary = $this->summarizeMonthlyMappingStatus($subjects, $monthlyMappingStatus);

        return new GradingSettingsViewData(
            templates: $this->getTemplatesForTerm($templateTermId),
            academicYears: AcademicYear::all(),
            grades: Grade::all(),
            terms: $this->termLookup->getActiveTerms(),
            subjects: $subjects,
            monthlyMappingStatus: $monthlyMappingStatus,
            monthlyMappingSummary: $monthlyMappingSummary
        );
    }

    /**
     * @param Collection<int, mixed> $subjects
     * @param array<int, array{status: string, mapped: int, total: int}> $status
     * @return array<string, int>
     */
    private function summarizeMonthlyMappingStatus(Collection $subjects, array $status): array
    {
        $summary = [
            'complete' => 0,
            'partial' => 0,
            'missing' => 0,
            'no_categories' => 0,
            'unavailable' => 0,
        ];

        foreach ($subjects as $subject) {
            $row = $status[$subject->id]['status'] ?? null;
            if (! $row || ! array_key_exists($row, $summary)) {
                $summary['unavailable']++;
                continue;
            }

            $summary[$row]++;
        }

        return $summary;
    }

    /**
     * @return array<int, array{status: string, mapped: int, total: int}>
     */
    public function getMonthlyMappingStatus(?int $gradeId, ?int $termId): array
    {
        if (! $gradeId || ! $termId) {
            return [];
        }

        $term = Term::find($termId);
        if (! $term) {
            return [];
        }

        $settings = GradebookSettings::findForYear($term->academic_year_id);
        $categories = $settings
            ? GradebookSettings::normalizeMonthlyCategories($settings->monthly_categories ?? [])
            : [];
        $categoryKeys = array_values(array_filter(array_map(
            fn($category) => $category['key'] ?? null,
            $categories
        )));
        $total = count($categoryKeys);

        $subjects = Grade::find($gradeId)?->subjects ?? collect();
        if ($subjects->isEmpty()) {
            return [];
        }

        $mappedCounts = MonthlyCategoryMapping::query()
            ->select('subject_id')
            ->selectRaw('COUNT(DISTINCT category_key) as mapped_count')
            ->where('academic_year_id', $term->academic_year_id)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->when($categoryKeys !== [], fn ($q) => $q->whereIn('category_key', $categoryKeys))
            ->groupBy('subject_id')
            ->pluck('mapped_count', 'subject_id')
            ->map(fn ($value) => (int) $value)
            ->all();

        $status = [];
        foreach ($subjects as $subject) {
            if ($total === 0) {
                $status[$subject->id] = [
                    'status' => 'no_categories',
                    'mapped' => 0,
                    'total' => 0,
                ];
                continue;
            }

            $mapped = $mappedCounts[$subject->id] ?? 0;
            $status[$subject->id] = [
                'status' => $mapped === 0 ? 'missing' : ($mapped < $total ? 'partial' : 'complete'),
                'mapped' => $mapped,
                'total' => $total,
            ];
        }

        return $status;
    }

    public function loadSubjectConfigs(int $gradeId, int $termId): array
    {
        $subjects = Grade::find($gradeId)?->subjects ?? [];
        $configs = SubjectGradingConfig::where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->pluck('grading_template_id', 'subject_id');

        $map = [];
        foreach ($subjects as $subject) {
            $map[$subject->id] = $configs[$subject->id] ?? '';
        }

        return $map;
    }

    public function saveSubjectConfig(SubjectConfigData $data): void
    {
        if (! $data->templateId) {
            throw new \InvalidArgumentException('يرجى اختيار قالب للمادة قبل الحفظ.');
        }

        if (! $this->templateMatchesTerm($data->templateId, $data->termId)) {
            throw new \InvalidArgumentException('القالب المختار لا يطابق الفصل الدراسي الحالي.');
        }

        $term = Term::find($data->termId);
        if ($term) {
            app(AcademicWriteGuard::class)->assertWritable($term->academic_year_id, $term->id);
        }

        $config = SubjectGradingConfig::firstOrNew([
            'subject_id' => $data->subjectId,
            'grade_id' => $data->gradeId,
            'term_id' => $data->termId,
        ]);

        $config->grading_template_id = $data->templateId;
        $config->save();
    }

    public function loadMonthlySettings(): ?MonthlySettingsData
    {
        $yearId = school()->activeYearId()
            ?? AcademicYear::first()?->id;

        if (! $yearId) {
            return null;
        }

        $settings = GradebookSettings::getForYear($yearId);
        $categories = GradebookSettings::normalizeMonthlyCategories(
            $settings->monthly_categories ?? []
        );
        if ($categories === []) {
            $categories = GradebookSettings::getDefaultCategories();
            $settings->update(['monthly_categories' => $categories]);
        }

        return new MonthlySettingsData(
            categories: $categories,
            attendanceDeductAfter: $settings->attendance_deduct_after,
            attendanceDeductPerAbsence: (float) $settings->attendance_deduct_per_absence,
            attendanceMaxScore: (float) $settings->attendance_max_score,
            allowCustomCategories: (bool) $settings->allow_custom_categories
        );
    }

    public function saveMonthlySettings(MonthlySettingsData $data): void
    {
        $yearId = school()->activeYearId()
            ?? AcademicYear::first()?->id;

        if (! $yearId) {
            return;
        }

        $this->assertYearWritable($yearId);

        $settings = GradebookSettings::getForYear($yearId);

        $settings->update([
            'monthly_categories' => GradebookSettings::normalizeMonthlyCategories($data->categories),
            'attendance_deduct_after' => $data->attendanceDeductAfter,
            'attendance_deduct_per_absence' => $data->attendanceDeductPerAbsence,
            'attendance_max_score' => $data->attendanceMaxScore,
            'allow_custom_categories' => $data->allowCustomCategories,
        ]);
    }

    private function assertYearWritable(?int $yearId): void
    {
        if ($yearId) {
            app(AcademicWriteGuard::class)->assertYearNotClosed($yearId);
        }
    }
}
