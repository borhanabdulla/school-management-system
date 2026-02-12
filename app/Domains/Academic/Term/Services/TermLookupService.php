<?php

namespace App\Domains\Academic\Term\Services;

use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use Illuminate\Support\Facades\Cache;
use App\Infrastructure\Context\AcademicContextService;
use Illuminate\Database\Eloquent\Collection;

class TermLookupService
{
    public const CACHE_KEY_CURRENT = AcademicContextService::CACHE_KEY_TERM;
    public const CACHE_KEY_ACTIVE_LIST = AcademicContextService::CACHE_KEY_TERM_ACTIVE_LIST;
    public const CACHE_KEY_UPCOMING = AcademicContextService::CACHE_KEY_TERM_UPCOMING;
    public const CACHE_TTL = 86400; // 24 hours

    /**
     * الحصول على الفصل الدراسي الحالي (نشط فقط)
     * Cached.
     */
    public function getCurrentTerm(): ?Term
    {
        return $this->getActiveTerm();
    }

    /**
     * الحصول على الفصل الدراسي النشط فقط
     * Cached.
     */
    public function getActiveTerm(): ?Term
    {
        return Cache::remember(self::CACHE_KEY_CURRENT, self::CACHE_TTL, function () {
            $activeYearId = school()->activeYearId();
            if (!$activeYearId) {
                return null;
            }

            return Term::where('academic_year_id', $activeYearId)
                ->where('status', TermStatus::Active)
                ->first();
        });
    }

    /**
     * الحصول على أقرب فصل دراسي قادم (Pending)
     * Cached.
     */
    public function getUpcomingTerm(): ?Term
    {
        return Cache::remember(self::CACHE_KEY_UPCOMING, self::CACHE_TTL, function () {
            $activeYearId = school()->activeYearId();
            if (!$activeYearId) {
                return null;
            }

            return Term::where('academic_year_id', $activeYearId)
                ->where('status', TermStatus::Pending)
                ->orderBy('start_date', 'asc')
                ->first();
        });
    }

    /**
     * الحصول على الفصول الدراسية النشطة والمعلقة
     * Cached.
     */
    public function getActiveTerms(): Collection
    {
        return Cache::remember(self::CACHE_KEY_ACTIVE_LIST, self::CACHE_TTL, function () {
            $activeYearId = school()->activeYearId();
            if (!$activeYearId) {
                return collect();
            }

            return Term::where('academic_year_id', $activeYearId)
                ->whereIn('status', [TermStatus::Active, TermStatus::Pending])
                ->orderBy('start_date', 'desc')
                ->select('id', 'name', 'academic_year_id', 'start_date', 'end_date', 'status')
                ->get();
        });
    }

    /**
     * Find a term by ID.
     */
    public function find(int $id): ?Term
    {
        return Term::find($id);
    }

    /**
     * Search terms with filters.
     */
    public function search(array $filters = []): Collection
    {
        $query = Term::with('academicYear');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['year_id'])) {
            $query->where('academic_year_id', $filters['year_id']);
        }

        return $query->orderBy('order_index', 'asc')->get();
    }

    /**
     * Invalidate all related caches.
     */
    public function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY_CURRENT);
        Cache::forget(self::CACHE_KEY_ACTIVE_LIST);
        Cache::forget(self::CACHE_KEY_UPCOMING);
        school()->invalidateTerm();
    }
}
