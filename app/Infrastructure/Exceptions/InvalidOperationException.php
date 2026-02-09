<?php

declare(strict_types=1);

namespace App\Infrastructure\Exceptions;

use Exception;

/**
 * InvalidOperationException - استثناء عملية غير صالحة
 * 
 * يُرمى عند محاولة تنفيذ عملية غير صالحة أو غير منطقية.
 * مثل: محاولة تفعيل سنة دراسية قبل إضافة الفصول.
 * 
 * @example
 * throw InvalidOperationException::make('لا يمكن تفعيل السنة قبل إضافة الفصول');
 * throw InvalidOperationException::cannotActivate('السنة الدراسية', 'يجب إضافة فصلين على الأقل');
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class InvalidOperationException extends Exception
{
    /**
     * العملية المراد تنفيذها
     */
    protected string $operation = '';

    /**
     * السبب
     */
    protected string $reason = '';

    /**
     * إنشاء استثناء جديد
     */
    public function __construct(string $message, string $operation = '', string $reason = '')
    {
        parent::__construct($message);
        $this->operation = $operation;
        $this->reason = $reason;
    }

    /**
     * Factory method
     */
    public static function make(string $message): static
    {
        return new static($message);
    }

    // ═══════════════════════════════════════════════════════════════
    // Factory Methods للحالات الشائعة
    // ═══════════════════════════════════════════════════════════════

    /**
     * لا يمكن التفعيل
     */
    public static function cannotActivate(string $resource, string $reason): static
    {
        return new static(
            "لا يمكن تفعيل {$resource}: {$reason}",
            'activate',
            $reason
        );
    }

    /**
     * لا يمكن الإلغاء
     */
    public static function cannotCancel(string $resource, string $reason): static
    {
        return new static(
            "لا يمكن إلغاء {$resource}: {$reason}",
            'cancel',
            $reason
        );
    }

    /**
     * لا يمكن التعديل
     */
    public static function cannotModify(string $resource, string $reason): static
    {
        return new static(
            "لا يمكن تعديل {$resource}: {$reason}",
            'modify',
            $reason
        );
    }

    /**
     * لا يمكن الإغلاق
     */
    public static function cannotClose(string $resource, string $reason): static
    {
        return new static(
            "لا يمكن إغلاق {$resource}: {$reason}",
            'close',
            $reason
        );
    }

    /**
     * العملية مكررة
     */
    public static function alreadyDone(string $operation): static
    {
        return new static(
            "العملية '{$operation}' تمت مسبقاً",
            $operation,
            'already_done'
        );
    }

    /**
     * الترتيب غير صحيح
     */
    public static function wrongOrder(string $operation, string $prerequisite): static
    {
        return new static(
            "لا يمكن تنفيذ '{$operation}' قبل '{$prerequisite}'",
            $operation,
            "يجب تنفيذ {$prerequisite} أولاً"
        );
    }

    // ═══════════════════════════════════════════════════════════════
    // Getters
    // ═══════════════════════════════════════════════════════════════

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
