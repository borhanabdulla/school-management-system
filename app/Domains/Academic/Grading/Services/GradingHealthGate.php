<?php

declare(strict_types=1);

namespace App\Domains\Academic\Grading\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;

final class GradingHealthGate
{
    public function __construct(
        private GradingConfigHealthChecker $checker
    ) {
    }

    /**
     * @return array{missing: int, invalid: int, total: int}
     */
    public function getTermIssues(Term $term): array
    {
        $report = $this->checker->checkTerm($term)->toArray();
        $missing = count($report['missing']);
        $invalid = count($report['invalid']);

        return [
            'missing' => $missing,
            'invalid' => $invalid,
            'total' => $missing + $invalid,
        ];
    }

    /**
     * @return array{missing: int, invalid: int, total: int}
     */
    public function getYearIssues(AcademicYear $year): array
    {
        $missing = 0;
        $invalid = 0;

        foreach ($year->terms()->whereIn('status', [TermStatus::Active->value, TermStatus::Completed->value])->get() as $term) {
            $issues = $this->getTermIssues($term);
            $missing += $issues['missing'];
            $invalid += $issues['invalid'];
        }

        return [
            'missing' => $missing,
            'invalid' => $invalid,
            'total' => $missing + $invalid,
        ];
    }

    public function assertTermHealthy(Term $term, string $operation): void
    {
        $issues = $this->getTermIssues($term);

        if ($issues['total'] === 0) {
            return;
        }

        throw InvalidOperationException::cannotModify(
            $operation,
            "إعدادات الدرجات غير مكتملة (Missing {$issues['missing']} / Invalid {$issues['invalid']})"
        );
    }

    public function assertYearHealthy(AcademicYear $year, string $operation): void
    {
        $issues = $this->getYearIssues($year);

        if ($issues['total'] === 0) {
            return;
        }

        throw InvalidOperationException::cannotModify(
            $operation,
            "إعدادات الدرجات غير مكتملة (Missing {$issues['missing']} / Invalid {$issues['invalid']})"
        );
    }
}
