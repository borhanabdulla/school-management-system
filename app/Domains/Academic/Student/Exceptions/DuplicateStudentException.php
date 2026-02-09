<?php

namespace App\Domains\Academic\Student\Exceptions;

use Exception;

class DuplicateStudentException extends Exception
{
    protected $nationalId;
    protected $existingStudent;

    public function __construct($nationalId, $existingStudent = null, $message = null)
    {
        $this->nationalId = $nationalId;
        $this->existingStudent = $existingStudent;

        if ($existingStudent) {
            $message = $message ?? "الطالب {$existingStudent->first_name_ar} {$existingStudent->family_name_ar} مسجل بالفعل بالرقم القومي {$nationalId}.";
        } else {
            $message = $message ?? "يوجد طالب مسجل بالفعل بالرقم القومي {$nationalId}.";
        }

        parent::__construct($message);
    }

    public function getNationalId()
    {
        return $this->nationalId;
    }

    public function getExistingStudent()
    {
        return $this->existingStudent;
    }
}
