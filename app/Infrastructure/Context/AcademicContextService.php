<?php

declare(strict_types=1);

namespace App\Infrastructure\Context;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Academic\Grading\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * AcademicContextService - قلب النظام الأكاديمي
 * 
 * هذا الكلاس هو "المصدر الوحيد للحقيقة" (Single Source of Truth) لـ:
 * - السنة الدراسية النشطة
 * - الفصل الدراسي الحالي
 * - إعدادات النظام
 * 
 * يستخدم نمط Singleton لضمان جلب البيانات مرة واحدة فقط في كل طلب HTTP.
 * يستخدم Redis/Cache لتخزين البيانات وتقليل الاستعلامات.
 * 
 * @example
 * // الوصول عبر الـ Helper
 * school()->activeYear();
 * school()->activeTerm();
 * school()->setting('grading.pass_mark', 50);
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
class AcademicContextService
{
    /**
     * Instance للـ Singleton Pattern
     */
    private static ?self $instance = null;

    /**
     * Cache Keys - مفاتيح الكاش المستخدمة
     */
    public const CACHE_KEY_YEAR = 'academic.context.active_year';
    public const CACHE_KEY_TERM = 'academic.context.active_term';
    public const CACHE_KEY_SETTINGS = 'academic.context.settings';
    public const CACHE_KEY_TERM_ACTIVE_LIST = 'academic.context.term_active_list';
    public const CACHE_KEY_TERM_UPCOMING = 'academic.context.term_upcoming';

    /**
     * Cache TTL - مدة صلاحية الكاش (24 ساعة)
     */
    private const CACHE_TTL = 60 * 60 * 24;

    /**
     * In-Memory Cache للطلب الحالي
     * يمنع تكرار جلب البيانات من Redis في نفس الطلب
     */
    private ?AcademicYear $cachedYear = null;
    private ?Term $cachedTerm = null;
    private ?Collection $cachedSettings = null;

    /**
     * Private Constructor - يمنع الإنشاء المباشر
     */
    private function __construct()
    {
        // Singleton Pattern: لا يمكن إنشاء instance من الخارج
    }

    /**
     * Prevent cloning
     */
    private function __clone(): void
    {
        // Singleton: منع النسخ
    }

