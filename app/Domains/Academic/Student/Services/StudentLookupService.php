<?php

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Data\StudentDirectoryFilterData;

use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Academic\Student\Enums\EnrollmentStatus;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * StudentLookupService - خدمة جلب بيانات الطلاب
 * 
 * مسؤولة عن جميع عمليات القراءة المتعلقة بالطلاب
 * مع تفعيل التكييش (Caching) لتحسين الأداء
 */
class StudentLookupService
{
    public const DIRECTORY_STATS_CACHE_PREFIX = 'students.directory.stats';
    public const CACHE_VERSION_PREFIX = 'students.cache_version';
    public const STATS_CACHE_PREFIX = 'students.stats';
    /**
     * Cache keys
     */
    public const CACHE_KEY_STUDENT_STATS = 'students_stats';
    public const CACHE_KEY_STUDENTS_LIST = 'students_list';
    public const CACHE_TTL = 3600; // 1 hour

    private function baseQuery(): Builder
    {
        return Student::query()->select('students.*');
    }

    private function withForList(): array
    {
        return [
            'user:id,email',
            'guardians' => fn($q) => $q->select('guardians.id', 'first_name', 'last_name', 'phone')
                ->wherePivot('is_emergency_contact', true)
                ->orWherePivot('is_financial_sponsor', true),
            'currentGrade:id,name',
            'currentClassSection:id,name,grade_id,academic_year_id',
            'currentClassSection.academicYear:id,name',
        ];
    }

    private function withForShow(): array
    {
        return [
            'user',
            'guardians',
            'enrollments' => fn($q) => $q->with(['grade:id,name', 'classSection:id,name', 'academicYear:id,name'])->latest('enrollment_date')->limit(5),
            'addresses' => fn($q) => $q->where('is_primary', true),
            'currentClassSection',
            'invoices',
            'annualResults',
            'attendances',
        ];
    }

    public function paginateForDirectory(StudentDirectoryFilterData $filter): LengthAwarePaginator
    {
        $unpaidStatuses = InvoiceStatus::unpaidValues();

        $query = $this->buildDirectoryQuery($filter);

        $query->withSum([
            'invoices as total_required' => fn($q) => $q->whereIn('status', $unpaidStatuses)
        ], 'total_amount')
            ->withSum([
                'invoices as total_paid' => fn($q) => $q->where('status', InvoiceStatus::Paid->value)
            ], 'paid_amount');

        $query->with($this->withForList());
        $query->orderBy($filter->sortBy, $filter->sortDirection);

        return $query->paginate($filter->perPage);
    }

    /**
     * جلب قائمة الطلاب للترحيل
     */
    public function paginateForPromotion(int $yearId, ?int $gradeId = null, ?string $search = null, string $statusFilter = 'pending', int $perPage = 25): LengthAwarePaginator
    {
        $query = Student::with([
            'currentGrade.stage',
            'currentClassSection',
            'annualResults' => fn($q) => $q->where('academic_year_id', $yearId)
        ]);

        $query->whereHas('annualResults', fn($q) => $q->where('academic_year_id', $yearId));

        if ($gradeId) {
            $query->where('current_grade_id', $gradeId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name_ar', 'like', "%{$search}%")
                    ->orWhere('family_name_ar', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhere('national_id', 'like', "%{$search}%");
            });
        }

        if ($statusFilter === 'pending') {
            $query->whereDoesntHave('promotions', fn($q) => $q->where('academic_year_id', $yearId)->where('is_reverted', false));
        } elseif ($statusFilter === 'promoted') {
            $query->whereHas('promotions', fn($q) => $q->where('academic_year_id', $yearId)->where('is_reverted', false));
        }

        return $query->paginate($perPage);
    }

    public function getDirectoryStats(StudentDirectoryFilterData $filter): array
    {
        $key = $this->directoryStatsCacheKey($filter);

        return Cache::remember($key, now()->addMinutes(5), function () use ($filter) {
            $unpaidStatuses = InvoiceStatus::unpaidValues();
            $query = $this->baseDirectoryQuery($filter);

            return [
                'total_students' => (clone $query)->count(),
                'new_enrollments' => (clone $query)->whereMonth('created_at', now()->month)->count(),
                'attendance_today' => $this->getTodayAttendancePercentage(),
                'fee_alerts' => (clone $query)->whereHas('invoices', fn($q) => $q->whereIn('status', $unpaidStatuses))->count(),
            ];
        });
    }

