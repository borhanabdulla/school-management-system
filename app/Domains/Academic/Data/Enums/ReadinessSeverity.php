<?php

namespace App\Domains\Academic\Data\Enums;

/**
 * Severity levels for readiness check items
 */
enum ReadinessSeverity: string
{
    case Blocking = 'blocking';
    case Warning = 'warning';

    /**
     * Get Arabic label for the severity
     */
    public function label(): string
    {
        return match ($this) {
            self::Blocking => 'إغلاق',
            self::Warning => 'تحذير',
        };
    }

    /**
     * Get CSS class for the severity
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Blocking => 'text-danger',
            self::Warning => 'text-warning',
        };
    }

    /**
     * Check if this severity blocks year closing
     */
    public function blocksClosure(): bool
    {
        return $this === self::Blocking;
    }
}