    /**
     * Prevent unserialization
     */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize a singleton.');
    }

    /**
     * الحصول على Instance الوحيد
     * 
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * السنة الدراسية النشطة
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * جلب السنة الدراسية النشطة
     * 
     * تسلسل الجلب:
     * 1. In-Memory Cache (أسرع - نفس الطلب)
     * 2. Redis/File Cache (سريع - بين الطلبات)
     * 3. Database (بطيء - أول مرة فقط)
     * 
     * @return AcademicYear|null
     */
    public function activeYear(): ?AcademicYear
    {
        // 1. Check In-Memory Cache
        if ($this->cachedYear !== null) {
            if ($this->cachedYear->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active) {
                $this->cachedYear = null;
                Cache::forget(self::CACHE_KEY_YEAR);
            } else {
                return $this->cachedYear;
            }
        }

        // 2. Check Redis/File Cache, or fetch from DB
        $this->cachedYear = Cache::remember(
            self::CACHE_KEY_YEAR,
            self::CACHE_TTL,
            fn() => AcademicYear::query()
                ->where('status', 'active')
                ->with(['terms' => fn($q) => $q->orderBy('order_index')])
                ->first()
        );

        if ($this->cachedYear && $this->cachedYear->status !== \App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus::Active) {
            Cache::forget(self::CACHE_KEY_YEAR);
            $this->cachedYear = AcademicYear::query()
                ->where('status', 'active')
                ->with(['terms' => fn($q) => $q->orderBy('order_index')])
                ->first();
        }

        return $this->cachedYear;
    }

    /**
     * جلب معرف السنة النشطة فقط (للاستعلامات)
     * 
     * @return int|null
     */
    public function activeYearId(): ?int
    {
        return $this->activeYear()?->id;
    }

    /**
     * التحقق من وجود سنة نشطة
     * 
     * @return bool
     */
    public function hasActiveYear(): bool
    {
        return $this->activeYear() !== null;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * الفصل الدراسي النشط
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * جلب الفصل الدراسي النشط
     * 
     * @return Term|null
     */
    public function activeTerm(): ?Term
    {
        // 1. Check In-Memory Cache
        if ($this->cachedTerm !== null) {
            return $this->cachedTerm;
        }

        // 2. لا يوجد سنة نشطة = لا يوجد فصل
        if (!$this->hasActiveYear()) {
            return null;
        }

        // 3. Check Redis/File Cache, or fetch from DB
        $this->cachedTerm = Cache::remember(
            self::CACHE_KEY_TERM,
            self::CACHE_TTL,
            fn() => Term::query()
                ->where('academic_year_id', $this->activeYearId())
                ->where('status', 'active')
                ->first()
        );

        return $this->cachedTerm;
    }

    /**
     * جلب معرف الفصل النشط فقط (للاستعلامات)
     * 
     * @return int|null
     */
    public function activeTermId(): ?int
    {
        return $this->activeTerm()?->id;
    }

    /**
     * التحقق من وجود فصل نشط
     * 
     * @return bool
     */
    public function hasActiveTerm(): bool
    {
        return $this->activeTerm() !== null;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * إعدادات النظام
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * جلب إعداد معين
     * 
     * @param string $key مفتاح الإعداد (مثل: 'grading.pass_mark')
     * @param mixed $default القيمة الافتراضية
     * @return mixed
     * 
     * @example
     * school()->setting('grading.pass_mark', 50);
     * school()->setting('school.name', 'المدرسة النموذجية');
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        $settings = $this->allSettings();

        return $settings->get($key, $default);
    }

    /**
     * جلب جميع الإعدادات
     * 
     * @return Collection
     */
    public function allSettings(): Collection
    {
        // 1. Check In-Memory Cache
        if ($this->cachedSettings !== null) {
            return $this->cachedSettings;
        }

        // 2. Check Redis/File Cache, or fetch from DB
        $this->cachedSettings = Cache::remember(
            self::CACHE_KEY_SETTINGS,
            self::CACHE_TTL,
            function () {
                return SystemSetting::all()
                    ->mapWithKeys(function ($setting) {
                        $value = match ($setting->type) {
                            'integer' => (int) $setting->value,
                            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                            'json' => json_decode($setting->value, true),
                            default => $setting->value,
                        };
                        return [$setting->key => $value];
                    });
            }
        );

        return $this->cachedSettings;
    }

    /**
     * جلب إعدادات مجموعة معينة
     * 
     * @param string $group اسم المجموعة (مثل: 'grading', 'school')
     * @return Collection
     */
    public function settingsGroup(string $group): Collection
    {
        return $this->allSettings()
            ->filter(fn($value, $key) => str_starts_with($key, $group . '.'));
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * إبطال الكاش (Cache Invalidation)
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * إبطال جميع الكاش
     * 
     * يُستدعى من الـ Observer عند:
     * - تغيير السنة النشطة
     * - تغيير الفصل النشط
     * - تعديل الإعدادات
     * 
     * @return void
     */
    public function invalidate(): void
    {
        // 1. Clear Redis/File Cache
        Cache::forget(self::CACHE_KEY_YEAR);
        Cache::forget(self::CACHE_KEY_TERM);
        Cache::forget(self::CACHE_KEY_SETTINGS);

        // 2. Clear In-Memory Cache
        $this->cachedYear = null;
        $this->cachedTerm = null;
        $this->cachedSettings = null;
    }

    /**
     * إبطال كاش السنة الدراسية فقط
     * 
     * @return void
     */
    public function invalidateYear(): void
    {
        Cache::forget(self::CACHE_KEY_YEAR);
        Cache::forget(self::CACHE_KEY_TERM); // الفصل مرتبط بالسنة
        $this->cachedYear = null;
        $this->cachedTerm = null;
    }

    /**
     * إبطال كاش الفصل الدراسي فقط
     * 
     * @return void
     */
    public function invalidateTerm(): void
    {
        Cache::forget(self::CACHE_KEY_TERM);
        // Cascading Invalidation: إبطال كاش السنة لأنها تحتوي على قائمة الفصول
        Cache::forget(self::CACHE_KEY_YEAR);

        $this->cachedTerm = null;
        $this->cachedYear = null; // يجب تصفير كاش السنة في الذاكرة أيضاً
    }

    /**
     * إبطال كاش الإعدادات فقط
     * 
     * @return void
     */
    public function invalidateSettings(): void
    {
        Cache::forget(self::CACHE_KEY_SETTINGS);
        $this->cachedSettings = null;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * أدوات مساعدة
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * أيام العمل الرسمية
     * 
     * @return array
     */
    public function workingDays(): array
    {
        return $this->setting('timetable.working_days', [0, 1, 2, 3, 4]);
    }

    /**
     * مدة الحصة الافتراضية
     */
    public function defaultSlotDuration(): int
    {
        return (int) $this->setting('timetable.slot_duration', 45);
    }

    /**
     * الحصول على ملخص الحالة (للـ Debugging)
     * 
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'active_year' => $this->activeYear()?->name,
            'active_year_id' => $this->activeYearId(),
            'active_term' => $this->activeTerm()?->name,
            'active_term_id' => $this->activeTermId(),
            'settings_count' => $this->allSettings()->count(),
            'cache_driver' => config('cache.default'),
        ];
    }

    /**
     * إعادة تعيين الـ Singleton (للاختبارات فقط)
     * 
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * سياق النظام العالمي (Global System Context)
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * الفرع النشط (للمستقبل)
     * 
     * @return int|null
     */
    public function activeBranch(): ?int
    {
        // TODO: Implement Multi-Branch logic
        return 1; // Default Main Branch
    }

    /**
     * هل باب التسجيل مفتوح؟
     * 
     * @return bool
     */
    public function isRegistrationOpen(): bool
    {
        return (bool) $this->setting('registration.open', false);
    }

    /**
     * العملة الافتراضية للنظام
     * 
     * @return string
     */
    public function currency(): string
    {
        return $this->setting('finance.currency', 'SAR');
    }

    /**
     * الدور النشط للمستخدم الحالي
     * 
     * @return string|null
     */
    public function activeRole(): ?string
    {
        if (!auth()->check()) {
            return null;
        }

        // TODO: Implement Role Switching Logic if needed
        return auth()->user()->roles->first()?->name;
    }
}
