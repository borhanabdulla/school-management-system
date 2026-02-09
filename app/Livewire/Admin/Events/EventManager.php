<?php

namespace App\Livewire\Admin\Events;

use Livewire\Component;
use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\Academic\Calendar\Services\SchoolCalendarLookupService;

class EventManager extends Component
{
    // Form Inputs
    public $title = '';
    public $start_date = '';
    public $end_date = '';
    public $description = '';
    public $type = 'holiday';
    public $is_holiday = true;

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:holiday,emergency,exam,activity',
        ], [
            'title.required' => 'عنوان الحدث مطلوب',
            'start_date.required' => 'تاريخ البداية مطلوب',
            'end_date.required' => 'تاريخ النهاية مطلوب',
            'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
            'type.required' => 'نوع الحدث مطلوب',
        ]);

        $year = school()->activeYear();
        if (!$year) {
            $this->dispatch('error', message: 'لا توجد سنة دراسية نشطة');
            return;
        }

        try {
            SchoolEvent::create([
                'academic_year_id' => $year->id,
                'title' => $this->title,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'type' => $this->type,
                'is_holiday' => (bool) $this->is_holiday,
                'description' => $this->description,
            ]);

            $this->reset(['title', 'start_date', 'end_date', 'description']);
            $this->type = 'holiday';
            $this->is_holiday = true;

            $this->dispatch('notify', message: 'تم حفظ الحدث بنجاح', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        SchoolEvent::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'تم حذف الحدث', type: 'success');
    }

    public function render(AcademicContextService $context, SchoolCalendarLookupService $calendarLookup)
    {
        $year = $context->activeYear();
        // Use lookup service if available, or direct query if needed (LookupService used direct query via model in getSchoolEventsForCurrentYear)
        // But here it does pagination/limit? No, it takes 50.
        // SchoolCalendarLookupService::getSchoolEventsForCurrentYear() returns all events.
        // The original code did: SchoolEvent::where(...)->latest()->take(50)->get()
        // Let's stick to the original logic but use Context for year.

        $events = $year
            ? SchoolEvent::where('academic_year_id', $year->id)->latest()->take(50)->get()
            : collect();

        return view('livewire.admin.events.event-manager', [
            'events' => $events,
        ])->layout('layouts.app');
    }
}
