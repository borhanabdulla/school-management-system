<?php

namespace App\Domains\HR\Staff\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

/**
 * يُرمى عندما لا يمكن حذف موظف بسبب سجلات مرتبطة
 */
class StaffNotDeletableException extends InvalidOperationException
{
    private array $reasons;

    public function __construct(array $reasons)
    {
        $this->reasons = $reasons;
        parent::__construct(
            'لا يمكن حذف هذا الموظف: ' . implode(', ', $reasons),
            'delete_staff',
            'staff_has_dependencies'
        );
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }
}
