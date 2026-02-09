<?php

namespace App\Domains\HR\Teacher\Services;

use App\Domains\HR\Teacher\Services\TeacherLookupService;
use App\Domains\HR\Teacher\Actions\CreateTeacherAction;
use App\Domains\HR\Teacher\Data\TeacherOnboardingData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;

class TeacherService
{
    protected TeacherLookupService $lookupService;
    protected CreateTeacherAction $createAction;

    public function __construct(
        TeacherLookupService $lookupService,
        CreateTeacherAction $createAction
    ) {
        $this->lookupService = $lookupService;
        $this->createAction = $createAction;
    }

    public function getTeachersList(array $filters): LengthAwarePaginator
    {
        return $this->lookupService->getTeachersList($filters);
    }

    // الدالة الجديدة للإنشاء
    public function createTeacher(TeacherOnboardingData $data)
    {
        return $this->createAction->execute($data);
    }
}
