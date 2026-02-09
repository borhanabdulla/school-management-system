<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Guardian;
use Illuminate\Support\Facades\DB;

/**
 * UpdateGuardianAction - تحديث بيانات ولي الأمر
 * 
 * يحدث بيانات ولي الأمر مع العنوان والمستخدم المرتبط
 */
class UpdateGuardianAction
{
    /**
     * تنفيذ تحديث ولي الأمر
     */
    public function execute(Guardian $guardian, array $data): Guardian
    {
        return DB::transaction(function () use ($guardian, $data) {
            // 1. تحديث البيانات الأساسية لولي الأمر
            $guardian->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'national_id' => $data['national_id'],
                'phone' => $data['phone'],
                'nationality_id' => $data['nationality_id'] ?? $guardian->nationality_id,
                'employer' => $data['employer'] ?? $guardian->employer,
                'work_phone' => $data['work_phone'] ?? $guardian->work_phone,
                'preferred_language' => $data['preferred_language'] ?? $guardian->preferred_language,
            ]);

            // 2. تحديث البريد الإلكتروني للمستخدم إذا وجد
            if ($guardian->user && !empty($data['email']) && $guardian->user->email !== $data['email']) {
                $guardian->user->update(['email' => $data['email']]);
            }

            // 3. تحديث العنوان إذا كان متوفراً
            if (!empty($data['address'])) {
                $guardian->addresses()->updateOrCreate(
                    ['is_primary' => true],
                    [
                        'city' => $data['address']['city'] ?? '',
                        'district' => $data['address']['district'] ?? '',
                        'street_name' => $data['address']['street_name'] ?? '',
                        'building_number' => $data['address']['building_number'] ?? null,
                    ]
                );
            }

            return $guardian->fresh();
        });
    }
}
