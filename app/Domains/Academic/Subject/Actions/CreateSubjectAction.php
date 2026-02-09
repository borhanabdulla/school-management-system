<?php

namespace App\Domains\Academic\Subject\Actions;

use App\Domains\Academic\Subject\Models\Subject;
use App\Domains\Academic\Subject\Data\SubjectData;

class CreateSubjectAction
{
    public function execute(SubjectData $data): Subject
    {
        return Subject::create($data->toArray());
    }
}
