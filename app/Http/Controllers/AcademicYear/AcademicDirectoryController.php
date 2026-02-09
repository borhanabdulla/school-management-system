<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Stage\Models\EducationalStage;
use Illuminate\View\View;

class AcademicDirectoryController extends Controller
{
    /**
     * عرض الدليل الأكاديمي التفاعلي
     */
    public function index(\App\Domains\Academic\AcademicYear\Services\AcademicYearLookupService $lookupService): View
    {
        // التحقق من وجود البيانات الأساسية
        $hasStages = EducationalStage::exists();
        $hasAcademicYears = $lookupService->exists();

        return view('academic-directory.index', [
            'hasStages' => $hasStages,
            'hasAcademicYears' => $hasAcademicYears,
        ]);
    }
}
