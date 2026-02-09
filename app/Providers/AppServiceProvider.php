<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Infrastructure\Context\AcademicContextService;
use App\Domains\Academic\Grading\Services\GradingConfigHealthChecker;
use App\Domains\Academic\Grading\Services\SubjectGradingConfigResolver;
use App\Domains\Academic\Grading\Services\Health\GradingConfigValidator;
use App\Domains\Academic\Grading\Services\Validators\DataFlowSanityValidator;
use App\Domains\Academic\Grading\Services\Validators\MonthlyCategoryMappingValidator;
use App\Domains\Academic\Grading\Services\Validators\TermConsistencyValidator;
use App\Domains\Academic\Grading\Services\Validators\TemplateIntegrityValidator;
use App\Domains\Academic\Grading\Services\Validators\ThresholdIntegrityValidator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AcademicContextService as Singleton
        // This ensures the same instance is used throughout the request
        $this->app->singleton(AcademicContextService::class, function () {
            return AcademicContextService::getInstance();
        });

        $this->app->tag([
            TemplateIntegrityValidator::class,
            ThresholdIntegrityValidator::class,
            TermConsistencyValidator::class,
            DataFlowSanityValidator::class,
            MonthlyCategoryMappingValidator::class,
        ], GradingConfigValidator::class);

        $this->app->bind(SubjectGradingConfigResolver::class, function ($app) {
            $validators = $app->tagged(GradingConfigValidator::class);
            return new SubjectGradingConfigResolver($validators);
        });

        $this->app->bind(GradingConfigHealthChecker::class, function ($app) {
            return new GradingConfigHealthChecker(
                $app->make(SubjectGradingConfigResolver::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::anonymousComponentPath(resource_path('views/components'));

        // ═══════════════════════════════════════════════════════════════
        // Polymorphic Morph Map (لتجنب تخزين المسار الكامل في قاعدة البيانات)
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Database\Eloquent\Relations\Relation::morphMap([
            // HR Domain - Staff
            'staff' => \App\Domains\HR\Staff\Models\Staff::class,
            'staff_attendance' => \App\Domains\HR\Staff\Models\StaffAttendance::class,
            'teacher' => \App\Domains\HR\Teacher\Models\Teacher::class,
            'leave_request' => \App\Domains\HR\Leave\Models\LeaveRequest::class,
            // HR Domain - Payroll
            'contract' => \App\Domains\HR\Payroll\Models\Contract::class,
            'payroll_record' => \App\Domains\HR\Payroll\Models\PayrollRecord::class,
            'payroll_batch' => \App\Domains\HR\Payroll\Models\PayrollBatch::class,
            'loan' => \App\Domains\HR\Payroll\Models\Loan::class,
            // Student Domain
            'student' => \App\Domains\Academic\Student\Models\Student::class, // سيتم نقله لاحقاً
        ]);

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });

        \Illuminate\Support\Facades\Gate::policy(\App\Domains\Academic\CourseOffering\Models\CourseOffering::class, \App\Domains\Academic\Grade\Policies\GradePolicy::class);

        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.glass');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.glass');
        \App\Domains\Academic\AcademicYear\Models\AcademicYear::observe(\App\Domains\Academic\AcademicYear\Observers\AcademicYearObserver::class);
        \App\Domains\Academic\Student\Models\Student::observe(\App\Domains\Academic\Student\Observers\StudentObserver::class);
        \App\Domains\Academic\Calendar\Models\SchoolEvent::observe(\App\Domains\Academic\Calendar\Observers\SchoolEventObserver::class);
        \App\Domains\HR\Leave\Models\LeaveRequest::observe(\App\Domains\HR\Leave\Observers\LeaveRequestObserver::class);
        \App\Domains\HR\Staff\Models\StaffAttendance::observe(\App\Domains\HR\Staff\Observers\StaffAttendanceObserver::class);
        \App\Domains\HR\Substitution\Models\Substitution::observe(\App\Domains\HR\Substitution\Observers\SubstitutionObserver::class);
        \App\Domains\Academic\Homework\Models\Homework::observe(\App\Domains\Academic\Homework\Observers\HomeworkObserver::class);
        \App\Domains\Academic\Homework\Models\HomeworkSubmission::observe(\App\Domains\Academic\Homework\Observers\HomeworkSubmissionObserver::class);

        // ═══════════════════════════════════════════════════════════════
        // Grading Observers (Cache Invalidation)
        // ═══════════════════════════════════════════════════════════════
        \App\Domains\Academic\Grading\Models\GradingTemplate::observe(\App\Domains\Academic\Grading\Observers\GradingTemplateObserver::class);
        \App\Domains\Academic\Grading\Models\TemplateCategory::observe(\App\Domains\Academic\Grading\Observers\TemplateCategoryObserver::class);
        \App\Domains\Academic\Grading\Models\SubjectGradingConfig::observe(\App\Domains\Academic\Grading\Observers\SubjectGradingConfigObserver::class);
        \App\Domains\Academic\Grading\Models\SystemSetting::observe(\App\Domains\Academic\Grading\Observers\GradingSystemSettingObserver::class);

        // ═══════════════════════════════════════════════════════════════
        // Finance Observers (PR-OB1: Automatic Cache Invalidation)
        // ═══════════════════════════════════════════════════════════════
        \App\Domains\Finance\Models\Payment::observe(\App\Domains\Finance\Observers\PaymentObserver::class);

        // ═══════════════════════════════════════════════════════════════
        // Payroll Observers (PR-OB2: Automatic Cache Invalidation)
        // ═══════════════════════════════════════════════════════════════
        \App\Domains\HR\Payroll\Models\PayrollBatch::observe(\App\Domains\HR\Payroll\Observers\PayrollBatchObserver::class);

        // ═══════════════════════════════════════════════════════════════
        // Grading Events Registration
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Academic\Grading\Events\MonthlyGradeSaved::class,
            \App\Domains\Academic\Grading\Listeners\SyncMonthlyToStudentMark::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Academic\Attendance\Events\AttendanceBatchSaved::class,
            \App\Domains\Academic\Grading\Listeners\SyncAttendanceToMonthlyGrade::class
        );

        // ═══════════════════════════════════════════════════════════════
        // Finance Events Registration (Cache Invalidation)
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\InvoiceCreated::class,
            [\App\Domains\Finance\Listeners\InvalidateFinanceCacheListener::class, 'handleInvoiceCreated']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentReceived::class,
            [\App\Domains\Finance\Listeners\InvalidateFinanceCacheListener::class, 'handlePaymentReceived']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\DiscountApplied::class,
            [\App\Domains\Finance\Listeners\InvalidateFinanceCacheListener::class, 'handleDiscountApplied']
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentCancelled::class,
            [\App\Domains\Finance\Listeners\InvalidateFinanceCacheListener::class, 'handlePaymentCancelled']
        );

        // ═══════════════════════════════════════════════════════════════
        // Promotion Self-Healing (Update Clearance on Payment)
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentReceived::class,
            \App\Domains\Academic\Promotion\Listeners\UpdatePromotionClearanceListener::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentCancelled::class,
            \App\Domains\Academic\Promotion\Listeners\UpdatePromotionClearanceListener::class
        );
        // ═══════════════════════════════════════════════════════════════
        // Payroll Events Registration (Cache Invalidation & Notifications)
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Support\Facades\Event::listen(
            [
                \App\Domains\HR\Payroll\Events\PayrollBatchGenerated::class,
                \App\Domains\HR\Payroll\Events\PayrollBatchFrozen::class,
                \App\Domains\HR\Payroll\Events\PayrollBatchApproved::class,
                \App\Domains\HR\Payroll\Events\PayrollBatchPaid::class,
            ],
            \App\Domains\HR\Payroll\Listeners\PayrollCacheInvalidationListener::class
        );

        // ═══════════════════════════════════════════════════════════════
        // Ledger Registration (PR3: Recording financial transactions)
        // ═══════════════════════════════════════════════════════════════
        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\HR\Payroll\Events\PayrollBatchPaid::class,
            \App\Domains\Finance\Ledger\Listeners\RecordPayrollPayoutToLedger::class
        );

        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentReceived::class,
            \App\Domains\Finance\Ledger\Listeners\RecordStudentPaymentToLedger::class
        );

        // PR5: إلغاء قيد Ledger عند إلغاء الدفعة
        \Illuminate\Support\Facades\Event::listen(
            \App\Domains\Finance\Events\PaymentCancelled::class,
            \App\Domains\Finance\Ledger\Listeners\CancelLedgerEntryOnPaymentCancelled::class
        );
    }
}
