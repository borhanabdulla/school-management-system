<?php

namespace App\Infrastructure\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait GradeScopes
{
    /**
     * Scope a query to only include grades for a specific stage.
     */
    public function scopeForStage(Builder $query, int $stageId): void
    {
        $query->where('educational_stage_id', $stageId);
    }

    /**
     * Scope a query to order grades by their level.
     */
    public function scopeOrderedByLevel(Builder $query): void
    {
        $query->orderBy('level_order');
    }

    /**
     * Scope a query to include grades with sections for a specific year.
     */
    public function scopeWithSectionsForYear(Builder $query, int $yearId): void
    {
        $query->with([
            'sections' => function ($q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            }
        ]);
    }

    /**
     * Scope a query to search grades by name.
     */
    public function scopeSearch(Builder $query, string $term): void
    {
        $query->where('name', 'like', "%{$term}%");
    }
}
