<?php

namespace App\Livewire\Admin\Promotion;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Promotion\Models\Promotion;

use App\Domains\Academic\Promotion\Services\PromotionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class PromotionManager extends Component
{
    use WithPagination;

    #[Locked]
    public $selectedYearId = null;

    #[Locked]
    public $selectedGradeId = null;

    public string $statusFilter = 'pending'; // pending, promoted, all
    public string $search = '';

    public bool $showBulkPromoteModal = false;
    public bool $showRevertModal = false;

    #[Locked]
    public ?int $revertingPromotionId = null;
    public string $revertReason = '';


    public array $selectedStudents = [];

    protected $queryString = ['selectedYearId', 'selectedGradeId', 'statusFilter', 'search'];

    protected ?\App\Domains\Academic\Student\Services\StudentLookupService $studentLookup = null;

    protected function lookup(): \App\Domains\Academic\Student\Services\StudentLookupService
    {
        return $this->studentLookup ??= app(\App\Domains\Academic\Student\Services\StudentLookupService::class);
    }

    public function mount()
    {
        $this->selectedYearId = school()->activeYearId()
            ?? AcademicYear::latest('start_date')->first()?->id;
    }

    public function updated($propertyName)
    {
        if ($this->$propertyName === '') {
            $this->$propertyName = null;
        }
    }

    public function getAcademicYearsProperty()
    {
        return AcademicYear::orderByDesc('start_date')->get();
    }

    public function getGradesProperty()
    {
        return Grade::with('stage')->get()->sortBy('stage.rank');
    }

    public function getNextYearProperty()
    {
        if (!$this->selectedYearId)
            return null;
        $currentYear = AcademicYear::find($this->selectedYearId);

        return app(PromotionService::class)->getNextAcademicYear($currentYear);
    }

    public function getStudentsProperty()
    {
        if (!$this->selectedYearId) {
            return collect();
        }

        return $this->lookup()->paginateForPromotion(
            yearId: $this->selectedYearId,
            gradeId: $this->selectedGradeId,
            search: $this->search,
            statusFilter: $this->statusFilter
        );
    }

    public function getPromotionsProperty()
    {
        if (!$this->selectedYearId) {
            return collect();
        }

        return Promotion::with(['student', 'fromGrade', 'toGrade', 'toClassSection', 'processedBy'])
            ->where('academic_year_id', $this->selectedYearId)
            ->where('is_reverted', false)
            ->when($this->selectedGradeId, fn($q) => $q->where('from_grade_id', $this->selectedGradeId))
            ->orderByDesc('processed_at')
            ->get();
    }

    public function getStatisticsProperty()
    {
        if (!$this->selectedYearId)
            return null;
        $year = AcademicYear::find($this->selectedYearId);
        return app(PromotionService::class)->getStatistics($year);
    }

    /**
     * فحص جاهزية الترحيل
     */
    public function getReadinessCheckProperty(): array
    {
        if (!$this->selectedYearId) {
            return ['ready' => false, 'issues' => [], 'stats' => []];
        }

        $year = AcademicYear::find($this->selectedYearId);
        return app(PromotionService::class)->validateSchoolReadyForPromotion($year);
    }

    /**
     * معاينة تكوين الصفوف للسنة الجديدة
     */
    public function getNewYearCompositionProperty(): array
    {
        if (!$this->selectedYearId) {
            return [];
        }

        $year = AcademicYear::find($this->selectedYearId);
        return app(PromotionService::class)->getNewYearComposition($year);
    }

    /**
     * ترحيل طالب واحد
     */
    public function promoteStudent(int $studentId)
    {
        try {
            $student = $this->lookup()->findForShow($studentId);
            if (!$student) {
                $this->dispatch('error', message: 'الطالب غير موجود');
                return;
            }

            $year = AcademicYear::findOrFail($this->selectedYearId);

            app(PromotionService::class)->promote($student, $year);
            $this->dispatch('notify', message: "تم ترحيل الطالب {$student->full_name_ar} بنجاح", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    /**
     * ترحيل جماعي
     */
    public function bulkPromote()
    {
        if (empty($this->selectedStudents)) {
            $this->dispatch('error', message: 'يرجى اختيار طلاب للترحيل');
            return;
        }

        try {
            $students = $this->lookup()->findManyByIds($this->selectedStudents);
            $year = AcademicYear::findOrFail($this->selectedYearId);

            $result = app(PromotionService::class)->bulkPromote($students, $year);

            $this->dispatch('notify', message: "تم ترحيل {$result['success']} طالب بنجاح. فشل: {$result['failed']}", type: 'success');

            $this->selectedStudents = [];
            $this->showBulkPromoteModal = false;
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public bool $promoting = false;
    public array $promotionProgress = [];

    /**
     * ترحيل المدرسة بالكامل (في الخلفية)
     */
    public function promoteEntireSchool()
    {
        try {
            $year = AcademicYear::findOrFail($this->selectedYearId);

            // Dispatch Job
            \App\Jobs\ProcessSchoolPromotion::dispatch($year->id, auth()->id());

            $this->promoting = true;
            $this->promotionProgress = [
                'status' => 'starting',
                'message' => 'جاري بدء الترحيل...',
                'percentage' => 0
            ];

            $this->dispatch('notify', message: 'تم بدء عملية الترحيل في الخلفية. سيتم تحديث الحالة تلقائياً.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    /**
     * التحقق من تقدم عملية الترحيل
     */
    public function checkPromotionProgress()
    {
        if (!$this->selectedYearId)
            return;

        $key = "promotion_progress_{$this->selectedYearId}";
        $progress = \Illuminate\Support\Facades\Cache::get($key);

        if ($progress) {
            $this->promotionProgress = $progress;

            if (isset($progress['total']) && $progress['total'] > 0) {
                $this->promotionProgress['percentage'] = round(($progress['processed'] / $progress['total']) * 100);
            } else {
                $this->promotionProgress['percentage'] = 0;
            }

            if (in_array($progress['status'], ['completed', 'failed'])) {
                $this->promoting = false;
                if ($progress['status'] === 'completed') {
                    $this->dispatch('notify', message: $progress['message'] ?? 'تمت العملية بنجاح.', type: 'success');
                } else {
                    $this->dispatch('error', message: $progress['message'] ?? 'حدث خطأ غير متوقع.');
                }
            }
        }
    }

    /**
     * فتح نافذة التراجع
     */
    public function openRevertModal(int $promotionId)
    {
        $this->revertingPromotionId = $promotionId;
        $this->revertReason = '';
        $this->showRevertModal = true;
    }

    /**
     * التراجع عن الترحيل
     */
    public function revertPromotion()
    {
        if (!$this->revertingPromotionId || !$this->revertReason) {
            $this->dispatch('error', message: 'يرجى إدخال سبب التراجع');
            return;
        }

        try {
            $promotion = Promotion::findOrFail($this->revertingPromotionId);
            app(PromotionService::class)->revert($promotion, $this->revertReason);

            $this->dispatch('notify', message: 'تم التراجع عن الترحيل بنجاح', type: 'success');
            $this->showRevertModal = false;
            $this->revertingPromotionId = null;
            $this->revertReason = '';
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    /**
     * إغلاق السنة وتفعيل الجديدة
     */
    public function closeYearAndActivateNext()
    {
        try {
            $year = AcademicYear::findOrFail($this->selectedYearId);
            $newYear = app(PromotionService::class)->closeYearAndActivateNext($year);

            $this->dispatch('notify', message: "تم إغلاق السنة وتفعيل السنة الجديدة: {$newYear->name}", type: 'success');
            $this->selectedYearId = $newYear->id;
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    /**
     * توزيع تلقائي على الشعب
     */
    public function autoDistribute()
    {
        if (!$this->selectedGradeId || !$this->nextYear) {
            $this->dispatch('error', message: 'يرجى اختيار الصف والتأكد من وجود سنة جديدة');
            return;
        }

        try {
            $grade = Grade::findOrFail($this->selectedGradeId);
            $count = app(PromotionService::class)->autoDistributeToSections($grade, $this->nextYear);

            $this->dispatch('notify', message: "تم توزيع {$count} طالب على الشعب", type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.promotion.promotion-manager');
    }
}
