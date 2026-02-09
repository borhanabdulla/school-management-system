<?php

namespace App\Livewire\Forms\Academic;

use Livewire\Form;
use Livewire\Attributes\Validate;
use Illuminate\Validation\Rule;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;

class AcademicYearForm extends Form
{
    public ?int $id = null;
    public string $name = '';
    public string $start_date = '';
    public string $end_date = '';
    public ?string $status = '';
    public array $terms = [];

    // إعداد النموذج للبيانات الموجودة (تعديل)
    public function setModel(AcademicYear $year): void
    {
        $this->id = $year->id;
        $this->name = $year->name;
        $this->start_date = $year->start_date->format('Y-m-d');
        $this->end_date = $year->end_date->format('Y-m-d');
        $this->status = $year->status->value;

        $this->terms = $year->terms->map(fn($term) => [
            'id' => $term->id,
            'name' => $term->name,
            'start_date' => $term->start_date->format('Y-m-d'),
            'end_date' => $term->end_date->format('Y-m-d'),
            'order_index' => $term->order_index,
        ])->toArray();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'regex:/^\\d{4}-\\d{4}$/',
                'max:255',
                Rule::unique('academic_years')->ignore($this->id)
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],

            // تحقق من الفصول الدراسية
            'terms' => ['array'], // في الإنشاء يفضل أن يكون 'required' إذا كان الويزارد يفرض ذلك
            'terms.*.name' => ['required', 'string', 'max:100'],
            'terms.*.start_date' => ['required', 'date'],
            'terms.*.end_date' => ['required', 'date', 'after:terms.*.start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'صيغة اسم السنة يجب أن تكون مثل 2025-2026.',
            'end_date.after' => 'تاريخ النهاية يجب أن يكون لاحقاً لتاريخ البداية.',
            'terms.*.end_date.after' => 'نهاية الفصل يجب أن تكون بعد بدايته.',
        ];
    }

    // إضافة فصل جديد للواجهة
    public function addTerm(): void
    {
        $this->terms[] = [
            'id' => null,
            'name' => '',
            'start_date' => '',
            'end_date' => '',
            'order_index' => count($this->terms) + 1
        ];
    }

    public function removeTerm(int $index): void
    {
        unset($this->terms[$index]);
        $this->terms = array_values($this->terms); // إعادة ترتيب الفهرس
    }

    // تحويل البيانات لـ DTO
    public function toDto(): AcademicYearData
    {
        return AcademicYearData::fromArray([
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'terms' => $this->terms
        ]);
    }
}
