<?php

namespace App\Domains\Academic\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\User;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Services\ReadinessService;
use App\Domains\Academic\Notifications\WeeklyReadinessReminder;

/**
 * Job to send weekly readiness reminders
 *
 * This job should be scheduled weekly in the Laravel scheduler.
 * It sends reminders only for years that have active issues.
 *
 * Usage in Kernel.php:
 * $schedule->job(SendWeeklyReadinessReminders::class)->weekly()->mondays()->at('08:00');
 */
class SendWeeklyReadinessReminders implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Execute the job.
     */
    public function handle(ReadinessService $readinessService): void
    {
        // Get all active years that can receive reminders
        $years = $this->getActiveYears();

        foreach ($years as $year) {
            $this->sendReminderForYear($year, $readinessService);
        }
    }

    /**
     * Get academic years that should receive reminders
     */
    protected function getActiveYears(): Collection
    {
        return AcademicYear::query()
            ->where('status', AcademicYearStatus::Active)
            ->whereHas('terms', function ($query) {
                $query->whereIn('status', [TermStatus::Active, TermStatus::Completed]);
            })
            ->get();
    }

    /**
     * Send reminder for a specific year
     */
    protected function sendReminderForYear(AcademicYear $year, ReadinessService $readinessService): void
    {
        // Get warnings for the year
        $warnings = $readinessService->getTeacherReadiness($year);

        // Only send if there are actual issues
        $issues = $warnings->filter(fn($item) => $item->hasIssues());

        if ($issues->isEmpty()) {
            return; // No issues, no reminder needed
        }

        // Get users who should receive the reminder
        $users = $this->getRecipients($year);

        foreach ($users as $user) {
            $user->notify(new WeeklyReadinessReminder(
                items: $issues,
                yearName: $year->name,
                yearId: $year->id
            ));
        }

        $this->logReminderSent($year, $users->count(), $issues->count());
    }

    /**
     * Get users who should receive reminders for this year
     */
    protected function getRecipients(AcademicYear $year): Collection
    {
        return User::query()
            ->whereNotNull('email')
            ->where('email_verified_at', '!=', null)
            ->where(function ($query) {
                $query->whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin', 'academic_manager']));
                // Add teacher role if needed
            })
            ->get();
    }

    /**
     * Log that reminder was sent
     */
    protected function logReminderSent(AcademicYear $year, int $recipientCount, int $issueCount): void
    {
        logger()->info('Weekly readiness reminder sent', [
            'year_id' => $year->id,
            'year_name' => $year->name,
            'recipients' => $recipientCount,
            'issues_count' => $issueCount,
        ]);
    }

    /**
     * Determine the time at which the job should timeout.
     */
    public function timeout(): int
    {
        return 300; // 5 minutes
    }
}
