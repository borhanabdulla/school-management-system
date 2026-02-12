<?php

namespace App\Domains\Academic\ClassSection\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ClassSectionScopes
{
    /**
     * Scope a query to only include sections for a specific academic year.
     */
    public function scopeForYear(Builder $query, int $yearId): void
    {
        $query->where('academic_year_id', $yearId);
    }

    /**
     * Scope a query to only include sections for a specific grade.
     */
    public function scopeForGrade(Builder $query, int $gradeId): void
    {
        $query->where('grade_id', $gradeId);
    }

    /**
     * Scope a query to only include sections of a specific gender type.
     */
    public function scopeByGenderType(Builder $query, string $genderType): void
    {
        $query->where('gender_type', $genderType);
    }

    /**
     * Scope a query to search sections by name.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where('name', 'like', "%{$term}%");
    }
}
