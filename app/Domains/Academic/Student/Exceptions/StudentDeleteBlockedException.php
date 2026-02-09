<?php

namespace App\Domains\Academic\Student\Exceptions;

use App\Infrastructure\Exceptions\InvalidOperationException;

class StudentDeleteBlockedException extends InvalidOperationException
{
    /**
     * @var array<int, string>
     */
    private array $blockers;

    /**
     * @param array<int, string> $blockers
     */
    public function __construct(array $blockers)
    {
        $this->blockers = $blockers;

        parent::__construct(
            'لا يمكن حذف الطالب: ' . implode('، ', $blockers),
            'delete',
            'blocked'
        );
    }

    /**
     * @return array<int, string>
     */
    public function blockers(): array
    {
        return $this->blockers;
    }
}
