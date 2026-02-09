<?php

namespace App\Livewire\Forms\HR;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Domains\HR\Leave\Models\LeaveRequest;

/**
 * Form Object لطلبات الإجازة
 */
class LeaveRequestForm extends Form
{
    // ============================================
    // بيانات الطلب
    // ============================================
    #[Validate('required|exists:leave_types,id')]
    public ?int $leaveTypeId = null;

    #[Validate('required|date|after_or_equal:today')]
    public string $startDate = '';

    #[Validate('required|date|after_or_equal:startDate')]
    public string $endDate = '';

    #[Validate('nullable|string|max:500')]
    public ?string $reason = null;

    #[Validate('nullable|string|max:50')]
    public ?string $contactPhone = null;

    // ============================================
    // المرفقات (للإجازات المرضية)
    // ============================================
    public $attachment = null;

    public function rules(): array
    {
        return [
            'leaveTypeId' => 'required|exists:leave_types,id',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'reason' => 'nullable|string|max:500',
            'contactPhone' => 'nullable|string|max:50',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'leaveTypeId.required' => 'يجب اختيار نوع الإجازة',
            'startDate.required' => 'تاريخ البداية مطلوب',
            'startDate.after_or_equal' => 'لا يمكن طلب إجازة بتاريخ سابق',
            'endDate.required' => 'تاريخ النهاية مطلوب',
            'endDate.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
        ];
    }

    /**
     * حساب عدد أيام الإجازة
     */
    public function getDaysCount(): int
    {
        if (!$this->startDate || !$this->endDate) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($this->startDate);
        $end = \Carbon\Carbon::parse($this->endDate);

        return $start->diffInDays($end) + 1;
    }

    public function setFromModel(LeaveRequest $request): void
    {
        $this->leaveTypeId = $request->leave_type_id;
        $this->startDate = $request->start_date->format('Y-m-d');
        $this->endDate = $request->end_date->format('Y-m-d');
        $this->reason = $request->reason;
        $this->contactPhone = $request->contact_phone;
    }

    public function toArray(): array
    {
        return [
            'leave_type_id' => $this->leaveTypeId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'reason' => $this->reason,
            'contact_phone' => $this->contactPhone,
        ];
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);
        $this->leaveTypeId = null;
        $this->startDate = '';
        $this->endDate = '';
        $this->reason = null;
        $this->contactPhone = null;
        $this->attachment = null;
    }
}
