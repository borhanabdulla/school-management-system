<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\WithPagination;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Services\TermService;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use App\Domains\Academic\Term\Data\TermData;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Actions\ActivateTermAction;

class TermManager extends Component
{
    use WithPagination;

    // State
    public \App\Livewire\Forms\Academic\TermForm $form;

    public $showModal = false;
    public $isEditing = false;
    public $search = '';
    public $filterYear = ''; // لتصفية الجدول حسب سنة معينة

    protected $listeners = ['refreshComponent' => '$refresh'];

    public function getStatusesProperty()
    {
        return TermStatus::cases();
    }

    public function mount($academic_year_id = null)
    {
        // إذا تم تمرير ID السنة عبر الرابط
        if ($academic_year_id) {
            $this->filterYear = $academic_year_id;
            $this->form->academic_year_id = $academic_year_id;
        } else {
            // استخدام Context لجلب السنة النشطة (سريع جداً)
            $activeYearId = school()->activeYearId();
            if ($activeYearId) {
                $this->filterYear = $activeYearId;
                $this->form->academic_year_id = $activeYearId;
            }
        }
    }

    public function getSelectedYearProperty()
    {
        if (!$this->filterYear) {
            return null;
        }
        $year = app(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService::class)->find((int) $this->filterYear);
        return $year ? $year->load('terms') : null;
    }

    public function render(
        \App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService $yearLookupService,
        \App\Domains\Academic\Term\Services\TermLookupService $termLookupService
    ) {
        $terms = $termLookupService->search([
            'search' => $this->search,
            'year_id' => $this->filterYear,
        ]);

        return view('livewire.academic.term-manager', [
            'terms' => $terms,
            'academicYears' => $yearLookupService->getList(),
            'statuses' => $this->statuses,
            'selectedYear' => $this->selectedYear,
        ]);
    }

    public function create()
    {
        $this->form->reset();
        // الاحتفاظ بالسنة المختارة في الفلتر كقيمة افتراضية
        $defaultYear = $this->filterYear ?: $this->form->academic_year_id;

        $this->form->academic_year_id = $defaultYear;

        // محاولة ذكية لتخمين الترتيب (اختياري): آخر ترتيب + 1
        if ($defaultYear) {
            $lastTerm = Term::where('academic_year_id', $defaultYear)->max('order_index');
            $this->form->order_index = $lastTerm ? $lastTerm + 1 : 1;
        } else {
            $this->form->order_index = 1;
        }

        $this->form->status = TermStatus::Pending->value;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit(Term $term)
    {
        $this->form->setTerm($term);
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save(TermService $service, ActivateTermAction $activateTermAction)
    {
        $this->form->validate();

        $shouldActivate = $this->form->status === TermStatus::Active->value;

        $data = TermData::fromArray([
            'academic_year_id' => $this->form->academic_year_id,
            'name' => $this->form->name,
            'start_date' => $this->form->start_date,
            'end_date' => $this->form->end_date,
            'order_index' => $this->form->order_index,
            'status' => $shouldActivate ? TermStatus::Pending->value : $this->form->status,
        ]);

        try {
            if ($this->isEditing) {
                $term = Term::findOrFail($this->form->id);
                $service->updateTerm($term, $data->toArray());
                if ($shouldActivate) {
                    $activateTermAction->execute($term->refresh());
                }
                $this->dispatch('notify', message: 'تم تحديث الفصل الدراسي بنجاح.');
            } else {
                $term = $service->createTerm($data->toArray());
                if ($shouldActivate) {
                    $activateTermAction->execute($term);
                }
                $this->dispatch('notify', message: 'تم إنشاء الفصل الدراسي بنجاح.');
            }

            $this->showModal = false;

        } catch (ValidationException $e) {
            // ربط أخطاء المنطق (التداخل، الحدود) بالحقول
            $this->setErrorBag($e->validator->getMessageBag());
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    public function delete($id, TermService $service)
    {
        try {
            $term = Term::findOrFail($id);
            $service->deleteTerm($term);
            $this->dispatch('notify', message: 'تم حذف الفصل الدراسي بنجاح.');
        } catch (ValidationException $e) {
            $this->dispatch('error', message: implode(' ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function activateTerm($id, ActivateTermAction $action)
    {
        try {
            $term = Term::findOrFail($id);
            $action->execute($term);
            $this->dispatch('notify', message: 'تم تفعيل الفصل الدراسي بنجاح.');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }
}
