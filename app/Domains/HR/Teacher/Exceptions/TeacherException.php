<?php

namespace App\Domains\HR\Teacher\Exceptions;

use Exception;

class TeacherException extends Exception
{
    public static function staffCreationFailed(string $message): self
    {
        return new self("فشل إنشاء سجل الموظف: {$message}");
    }

    public static function userCreationFailed(string $message): self
    {
        return new self("فشل إنشاء حساب المستخدم: {$message}");
    }

    public static function duplicateEmail(string $email): self
    {
        return new self("البريد الإلكتروني {$email} مسجل بالفعل.");
    }

    public static function invalidHireDate(string $date): self
    {
        return new self("تاريخ التعيين {$date} غير صالح. لا يمكن أن يكون في المستقبل.");
    }

    public static function maxClassesExceeded(int $count, int $max): self
    {
        return new self("عدد الحصص الأسبوعية ({$count}) يتجاوز الحد المسموح به ({$max}).");
    }

    public static function generalError(string $message): self
    {
        return new self("حدث خطأ أثناء معالجة بيانات المعلم: {$message}");
    }
}
