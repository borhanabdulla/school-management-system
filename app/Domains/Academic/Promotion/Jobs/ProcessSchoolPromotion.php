<?php

namespace App\Domains\Academic\Promotion\Jobs;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\Promotion\Services\PromotionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ProcessSchoolPromotion - وظيفة معالجة ترحيل الطلاب
 */
class ProcessSchoolPromotion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;
    public int $tries = 1;

    protected int $academicYearId;
    protected ?int $processedById;
    protected string $progressKey;

    public function __construct(int $academicYearId, ?int $processedById = null)
    {
        $this->academicYearId = $academicYearId;
        $this->processedById = $processedById;
        $this->progressKey = "promotion_progress_{$academicYearId}";
    }

    public function getProgressKey(): string
    {
        return $this->progressKey;
    }

    public function handle(PromotionService $promotionService): void
    {
        $fromYear = AcademicYear::findOrFail($this->academicYearId);
        $processedBy = $this->processedById ? User::find($this->processedById) : null;

        $this->updateProgress([
            'status' => 'running',
            'message' => 'جاري تحميل بيانات الطلاب...',
            'processed' => 0,
            'total' => 0,
            'success' => 0,
            'failed' => 0,
            'errors' => [],
            'started_at' => now()->toIso8601String(),
        ]);

        try {
            // ✅ استخدام PromotionService بدلاً من StudentLookupService
            // لأن الدالة تستعلم من Results و Promotion domains
            $students = $promotionService->studentsEligibleForPromotion($fromYear->id)->get();

            $total = $students->count();
            $processed = 0;
            $success = 0;
            $failed = 0;
            $errors = [];

            $this->updateProgress([
                'status' => 'running',
                'message' => "جاري ترحيل {$total} طالب...",
                'processed' => 0,
                'total' => $total,
            ]);

            foreach ($students as $student) {
                try {
                    $promotionService->promote($student, $fromYear, null, $processedBy);
                    $success++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name_ar,
                        'error' => $e->getMessage()
                    ];
                    Log::warning("Promotion failed for student {$student->id}: " . $e->getMessage());
                }

                $processed++;

                if ($processed % 10 === 0 || $processed === $total) {
                    $this->updateProgress([
                        'status' => 'running',
                        'message' => "تم معالجة {$processed} من {$total} طالب",
                        'processed' => $processed,
                        'total' => $total,
                        'success' => $success,
                        'failed' => $failed,
                        'errors' => array_slice($errors, -5),
                    ]);
                }
            }

            $this->updateProgress([
                'status' => 'completed',
                'message' => "اكتمل الترحيل: {$success} نجاح، {$failed} فشل",
                'processed' => $processed,
                'total' => $total,
                'success' => $success,
                'failed' => $failed,
                'errors' => $errors,
                'completed_at' => now()->toIso8601String(),
            ]);

            Log::info("School promotion completed: {$success} succeeded, {$failed} failed");

        } catch (\Exception $e) {
            $this->updateProgress([
                'status' => 'failed',
                'message' => 'فشل الترحيل: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ]);
            Log::error("School promotion job failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function updateProgress(array $data): void
    {
        $current = Cache::get($this->progressKey, []);
        Cache::put($this->progressKey, array_merge($current, $data), now()->addHours(2));
    }

    public function failed(\Throwable $exception): void
    {
        $this->updateProgress([
            'status' => 'failed',
            'message' => 'فشل الترحيل: ' . $exception->getMessage(),
            'error' => $exception->getMessage(),
            'failed_at' => now()->toIso8601String(),
        ]);
        Log::error("ProcessSchoolPromotion job failed: " . $exception->getMessage());
    }
}
