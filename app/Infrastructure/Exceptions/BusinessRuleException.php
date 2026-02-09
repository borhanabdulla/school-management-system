<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * BusinessRuleException - استثناء انتهاك قاعدة عمل
 * 
 * يُرمى عند انتهاك قاعدة من قواعد العمل (Business Rules).
 * مثل: محاولة تسجيل طالب بعد انتهاء فترة التسجيل.
 * 
 * @example
 * throw BusinessRuleException::make('لا يمكن التسجيل بعد انتهاء الفترة المحددة');
 * throw BusinessRuleException::registrationClosed();
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class BusinessRuleException extends Exception
{
    /**
     * كود القاعدة (للتتبع)
     */
    protected string $ruleCode = '';

    /**
     * بيانات إضافية
     */
    protected array $context = [];

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(string $message, string $ruleCode = '', array $context = [])
    {
        parent::__construct($message);
        $this->ruleCode = $ruleCode;
        $this->context = $context;
    }

    /**
     * Factory method
     */
    public static function make(string $message, string $ruleCode = '', array $context = []): static
    {
        return new static($message, $ruleCode, $context);
    }

    /**
     * الحصول على كود القاعدة
     */
    public function getRuleCode(): string
    {
        return $this->ruleCode;
    }

    /**
     * الحصول على السياق
     */
    public function getContext(): array
    {
        return $this->context;
    }

    // ═══════════════════════════════════════════════════════════════
    // استثناءات جاهزة للاستخدام المتكرر
    // ═══════════════════════════════════════════════════════════════

    /**
     * التسجيل مغلق
     */
    public static function registrationClosed(): static
    {
        return new static(
            'فترة التسجيل مغلقة حالياً',
            'REGISTRATION_CLOSED'
        );
    }

    /**
     * السنة الدراسية غير نشطة
     */
    public static function academicYearNotActive(): static
    {
        return new static(
            'لا توجد سنة دراسية نشطة',
            'NO_ACTIVE_YEAR'
        );
    }

    /**
     * الفصل الدراسي غير نشط
     */
    public static function termNotActive(): static
    {
        return new static(
            'لا يوجد فصل دراسي نشط',
            'NO_ACTIVE_TERM'
        );
    }

    /**
     * العملية غير مسموحة في هذه الحالة
     */
    public static function notAllowedInCurrentState(string $operation, string $currentState): static
    {
        return new static(
            "لا يمكن تنفيذ '{$operation}' في الحالة الحالية: {$currentState}",
            'INVALID_STATE_FOR_OPERATION',
            ['operation' => $operation, 'state' => $currentState]
        );
    }

    /**
     * تجاوز الحد الأقصى
     */
    public static function limitExceeded(string $resource, int $limit, int $current): static
    {
        return new static(
            "تم تجاوز الحد الأقصى لـ {$resource}: الحد {$limit}، الحالي {$current}",
            'LIMIT_EXCEEDED',
            ['resource' => $resource, 'limit' => $limit, 'current' => $current]
        );
    }

    /**
     * تعارض في البيانات
     */
    public static function dataConflict(string $message): static
    {
        return new static($message, 'DATA_CONFLICT');
    }
}
