<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * ResourceNotFoundException - استثناء عدم وجود المورد
 * 
 * يُرمى عند عدم العثور على مورد معين.
 * أوضح وأكثر تفصيلاً من ModelNotFoundException.
 * 
 * @example
 * throw ResourceNotFoundException::forModel('Student', 123);
 * throw ResourceNotFoundException::forRecord('طالب', 'الرقم الأكاديمي', 'STU-2024-001');
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class ResourceNotFoundException extends Exception
{
    /**
     * نوع المورد
     */
    protected string $resourceType;

    /**
     * معرف المورد
     */
    protected mixed $identifier;

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(string $message, string $resourceType = '', mixed $identifier = null)
    {
        parent::__construct($message);
        $this->resourceType = $resourceType;
        $this->identifier = $identifier;
    }

    /**
     * لموديل معين
     */
    public static function forModel(string $modelClass, mixed $id): static
    {
        $modelName = class_basename($modelClass);
        return new static(
            "لم يتم العثور على {$modelName} بالمعرف: {$id}",
            $modelName,
            $id
        );
    }

    /**
     * لسجل معين بوصف عربي
     */
    public static function forRecord(string $type, string $field, mixed $value): static
    {
        return new static(
            "لم يتم العثور على {$type} بـ {$field}: {$value}",
            $type,
            $value
        );
    }

    /**
     * الحصول على نوع المورد
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    /**
     * الحصول على المعرف
     */
    public function getIdentifier(): mixed
    {
        return $this->identifier;
    }
}
