<?php

namespace App\Domains\Academic\Stage\Services;

use App\Domains\Academic\Stage\Models\EducationalStage;
use App\Domains\Academic\Stage\Actions\CreateStageAction;
use App\Domains\Academic\Stage\Actions\UpdateStageAction;
use App\Domains\Academic\Stage\Actions\DeleteStageAction;
use App\Domains\Academic\Stage\Data\StageData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class StageService
{
    public const CACHE_KEY_STAGES_LIST = 'educational_stages_list';

    public function __construct(
        protected CreateStageAction $createAction,
        protected UpdateStageAction $updateAction,
        protected DeleteStageAction $deleteAction,
    ) {
    }

    /**
     * Get all stages, cached.
     *
     * @return Collection
     */
    public function getStagesList(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY_STAGES_LIST, function () {
            return EducationalStage::orderBy('rank')->get();
        });
    }

    public function createStage(StageData $data): EducationalStage
    {
        $stage = $this->createAction->execute($data);
        $this->invalidateCache();
        return $stage;
    }

    public function updateStage(EducationalStage $stage, StageData $data): EducationalStage
    {
        $updatedStage = $this->updateAction->execute($stage, $data);
        $this->invalidateCache();
        return $updatedStage;
    }

    public function deleteStage(EducationalStage $stage): void
    {
        $this->deleteAction->execute($stage);
        $this->invalidateCache();
    }

    protected function invalidateCache(): void
    {
        Cache::forget(self::CACHE_KEY_STAGES_LIST);
    }
}
