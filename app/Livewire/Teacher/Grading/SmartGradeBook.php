<?php

namespace App\Livewire\Teacher\Grading;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Actions\RecordMonthlyGradeAction;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Models\GradebookMonth;
use App\Domains\Academic\Grading\Models\GradebookSettings;
use App\Domains\Academic\Grading\Models\MonthlyCategoryMapping;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Services\AcademicWriteGuard;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;

#[Layout('layouts.app')]
class SmartGradeBook extends Component
{
    public $courseOfferingId;
    public $courseOffering;
    public $settings;
    public $gradeScale = [];

    // UI State
    public bool $showAddCategoryModal = false;
    public string $newCategoryName = '';
    public float $newCategoryMaxScore = 10;

    /**
     * Grades Data: [student_id][month_id][category_key] = score
     */
    public array $grades = [];

    /**
     * Caches to avoid re-querying on the same request / re-renders.
     * Keeping them public is okay for Livewire hydration; if you prefer, you can make them protected
     * but then ensure they are always re-built on mount.
     */
    public $monthsCache;   // Collection|null
    public $studentsCache; // Collection|null

    public function mount($courseOfferingId)
    {
        $this->courseOfferingId = $courseOfferingId;

        $this->courseOffering = CourseOffering::with([
            'subject',
            'classSection.grade',
            // important: don't eager-load students here if you later call students() with withCount
            // but if you keep it, it won't break, just may load extra.
            'academicYear',
            'term',
        ])->findOrFail($courseOfferingId);

        $this->authorizeOffering('view');

        $this->loadSettings();
        $this->loadGradeScale();

        // Ensure months exist ONCE (no side-effects in Computed)
        $this->ensureMonthsForTerm();

        // Warm caches once so later calls don't keep querying
        $this->monthsCache = $this->queryMonths();
        $this->studentsCache = $this->queryStudentsWithAbsences();

        $this->loadGrades();
    }

    public function loadSettings()
    {
        $academicYearId = (int) $this->courseOffering->academic_year_id;
        $this->settings = GradebookSettings::findForYear($academicYearId)
            ?? GradebookSettings::makeDefault($academicYearId);
    }

    public function loadGradeScale()
    {
        $this->gradeScale = SystemSetting::get('grading.scale', []);
    }

