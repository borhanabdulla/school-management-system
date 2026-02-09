<?php

namespace App\Domains\Academic\Student\Exceptions;

use Exception;

class InvalidStudentAgeException extends Exception
{
    protected $studentAge;
    protected $minAge;
    protected $maxAge;
    protected $gradeName;

    public function __construct($studentAge, $minAge, $maxAge, $gradeName, $message = null)
    {
        $this->studentAge = $studentAge;
        $this->minAge = $minAge;
        $this->maxAge = $maxAge;
        $this->gradeName = $gradeName;

        $message = $message ?? "عمر الطالب ({$studentAge} سنوات) غير مناسب للصف {$gradeName}. العمر المطلوب بين {$minAge} و {$maxAge} سنة.";

        parent::__construct($message);
    }

    public function getStudentAge()
    {
        return $this->studentAge;
    }

    public function getAgeRange()
    {
        return ['min' => $this->minAge, 'max' => $this->maxAge];
    }
}
