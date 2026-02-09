# خطة إصلاح نظام إغلاق السنة الدراسية

**تاريخ الخطة:** 2026-02-07
**الحالة:** جاهزة للتنفيذ
**الأولوية:** 🔴 حرجة (Critical)

---

## 1. نظرة عامة

### الهدف
معالجة الثغرات الأمنية (الصلاحيات)، تحسين جودة الكود، تصحيح المنطق، وضمان ميكانيكية النظام لإغلاق السنة الدراسية بشكل آمن وموثوق.

### المشاكل المستهدفة
| # | المشكلة | الأولوية | الملف |
|---|--------|---------|-------|
| 1 | صلاحية `academic.year.close` مفقودة | 🔴 | RoleSeeder.php |
| 2 | استخدام نصوص بدلاً من Enums | 🔧 | YearClosingWizard.php, SendWeeklyReadinessReminders.php |
| 3 | منطق `countMissingAttendance()` خاطئ | 🔧 | ReadinessService.php |
| 4 | Job غير مجدول | 🔴 | Kernel.php |
| 5 | لا يوجد Event للإغلاق | 🔧 | CloseAcademicYearAction.php |
| 6 | AcademicWriteGuard غير محسن | ⚠️ | AcademicWriteGuard.php |

---

## 2. الخطوة 1: الصلاحيات والأمان

### الهدف
إضافة صلاحية `academic.year.close` لتمكين المدراء من استخدام معالج الإغلاق.

### الملفات المعدلة

#### 1.1 [MODIFY] database/seeders/RoleSeeder.php

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // 🛡️ صلاحيات الأكاديميا
        $academicPermissions = [
            'academic.year.close',        // إغلاق سنة دراسية
            'academic.year.reopen',       // إعادة فتح سنة مغلقة
            'academic.term.close',        // إغلاق ترم دراسي
            'academic.amendment.create',  // إنشاء تعديل
        ];

        // 🛡️ صلاحيات الدرجات
        $gradingPermissions = [
            'grades.override',            // تجاوز الدرجات
            'grades.view.all',            // عرض جميع الدرجات
            'grades.recalculate',        // إعادة حساب الدرجات
        ];

        // دمج جميع الصلاحيات
        $allPermissions = array_merge(
            // الصلاحيات الموجودة
            [
                'marks.view',
                'marks.edit',
                'marks.override',
                'curriculum.manage',
                'attendance.manage',
                'attendance.view',
                'staff.view',
                'staff.create',
                'staff.edit',
                'roles.manage',
                'students.promote',
                'settings.edit',
                'leaves.approve',
                'leave.request',
                'payroll.manage',
                'finance.apply_discount',
                'finance.record_payment',
                'finance.cancel_payment',
            ],
            // الصلاحيات الجديدة
            $academicPermissions,
            $gradingPermissions
        );

        // حذف الصلاحيات المكررة
        $allPermissions = array_unique($allPermissions);
        sort($allPermissions);

        // إنشاء الصلاحيات
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        $this->command->info("✅ Created " . count($allPermissions) . " permissions.");
    }
}
```

### الكود الجديد

```php
// داخل run() method:
// 1. تحديد الصلاحيات الأكاديمية الجديدة
$academicPermissions = [
    'academic.year.close',        // إغلاق سنة دراسية
    'academic.year.reopen',       // إعادة فتح سنة مغلقة
];

// 2. إضافتها لمصفوفة الصلاحيات
$allPermissions = array_merge($allPermissions, $academicPermissions);
```

---

## 3. الخطوة 2: تحسين جودة الكود (Enums)

### الهدف
استبدال النصوص الصريحة بالقيم الثابتة (Enums).

### الملفات المعدلة

#### 2.1 [MODIFY] app/Livewire/Academic/YearClosingWizard.php

```php
<?php

namespace App\Livewire\Academic;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Domains\Academic\Models\User;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Services\ReadinessService;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator;
use Illuminate\Support\Facades\Auth;

#[Layout('components.guest-layout')]
#[Title('معالج إغلاق السنة الدراسية')]
class YearClosingWizard extends Component
{
    // Wizard state
    public AcademicYear $year;
    public int $currentStep = 1;
    public int $totalSteps = 3;

