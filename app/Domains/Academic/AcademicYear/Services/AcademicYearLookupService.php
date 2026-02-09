<?php

namespace App\Domains\Academic\AcademicYear\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AcademicYearLookupService
{
    public const CACHE_KEY_LIST = 'academic_years_list';
    public const CACHE_KEY_STATISTICS = 'academic_years_statistics';
    public const CACHE_TTL = 86400; // 24 hours

    /**
     * Get a lightweight list of academic years for dropdowns.
     * Cached.
     *
     * @return Collection
     */
    public function getList(): Collection
    {
        return Cache::remember(self::CACHE_KEY_LIST, self::CACHE_TTL, function () {
            return AcademicYear::orderBy('start_date', 'desc')
                ->select('id', 'name', 'status', 'start_date', 'end_date')
                ->get();
        });
    }

    /**
     * Get statistics for the dashboard.
     * Cached.
     * Optimized to use single query.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return Cache::remember(self::CACHE_KEY_STATISTICS, self::CACHE_TTL, function () {
            // استخدام Conditional Aggregation لتقليل عدد الاستعلامات من 4 إلى 1
            $active = AcademicYearStatus::Active->value;
            $pending = AcademicYearStatus::Pending->value;

            $stats = AcademicYear::select(
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status = '{$active}' THEN 1 ELSE 0 END) as active"),
                DB::raw("SUM(CASE WHEN status = '{$pending}' THEN 1 ELSE 0 END) as pending"),
                DB::raw("SUM(CASE WHEN status NOT IN ('{$active}', '{$pending}') THEN 1 ELSE 0 END) as closed")
            )->first();

            return [
                'total' => (int) $stats->total,
                'active' => (int) $stats->active,
                'pending' => (int) $stats->pending,
                'closed' => (int) $stats->closed,
            ];
        });
    }

    /**
     * Find an academic year by ID.
     *
     * @param int $id
     * @return AcademicYear|null
     */
    public function find(int $id): ?AcademicYear
    {
        return AcademicYear::find($id);
    }

    /**
     * Check if any academic year exists.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return AcademicYear::exists();
    }

    /**
     * Invalidate all related caches.
     * Should be called by Observers.
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY_LIST);
        Cache::forget(self::CACHE_KEY_STATISTICS);

        // إبطال كاش السياق الأكاديمي أيضاً
        school()->invalidateYear();
    }
}
