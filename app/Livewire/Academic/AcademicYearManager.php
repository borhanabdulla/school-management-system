<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Services\AcademicYearService;
use App\Livewire\Forms\Academic\AcademicYearForm;
use Illuminate\Validation\ValidationException;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Livewire\Attributes\Url;
use Carbon\Carbon;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\UpdateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ArchiveAcademicYearAction;

class AcademicYearManager extends Component
{
    use WithPagination;

    // Form Object
    public AcademicYearForm $form;

    // UI State
    public $showModal = false;
    public $isEditing = false;
    public $activateAfterSave = false;
    public ?AcademicYear $editingYear = null;

    // Wizard State
    #[Url(keep: true)]
    public $step = 1;

    // Data State
    public $search = '';
    public $filterStatus = 'all';
    public $sortBy = 'start_date_desc';

    // حقن السيرفس
    protected AcademicYearService $service;
    protected \App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService $lookupService;
    protected \App\Domains\Academic\AcademicYear\Validation\AcademicYearValidator $validator;

    // نقوم بحقن السيرفس هنا ليتم استخدامه في كل الدوال
    public function boot(
        AcademicYearService $service,
        \App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService $lookupService,
        \App\Domains\Academic\AcademicYear\Validation\AcademicYearValidator $validator
    ) {
        $this->service = $service;
        $this->lookupService = $lookupService;
        $this->validator = $validator;
    }

    public function getStatusesProperty()
    {
        return AcademicYearStatus::cases();
    }

    /**
     * الإحصائيات من AcademicYearService (Cached Forever)
     */
    public function getStatisticsProperty()
    {
        return $this->lookupService->getStatistics();
    }

    /**
     * فحص وجود سنة نشطة (Cached via Context)
     */
    public function getAnyActiveYearExistsProperty(): bool
    {
        return app(\App\Infrastructure\Context\AcademicContextService::class)->hasActiveYear();
    }

