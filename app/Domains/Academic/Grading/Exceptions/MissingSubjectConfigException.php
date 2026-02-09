<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Exceptions;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Term\Models\Term;
use RuntimeException;

class MissingSubjectConfigException extends RuntimeException
{
    public function __construct(CourseOffering $courseOffering, Term $term)
    {
        parent::__construct(sprintf(
            'Missing SubjectGradingConfig for course offering %d in term %d',
            $courseOffering->id,
            $term->id
        ));
    }
}