    // Readiness data
    public array $readinessSummary = [];
    public array $blockingItems = [];
    public array $warningItems = [];

    // Final checks
    public bool $confirmNoBlockingIssues = false;
    public bool $confirmBackupTaken = false;
    public bool $confirmResponsibility = false;

    // Closing state
    public bool $isClosing = false;
    public ?string $closingError = null;
    public ?string $closingSuccess = null;

    // Services
    protected ReadinessService $readinessService;
    protected AcademicYearClosureValidator $closureValidator;

    /**
     * Mount the wizard with the given year.
     */
    public function mount(AcademicYear $year): void
    {
        $this->year = $year;
        $this->authorizeAccess();
        $this->initializeServices();
        $this->loadReadinessData();
    }

    /**
     * Initialize services using Laravel container.
     */
    protected function initializeServices(): void
    {
        $this->readinessService = app(ReadinessService::class);
        $this->closureValidator = app(AcademicYearClosureValidator::class);
    }

    /**
     * Authorize access to the wizard.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function authorizeAccess(): void
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            abort(403, 'يجب تسجيل الدخول للوصول إلى هذه الصفحة.');
        }

        // 🛡️ التحقق من الصلاحية (تفعيل التحقق)
        if (!Auth::user()->can('academic.year.close')) {
            abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية.');
        }

        // ✅ استخدام Enum بدلاً من النص
        if ($this->year->status->value === AcademicYearStatus::Closed->value) {
            abort(400, 'السنة الدراسية مغلقة بالفعل.');
        }

        // التحقق من وجود ترمات
        if (!$this->year->terms()->exists()) {
            abort(400, 'لا توجد ترمات لهذه السنة الدراسية.');
        }
    }

    /**
     * Load readiness data for the year.
     */
    protected function loadReadinessData(): void
    {
        $this->readinessSummary = $this->readinessService->getSummary($this->year);

        $this->blockingItems = collect($this->readinessSummary['items'])
            ->filter(fn($item) => $item['is_blocking'] && $item['has_issues'])
            ->values()
            ->toArray();

        $this->warningItems = collect($this->readinessSummary['items'])
            ->filter(fn($item) => $item['is_warning'] && $item['has_issues'])
            ->values()
            ->toArray();
    }

    /**
     * Navigate to next step.
     */
    public function nextStep(): void
    {
        if ($this->currentStep >= $this->totalSteps) {
            return;
        }

        match ($this->currentStep) {
            1 => $this->validateAndProceedFromStep1(),
            2 => $this->validateAndProceedFromStep2(),
            default => $this->currentStep++,
        };
    }

    /**
     * Validate and proceed from step 1.
     */
    protected function validateAndProceedFromStep1(): void
    {
        if (!empty($this->blockingItems)) {
            $this->addError('step1', 'لا يمكنك المتابعة بينما توجد مشاكل تمنع الإغلاق.');
            return;
        }

        $this->currentStep++;
    }

    /**
     * Validate and proceed from step 2.
     */
    protected function validateAndProceedFromStep2(): void
    {
        if (!$this->canProceedFromStep2()) {
            $this->addError('step2', 'يجب تأكيد جميع الفحوصات للمتابعة.');
            return;
        }

        $this->currentStep++;
    }

