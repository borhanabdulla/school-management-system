<?php

namespace App\Livewire\Admin\Control;

use App\Domains\Academic\Control\Models\ExamSession;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Control\Services\SecrecyService;
use App\Domains\Academic\Grading\Services\GradingHealthGate;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ControlDashboard extends Component
{
    public $showCreateModal = false;

    // Form fields
    public $name;

    #[Locked]
    public $academic_year_id;

    #[Locked]
    public $term_id;

    public $start_date;
    public $end_date;

    // Print Modal
    public $showPrintModal = false;

    #[Locked]
    public $selectedSessionId;

    #[Locked]
    public $selectedClassSectionId = '';


    protected $rules = [
        'name' => 'required|string|max:255',
        'academic_year_id' => 'required|exists:academic_years,id',
        'term_id' => 'required|exists:terms,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];

    public function getSessionsProperty()
    {
        return ExamSession::with(['academicYear', 'term'])
            ->withCount([
                'seatings as students_count',
                'marks as marks_entered_count' => function ($q) {
                    $q->whereNotNull('score');
                }
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getAcademicYearsProperty()
    {
        return app(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::class)->getList();
    }

    public function getTermsProperty()
    {
        if (!$this->academic_year_id) {
            return collect();
        }
        return app(\App\Domains\Academic\Term\Services\TermLookupService::class)->search([
            'year_id' => $this->academic_year_id
        ]);
    }

    public function getClassSectionsProperty()
    {
        return \App\Models\ClassSection::with('grade')
            ->get()
            ->sortBy(['grade.name', 'name']);
    }

    public function openPrintModal($sessionId)
    {
        $this->selectedSessionId = $sessionId;
        $this->selectedClassSectionId = '';
        $this->showPrintModal = true;
    }

    public function updatedAcademicYearId()
    {
        $this->term_id = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function save()
    {
        $this->validate();

        ExamSession::create([
            'name' => $this->name,
            'academic_year_id' => $this->academic_year_id,
            'term_id' => $this->term_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => 'setup',
            'is_active' => false,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->dispatch('notify', message: 'تم إنشاء الدورة الامتحانية بنجاح.');
    }

    public function activate(int $sessionId)
    {
        // إلغاء تفعيل أي دورة نشطة سابقة
        ExamSession::where('is_active', true)->update(['is_active' => false]);

        $session = ExamSession::findOrFail($sessionId);
        $session->update([
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->dispatch('notify', message: 'تم تفعيل الدورة الامتحانية.');
    }

    public function closeSession(int $sessionId)
    {
        $session = ExamSession::findOrFail($sessionId);
        $session->update([
            'status' => 'closed',
            'is_active' => false,
        ]);

        $this->dispatch('notify', message: 'تم إغلاق الدورة الامتحانية.');
    }

    public function generateNumbers(int $sessionId, SecrecyService $secrecyService)
    {
        $session = ExamSession::findOrFail($sessionId);

        if (!$session->isSetup()) {
            $this->dispatch('error', message: 'لا يمكن توليد الأرقام بعد بدء الامتحانات.');
            return;
        }

        $generated = $secrecyService->generateForSession($session, 1000);

        $this->dispatch('notify', message: "تم توليد أرقام لـ {$generated->count()} طالب.");
    }

    protected function resetForm()
    {
        $this->reset(['name', 'academic_year_id', 'term_id', 'start_date', 'end_date']);
    }

    /**
     * معالجة النتائج - دمج أعمال السنة مع درجات الكنترول
     */
    public function processResults(int $sessionId, \App\Domains\Academic\Control\Services\ResultProcessingService $resultService)
    {
        $session = ExamSession::findOrFail($sessionId);

        if (!$session->isActive() && !$session->isProcessing()) {
            $this->dispatch('error', message: 'يجب أن تكون الدورة نشطة أو في وضع المعالجة.');
            return;
        }

        try {
            $term = $session->term ?? Term::findOrFail($session->term_id);
            app(GradingHealthGate::class)->assertTermHealthy($term, 'معالجة النتائج');
        } catch (\Throwable $e) {
            $this->dispatch('error', message: $e->getMessage());
            return;
        }

        // تحويل الحالة إلى "معالجة"
        $session->update(['status' => 'processing']);

        try {
            $count = $resultService->processAll($session);
            $this->dispatch('notify', message: "تمت معالجة {$count} نتيجة بنجاح.");
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء المعالجة: ' . $e->getMessage());
        }
    }

    /**
     * نشر النتائج - جعلها مرئية للطلاب
     */
    public function publishResults(int $sessionId, \App\Domains\Academic\Control\Services\ResultProcessingService $resultService)
    {
        $session = ExamSession::findOrFail($sessionId);

        if (!$session->isProcessing()) {
            $this->dispatch('error', message: 'يجب معالجة النتائج أولاً قبل النشر.');
            return;
        }

        try {
            $term = $session->term ?? Term::findOrFail($session->term_id);
            app(GradingHealthGate::class)->assertTermHealthy($term, 'نشر النتائج');
        } catch (\Throwable $e) {
            $this->dispatch('error', message: $e->getMessage());
            return;
        }

        try {
            $count = $resultService->publishResults($session);
            $session->update(['status' => 'published']);
            $this->dispatch('notify', message: "تم نشر {$count} نتيجة بنجاح.");
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ أثناء النشر: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.control.control-dashboard')
            ->layout('layouts.app');
    }
}
