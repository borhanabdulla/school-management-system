<?php

namespace App\Domains\Academic\CourseOffering\Actions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;

class DeleteCourseOfferingAction
{
    public function execute(CourseOffering $courseOffering): void
    {
        $courseOffering->delete();
    }
}
