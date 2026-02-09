<?php

namespace App\Domains\Academic\ClassSection\Services;

use App\Domains\Academic\ClassSection\Models\ClassSection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class ClassSectionLookupService
{
    public const CACHE_PREFIX = 'academic:sections:grade:';

    /**
     * Get sections by grade and year, cached.
     * 
     * @param int $gradeId
     * @param int $yearId
     * @param string|null $search Optional search term to filter results in memory
     * @return Collection
     */
    public function getSections(int $gradeId, int $yearId, ?string $search = null): Collection
    {
        $key = self::CACHE_PREFIX . "{$gradeId}:year:{$yearId}";

        // 1. Get full list from cache (Fast)
        $sections = Cache::rememberForever($key, function () use ($gradeId, $yearId) {
            return ClassSection::where('grade_id', $gradeId)
                ->where('academic_year_id', $yearId)
                ->with(['grade', 'academicYear']) // Eager load to prevent N+1
                ->orderBy('name')
                ->get();
        });

        // 2. Filter in memory (Centralized Logic)
        if ($search) {
            return $sections->filter(function ($section) use ($search) {
                return str_contains($section->name, $search);
            });
        }

        return $sections;
    }

    /**
     * Invalidate cache for a specific grade and year.
     * Should be called by Actions.
     * 
     * @param int $gradeId
     * @param int $yearId
     * @return void
     */
    public function invalidateCache(int $gradeId, int $yearId): void
    {
        $key = self::CACHE_PREFIX . "{$gradeId}:year:{$yearId}";
        Cache::forget($key);
    }
    /**
     * Get flat list of all active sections for the current year.
     * Optimized for frontend (Alpine.js).
     */
    public function getAllActiveSectionsFlat()
    {
        $currentYear = \App\Infrastructure\Context\AcademicContextService::getInstance()->activeYear();
        $yearId = $currentYear ? $currentYear->id : 'all';

        return Cache::remember("sections_flat_list_year_{$yearId}", 3600, function () use ($currentYear) {
            $query = ClassSection::query()
                ->select('id', 'name', 'grade_id');

            if ($currentYear) {
                $query->where('academic_year_id', $currentYear->id);
            }

            return $query->orderBy('name')->get();
        });
    }

    /**
     * Get sections specific for a grade (Traditional Backend Select)
     */
    public function getSectionsByGrade(int $gradeId, ?int $academicYearId = null)
    {
        $yearId = $academicYearId ?? \App\Infrastructure\Context\AcademicContextService::getInstance()->activeYearId();

        if (!$yearId) {
            return collect([]);
        }

        return Cache::remember("sections_grade_{$gradeId}_year_{$yearId}", 3600, function () use ($gradeId, $yearId) {
            return ClassSection::where('grade_id', $gradeId)
                ->where('academic_year_id', $yearId)
                ->where('is_active', true)
                ->orderBy('name')
                ->select('id', 'name', 'grade_id', 'academic_year_id', 'max_capacity', 'gender_type')
                ->get();
        });
    }
}
