<?php

namespace App\Livewire\HR;

use App\Domains\HR\WorkShift\Models\WorkShift;
use Livewire\Component;
use Livewire\WithPagination;

class WorkShiftManager extends Component
{
    use WithPagination;

    // Form State
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form Fields
    public string $name = '';
    public string $season = 'all';
    public string $start_time = '07:00';
    public string $end_time = '14:00';
    public int $grace_period_minutes = 15;
    public array $working_days = ['sun', 'mon', 'tue', 'wed', 'thu'];
    public bool $works_on_holidays = false;
    public bool $is_active = true;

    // Filters
    public string $search = '';

    protected $queryString = ['search'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'season' => 'required|in:summer,winter,all',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_period_minutes' => 'required|integer|min:0|max:60',
            'working_days' => 'required|array|min:1',
            'working_days.*' => 'in:sun,mon,tue,wed,thu,fri,sat',
            'works_on_holidays' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'اسم الوردية مطلوب',
        'end_time.after' => 'وقت الانتهاء يجب أن يكون بعد وقت البداية',
        'working_days.required' => 'يجب اختيار يوم عمل واحد على الأقل',
    ];

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $shift = WorkShift::findOrFail($id);

        $this->editingId = $shift->id;
        $this->name = $shift->name;
        $this->season = $shift->season;
        $this->start_time = \Carbon\Carbon::parse($shift->start_time)->format('H:i');
        $this->end_time = \Carbon\Carbon::parse($shift->end_time)->format('H:i');
        $this->grace_period_minutes = $shift->grace_period_minutes;
        $this->working_days = $shift->working_days ?? [];
        $this->works_on_holidays = $shift->works_on_holidays;
        $this->is_active = $shift->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'season' => $this->season,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'grace_period_minutes' => $this->grace_period_minutes,
            'working_days' => $this->working_days,
            'works_on_holidays' => $this->works_on_holidays,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            $shift = WorkShift::find($this->editingId);
            app(\App\Domains\HR\WorkShift\Actions\UpdateWorkShiftAction::class)->execute($shift, $data);
            $this->dispatch('notify', message: 'تم تحديث الوردية بنجاح.', type: 'success');
        } else {
            app(\App\Domains\HR\WorkShift\Actions\CreateWorkShiftAction::class)->execute($data);
            $this->dispatch('notify', message: 'تم إنشاء الوردية بنجاح.', type: 'success');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $shift = WorkShift::findOrFail($id);

        try {
            app(\App\Domains\HR\WorkShift\Actions\DeleteWorkShiftAction::class)->execute($shift);
            $this->dispatch('notify', message: 'تم حذف الوردية بنجاح.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function toggleActive(int $id): void
    {
        $shift = WorkShift::findOrFail($id);
        app(\App\Domains\HR\WorkShift\Actions\ToggleWorkShiftStatusAction::class)->execute($shift);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->season = 'all';
        $this->start_time = '07:00';
        $this->end_time = '14:00';
        $this->grace_period_minutes = 15;
        $this->working_days = ['sun', 'mon', 'tue', 'wed', 'thu'];
        $this->works_on_holidays = false;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $shifts = WorkShift::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.hr.work-shift-manager', [
            'shifts' => $shifts,
            'dayOptions' => $this->getDayOptions(),
            'seasonOptions' => $this->getSeasonOptions(),
        ])->layout('layouts.app');
    }

    private function getDayOptions(): array
    {
        return [
            'sun' => 'الأحد',
            'mon' => 'الإثنين',
            'tue' => 'الثلاثاء',
            'wed' => 'الأربعاء',
            'thu' => 'الخميس',
            'fri' => 'الجمعة',
            'sat' => 'السبت',
        ];
    }

    private function getSeasonOptions(): array
    {
        return [
            'all' => 'طوال العام',
            'summer' => 'الدوام الصيفي',
            'winter' => 'الدوام الشتوي',
        ];
    }
}
