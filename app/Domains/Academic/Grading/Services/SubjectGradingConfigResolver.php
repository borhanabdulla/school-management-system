<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Exceptions\InvalidGradingConfigException;
use App\Domains\Academic\Grading\Exceptions\MissingSubjectConfigException;
use App\Domains\Academic\Grading\Models\SubjectGradingConfig;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;
use App\Domains\Academic\Term\Models\Term;

final class SubjectGradingConfigResolver
{
    /**
     * @var array<string, SubjectGradingConfig>
     */
    private array $cache = [];

    /**
     * @var array<string, array<int, GradingConfigViolation>>
     */
    private array $warnings = [];

    /**
     * @param iterable<GradingConfigValidator> $validators
     */
    public function __construct(private iterable $validators)
    {
    }

    public function resolve(CourseOffering $courseOffering, Term $term): SubjectGradingConfig
    {
        $key = $this->buildKey($courseOffering, $term);

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $config = $this->fetchConfig($courseOffering, $term);
        $violations = $this->validate($courseOffering, $config);

        $warnings = array_filter(
            $violations,
            fn(GradingConfigViolation $violation) => $violation->severity() === GradingConfigViolation::SEVERITY_WARNING
        );

        $this->warnings[$key] = array_values($warnings);

        $invalid = array_filter(
            $violations,
            fn(GradingConfigViolation $violation) => $violation->severity() !== GradingConfigViolation::SEVERITY_WARNING
        );

        if ($invalid) {
            throw new InvalidGradingConfigException(array_values($invalid));
        }

        return $this->cache[$key] = $config;
    }

    /**
     * @return array<int, GradingConfigViolation>
     */
    private function validate(CourseOffering $courseOffering, SubjectGradingConfig $config): array
    {
        $violations = [];

        foreach ($this->validators as $validator) {
            if (! $validator instanceof GradingConfigValidator) {
                continue;
            }

            $violations = [...$violations, ...$validator->validate($courseOffering, $config)];
        }

        return $violations;
    }

    public function warningsFor(CourseOffering $courseOffering, Term $term): array
    {
        return $this->warnings[$this->buildKey($courseOffering, $term)] ?? [];
    }

    private function fetchConfig(CourseOffering $courseOffering, Term $term): SubjectGradingConfig
    {
        // Binding path: subject_id + grade_id + term_id defines the template for an offering.
        /** @var SubjectGradingConfig|null $config */
        $config = SubjectGradingConfig::where('subject_id', $courseOffering->subject_id)
            ->where('grade_id', $courseOffering->classSection?->grade_id)
            ->where('term_id', $term->id)
            ->with('template.categories')
            ->first();

        if (! $config || ! $config->template) {
            throw new MissingSubjectConfigException($courseOffering, $term);
        }

        $config->template->load(['categories' => fn($q) => $q->with('children')]);

        return $config;
    }

    private function buildKey(CourseOffering $courseOffering, Term $term): string
    {
        return sprintf(
            '%s_%s_%s',
            $courseOffering->classSection?->grade_id ?? '0',
            $courseOffering->subject_id ?? '0',
            $term->id
        );
    }
}
