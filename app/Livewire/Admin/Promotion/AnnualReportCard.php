<?php

namespace App\Livewire\Admin\Promotion;

use App\Domains\Academic\Results\Models\AnnualResult;

use App\Domains\Academic\Grading\Models\SystemSetting;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AnnualReportCard extends Component
{
    public AnnualResult $result;
    public $schoolName;
    public $academicYearName;

    public function mount(int $resultId)
    {
        $this->result = AnnualResult::with(['student.currentClassSection', 'grade', 'academicYear'])
            ->findOrFail($resultId);

        $this->schoolName = SystemSetting::where('key', 'school_name')->value('value') ?? 'المدرسة النموذجية';
        $this->academicYearName = $this->result->academicYear->name;
    }

    public function render()
    {
        return view('livewire.admin.promotion.annual-report-card');
    }
}
