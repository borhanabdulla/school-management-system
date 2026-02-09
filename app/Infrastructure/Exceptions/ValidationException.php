<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * ValidationException - استثناء التحقق من البيانات
 * 
 * يُرمى عند فشل التحقق من صحة البيانات على مستوى الـ Domain.
 * يختلف عن Laravel's ValidationException بأنه للتحقق على مستوى منطق الأعمال.
 * 
 * @example
 * throw ValidationException::forField('national_id', 'الرقم الوطني مستخدم مسبقاً');
 * throw ValidationException::forFields(['email' => 'البريد مستخدم', 'phone' => 'الجوال مستخدم']);
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class ValidationException extends Exception
{
    /**
     * الأخطاء حسب الحقل
     */
    protected array $errors = [];

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * إنشاء لحقل واحد
     */
    public static function forField(string $field, string $message): static
    {
        return new static($message, [$field => [$message]]);
    }

    /**
     * إنشاء لعدة حقول
     */
    public static function forFields(array $errors): static
    {
        $messages = [];
        foreach ($errors as $field => $message) {
            $messages[] = is_array($message) ? implode(', ', $message) : $message;
        }

        return new static(implode('. ', $messages), $errors);
    }

    /**
     * قيمة مكررة
     */
    public static function duplicate(string $field, string $value): static
    {
        return static::forField($field, "القيمة '{$value}' مستخدمة مسبقاً");
    }

    /**
     * قيمة مطلوبة
     */
    public static function required(string $field): static
    {
        return static::forField($field, 'هذا الحقل مطلوب');
    }

    /**
     * الحصول على الأخطاء
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * التحقق من وجود خطأ لحقل معين
     */
    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    /**
     * الحصول على خطأ حقل معين
     */
    public function getError(string $field): ?string
    {
        $error = $this->errors[$field] ?? null;
        return is_array($error) ? ($error[0] ?? null) : $error;
    }
}