    /**
     * Side-effect safe: only read months.
     */
    private function queryMonths()
    {
        $termId = $this->courseOffering->term_id;

        if (!$termId) {
            return collect();
        }

        return GradebookMonth::where('term_id', $termId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Runs once in mount to ensure months exist (if your generateForTerm is idempotent, great).
     * This avoids "writing to DB" inside a Computed property.
     */
    private function ensureMonthsForTerm(): void
    {
        $termId = $this->courseOffering->term_id;

        if (!$termId || !$this->courseOffering->term) {
            return;
        }

        $exists = GradebookMonth::where('term_id', $termId)->exists();
        if (!$exists) {
            app(AcademicWriteGuard::class)->assertTermNotCompleted($termId);
            GradebookMonth::generateForTerm($this->courseOffering->term);
        }
    }

    /**
     * Fix: closure must capture $term using "use ($term)".
     * Also: use query builder from relation to avoid loading full attendances.
     */
    private function queryStudentsWithAbsences()
    {
        $term = $this->courseOffering->term;

        // Relation query (students of classSection)
        return $this->courseOffering->classSection->students()
            ->withCount([
                'attendances as absences_count' => function ($query) use ($term) {
                    $query->where('status', 'absent');

                    if ($term && $term->start_date && $term->end_date) {
                        $query->whereBetween('date', [$term->start_date, $term->end_date]);
                    }
                },
            ])
            ->get();
    }

    #[Computed]
    public function months()
    {
        // Return cache if available, otherwise query
        return $this->monthsCache ??= $this->queryMonths();
    }

    #[Computed]
    public function categories(): array
    {
        // Guard: settings could be null (edge case)
        $categories = $this->settings?->monthly_categories ?? null;

        $categories = is_array($categories) && !empty($categories)
            ? $categories
            : GradebookSettings::getDefaultCategories();

        return GradebookSettings::normalizeMonthlyCategories($categories);
    }

    #[Computed]
    public function canManageGradebookSettings(): bool
    {
        $user = auth()->user();
        return $user !== null && $user->can('curriculum.manage');
    }

    #[Computed]
    public function students()
    {
        // Return cache if available, otherwise query
        return $this->studentsCache ??= $this->queryStudentsWithAbsences();
    }

    public function loadGrades()
    {
        $studentIds = $this->students->pluck('id');
        $monthIds   = $this->months->pluck('id');

        if ($studentIds->isEmpty() || $monthIds->isEmpty()) {
            $this->grades = [];
            return;
        }

        $categoryKeyMap = $this->resolveTemplateCategoryKeyMap();

        $existingGrades = MonthlyGrade::where('course_offering_id', $this->courseOfferingId)
            ->whereIn('student_id', $studentIds)
            ->whereIn('gradebook_month_id', $monthIds)
            // performance: fetch only what we need
            ->get(['student_id', 'gradebook_month_id', 'category', 'category_key', 'template_category_id', 'score']);

        foreach ($existingGrades as $grade) {
            $categoryKey = '';
            $templateCategoryId = $grade->template_category_id;
            if ($templateCategoryId && isset($categoryKeyMap[$templateCategoryId])) {
                $categoryKey = (string) $categoryKeyMap[$templateCategoryId];
            } else {
                $categoryKey = (string) ($grade->category_key ?? '');
                if ($categoryKey === '' && $grade->category) {
                    $categoryKey = $this->resolveCategoryKey($grade->category);
                }
            }

            if ($categoryKey === '') {
                continue;
            }

            $this->grades[$grade->student_id][$grade->gradebook_month_id][$categoryKey] = $grade->score;
        }
    }

    public function getAbsenceWarning($studentId)
    {
        $student = $this->students->firstWhere('id', $studentId);
        $absences = $student->absences_count ?? 0;

        if ($absences >= 4) {
            return [
                'warning' => true,
                'message' => "تجاوز حد الغياب ($absences أيام)",
                'color' => 'text-red-600 font-bold',
            ];
        }

        return ['warning' => false];
    }

    public function getGradeInfo($score)
    {
        $maxScore = $this->getGrandMaxScore();
        $isPassing = $score >= ($maxScore * 0.5);
        $grade = $this->getLetterGrade($score, $maxScore);

        return array_merge($grade, ['is_passing' => $isPassing]);
    }

    public function updateGrade($studentId, $monthId, $categoryKey, $value)
    {
        $this->authorizeOffering('edit');

        $termId = $this->courseOffering->term_id;
        if ($termId) {
            app(AcademicWriteGuard::class)->assertTermNotCompleted((int) $termId);
        }

        $value = $value === '' ? null : (float) $value;

        // Find max score for this category (avoid rebuilding collection too often)
        $categoryData = null;
        foreach ($this->categories as $cat) {
            if (($cat['key'] ?? null) === $categoryKey) {
                $categoryData = $cat;
                break;
            }
        }
        $maxScore = $categoryData['max_score'] ?? 10;
        $categoryLabel = $categoryData['label'] ?? (string) $categoryKey;

        // Validate bounds
        if ($value !== null && ($value < 0 || $value > $maxScore)) {
            $this->dispatch('notify', message: "الدرجة يجب أن تكون بين 0 و $maxScore", type: 'error');
            return;
        }

        try {
            app(RecordMonthlyGradeAction::class)->execute(
                offering: $this->courseOffering,
                studentId: (int) $studentId,
                monthId: (int) $monthId,
                categoryKey: (string) $categoryKey,
                score: $value,
                maxScore: (float) $maxScore,
                categoryLabel: (string) $categoryLabel,
                gradedByUserId: auth()->id()
            );
        } catch (InvalidOperationException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
            return;
        }

        // Update local state (keeps UI consistent without reloading)
        $this->grades[$studentId][$monthId][$categoryKey] = $value;

        $this->dispatch('notify', message: 'تم حفظ الدرجة', type: 'success');
    }

    public function getStudentMonthTotal($studentId, $monthId): float
    {
        $total = 0.0;

        foreach ($this->categories as $cat) {
            $key = $cat['key'] ?? null;
            if (!$key) {
                continue;
            }
            $total += (float) ($this->grades[$studentId][$monthId][$key] ?? 0);
        }

        return round($total, 1);
    }

    public function getStudentGrandTotal($studentId): float
    {
        $total = 0.0;

        foreach ($this->months as $month) {
            $total += $this->getStudentMonthTotal($studentId, $month->id);
        }

        return round($total, 1);
    }

    public function getMonthMaxScore(): float
    {
        $sum = 0.0;
        foreach ($this->categories as $cat) {
            $sum += (float) ($cat['max_score'] ?? 0);
        }
        return $sum;
    }

    public function getGrandMaxScore(): float
    {
        return $this->getMonthMaxScore() * (float) $this->months->count();
    }

    public function getLetterGrade($score, $maxScore): array
    {
        if ((float) $maxScore === 0.0) {
            return ['grade' => '-', 'color' => 'gray']; // avoid invalid "#gray"
        }

        $percentage = ((float) $score / (float) $maxScore) * 100;

        foreach ($this->gradeScale as $level) {
            $min = $level['min'] ?? null;
            $max = $level['max'] ?? null;

            if ($min === null || $max === null) {
                continue;
            }

            if ($percentage >= $min && $percentage <= $max) {
                return $level;
            }
        }

        return ['grade' => '-', 'color' => 'gray'];
    }

    #[Computed]
    public function classStats(): array
    {
        $totals = [];

        foreach ($this->students as $student) {
            $totals[] = $this->getStudentGrandTotal($student->id);
        }

        if (empty($totals)) {
            return ['avg' => 0, 'max' => 0, 'min' => 0, 'passing' => 0];
        }

        $passScore = $this->getGrandMaxScore() * 0.5;

        return [
            'avg' => round(array_sum($totals) / count($totals), 1),
            'max' => max($totals),
            'min' => min($totals),
            'passing' => count(array_filter($totals, fn ($t) => $t >= $passScore)),
        ];
    }

    public function addCustomCategory()
    {
        $this->authorizeGradebookSettingsWrite();

        if (!$this->settings?->allow_custom_categories) {
            $this->dispatch('notify', message: 'الإضافة غير مسموحة حالياً', type: 'error');
            return;
        }

        $this->validate([
            'newCategoryName' => 'required|string|max:50',
            'newCategoryMaxScore' => 'required|numeric|min:1|max:100',
        ]);

        $label = trim($this->newCategoryName);

        $categories = $this->settings->monthly_categories ?? [];
        $normalized = GradebookSettings::normalizeMonthlyCategories($categories);

        // Optional safe improvement: prevent duplicates (doesn't break existing behavior, just guards)
        foreach ($normalized as $cat) {
            if (isset($cat['label']) && mb_strtolower($cat['label']) === mb_strtolower($label)) {
                $this->dispatch('notify', message: 'هذا القسم موجود مسبقاً', type: 'error');
                return;
            }
        }

        $existingKeys = array_map(fn($cat) => $cat['key'] ?? '', $normalized);
        $key = GradebookSettings::ensureUniqueCategoryKey(
            GradebookSettings::generateCategoryKey($label),
            $existingKeys
        );

        $normalized[] = [
            'key' => $key,
            'label' => $label,
            'max_score' => $this->newCategoryMaxScore,
            'is_default' => false,
            'is_attendance' => false,
        ];

        $this->settings->update(['monthly_categories' => $normalized]);

        // Refresh categories cache usage is automatic via $this->settings
        $this->showAddCategoryModal = false;
        $this->newCategoryName = '';
        $this->newCategoryMaxScore = 10;

        $this->dispatch('notify', message: 'تمت إضافة القسم بنجاح', type: 'success');
    }

    public function render()
    {
        return view('livewire.teacher.grading.smart-grade-book');
    }

    private function resolveCategoryKey(string $label): string
    {
        foreach ($this->categories as $cat) {
            if (($cat['label'] ?? null) === $label) {
                return (string) ($cat['key'] ?? '');
            }
        }

        return GradebookSettings::generateCategoryKey($label);
    }

    private function resolveTemplateCategoryKeyMap(): array
    {
        $termId = $this->courseOffering->term_id;
        $gradeId = $this->courseOffering->classSection?->grade_id;
        $subjectId = $this->courseOffering->subject_id;
        $academicYearId = $this->courseOffering->academic_year_id;

        if (! $termId || ! $gradeId || ! $subjectId || ! $academicYearId) {
            return [];
        }

        return MonthlyCategoryMapping::query()
            ->where('academic_year_id', $academicYearId)
            ->where('term_id', $termId)
            ->where('grade_id', $gradeId)
            ->where('subject_id', $subjectId)
            ->pluck('category_key', 'template_category_id')
            ->toArray();
    }

    private function authorizeOffering(string $ability): void
    {
        Gate::authorize($ability, $this->courseOffering);
    }

    private function authorizeGradebookSettingsWrite(): void
    {
        Gate::authorize('curriculum.manage');
    }
}
