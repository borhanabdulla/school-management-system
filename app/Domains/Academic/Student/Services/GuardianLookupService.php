<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Services;

use App\Domains\Academic\Student\Models\Guardian;
use Illuminate\Database\Eloquent\Collection;

/**
 * GuardianLookupService - خدمة البحث عن أولياء الأمور
 * 
 * خدمة للقراءة فقط - لا تحتوي على عمليات كتابة
 */
class GuardianLookupService
{
    /**
     * البحث عن ولي أمر برقم الهوية
     */
    public function findByNationalId(string $nationalId): ?Guardian
    {
        return Guardian::where('national_id', $nationalId)->first();
    }

    /**
     * البحث عن ولي أمر برقم الهاتف
     */
    public function findByPhone(string $phone): ?Guardian
    {
        return Guardian::where('phone', $phone)->first();
    }

    /**
     * البحث عن ولي أمر بالبريد الإلكتروني
     */
    public function findByEmail(string $email): ?Guardian
    {
        return Guardian::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->first();
    }

    /**
     * جلب أولياء الأمور لطالب معين
     */
    public function getByStudent(int $studentId): Collection
    {
        return Guardian::whereHas('students', function ($query) use ($studentId) {
            $query->where('student_id', $studentId);
        })->get();
    }

    /**
     * البحث عن ولي أمر موجود بأحد المعرفات
     */
    public function findExisting(array $data): ?Guardian
    {
        // البحث برقم الهوية أولاً
        if (!empty($data['national_id'])) {
            $guardian = $this->findByNationalId($data['national_id']);
            if ($guardian) {
                return $guardian;
            }
        }

        // البحث برقم الهاتف
        if (!empty($data['phone'])) {
            $guardian = $this->findByPhone($data['phone']);
            if ($guardian) {
                return $guardian;
            }
        }

        // البحث بالبريد الإلكتروني
        if (!empty($data['email'])) {
            $guardian = $this->findByEmail($data['email']);
            if ($guardian) {
                return $guardian;
            }
        }

        return null;
    }

    /**
     * التحقق من وجود ولي أمر برقم الهوية
     */
    public function existsByNationalId(string $nationalId): bool
    {
        return Guardian::where('national_id', $nationalId)->exists();
    }

    /**
     * جلب ولي أمر مع العلاقات
     */
    public function getWithRelations(int $guardianId, array $relations = ['students', 'addresses', 'user']): ?Guardian
    {
        return Guardian::with($relations)->find($guardianId);
    }

    /**
     * جلب ولي أمر للعرض التفصيلي
     */
    public function findForShow(int $guardianId): ?Guardian
    {
        return Guardian::with([
            'students' => function ($query) {
                $query->with(['currentGrade', 'currentClassSection']);
            },
            'user'
        ])->findOrFail($guardianId);
    }

    /**
     * البحث العام عن أولياء الأمور (للقوائم المنسدلة وغيرها)
     */
    public function search(string $query, int $limit = 5): Collection
    {
        $normalized = trim($query);

        if ($normalized === '') {
            return new Collection();
        }

        return Guardian::query()
            ->select('id', 'first_name', 'last_name', 'phone', 'national_id')
            ->where(function ($q) use ($normalized) {
                $q->where('national_id', 'like', "%{$normalized}%")
                    ->orWhere('phone', 'like', "%{$normalized}%")
                    ->orWhere('first_name', 'like', "%{$normalized}%")
                    ->orWhere('last_name', 'like', "%{$normalized}%");
            })
            ->limit($limit)
            ->get();
    }
}
