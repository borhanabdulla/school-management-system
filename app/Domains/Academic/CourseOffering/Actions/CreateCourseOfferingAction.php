<?php

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\CourseOffering\Data\CourseOfferingData;

class CreateCourseOfferingAction
{
    public function execute(CourseOfferingData $data): CourseOffering
    {
        return CourseOffering::create($data->toArray());
    }
}
