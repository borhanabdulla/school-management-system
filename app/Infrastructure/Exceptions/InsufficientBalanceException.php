<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * InsufficientBalanceException - استثناء عدم كفاية الرصيد
 * 
 * يُستخدم في:
 * - نظام الإجازات: عندما لا يكفي رصيد الإجازات
 * - نظام الرواتب: عندما لا يكفي الراتب لسداد السلف
 * 
 * @example
 * throw InsufficientBalanceException::forLeave('إجازة سنوية', 5, 3);
 * throw InsufficientBalanceException::forPayroll(1000, 800);
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class InsufficientBalanceException extends Exception
{
    /**
     * نوع الرصيد
     */
    protected string $balanceType;

    /**
     * الرصيد المطلوب
     */
    protected float $required;

    /**
     * الرصيد المتاح
     */
    protected float $available;

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(
        string $message,
        string $balanceType = '',
        float $required = 0,
        float $available = 0
    ) {
        parent::__construct($message);
        $this->balanceType = $balanceType;
        $this->required = $required;
        $this->available = $available;
    }

    // ═══════════════════════════════════════════════════════════════
    // Factory Methods
    // ═══════════════════════════════════════════════════════════════

    /**
     * رصيد إجازات غير كافي
     * 
     * @param string $leaveType نوع الإجازة
     * @param int $requested الأيام المطلوبة
     * @param int $available الأيام المتاحة
     */
    public static function forLeave(string $leaveType, int $requested, int $available): static
    {
        $message = "رصيد {$leaveType} غير كافي. المطلوب: {$requested} يوم، المتاح: {$available} يوم";

        return new static($message, 'leave', $requested, $available);
    }

    /**
     * راتب غير كافي لسداد القسط
     * 
     * @param float $installment قيمة القسط
     * @param float $netSalary صافي الراتب
     */
    public static function forPayroll(float $installment, float $netSalary): static
    {
        $message = "صافي الراتب غير كافي لسداد القسط. القسط: {$installment}، الصافي: {$netSalary}";

        return new static($message, 'payroll', $installment, $netSalary);
    }

    /**
     * رصيد سلفة غير كافي
     * 
     * @param float $requested المبلغ المطلوب
     * @param float $maxAllowed الحد الأقصى المسموح
     */
    public static function forLoan(float $requested, float $maxAllowed): static
    {
        $message = "المبلغ المطلوب يتجاوز الحد المسموح. المطلوب: {$requested}، المسموح: {$maxAllowed}";

        return new static($message, 'loan', $requested, $maxAllowed);
    }

    // ═══════════════════════════════════════════════════════════════
    // Getters
    // ═══════════════════════════════════════════════════════════════

    public function getBalanceType(): string
    {
        return $this->balanceType;
    }

    public function getRequired(): float
    {
        return $this->required;
    }

    public function getAvailable(): float
    {
        return $this->available;
    }

    public function getShortage(): float
    {
        return $this->required - $this->available;
    }
}
