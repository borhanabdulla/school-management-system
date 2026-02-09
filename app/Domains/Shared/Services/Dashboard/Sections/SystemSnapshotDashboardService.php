<?php

namespace App\Domains\Shared\Services\Dashboard\Sections;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Calendar\Models\SchoolEvent;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Finance\Models\Invoice;
use App\Domains\HR\Staff\Models\Staff;
use App\Domains\HR\Teacher\Models\Teacher;
use App\Domains\Shared\Services\Dashboard\Concerns\HasDashboardCache;

class SystemSnapshotDashboardService
{
    use HasDashboardCache;

    public function systemSnapshot(): array
    {
        return $this->rememberDashboard([], 'system_snapshot', 15, function () {
            return [
                'students' => Student::query()->count(),
                'teachers' => Teacher::query()->count(),
                'staff' => Staff::query()->count(),
                'years' => AcademicYear::query()->count(),
                'terms' => Term::query()->count(),
                'grades' => Grade::query()->count(),
                'sections' => ClassSection::query()->count(),
                'subjects' => Subject::query()->count(),
                'invoices' => Invoice::query()->count(),
                'events' => SchoolEvent::query()->count(),
            ];
        }, includeFilters: false);
    }
}
