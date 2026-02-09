<?php

namespace App\Livewire\Forms\Payroll;

use Livewire\Form;
use Livewire\Attributes\Validate;

/**
 * Form Object لإنشاء/تعديل العقود
 */
class ContractForm extends Form
{
    // ============================================
    // بيانات الموظف
    // ============================================
    #[Validate('required|exists:staff,id')]
    public ?int $staffId = null;

    // ============================================
    // بيانات العقد
    // ============================================
    #[Validate('required|in:permanent,temporary,contractor')]
    public string $type = 'permanent';

    #[Validate('required|date')]
    public string $startDate = '';

    #[Validate('nullable|date|after:startDate')]
    public ?string $endDate = null;

    #[Validate('required|numeric|min:0')]
    public float $basicSalary = 0;

    #[Validate('nullable|string|max:50')]
    public ?string $bankName = null;

    #[Validate('nullable|string|max:30')]
    public ?string $bankAccountNumber = null;

    #[Validate('nullable|string|max:30')]
    public ?string $bankIban = null;

    // ============================================
    // البدلات (مرنة)
    // ============================================
    public array $allowances = [];

    public function rules(): array
    {
        $rules = [
            'staffId' => 'required|exists:staff,id',
            'type' => 'required|in:permanent,temporary,contractor',
            'startDate' => 'required|date',
            'basicSalary' => 'required|numeric|min:0',
            'bankName' => 'nullable|string|max:50',
            'bankAccountNumber' => 'nullable|string|max:30',
            'bankIban' => 'nullable|string|max:30',
            'allowances' => 'array',
            'allowances.*.name' => 'required|string',
            'allowances.*.amount' => 'required|numeric|min:0',
        ];

        // العقد المؤقت يجب أن يكون له تاريخ انتهاء
        if ($this->type !== 'permanent') {
            $rules['endDate'] = 'required|date|after:startDate';
        } else {
            $rules['endDate'] = 'nullable|date|after:startDate';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'staffId.required' => 'يجب اختيار الموظف',
            'basicSalary.required' => 'الراتب الأساسي مطلوب',
            'endDate.required' => 'تاريخ الانتهاء مطلوب للعقود المؤقتة',
            'endDate.after' => 'تاريخ الانتهاء يجب أن يكون بعد تاريخ البداية',
        ];
    }

    public function addAllowance(): void
    {
        $this->allowances[] = ['name' => '', 'amount' => 0];
    }

    public function removeAllowance(int $index): void
    {
        unset($this->allowances[$index]);
        $this->allowances = array_values($this->allowances);
    }

    public function setFromModel(\App\Models\Contract $contract): void
    {
        $this->staffId = $contract->staff_id;
        $this->type = $contract->type;
        $this->startDate = $contract->start_date->format('Y-m-d');
        $this->endDate = $contract->end_date?->format('Y-m-d');
        $this->basicSalary = $contract->basic_salary;
        $this->bankName = $contract->bank_name;
        $this->bankAccountNumber = $contract->bank_account_number;
        $this->bankIban = $contract->bank_iban;

        $this->allowances = $contract->items->map(fn($item) => [
            'name' => $item->name,
            'amount' => $item->amount,
        ])->toArray();
    }

    public function toArray(): array
    {
        return [
            'staff_id' => $this->staffId,
            'type' => $this->type,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'basic_salary' => $this->basicSalary,
            'bank_name' => $this->bankName,
            'bank_account_number' => $this->bankAccountNumber,
            'bank_iban' => $this->bankIban,
        ];
    }
}
