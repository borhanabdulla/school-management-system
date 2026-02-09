<?php

namespace Tests;

use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function seedActiveAcademicYearForDate(?Carbon $date = null): AcademicYear
    {
        $date = $date ?? now();
        $startYear = $date->month >= 9 ? $date->year : $date->year - 1;
        $endYear = $startYear + 1;

        return AcademicYear::create([
            'name' => "{$startYear}-{$endYear}",
            'start_date' => Carbon::create($startYear, 9, 1),
            'end_date' => Carbon::create($endYear, 6, 30),
            'status' => AcademicYearStatus::Active,
        ]);
    }
}
