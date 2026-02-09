<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * UnauthorizedException - استثناء عدم الصلاحية
 * 
 * يُرمى عند محاولة تنفيذ عملية بدون صلاحية.
 * أوضح من AuthorizationException العام ويدعم رسائل عربية.
 * 
 * @example
 * throw UnauthorizedException::forAction('تعديل الدرجات');
 * throw UnauthorizedException::forResource('Student', 123);
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class UnauthorizedException extends Exception
{
    /**
     * العملية المحمية
     */
    protected string $action;

    /**
     * المورد المحمي
     */
    protected ?string $resource;

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(string $message, string $action = '', ?string $resource = null)
    {
        parent::__construct($message);
        $this->action = $action;
        $this->resource = $resource;
    }

    /**
     * لعملية معينة
     */
    public static function forAction(string $action): static
    {
        return new static(
            "ليس لديك صلاحية لـ: {$action}",
            $action
        );
    }

    /**
     * لمورد معين
     */
    public static function forResource(string $resource, mixed $id = null): static
    {
        $message = $id
            ? "ليس لديك صلاحية الوصول إلى {$resource} #{$id}"
            : "ليس لديك صلاحية الوصول إلى {$resource}";

        return new static($message, 'access', $resource);
    }

    /**
     * العملية تحتاج صلاحية معينة
     */
    public static function requiresPermission(string $permission): static
    {
        return new static(
            "هذه العملية تحتاج صلاحية: {$permission}",
            $permission
        );
    }

    /**
     * العملية تحتاج دور معين
     */
    public static function requiresRole(string $role): static
    {
        return new static(
            "هذه العملية تحتاج أن تكون: {$role}",
            'role',
            $role
        );
    }

    /**
     * الحصول على العملية
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * الحصول على المورد
     */
    public function getResource(): ?string
    {
        return $this->resource;
    }
}
