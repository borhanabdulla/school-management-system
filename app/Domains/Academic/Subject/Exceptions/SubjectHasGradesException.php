<?php

namespace App\Domains\Academic\Subject\Exceptions;

use Exception;

class SubjectHasGradesException extends Exception
{
    public static function create(string $subjectName): self
    {
        return new self(__('validation.subject_has_grades', ['name' => $subjectName]));
    }
}
