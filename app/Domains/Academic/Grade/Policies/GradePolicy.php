<?php

namespace App\Domains\Academic\Grade\Policies;

use App\Domains\Shared\Models\User;
use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use Illuminate\Auth\Access\Response;

class GradePolicy
{
    /**
     * Determine whether the user can view grades.
     */
    public function view(User $user, CourseOffering $course): bool
    {
        // Check 1: Permission
        if (!$user->can('marks.view')) {
            return false;
        }

        // Check 2: Ownership (Optional for viewing, but good for privacy)
        // If user has 'marks.override', they can view any course.
        if ($user->can('marks.override')) {
            return true;
        }

        // Otherwise, must be the teacher of the course
        return $user->teacher && $user->teacher->id === $course->teacher_id;
    }

    /**
     * Determine whether the user can edit grades.
     * This implements the "Dual Check" logic.
     */
    public function edit(User $user, CourseOffering $course): bool
    {
        // 1. Super Admin bypass is handled by Gate::before in AppServiceProvider

        // 2. Override Permission (e.g., Admin, Principal)
        if ($user->can('marks.override')) {
            return true;
        }

        // 3. Standard Teacher Check
        // Must have 'marks.edit' permission AND be the assigned teacher
        if ($user->can('marks.edit') && $user->teacher && $user->teacher->id === $course->teacher_id) {
            return true;
        }

        return false;
    }
}