    public function findManyByIds(array $ids): Collection
    {
        $query = $this->baseQuery();
        $query->whereIn('id', $ids);
        $query->with($this->withForList());

        return $query->get();
    }

    public function findForShow(int $studentId): ?Student
    {
        return Student::with($this->withForShow())->find($studentId);
    }

    /**
     * جلب الطلاب لجلسة اختبار (للطباعة)
     */
    public function getStudentsForExamSession(int $sessionId, ?int $studentId = null, ?int $classSectionId = null): Collection
    {
        $query = Student::query()
            ->whereHas('seatings', function ($q) use ($sessionId) {
                $q->where('exam_session_id', $sessionId);
            })
            ->with([
                'currentClassSection.grade',
                'seatings' => function ($q) use ($sessionId) {
                    $q->where('exam_session_id', $sessionId);
                }
            ]);

        if ($studentId) {
            $query->where('id', $studentId);
        } elseif ($classSectionId) {
            $query->where('current_class_section_id', $classSectionId);
        }

        return $query->get();
    }

    /**
     * البحث عن طلاب لربطهم بولي أمر (يستثني المرتبطين بالفعل)
     */
    public function searchStudentsForGuardianLink(string $query, int $excludeGuardianId, int $limit = 5): Collection
    {
        return Student::query()
            ->where(function ($q) use ($query) {
                $term = '%' . trim($query) . '%';
                $q->where('first_name_ar', 'like', $term)
                    ->orWhere('family_name_ar', 'like', $term)
                    ->orWhere('first_name_en', 'like', $term)
                    ->orWhere('family_name_en', 'like', $term)
                    ->orWhere('admission_number', 'like', $term);
            })
            ->whereDoesntHave('guardians', function ($q) use ($excludeGuardianId) {
                $q->where('guardian_id', $excludeGuardianId);
            })
            ->limit($limit)
            ->get(['id', 'first_name_ar', 'family_name_ar', 'first_name_en', 'family_name_en', 'admission_number']);
    }

    public function findForDelete(int $studentId): ?Student
    {
        return Student::with([
            'invoices',
            'attendances',
            'annualResults',
            'enrollments',
            'user',
        ])->find($studentId);
    }

    private function directoryStatsCacheKey(StudentDirectoryFilterData $filter): string
    {
        $filtersHash = md5(json_encode($filter->filters()));
        $yearSegment = $filter->academicYearId ? "year_{$filter->academicYearId}" : 'year_global';
        $version = self::getCacheVersion($filter->academicYearId);

        return self::DIRECTORY_STATS_CACHE_PREFIX . ":v{$version}:{$yearSegment}:{$filtersHash}";
    }

    private function buildDirectoryQuery(StudentDirectoryFilterData $filter): Builder
    {
        return $this->baseDirectoryQuery($filter);
    }

    /**
     * الاستعلام الأساسي الموحد للدليل والإحصائيات
     * يضمن تطابق الأرقام بين القائمة والإحصائيات
     */
    private function baseDirectoryQuery(StudentDirectoryFilterData $filter): Builder
    {
        $query = $this->baseQuery();
        return $this->applyDirectoryFilters($query, $filter);
    }

