<?php

namespace App\Domains\Shared\Services\Dashboard\Concerns;

trait HasDashboardCache
{
    protected array $memoized = [];

    protected function rememberDashboard(
        array $filters,
        string $suffix,
        int $minutes,
        callable $callback,
        bool $includeFilters = true,
        array $extra = []
    ) {
        $key = $this->dashboardCacheKey($filters, $suffix, $includeFilters, $extra);

        if (array_key_exists($key, $this->memoized)) {
            return $this->memoized[$key];
        }

        $value = $callback();
        $this->memoized[$key] = $value;

        return $value;
    }

    protected function dashboardCacheKey(
        array $filters,
        string $suffix,
        bool $includeFilters = true,
        array $extra = []
    ): string {
        $parts = ['dashboard', $suffix];

        if ($includeFilters) {
            $parts[] = 'year:' . ($filters['academicYearId'] ?? 'all');
            $parts[] = 'term:' . ($filters['termId'] ?? 'all');
            $parts[] = 'grade:' . ($filters['gradeId'] ?? 'all');
            $parts[] = 'range:' . ($filters['range'] ?? 'all');
        }

        foreach ($extra as $key => $value) {
            $parts[] = $key . ':' . ($value ?? 'all');
        }

        return implode('|', $parts);
    }
}
