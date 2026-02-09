<?php

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\CourseOffering\Data\CourseOfferingData;

class UpdateCourseOfferingAction
{
    public function execute(CourseOffering $courseOffering, CourseOfferingData $data): CourseOffering
    {
        $courseOffering->update($data->toArray());
        return $courseOffering;
    }
}
