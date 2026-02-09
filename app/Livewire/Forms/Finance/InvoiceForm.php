<?php

namespace App\Livewire\Forms\Finance;

use Livewire\Form;
use Livewire\Attributes\Validate;

/**
 * Form Object لإنشاء الفواتير
 */
class InvoiceForm extends Form
{
    // ============================================
    // بيانات الفاتورة الأساسية
    // ============================================
    #[Validate('required|exists:students,id')]
    public ?int $studentId = null;

    #[Validate('required|exists:academic_years,id')]
    public ?int $academicYearId = null;

    #[Validate('required|date')]
    public string $issueDate = '';

    #[Validate('required|date|after_or_equal:issueDate')]
    public string $dueDate = '';

    #[Validate('nullable|string|max:500')]
    public ?string $notes = null;

    // ============================================
    // بنود الفاتورة
    // ============================================
    public array $items = [];

    public function rules(): array
    {
        return [
            'studentId' => 'required|exists:students,id',
            'academicYearId' => 'required|exists:academic_years,id',
            'issueDate' => 'required|date',
            'dueDate' => 'required|date|after_or_equal:issueDate',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.fee_type_id' => 'required|exists:fee_types,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.discount_id' => 'nullable|exists:discounts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'studentId.required' => 'يجب اختيار الطالب',
            'academicYearId.required' => 'يجب اختيار السنة الدراسية',
            'items.required' => 'يجب إضافة بند واحد على الأقل',
            'items.min' => 'يجب إضافة بند واحد على الأقل',
            'dueDate.after_or_equal' => 'تاريخ الاستحقاق يجب أن يكون بعد أو يساوي تاريخ الإصدار',
        ];
    }

    public function mount(): void
    {
        $this->issueDate = now()->format('Y-m-d');
        $this->dueDate = now()->addMonth()->format('Y-m-d');
        $this->academicYearId = school()->activeYear()?->id;
    }

    public function addItem(): void
    {
        $this->items[] = [
            'fee_type_id' => null,
            'amount' => 0,
            'discount_id' => null,
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * حساب إجمالي الفاتورة
     */
    public function getTotal(): float
    {
        return collect($this->items)->sum('amount');
    }

    /**
     * حساب إجمالي الخصومات
     */
    public function getTotalDiscount(): float
    {
        // سيتم حسابها بناءً على الخصومات المطبقة
        return 0;
    }

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId,
            'academic_year_id' => $this->academicYearId,
            'issue_date' => $this->issueDate,
            'due_date' => $this->dueDate,
            'notes' => $this->notes,
            'total_amount' => $this->getTotal(),
        ];
    }
}
