<?php

namespace App\Http\Controllers\AcademicYear;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    /** Display list of academic years */
    public function index(): View
    {
        return view('academic-years.index');
    }

    public function terms(?int $year_id = null)
    {
        $resolvedYearId = $year_id ?? request('year_id');
        $academicYear = $resolvedYearId
            ? \App\Domains\Academic\AcademicYear\Models\AcademicYear::find($resolvedYearId)
            : null;

        return view('terms.index', [
            'academicYearId' => $resolvedYearId,
            'academicYear' => $academicYear,
        ]);
    }

    public function structure()
    {
        return view('structure.index');
    }

    public function classSections()
    {
        return view('class-sections.index');
    }

    public function subjectManager()
    {
        return view('subject-manager.index');
    }
}
