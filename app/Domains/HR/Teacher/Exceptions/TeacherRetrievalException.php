<?php

namespace App\Domains\HR\Teacher\Exceptions;

use Exception;

/**
 * TeacherRetrievalException - استثناء استرداد بيانات المعلم
 */
class TeacherRetrievalException extends Exception
{
    public static function errorFetchingList($message = null)
    {
        return new self("فشل في استرداد قائمة المعلمين: " . ($message ?? 'خطأ غير متوقع.'));
    }
}