    /**
     * Navigate to previous step.
     */
    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->closingError = null;
        }
    }

    /**
     * Check if can proceed from step 2.
     */
    public function canProceedFromStep2(): bool
    {
        return $this->confirmNoBlockingIssues
            && $this->confirmBackupTaken
            && $this->confirmResponsibility;
    }

    /**
     * Close the year.
     */
    public function closeYear(CloseAcademicYearAction $closeAction): void
    {
        $this->isClosing = true;
        $this->closingError = null;
        $this->closingSuccess = null;

        try {
            // التحقق النهائي
            $validation = $this->closureValidator->validate($this->year);

            if (!$validation['can']) {
                throw new \Exception(
                    'توجد مشاكل تمنع إغلاق السنة: ' . implode('، ', $validation['issues'])
                );
            }

            // تنفيذ الإغلاق
            $closeAction->execute($this->year, Auth::user());

            $this->closingSuccess = 'تم إغلاق السنة الدراسية بنجاح.';

            // إعادة تحميل البيانات
            $this->year->refresh();
            $this->loadReadinessData();

            // الانتقال للخطوة الأخيرة
            $this->currentStep = $this->totalSteps;

        } catch (\Exception $e) {
            $this->closingError = $e->getMessage();
        } finally {
            $this->isClosing = false;
        }
    }

    /**
     * Render the wizard.
     */
    public function render()
    {
        return view('livewire.academic.year-closing-wizard', [
            'year' => $this->year,
            'readinessSummary' => $this->readinessSummary,
            'blockingItems' => $this->blockingItems,
            'warningItems' => $this->warningItems,
        ]);
    }
}
```

### التغييرات الرئيسية

```diff
- if ($this->year->status === 'closed') {
+ if ($this->year->status->value === AcademicYearStatus::Closed->value) {

- // TODO: تفعيل الصلاحيات
- // if (!Auth::user()->can('close.year')) {
- //     abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية');
- // }

+ if (!Auth::user()->can('academic.year.close')) {
+     abort(403, 'ليس لديك صلاحية إغلاق السنة الدراسية.');
+ }
```

#### 2.2 [MODIFY] app/Domains/Academic/Jobs/SendWeeklyReadinessReminders.php

```php
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
        $years = $this->getActiveYears();

        foreach ($years as $year) {
            $this->sendReminderForYear($year, $readinessService);
        }
    }

    /**
     * Get academic years that should receive reminders.
     *
     * @return Collection<int, AcademicYear>
     */
    protected function getActiveYears(): Collection
    {
        return AcademicYear::query()
            ->where('status', AcademicYearStatus::Active->value)  // ✅ استخدام Enum
            ->whereHas('terms', function ($query) {
                $query->whereIn('status', [
                    TermStatus::Active->value,
                    TermStatus::Completed->value,
                ]);
            })
            ->get();
    }

    /**
     * Send reminder for a specific year.
     */
    protected function sendReminderForYear(
        AcademicYear $year,
        ReadinessService $readinessService
    ): void {
        $warnings = $readinessService->getTeacherReadiness($year);
        $issues = $warnings->filter(fn($item) => $item->hasIssues());

        if ($issues->isEmpty()) {
            return;
        }

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
     * Get users who should receive reminders for this year.
     *
     * @return Collection<int, User>
     */
    protected function getRecipients(): Collection
    {
        return User::query()
            ->whereNotNull('email')
            ->whereNotNull('email_verified_at')
            ->whereHas('roles', fn($query) => $query->whereIn(
                'name',
                ['admin', 'super_admin', 'academic_manager']
            ))
            ->get();
    }

    /**
     * Log that reminder was sent.
     */
    protected function logReminderSent(
        AcademicYear $year,
        int $recipientCount,
        int $issueCount
    ): void {
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
```

---

## 4. الخطوة 3: تصحيح منطق الجاهزية

### الهدف
تحسين دقة تقارير الجاهزية، خاصة فيما يتعلق باحتساب الغياب.

#### 4.1 [MODIFY] app/Domains/Academic/Services/ReadinessService.php

```php
<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\Data\ReadinessItem;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Services\StudentEnrollmentQueryService;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Attendance\Models\AttendanceSetting;
use App\Domains\Academic\Attendance\Enums\AttendanceMode;
use App\Domains\Academic\Grading\Models\MonthlyGrade;
use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection;

class ReadinessService
{
    // Blocking badge keys
    public const KEY_TERMS_NOT_COMPLETED = 'terms_not_completed';
    public const KEY_ANNUAL_RESULTS_PENDING = 'annual_results_pending';
    public const KEY_PROMOTION_INCOMPLETE = 'promotion_incomplete';

    // Warning badge keys
    public const KEY_ATTENDANCE_MISSING = 'attendance_missing_sessions';
    public const KEY_MARKS_MISSING = 'marks_missing_entries';
    public const KEY_GRADING_CONFIG_ISSUES = 'grading_config_issues';
    public const KEY_ATTENDANCE_MODE_UNSUPPORTED = 'attendance_mode_unsupported';

    public function __construct(
        protected StudentEnrollmentQueryService $studentService
    ) {}

    /**
     * Get all readiness items for an academic year.
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getReadinessItems(AcademicYear $year): Collection
    {
        return collect([
            $this->checkTermsCompleted($year),
            $this->checkAnnualResultsPending($year),
            $this->checkPromotionIncomplete($year),
            $this->checkGradingConfigIssues($year),
            $this->checkAttendanceMissing($year),
            $this->checkAttendanceModeSupport($year),
            $this->checkMarksMissing($year),
        ]);
    }

    /**
     * Get only blocking items.
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getBlockingItems(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year)
            ->filter(fn(ReadinessItem $item) => $item->isBlocking());
    }

    /**
     * Get only warning items.
     *
     * @return Collection<int, ReadinessItem>
     */
    public function getWarningItems(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year)
            ->filter(fn(ReadinessItem $item) => $item->isWarning());
    }

    /**
     * Check if year can be closed.
     */
    public function canClose(AcademicYear $year): bool
    {
        return $this->getBlockingItems($year)->every(
            fn(ReadinessItem $item) => !$item->hasIssues()
        );
    }

    /**
     * Get summary for the year.
     */
    public function getSummary(AcademicYear $year): array
    {
        $items = $this->getReadinessItems($year);

        return [
            'year_id' => $year->id,
            'year_name' => $year->name,
            'can_close' => $this->canClose($year),
            'blocking_count' => $this->getBlockingItems($year)->filter->hasIssues()->count(),
            'warning_count' => $this->getWarningItems($year)->filter->hasIssues()->count(),
            'items' => $items->map(fn($item) => $item->toArray())->toArray(),
        ];
    }

    /**
     * Check if there are any warnings for a year.
     */
    public function hasWarnings(AcademicYear $year): bool
    {
        return $this->getWarningItems($year)->filter->hasIssues()->isNotEmpty();
    }

    /**
     * Check if all terms are completed (Blocking).
     */
    protected function checkTermsCompleted(AcademicYear $year): ReadinessItem
    {
        $totalTerms = $year->terms()->count();
        $completedTerms = $year->terms()
            ->where('status', TermStatus::Completed->value)
            ->count();
        $incompleteCount = $totalTerms - $completedTerms;

        return ReadinessItem::blocking(
            key: self::KEY_TERMS_NOT_COMPLETED,
            label: 'ترمات غير مكتملة',
            message: $incompleteCount === 0
                ? 'جميع الترمات مكتملة'
                : "توجد {$incompleteCount} ترمات غير مكتملة",
            count: $incompleteCount,
            route: 'terms.index',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check if all annual results are calculated (Blocking).
     */
    protected function checkAnnualResultsPending(AcademicYear $year): ReadinessItem
    {
        $pendingCount = AnnualResult::where('academic_year_id', $year->id)
            ->where('decision', 'pending')
            ->count();

        return ReadinessItem::blocking(
            key: self::KEY_ANNUAL_RESULTS_PENDING,
            label: 'نتائج سنوية معلقة',
            message: $pendingCount === 0
                ? 'جميع النتائج السنوية محسوبة'
                : "توجد {$pendingCount} نتيجة سنوية معلقة",
            count: $pendingCount,
            route: 'results.annual.index',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check if all eligible students are promoted (Blocking).
     */
    protected function checkPromotionIncomplete(AcademicYear $year): ReadinessItem
    {
        $eligibleCount = $this->studentService->eligibleForClosureCount($year);

        if ($eligibleCount === 0) {
            return ReadinessItem::blocking(
                key: self::KEY_PROMOTION_INCOMPLETE,
                label: 'ترحيلات غير مكتملة',
                message: 'لا يوجد طلاب مؤهلين للترحيل',
                count: 0
            );
        }

        $promotedCount = Promotion::where('academic_year_id', $year->id)
            ->where('is_reverted', false)
            ->count();

        $remaining = max(0, $eligibleCount - $promotedCount);

        return ReadinessItem::blocking(
            key: self::KEY_PROMOTION_INCOMPLETE,
            label: 'ترحيلات غير مكتملة',
            message: $remaining === 0
                ? 'جميع الطلاب تم ترحيلهم'
                : "بقي {$remaining} طالب لم يتم ترحيلهم",
            count: $remaining,
            route: 'promotions.index',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check for missing attendance sessions (Warning).
     *
     * ⚠️ مُصحح: الآن يحسب الفرق بين المتوقع والمسجل
     */
    protected function checkAttendanceMissing(AcademicYear $year): ReadinessItem
    {
        $activeTerms = $year->terms()
            ->whereIn('status', [TermStatus::Active->value, TermStatus::Completed->value])
            ->get();

        $missingCount = 0;

        foreach ($activeTerms as $term) {
            $missingCount += $this->calculateMissingAttendance($year, $term);
        }

        return ReadinessItem::warning(
            key: self::KEY_ATTENDANCE_MISSING,
            label: 'غياب في الحضور',
            message: $missingCount === 0
                ? 'لا توجد حصص حضور ناقصة'
                : "توجد {$missingCount} حصة حضور ناقصة",
            count: $missingCount,
            route: 'teacher.attendance.report',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Calculate missing attendance for a term.
     *
     * @return int Number of missing attendance sessions
     */
    protected function calculateMissingAttendance(AcademicYear $year, Term $term): int
    {
        // استخراج: عدد الحصص المجدولة
        $scheduledSessions = $this->countScheduledSessions($year, $term);

        // الموجود: عدد سجلات الحضور
        $recordedSessions = Attendance::where('academic_year_id', $year->id)
            ->where('term_id', $term->id)
            ->count();

        // الناقص = المجدول - الموجود
        return max(0, $scheduledSessions - $recordedSessions);
    }

    /**
     * Count scheduled sessions based on timetable.
     *
     * @return int
     */
    protected function countScheduledSessions(AcademicYear $year, Term $term): int
    {
        // هذه دالة مؤقتة - يجب تعديلها حسب منطق الجدول المدرسي
        // مثال: إذا كان هناك 5 حصص × 16 أسبوع × عدد الشعب
        $weeksInTerm = $term->start_date->diffInWeeks($term->end_date);

        return $year->sections()->count() * 5 * $weeksInTerm;
    }

    /**
     * Check for missing marks entries (Warning).
     */
    protected function checkMarksMissing(AcademicYear $year): ReadinessItem
    {
        $completedTerms = $year->terms()
            ->where('status', TermStatus::Completed->value)
            ->get();

        $missingCount = $completedTerms->sum(
            fn($term) => $this->countMissingMarks($year, $term)
        );

        return ReadinessItem::warning(
            key: self::KEY_MARKS_MISSING,
            label: 'درجات ناقصة',
            message: $missingCount === 0
                ? 'لا توجد درجات ناقصة'
                : "توجد {$missingCount} درجة ناقصة",
            count: $missingCount,
            route: 'grading.gradebooks',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Count missing marks for a term.
     */
    protected function countMissingMarks(AcademicYear $year, Term $term): int
    {
        return MonthlyGrade::query()
            ->whereNull('score')
            ->whereHas('gradebookMonth', function ($query) use ($term, $year) {
                $query->where('term_id', $term->id)
                    ->where('academic_year_id', $year->id);
            })
            ->whereHas('courseOffering', fn($query) => $query->where('academic_year_id', $year->id))
            ->count();
    }

    /**
     * Check grading configuration health (Warning).
     */
    protected function checkGradingConfigIssues(AcademicYear $year): ReadinessItem
    {
        $terms = $year->terms()
            ->whereIn('status', [TermStatus::Active->value, TermStatus::Completed->value])
            ->get();

        if ($terms->isEmpty()) {
            return ReadinessItem::warning(
                key: self::KEY_GRADING_CONFIG_ISSUES,
                label: 'إعدادات الدرجات',
                message: 'لا توجد ترمات للفحص',
                count: 0,
                route: 'grading.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        $checker = app(GradingConfigHealthChecker::class);
        $missing = 0;
        $invalid = 0;

        foreach ($terms as $term) {
            $report = $checker->checkTerm($term)->toArray();
            $missing += count($report['missing']);
            $invalid += count($report['invalid']);
        }

        $count = $missing + $invalid;

        return ReadinessItem::warning(
            key: self::KEY_GRADING_CONFIG_ISSUES,
            label: 'إعدادات الدرجات',
            message: $count === 0
                ? 'لا توجد مشاكل في إعدادات الدرجات'
                : "توجد {$count} مشكلة في إعدادات الدرجات (Missing {$missing} / Invalid {$invalid})",
            count: $count,
            route: 'grading.settings',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Check if attendance mode is supported by grading sync (Warning).
     */
    protected function checkAttendanceModeSupport(AcademicYear $year): ReadinessItem
    {
        $setting = AttendanceSetting::where('academic_year_id', $year->id)->first();

        if (!$setting) {
            return ReadinessItem::warning(
                key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
                label: 'نمط الحضور',
                message: 'لا توجد إعدادات حضور لهذه السنة',
                count: 1,
                route: 'attendance.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        if ($setting->mode === AttendanceMode::Daily) {
            return ReadinessItem::warning(
                key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
                label: 'نمط الحضور',
                message: 'نمط الحضور اليومي غير مدعوم حالياً في مزامنة الدرجات',
                count: 1,
                route: 'attendance.settings',
                routeParams: ['academic_year' => $year->id]
            );
        }

        return ReadinessItem::warning(
            key: self::KEY_ATTENDANCE_MODE_UNSUPPORTED,
            label: 'نمط الحضور',
            message: 'نمط الحضور مدعوم',
            count: 0,
            route: 'attendance.settings',
            routeParams: ['academic_year' => $year->id]
        );
    }

    /**
     * Get teacher-specific readiness (warnings only).
     */
    public function getTeacherReadiness(AcademicYear $year, ?int $teacherId = null): Collection
    {
        return $this->getWarningItems($year);
    }

    /**
     * Get readiness for admin dashboard (all items).
     */
    public function getAdminReadiness(AcademicYear $year): Collection
    {
        return $this->getReadinessItems($year);
    }
}
```

---

## 5. الخطوة 4: الأحداث والجدولة

### الهدف
ضمان تفاعل النظام مع عملية الإغلاق وتشغيل التذكيرات بشكل دوري.

#### 5.1 [NEW] app/Domains/Academic/AcademicYear/Events/AcademicYearClosed.php

```php
<?php

namespace App\Domains\Academic\AcademicYear\Events;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AcademicYearClosed
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public AcademicYear $year,
        public ?User $closedBy = null
    ) {}
}
```

#### 5.2 [NEW] app/Domains/Academic/AcademicYear/Listeners/InvalidateAcademicYearCache.php

```php
<?php

namespace App\Domains\Academic\AcademicYear\Listeners;

use App\Domains\Academic\AcademicYear\Events\AcademicYearClosed;
use Illuminate\Support\Facades\Cache;

class InvalidateAcademicYearCache
{
    /**
     * Handle the event.
     */
    public function handle(AcademicYearClosed $event): void
    {
        // حذف الكاش المتعلق بالسنة الدراسية
        Cache::tags(['academic', 'years'])->flush();

        // حذف كاش السنة المحددة
        Cache::forget("academic.year.{$event->year->id}");
        Cache::forget("academic.current_year");
        Cache::forget("academic.years.list");
    }
}
```

#### 5.3 [NEW] app/Domains/Academic/AcademicYear/Listeners/LogAcademicYearClosure.php

```php
<?php

namespace App\Domains\Academic\AcademicYear\Listeners;

use App\Domains\Academic\AcademicYear\Events\AcademicYearClosed;
use Illuminate\Support\Facades\Log;

class LogAcademicYearClosure
{
    /**
     * Handle the event.
     */
    public function handle(AcademicYearClosed $event): void
    {
        Log::channel('academic')->info('Academic year closed', [
            'year_id' => $event->year->id,
            'year_name' => $event->year->name,
            'closed_by' => $event->closedBy?->id,
            'closed_by_name' => $event->closedBy?->name,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
```

#### 5.4 [MODIFY] app/Providers/EventServiceProvider.php

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Domains\Academic\AcademicYear\Events\AcademicYearClosed;
use App\Domains\Academic\AcademicYear\Listeners\InvalidateAcademicYearCache;
use App\Domains\Academic\AcademicYear\Listeners\LogAcademicYearClosure;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        AcademicYearClosed::class => [
            InvalidateAcademicYearCache::class,
            LogAcademicYearClosure::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
```

#### 5.5 [MODIFY] app/Domains/Academic/AcademicYear/Actions/CloseAcademicYearAction.php

```php
<?php

namespace App\Domains\Academic\AcademicYear\Actions;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Events\AcademicYearClosed;
use App\Domains\Academic\AcademicYear\Validation\AcademicYearClosureValidator;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class CloseAcademicYearAction
{
    public function __construct(
        protected AcademicYearClosureValidator $closureValidator
    ) {}

    /**
     * Close an academic year.
     *
     * @throws \App\Infrastructure\Exceptions\BusinessRuleException
     */
    public function execute(AcademicYear $year, ?User $closedBy = null): void
    {
        // التحقق من حالة السنة
        if ($year->status !== AcademicYearStatus::Active) {
            throw new InvalidOperationException(
                'لا يمكن إغلاق سنة غير نشطة.'
            );
        }

        // التحقق من جاهزية الإغلاق
        $validation = $this->closureValidator->validate($year);

        if (!$validation['can']) {
            throw InvalidOperationException::cannotClose(
                'السنة الدراسية',
                implode('، ', $validation['issues'])
            );
        }

        // تنفيذ الإغلاق في transaction
        DB::transaction(function () use ($year, $closedBy): void {
            // Lock row
            $year = AcademicYear::where('id', $year->id)
                ->lockForUpdate()
                ->first();

            // تحديث الحالة
            $year->update(['status' => AcademicYearStatus::Closed]);

            // إطلاق Event
            event(new AcademicYearClosed($year, $closedBy));

            Log::info('Academic year closed', [
                'year_id' => $year->id,
                'year_name' => $year->name,
                'closed_by' => $closedBy?->id,
            ]);
        });
    }
}
```

#### 5.6 [MODIFY] app/Console/Kernel.php

```php
<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Domains\Academic\Jobs\SendWeeklyReadinessReminders;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 🗓️ التذكيرات الأسبوعية للجاهزية
        $schedule->job(SendWeeklyReadinessReminders::class)
            ->weekly()
            ->mondays()
            ->at('08:00')
            ->withoutOverlapping()
            ->runInBackground();

        // مثال: تنظيف الكاش اليومي
        // $schedule->command('cache:prune-stale-tags')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
```

---

## 6. الخطوة 5: تحسين الأداء

### الهدف
تحسين استعلامات قاعدة البيانات.

#### 6.1 [MODIFY] app/Domains/Academic/Services/AcademicWriteGuard.php

```php
<?php

namespace App\Domains\Academic\Services;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;
use App\Infrastructure\Exceptions\ResourceNotFoundException;

class AcademicWriteGuard
{
    /**
     * Deny writing if academic year is closed.
     *
     * @throws ResourceNotFoundException
     * @throws InvalidOperationException
     */
    public function assertYearNotClosed(int $academicYearId): void
    {
        $year = AcademicYear::find($academicYearId);  // ✅ استخدام find()

        if (!$year) {
            throw ResourceNotFoundException::forModel(AcademicYear::class, $academicYearId);
        }

        if ($year->status === AcademicYearStatus::Closed) {
            throw InvalidOperationException::cannotModify(
                'السنة الدراسية "' . $year->name . '"',
                'مغلقة ولا يمكن التعديل'
            );
        }
    }

    /**
     * Deny writing if term is completed.
     *
     * @throws ResourceNotFoundException
     * @throws InvalidOperationException
     */
    public function assertTermNotCompleted(int $termId): void
    {
        $term = Term::find($termId);  // ✅ استخدام find()

        if (!$term) {
            throw ResourceNotFoundException::forModel(Term::class, $termId);
        }

        if ($term->status === TermStatus::Completed) {
            throw InvalidOperationException::cannotModify(
                'الفصل الدراسي "' . $term->name . '"',
                'مكتمل ولا يمكن التعديل'
            );
        }

        // أيضًا تأكد أن السنة الأم ليست مغلقة
        $this->assertYearNotClosed($term->academic_year_id);
    }

    /**
     * Combined: check year and term together.
     */
    public function assertWritable(int $academicYearId, ?int $termId = null): void
    {
        $this->assertYearNotClosed($academicYearId);

        if ($termId) {
            $this->assertTermNotCompleted($termId);
        }
    }

    /**
     * Check if writable without throwing exception.
     */
    public function isWritable(int $academicYearId, ?int $termId = null): bool
    {
        try {
            $this->assertWritable($academicYearId, $termId);
            return true;
        } catch (InvalidOperationException $e) {
            return false;
        }
    }

    /**
     * Get clear error message.
     */
    public function getBlockedMessage(int $academicYearId, ?int $termId = null): string
    {
        $year = AcademicYear::find($academicYearId);
        $yearName = $year?->name ?? 'غير معروفة';

        if ($termId) {
            $term = Term::find($termId);
            $termName = $term?->name ?? 'غير معروف';

            if ($year?->status === AcademicYearStatus::Closed) {
                return "لا يمكن التعديل لأن السنة الدراسية '{$yearName}' مغلقة.";
            }

            if ($term?->status === TermStatus::Completed) {
                return "لا يمكن التعديل لأن الفصل الدراسي '{$termName}' مكتمل.";
            }
        }

        if ($year?->status === AcademicYearStatus::Closed) {
            return "لا يمكن التعديل لأن السنة الدراسية '{$yearName}' مغلقة.";
        }

        return 'غير مسموح بالتعديل.';
    }
}
```

---

## 7. خطوات التنفيذ

### الخطوة 1: تشغيل migrations و seeders

```bash
# 1. إنشاء Migration للصلاحيات الجديدة (إذا لزم الأمر)
php artisan make:migration add_academic_year_permissions

# 2. تشغيل Seeders
php artisan db:seed --class=RoleSeeder

# 3. تشغيلigrations
php artisan migrate
```

### الخطوة 2: تشغيل الـ Schedule

```bash
# التحقق من الجدولة
php artisan schedule:list
```

### الخطوة 3: اختبار الصلاحيات

```php
// في tinker
php artisan tinker

>>> $user = \App\Models\User::first();
>>> $user->can('academic.year.close');
=> true/false
```

---

## 8. خطوات التحقق

### ✅ التحقق اليدوي

1. **الصلاحيات:**
   ```bash
   php artisan tinker
   >>> Permission::where('name', 'academic.year.close')->exists()
   ```

2. **المنطق:**
   ```bash
   # تجربة الإغلاق لسنة نشطة
   # مراقبة الـ Logs
   tail -f storage/logs/laravel.log
   ```

3. **الجدولة:**
   ```bash
   php artisan schedule:list
   # يجب ظهور SendWeeklyReadinessReminders
   ```

### ✅ اختبار الكود

```bash
# تشغيل جميع الاختبارات
php artisan test

# تشغيل اختبارات محددة
php artisan test --filter=AcademicYearTest
```

---

## 9. ملخص التغييرات

| # | الملف | نوع التغيير | وصف |
|---|-------|------------|------|
| 1 | `RoleSeeder.php` | تعديل | إضافة صلاحيات الأكاديميا |
| 2 | `YearClosingWizard.php` | تعديل | استخدام Enums + تفعيل الصلاحيات |
| 3 | `SendWeeklyReadinessReminders.php` | تعديل | استخدام Enums |
| 4 | `ReadinessService.php` | تعديل | تصحيح منطق الغياب |
| 5 | `AcademicYearClosed.php` | جديد | Event للإغلاق |
| 6 | `InvalidateAcademicYearCache.php` | جديد | Listener لحذف الكاش |
| 7 | `LogAcademicYearClosure.php` | جديد | Listener للتسجيل |
| 8 | `EventServiceProvider.php` | تعديل | تسجيل الـ Listeners |
| 9 | `CloseAcademicYearAction.php` | تعديل | إطلاق Event |
| 10 | `Kernel.php` | تعديل | جدولة الـ Job |
| 11 | `AcademicWriteGuard.php` | تعديل | استخدام find() |

---

## 10. الجدول الزمني

| المرحلة | المهمة | الوقت المتوقع |
|---------|--------|--------------|
| 1 | الصلاحيات | 15 دقيقة |
| 2 | Enums + YearClosingWizard | 30 دقيقة |
| 3 | ReadinessService | 45 دقيقة |
| 4 | Events + Listeners | 30 دقيقة |
| 5 | Kernel Schedule | 15 دقيقة |
| 6 | الاختبار | 30 دقيقة |

**المجموع:** ~2.5 ساعة
