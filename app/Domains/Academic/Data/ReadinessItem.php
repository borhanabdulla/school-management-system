<?php

namespace App\Domains\Academic\Data;

use App\Domains\Academic\Data\Enums\ReadinessSeverity;

/**
 * DTO for readiness check items (badges)
 */
class ReadinessItem
{
    public function __construct(
        public readonly string $key,
        public readonly ReadinessSeverity $severity,
        public readonly string $label,
        public readonly string $message,
        public readonly int $count = 0,
        public readonly ?string $route = null,
        public readonly array $routeParams = []
    ) {}

    /**
     * Create a blocking (critical) readiness item
     */
    public static function blocking(
        string $key,
        string $label,
        string $message,
        int $count = 0,
        ?string $route = null,
        array $routeParams = []
    ): self {
        return new self(
            key: $key,
            severity: ReadinessSeverity::Blocking,
            label: $label,
            message: $message,
            count: $count,
            route: $route,
            routeParams: $routeParams
        );
    }

    /**
     * Create a warning readiness item
     */
    public static function warning(
        string $key,
        string $label,
        string $message,
        int $count = 0,
        ?string $route = null,
        array $routeParams = []
    ): self {
        return new self(
            key: $key,
            severity: ReadinessSeverity::Warning,
            label: $label,
            message: $message,
            count: $count,
            route: $route,
            routeParams: $routeParams
        );
    }

    /**
     * Check if this item blocks year closing
     */
    public function isBlocking(): bool
    {
        return $this->severity === ReadinessSeverity::Blocking;
    }

    /**
     * Check if this is just a warning
     */
    public function isWarning(): bool
    {
        return $this->severity === ReadinessSeverity::Warning;
    }

    /**
     * Check if there are any issues
     */
    public function hasIssues(): bool
    {
        return $this->count > 0;
    }

    /**
     * Get CSS class for badge styling
     */
    public function getBadgeClass(): string
    {
        return match ($this->severity) {
            ReadinessSeverity::Blocking => 'badge-danger',
            ReadinessSeverity::Warning => 'badge-warning',
        };
    }

    /**
     * Get icon for badge
     */
    public function getIcon(): string
    {
        return match ($this->severity) {
            ReadinessSeverity::Blocking => 'heroicon-o-x-circle',
            ReadinessSeverity::Warning => 'heroicon-o-exclamation-triangle',
        };
    }

    /**
     * Convert to array for JSON/Blade
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'severity' => $this->severity->value,
            'label' => $this->label,
            'message' => $this->message,
            'count' => $this->count,
            'route' => $this->route,
            'route_params' => $this->routeParams,
            'is_blocking' => $this->isBlocking(),
            'is_warning' => $this->isWarning(),
            'has_issues' => $this->hasIssues(),
            'badge_class' => $this->getBadgeClass(),
            'icon' => $this->getIcon(),
        ];
    }
}
