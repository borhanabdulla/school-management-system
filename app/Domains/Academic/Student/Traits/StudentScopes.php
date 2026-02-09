<?php

namespace App\Domains\Academic\Student\Traits;

use Illuminate\Database\Eloquent\Builder;

trait StudentScopes
{
    /**
     * Scope a query to only include active students.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    /**
     * Scope a query to only include students of a specific gender.
     */
    public function scopeByGender(Builder $query, string $gender): void
    {
        $query->where('gender', $gender);
    }

    /**
     * Scope a query to only include students in a specific grade.
     */
    public function scopeInGrade(Builder $query, int $gradeId): void
    {
        $query->where('current_grade_id', $gradeId);
    }

    /**
     * Scope a query to search students by name (Arabic or English) or admission number.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where(function ($q) use ($term) {
            $q->where('first_name_ar', 'like', "%{$term}%")
                ->orWhere('family_name_ar', 'like', "%{$term}%")
                ->orWhere('first_name_en', 'like', "%{$term}%")
                ->orWhere('family_name_en', 'like', "%{$term}%")
                ->orWhere('admission_number', 'like', "%{$term}%")
                ->orWhere('national_id', 'like', "%{$term}%");
        });
    }
}
