<?php

namespace App\Console\Commands;

use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use App\Domains\Academic\Term\Models\Term;
use Illuminate\Console\Command;

class GradingConfigCheck extends Command
{
    protected $signature = 'grading:check-configs {termId? : Term ID to inspect}';
    protected $description = 'Ensure each course offering has SubjectGradingConfig for the term.';

    public function __construct(private GradingConfigHealthChecker $checker)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $termId = $this->argument('termId');
        $term = Term::find($termId) ?? Term::where('status', 'active')->first();

        if (! $term) {
            $this->error('Term not found.');
            return Command::FAILURE;
        }

        $report = $this->checker->checkTerm($term);
        $summary = $report->toArray();

        if (empty($summary['missing']) && empty($summary['invalid'])) {
            $this->info("All {$summary['checked']} course offerings for term {$term->name} passed the health check.");
            return Command::SUCCESS;
        }

        $this->warn("Health check found issues (checked: {$summary['checked']}):");

        if (! empty($summary['missing'])) {
            $this->warn('Missing configurations:');
            foreach ($summary['missing'] as $row) {
                $this->line(sprintf(
                    '  CourseOffering %d (Subject %d, Grade %d) -> missing config',
                    $row['course_offering_id'],
                    $row['subject_id'] ?? 0,
                    $row['grade_id'] ?? 0
                ));
            }
        }

        if (! empty($summary['invalid'])) {
            $this->warn('Invalid configurations:');
            foreach ($summary['invalid'] as $row) {
                $violation = $row['violation'] ?? [];
                $this->line(sprintf(
                    '  CourseOffering %d (Subject %d, Grade %d) -> %s: %s',
                    $row['course_offering_id'],
                    $row['subject_id'] ?? 0,
                    $row['grade_id'] ?? 0,
                    $violation['type'] ?? 'unknown',
                    $violation['message'] ?? 'unknown issue'
                ));
            }
        }

        if (! empty($summary['warnings'])) {
            $this->warn('Warnings:');
            foreach ($summary['warnings'] as $row) {
                $violation = $row['violation'] ?? [];
                $this->line(sprintf(
                    '  CourseOffering %d -> %s: %s',
                    $row['course_offering_id'],
                    $violation['type'] ?? 'warning',
                    $violation['message'] ?? 'warning'
                ));
            }
        }

        return Command::FAILURE;
    }
}
