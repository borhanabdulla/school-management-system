<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Services\ReadinessService;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator;
use App\Infrastructure\Security\SensitiveAccess;
use Illuminate\Support\Facades\Auth;

/**
 * YearClosingWizard - معالج إغلاق السنة الدراسية
 *
 * معالج من 3 خطوات لإغلاق السنة الدراسية بأمان:
 * - الخطوة 1: عرض تقرير الجاهزية (Blocking + Warnings)
 * - الخطوة 2: الفحوصات النهائية
 * - الخطوة 3: تنفيذ الإغلاق
 */
#[Layout('components.guest-layout')]
#[Title('معالج إغلاق السنة الدراسية')]
class YearClosingWizard extends Component
{
    // Wizard state
    public AcademicYear $year;
    public int $currentStep = 1;
    public int $totalSteps = 3;

    // Readiness data
    public array $readinessSummary = [];
    public array $blockingItems = [];
    public array $warningItems = [];

    // Final checks
    public bool $confirmNoBlockingIssues = false;
    public bool $confirmBackupTaken = false;
    public bool $confirmResponsibility = false;

    // Closing state
    public bool $isClosing = false;
    public ?string $closingError = null;
    public ?string $closingSuccess = null;

    // Validation
    protected $readinessService;
    protected $closureValidator;

    public function mount(AcademicYear $year)
    {
        $this->year = $year;
        $this->authorizeAccess();
        $this->ensureSensitiveAccess();

        $this->readinessService = app(ReadinessService::class);
        $this->closureValidator = app(AcademicYearClosureValidator::class);

        $this->loadReadinessData();
    }

    public function hydrate(): void
    {
        $this->ensureSensitiveAccess();
    }

    /**
     * Load readiness data for the year
     */
    protected function loadReadinessData(): void
    {
        $this->readinessSummary = $this->readinessService->getSummary($this->year);

        $blockingItems = collect($this->readinessSummary['items'])
            ->filter(fn($item) => $item['is_blocking'] && $item['has_issues'])
            ->values()
            ->toArray();

        $warningItems = collect($this->readinessSummary['items'])
            ->filter(fn($item) => $item['is_warning'] && $item['has_issues'])
            ->values()
            ->toArray();

        $this->blockingItems = $blockingItems;
        $this->warningItems = $warningItems;
    }

    /**
     * Authorize access to the wizard
     */
    protected function authorizeAccess(): void
    {
        if (!Auth::check()) {
            abort(403, 'يجب تسجيل الدخول للوصول إلى هذه الصفحة');
        }

        if (!Auth::user()->can('close.year')) {
            abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية');
        }

        // التحقق من أن السنة ليست مغلقة بالفعل
        if ($this->year->status === AcademicYearStatus::Closed) {
            abort(400, 'السنة الدراسية مغلقة بالفعل');
        }

        // التحقق من وجود ترمات نشطة أو مكتملة
        if (!$this->year->terms()->exists()) {
            abort(400, 'لا توجد ترمات لهذه السنة الدراسية');
        }
    }

    /**
     * Navigate to next step
     */
    public function nextStep(): void
    {
        if (!$this->ensureSensitiveAccess()) {
            return;
        }

        if ($this->currentStep < $this->totalSteps) {
            // التحقق من إمكانية الانتقال
            if ($this->currentStep === 1 && !$this->canProceedFromStep1()) {
                $this->addError('step1', 'لا يمكنك المتابعة بينما توجد مشاكل تمنع الإغلاق');
                return;
            }

            if ($this->currentStep === 2 && !$this->canProceedFromStep2()) {
                $this->addError('step2', 'يجب تأكيد جميع الفحوصات للمتابعة');
                return;
            }

            $this->currentStep++;
        }
    }

    /**
     * Navigate to previous step
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->closingError = null;
        }
    }

    /**
     * Check if can proceed from step 1
     */
    protected function canProceedFromStep1(): bool
    {
        return empty($this->blockingItems);
    }

    /**
     * Check if can proceed from step 2
     */
    protected function canProceedFromStep2(): bool
    {
        return $this->confirmNoBlockingIssues
            && $this->confirmBackupTaken
            && $this->confirmResponsibility;
    }

    /**
     * Close the year
     */
    public function closeYear(CloseAcademicYearAction $closeAction): void
    {
        if (!$this->ensureSensitiveAccess()) {
            return;
        }

        $this->isClosing = true;
        $this->closingError = null;
        $this->closingSuccess = null;

        try {
            // التحقق النهائي
            $validation = $this->closureValidator->validate($this->year);

            if (!$validation['can']) {
                throw new \Exception('توجد مشاكل تمنع إغلاق السنة: ' . implode(', ', $validation['issues']));
            }

            // تنفيذ الإغلاق
            $closeAction->execute($this->year);

            $this->closingSuccess = 'تم إغلاق السنة الدراسية بنجاح';

            // إعادة تحميل البيانات
            $this->year->refresh();
            $this->loadReadinessData();

            // الانتقال للخطوة الأخيرة
            $this->currentStep = $this->totalSteps;

        } catch (\Exception $e) {
            $this->closingError = $e->getMessage();
        } finally {
            $this->isClosing = false;
        }
    }

    /**
     * Get step 1 title
     */
    public function getStep1Title(): string
    {
        return 'تقرير الجاهزية';
    }

    /**
     * Get step 2 title
     */
    public function getStep2Title(): string
    {
        return 'الفحوصات النهائية';
    }

    /**
     * Get step 3 title
     */
    public function getStep3Title(): string
    {
        return 'تأكيد الإغلاق';
    }

    /**
     * Render the wizard
     */
    public function render()
    {
        return view('livewire.academic.year-closing-wizard', [
            'year' => $this->year,
            'readinessSummary' => $this->readinessSummary,
            'blockingItems' => $this->blockingItems,
            'warningItems' => $this->warningItems,
        ]);
    }

    protected function ensureSensitiveAccess(): bool
    {
        if (!auth()->check() || !auth()->user()->can('close.year')) {
            abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية.');
        }

        if (SensitiveAccess::isVerified(request())) {
            return true;
        }

        if (!session()->has('sensitive_access_intended')) {
            $intended = request()->headers->get('referer') ?: url()->current();
            session(['sensitive_access_intended' => $intended]);
        }
        $this->redirect(route('security.sensitive-verify'), navigate: true);

        return false;
    }
}
