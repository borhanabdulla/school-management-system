<?php

declare(strict_types=1);

namespace App\Domains\Academic\Student\Actions;

use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Shared\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * CreateGuardianAction - إنشاء ولي أمر جديد
 * 
 * ينشئ ولي أمر مع حساب مستخدم اختياري وعنوان
 */
class CreateGuardianAction
{
    /**
     * تنفيذ إنشاء ولي الأمر
     */
    public function execute(array $data): Guardian
    {
        return DB::transaction(function () use ($data) {
            // 1. إنشاء حساب المستخدم (اختياري، مطلوب إذا كان البريد موجود)
            $user = null;
            if (!empty($data['email'])) {
                $username = $this->generateUniqueUsername($data['email']);

                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'username' => $username,
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Hash::make(Str::random(10)), // كلمة مرور مؤقتة
                ]);
            }

            // 2. إنشاء ولي الأمر
            $guardian = Guardian::create([
                'user_id' => $user?->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'national_id' => $data['national_id'],
                'phone' => $data['phone'],
                'nationality_id' => $data['nationality_id'] ?? null,
                'employer' => $data['employer'] ?? null,
                'work_phone' => $data['work_phone'] ?? null,
                'preferred_language' => $data['preferred_language'] ?? 'ar',
            ]);

            // 3. إنشاء العنوان إذا كان متوفراً
            if (!empty($data['address'])) {
                $guardian->addresses()->create([
                    'city' => $data['address']['city'] ?? '',
                    'district' => $data['address']['district'] ?? '',
                    'street_name' => $data['address']['street_name'] ?? '',
                    'building_number' => $data['address']['building_number'] ?? null,
                    'is_primary' => true,
                ]);
            }

            return $guardian;
        });
    }

    /**
     * توليد اسم مستخدم فريد
     */
    private function generateUniqueUsername(string $email): string
    {
        $username = explode('@', $email)[0];

        if (User::where('username', $username)->exists()) {
            $username .= rand(100, 999);
        }

        return $username;
    }
}