    public function filterByStatus($status)
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, ['form.start_date', 'form.end_date'])) {
            $this->validateDateOverlap();
        }
    }

    protected function validateDateOverlap()
    {
        if (!$this->form->start_date || !$this->form->end_date)
            return;

        try {
            $start = Carbon::parse($this->form->start_date);
            $end = Carbon::parse($this->form->end_date);

            $this->service->validateDateOverlap($start, $end, $this->form->id);

            $this->resetValidation(['form.start_date', 'form.end_date']);

        } catch (\App\Domains\Academic\AcademicYear\Exceptions\DateOverlapException $e) {
            $this->addError('form.start_date', $e->getMessage());
            $this->addError('form.end_date', $e->getMessage());
        } catch (\App\Domains\Academic\AcademicYear\Exceptions\InvalidDateRangeException $e) {
            $this->addError('form.end_date', 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
        } catch (\Exception $e) {
            // Ignore other errors during typing
        }
    }

    public function render()
    {
        // استخدام Scopes من الموديل
        $query = AcademicYear::query()->withStats();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->filterStatus !== 'all') {
            if ($this->filterStatus === AcademicYearStatus::Closed->value) {
                $query->whereIn('status', [
                    AcademicYearStatus::Closed,
                    AcademicYearStatus::Archived,
                ]);
            } else {
                $query->where('status', $this->filterStatus);
            }
        }

        switch ($this->sortBy) {
            case 'start_date_asc':
                $query->orderBy('start_date', 'asc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'created_at_desc':
                $query->latest();
                break;
            case 'start_date_desc':
            default:
                $query->sorted();
                break;
        }

        return view('livewire.academic.academic-year-manager', [
            'academicYears' => $query->paginate(10),
            'statuses' => $this->statuses,
            'statistics' => $this->statistics,
        ]);
    }

    // ================== Modal & Wizard Logic ==================

    public function create()
    {
        $this->form->reset();
        $this->form->status = AcademicYearStatus::Pending->value;
        $this->isEditing = false;
        $this->editingYear = null;
        $this->activateAfterSave = false;
        $this->step = 1;
        $this->showModal = true;
    }

    public function edit(AcademicYear $academicYear)
    {
        $this->form->setModel($academicYear);
        $this->isEditing = true;
        $this->editingYear = $academicYear;
        $this->step = 1;
        $this->showModal = true;
    }

    // تعديل الاسم فقط للسنة النشطة
    public function editName(AcademicYear $academicYear)
    {
        if (!$academicYear->canEditName()) {
            $this->dispatch('error', message: 'يمكن تعديل اسم السنة النشطة أو المغلقة فقط.');
            return;
        }

        $this->form->setModel($academicYear);
        $this->isEditing = true;
        $this->editingYear = $academicYear;
        $this->step = 1;
        $this->showModal = true;
    }

    public function nextStep()
    {
        if ($this->step === 1) {
            // تحقق مبدئي للخطوة الأولى
            $this->form->validate([
                'name' => [
                    'required',
                    'string',
                    'regex:/^\\d{4}-\\d{4}$/',
                    'max:255',
                    'unique:academic_years,name,' . $this->form->id,
                ],
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            // إنشاء فصول افتراضية إذا لم تكن موجودة
            if (empty($this->form->terms)) {
                $this->generateDefaultTerms();
            }

            $this->step = 2;
        }
    }

    public function previousStep()
    {
        $this->step = max(1, $this->step - 1);
    }

    // ================== Terms Logic ==================

    public function generateDefaultTerms()
    {
        if (!$this->form->start_date || !$this->form->end_date)
            return;

        try {
            $start = Carbon::parse($this->form->start_date);
            $end = Carbon::parse($this->form->end_date);
            $midPoint = $start->copy()->addDays($start->diffInDays($end) / 2);

            $this->form->terms = [
                [
                    'name' => 'الفصل الدراسي الأول',
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $midPoint->copy()->subDays(7)->format('Y-m-d'),
                    'order_index' => 1,
                ],
                [
                    'name' => 'الفصل الدراسي الثاني',
                    'start_date' => $midPoint->copy()->addDays(7)->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'order_index' => 2,
                ]
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to generate default terms: ' . $e->getMessage());
            $this->addError('terms', 'حدث خطأ أثناء إنشاء الفصول الافتراضية.');
        }
    }

    public function cloneStructureFromPrevious()
    {
        $previousYear = AcademicYear::where('id', '!=', $this->form->id ?? 0)
            ->where('status', '!=', AcademicYearStatus::Pending)
            ->latest('end_date')
            ->first();

        if ($previousYear) {
            $this->form->terms = $previousYear->terms->map(fn($term) => [
                'name' => $term->name,
                'start_date' => null,
                'end_date' => null,
                'order_index' => $term->order_index,
            ])->toArray();

            $this->dispatch('notify', message: 'تم نسخ هيكل الفصول من ' . $previousYear->name);
        } else {
            $this->dispatch('error', message: 'لا توجد سنة سابقة للنسخ منها.');
        }
    }

    public function addTerm()
    {
        $this->form->addTerm();
    }
    public function removeTerm($index)
    {
        $this->form->removeTerm($index);
    }

    // ================== Main Actions (Service Usage) ==================

    public function save()
    {
        $this->form->validate(); // التحقق النهائي

        try {
            // تحويل بيانات الفورم إلى DTO
            $data = $this->form->toDto();

            if ($this->isEditing) {
                // التعديل باستخدام Action
                $academicYear = AcademicYear::findOrFail($this->form->id);
                app(UpdateAcademicYearAction::class)->execute($academicYear, $data);

                $this->dispatch('notify', message: 'تم تحديث السنة الدراسية بنجاح.');
            } else {
                $this->form->status = $this->activateAfterSave
                    ? AcademicYearStatus::Active->value
                    : AcademicYearStatus::Pending->value;
                $data = $this->form->toDto();

                // الإنشاء باستخدام Action
                $year = app(CreateAcademicYearAction::class)->execute($data);

                if ($this->activateAfterSave && $year->status === AcademicYearStatus::Active) {
                    $this->dispatch('notify', message: 'تم إنشاء السنة وتفعيلها بنجاح.');
                } elseif ($this->activateAfterSave) {
                    $this->dispatch('notify', message: 'تم إنشاء السنة كمسودة لوجود سنة نشطة حالياً.');
                } else {
                    $this->dispatch('notify', message: 'تم إنشاء السنة بنجاح.');
                }
            }

            $this->showModal = false;
            $this->dispatch('refresh-list');

        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function archive($id)
    {
        try {
            $year = AcademicYear::findOrFail($id);
            app(ArchiveAcademicYearAction::class)->execute($year);
            $this->dispatch('notify', message: 'تم أرشفة السنة بنجاح.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            // استخدام Action للحذف
            app(DeleteAcademicYearAction::class)->execute($id);
            $this->dispatch('notify', message: 'تم الحذف بنجاح.');
        } catch (ValidationException $e) {
            $this->dispatch('error', message: implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function activateYear($id)
    {
        try {
            $year = AcademicYear::findOrFail($id);
            app(ActivateAcademicYearAction::class)->execute($year);
            $this->dispatch('notify', message: 'تم تفعيل السنة وبدء الدراسة.');
        } catch (ValidationException $e) {
            $this->dispatch('error', message: implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function cloneYear($id)
    {
        try {
            $targetYear = AcademicYear::findOrFail($id);

            $this->create(); // إعادة تعيين الفورم
            $this->editingYear = null;
            $this->form->name = 'نسخة من ' . $targetYear->name;
            // نسخ هيكل الترام
            $this->form->terms = $targetYear->terms->map(fn($t) => [
                'name' => $t->name,
                'start_date' => '',
                'end_date' => '',
                'order_index' => $t->order_index
            ])->toArray();

            $this->dispatch('notify', message: 'تم نسخ الهيكل. يرجى تحديد التواريخ الجديدة.');

        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function closeYear($id)
    {
        try {
            $year = AcademicYear::findOrFail($id);
            app(CloseAcademicYearAction::class)->execute($year);

            $this->dispatch('notify', message: 'تم إغلاق السنة الدراسية بنجاح.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
    }
}