    private function applyDirectoryFilters(Builder $query, StudentDirectoryFilterData $filter): Builder
    {
        if ($filter->academicYearId) {
            $query->whereHas('enrollments', function ($q) use ($filter) {
                $q->where('academic_year_id', $filter->academicYearId);

                if ($filter->gradeId) {
                    $q->where('grade_id', $filter->gradeId);
                }

                if ($filter->sectionId) {
                    $q->where('class_section_id', $filter->sectionId);
                }
            });
        } else {
            $query->when($filter->gradeId, fn($q) => $q->where('current_grade_id', $filter->gradeId))
                ->when($filter->sectionId, fn($q) => $q->where('current_class_section_id', $filter->sectionId));
        }

        if ($filter->status) {
            $query->where('status', $filter->status);
        }

        if ($filter->financialStatus) {
            $unpaidStatuses = InvoiceStatus::unpaidValues();

            if ($filter->financialStatus === 'paid') {
                $query->whereDoesntHave('invoices', fn($sq) => $sq->whereIn('status', $unpaidStatuses));
            } else {
                $query->whereHas('invoices', fn($sq) => $sq->whereIn('status', $unpaidStatuses));
            }
        }

        if ($filter->search) {
            $search = $filter->search;
            $query->where(function ($q) use ($search) {
                $q->where('national_id', 'like', "%{$search}%")
                    ->orWhere('first_name_ar', 'like', "%{$search}%")
                    ->orWhere('family_name_ar', 'like', "%{$search}%")
                    ->orWhere('admission_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('guardians', fn($sq) => $sq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        return $query;
    }
    /**
     * جلب قائمة الطلاب مع الفلترة والتكييش
     */
    public function getStudentsList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $normalizedFilters = array_merge($filters, ['per_page' => $perPage]);
        $filterData = StudentDirectoryFilterData::fromArray($normalizedFilters);

        return $this->paginateForDirectory($filterData);
    }

    /**
     * جلب إحصائيات الطلاب مع التكييش
     */
    public function getStats(?int $academicYearId = null): array
    {
        $yearId = $academicYearId ?? school()->activeYear()?->id;
        $cacheKey = $this->statsCacheKey($yearId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($yearId) {
            $baseQuery = $this->studentStatsQuery($yearId);
            $unpaidStatuses = InvoiceStatus::unpaidValues();

            $totalStudents = (clone $baseQuery)->active()->count();

            // حساب عدد الطلاب الذين لديهم مستحقات مالية
            $feeAlerts = (clone $baseQuery)
                ->active()
                ->whereHas('invoices', fn($q) => $q->whereIn('status', $unpaidStatuses))
                ->count();

            // التسجيلات الجديدة هذا الشهر
            $newEnrollments = (clone $baseQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            return [
                'total_students' => $totalStudents,
                'attendance_today' => $this->getTodayAttendancePercentage(),
                'new_enrollments' => $newEnrollments,
                'fee_alerts' => $feeAlerts,
            ];
        });
    }

    /**
     * جلب نسبة الحضور اليوم
     */
    private function getTodayAttendancePercentage(): string
    {
        // TODO: يمكن ربطها بخدمة الحضور
        return '85%';
    }

    /**
     * جلب طالب مع علاقاته الكاملة
     */
    public function getStudentWithRelations(int $studentId): ?Student
    {
        return $this->findForShow($studentId);
    }



    /**
     * جلب الحالة المالية للطالب
     */
    public function getFinancialStatus(Student $student): array
    {
        $unpaidAmount = $this->resolveUnpaidAmount($student);

        return $this->formatFinancialStatus($unpaidAmount);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapFinancialStatuses(Collection $students): array
    {
        $statuses = [];

        foreach ($students as $student) {
            $statuses[$student->id] = $this->getFinancialStatus($student);
        }

        return $statuses;
    }

    private function resolveUnpaidAmount(Student $student): float
    {
        if (
            array_key_exists('total_required', $student->getAttributes())
            || array_key_exists('total_paid', $student->getAttributes())
        ) {
            return (float) (($student->getAttribute('total_required') ?? 0)
                - ($student->getAttribute('total_paid') ?? 0));
        }

        return $student->invoices()
            ->whereIn('status', InvoiceStatus::unpaidValues())
            ->get()
            ->sum(function ($invoice) {
                return $invoice->total_amount - $invoice->paid_amount;
            });
    }

    private function formatFinancialStatus(float $unpaidAmount): array
    {
        $status = $unpaidAmount > 0 ? InvoiceStatus::Unpaid : InvoiceStatus::Paid;

        return [
            'status' => $status->value,
            'label' => $status === InvoiceStatus::Paid ? 'خالص' : 'عليه مستحقات',
            'amount' => $unpaidAmount,
            'color' => $status === InvoiceStatus::Paid ? 'green' : 'red',
        ];
    }

    /**
     * مسح الكاش
     */
    public static function clearCache(?int $yearId = null): void
    {
        $yearId ??= school()->activeYear()?->id;

        self::bumpCacheVersion(null);
        if ($yearId) {
            self::bumpCacheVersion($yearId);
        }

        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            Cache::tags(['students'])->flush();
        }
    }
    /**
     * Get all health condition types, cached.
     */
    public function getHealthConditionTypes()
    {
        return Cache::remember('health_condition_types_all', 60 * 60 * 24, function () {
            return \App\Domains\Academic\Student\Models\HealthConditionType::all();
        });
    }

    private static function cacheVersionKey(?int $yearId): string
    {
        $segment = $yearId ? "year.{$yearId}" : 'global';

        return self::CACHE_VERSION_PREFIX . '.' . $segment;
    }

    private static function getCacheVersion(?int $yearId): int
    {
        return (int) Cache::get(self::cacheVersionKey($yearId), 1);
    }

    private static function bumpCacheVersion(?int $yearId): void
    {
        $key = self::cacheVersionKey($yearId);
        $current = (int) Cache::get($key, 1);

        Cache::forever($key, $current + 1);
    }

    private static function statsCacheKey(?int $yearId): string
    {
        $keyYear = $yearId ? (string) $yearId : 'global';
        $version = self::getCacheVersion($yearId);

        return self::CACHE_KEY_STUDENT_STATS . "_{$keyYear}:v{$version}";
    }

    private function studentStatsQuery(?int $yearId): Builder
    {
        $query = Student::query();

        if ($yearId) {
            $query->whereHas('enrollments', function (Builder $q) use ($yearId) {
                $q->where('academic_year_id', $yearId);
            });
        }

        return $query;
    }

    /**
     * جلب مجموعة طلاب مع أولياء أمورهم (للإشعارات)
     * 
     * @param array $studentIds
     * @return Collection
     */
    public function findManyWithGuardians(array $studentIds): Collection
    {
        return Student::whereIn('id', $studentIds)
            ->with(['guardians.user'])
            ->get();
    }

    /**
     * جلب طلاب شعبة معينة ضمن سنة دراسية محددة (للتقارير)
     * 
     * @param int $classSectionId
     * @param int $academicYearId
     * @return Collection
     */
    public function getByClassSection(int $classSectionId, int $academicYearId): Collection
    {
        return Student::query()
            ->whereHas('enrollments', function (Builder $q) use ($classSectionId, $academicYearId) {
                $q->where('class_section_id', $classSectionId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('status', EnrollmentStatus::Active->value);
            })
            ->active()
            ->orderBy('first_name_ar')
            ->get(['id', 'first_name_ar', 'family_name_ar', 'admission_number']);
    }
    /**
     * جلب الطلاب المسجلين بنشاط في سنة دراسية معينة
     */
    public function studentsActiveEnrolledInYear(int $yearId): Builder
    {
        return Student::whereHas('enrollments', function ($q) use ($yearId) {
            $q->where('academic_year_id', $yearId)
                ->where('status', EnrollmentStatus::Active);
        });
    }

    /**
     * جلب الطلاب مع معلومات الـ enrollment باستخدام JOIN
     * هذا يسمح بالحصول على grade_id من enrollment بدلاً من current_grade_id
     * 
     * ✅ يستخدم JOIN لتجنب N+1 ولإتاحة select من جدول enrollment
     * 
     * @param int $yearId السنة الدراسية
     * @return Builder
     */
    public function studentsWithEnrollmentJoin(int $yearId): Builder
    {
        return Student::query()
            ->join('student_enrollments', function ($join) use ($yearId) {
                $join->on('students.id', '=', 'student_enrollments.student_id')
                    ->where('student_enrollments.academic_year_id', '=', $yearId)
                    ->where('student_enrollments.status', '=', EnrollmentStatus::Active->value);
            })
            ->select([
                'students.id',
                'students.full_name_ar',
                'students.status',
                'students.current_grade_id',
                'student_enrollments.grade_id as enrollment_grade_id',
                'student_enrollments.class_section_id as enrollment_section_id',
            ]);
    }

    /**
     * جلب الطلاب حسب الصف الحالي
     */
    public function byCurrentGrade(int $gradeId): Builder
    {
        return Student::where('current_grade_id', $gradeId);
    }

    /**
     * جلب الطلاب حسب الشعب الحالية
     */
    public function byCurrentClassSections(array $sectionIds): Builder
    {
        return Student::whereIn('current_class_section_id', $sectionIds);
    }

    /**
     * جلب مجموعة طلاب بواسطة المعرفات
     */
    public function findMany(array $ids): Collection
    {
        return Student::whereIn('id', $ids)->get();
    }

    /**
     * جلب الطلاب في صف معين وليس لديهم شعبة (للتوزيع التلقائي)
     */
    public function studentsInGradeWithoutSection(int $gradeId): Collection
    {
        return Student::where('current_grade_id', $gradeId)
            ->whereNull('current_class_section_id')
            ->get();
    }

    /**
     * حساب عدد الطلاب في مجموعة شعب
     */
    public function countStudentsInSections(Collection|array $sectionIds): Collection
    {
        return Student::whereIn('current_class_section_id', $sectionIds)
            ->selectRaw('current_class_section_id, COUNT(*) as total')
            ->groupBy('current_class_section_id')
            ->pluck('total', 'current_class_section_id');
    }
}
