<?php

namespace App\Domains\Academic\Student\Exceptions;

use Exception;

class StudentHasDebtException extends Exception
{
    protected $student;
    protected $debtAmount;

    public function __construct($student, $debtAmount, $message = null)
    {
        $this->student = $student;
        $this->debtAmount = $debtAmount;

        $message = $message ?? "لا يمكن تسجيل الطالب {$student->first_name_ar} {$student->family_name_ar} لوجود ديون مستحقة بقيمة {$debtAmount} ريال.";

        parent::__construct($message);
    }

    public function getStudent()
    {
        return $this->student;
    }

    public function getDebtAmount()
    {
        return $this->debtAmount;
    }
}
