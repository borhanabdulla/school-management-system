<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Support\Collection;

class SubjectScorePolicyResolver
{
    /**
     * @var array<string, ?SubjectGradingConfig>
     */
    private array $subjectConfigs = [];

    public function preloadForCourseOfferings(Collection $courseOfferings, int $termId): void
    {
        $gradeIds = $courseOfferings->pluck('classSection.grade_id')->filter()->unique()->values();
        $subjectIds = $courseOfferings->pluck('subject_id')->filter()->unique()->values();

        if ($gradeIds->isEmpty() || $subjectIds->isEmpty()) {
            return;
        }

        $subjectConfigs = SubjectGradingConfig::where('term_id', $termId)
            ->whereIn('grade_id', $gradeIds)
            ->whereIn('subject_id', $subjectIds)
            ->get()
            ->keyBy(fn($row) => "{$row->grade_id}_{$row->subject_id}_{$row->term_id}");

        foreach ($subjectConfigs as $key => $config) {
            $this->subjectConfigs[$key] = $config;
        }
    }

    /**
     * @return array{max_score: float, pass_score: float}
     */
    public function resolveScores(CourseOffering $courseOffering, int $termId): array
    {
        $maxScore = $this->resolveMaxScore($courseOffering, $termId);
        $passScore = $this->resolvePassScore($courseOffering, $termId, $maxScore);

        return [
            'max_score' => $maxScore,
            'pass_score' => $passScore,
        ];
    }

    public function resolveMaxScore(CourseOffering $courseOffering, int $termId): float
    {
        $gradeId = $courseOffering->classSection?->grade_id;
        $subjectId = $courseOffering->subject_id;

        if ($gradeId === null || $subjectId === null) {
            return 100.0;
        }

        $config = $this->getSubjectConfig($gradeId, $subjectId, $termId);
        if ($config?->max_score) {
            return (float) $config->max_score;
        }
        return 100.0;
    }

    public function resolvePassScore(CourseOffering $courseOffering, int $termId, ?float $maxScore = null): float
    {
        $gradeId = $courseOffering->classSection?->grade_id;
        $subjectId = $courseOffering->subject_id;

        if ($gradeId === null || $subjectId === null) {
            return ($maxScore ?? 100.0) * 0.5;
        }

        $config = $this->getSubjectConfig($gradeId, $subjectId, $termId);
        if ($config?->pass_score) {
            return (float) $config->pass_score;
        }

        $baseMax = $maxScore ?? 100.0;

        $defaultPassPercent = (float) SystemSetting::get('grading.default_pass_score', 50);
        $defaultPassPercent = max(0.0, min(100.0, $defaultPassPercent));

        return $baseMax * ($defaultPassPercent / 100);
    }

    private function getSubjectConfig(int $gradeId, int $subjectId, int $termId): ?SubjectGradingConfig
    {
        $key = "{$gradeId}_{$subjectId}_{$termId}";

        if (array_key_exists($key, $this->subjectConfigs)) {
            return $this->subjectConfigs[$key];
        }

        $config = SubjectGradingConfig::where('subject_id', $subjectId)
            ->where('grade_id', $gradeId)
            ->where('term_id', $termId)
            ->first();

        $this->subjectConfigs[$key] = $config;

        return $config;
    }

}
