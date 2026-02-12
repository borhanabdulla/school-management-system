<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\CourseOffering\Models\CourseOffering;
use App\Domains\Academic\Grading\Exceptions\InvalidGradingConfigException;
use App\Domains\Academic\Grading\Exceptions\MissingSubjectConfigException;
use App\Domains\Academic\Grading\Models\SystemSetting;
use App\Domains\Academic\Grading\Services\Health\GradingConfigViolation;
use App\Domains\Academic\Grading\Services\Health\GradingConfigHealthReport;
use App\Domains\Academic\Term\Models\Term;

final class GradingConfigHealthChecker
{
    public function __construct(
        private SubjectGradingConfigResolver $resolver
    ) {
    }

    public function checkTerm(Term $term): GradingConfigHealthReport
    {
        $report = new GradingConfigHealthReport();
        $storedScale = SystemSetting::get('grading.scale', null);
        $scale = is_array($storedScale) && $storedScale !== []
            ? $storedScale
            : GradeScaleValidator::defaultScale();
        $scaleIssues = app(GradeScaleValidator::class)->validate($scale);

        if ($scaleIssues !== []) {
            $report->recordInvalid(
                [
                    'term_id' => $term->id,
                    'course_offering_id' => 0,
                    'subject_id' => null,
                    'grade_id' => null,
                ],
                new GradingConfigViolation(
                    'GRADE_SCALE_INVALID',
                    'سلم التقديرات غير صالح.',
                    ['issues' => $scaleIssues]
                )
            );
        }

        // Preload all grading configs for the term (avoids N+1)
        $this->resolver->preloadForTerm($term);

        CourseOffering::query()
            ->where('term_id', $term->id)
            ->with(['classSection'])
            ->select(['id', 'term_id', 'subject_id', 'class_section_id', 'academic_year_id'])
            ->chunkById(200, function ($offerings) use ($term, $report) {
                /** @var CourseOffering $offering */
                foreach ($offerings as $offering) {
                    $report->incrementChecked();

                    $payload = [
                        'term_id' => $term->id,
                        'course_offering_id' => $offering->id,
                        'subject_id' => $offering->subject_id,
                        'grade_id' => $offering->classSection?->grade_id,
                    ];

                    try {
                        $this->resolver->resolve($offering, $term);
                        foreach ($this->resolver->warningsFor($offering, $term) as $warning) {
                            $report->recordWarning($payload, $warning);
                        }
                    } catch (MissingSubjectConfigException) {
                        $report->recordMissing($payload);
                        continue;
                    } catch (InvalidGradingConfigException $exception) {
                        foreach ($exception->violations() as $violation) {
                            $report->recordInvalid($payload, $violation);
                        }
                        continue;
                    }
                }
            });

        return $report;
    }
}
