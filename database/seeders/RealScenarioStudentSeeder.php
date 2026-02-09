<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Student\Actions\RegisterStudentAction;
use App\Domains\Academic\Student\Data\StudentRegistrationData;
use App\Domains\Shared\Enums\Gender;
use App\Domains\Academic\Student\Enums\GuardianRelationship;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use Illuminate\Support\Facades\Log;

/**
 * RealScenarioStudentSeeder - تسجيل الطلاب (السيناريو الحقيقي)
 * 
 * يقوم بتسجيل طلاب في الصفوف المختلفة باستخدام RegisterStudentAction
 * ليحاكي عملية التسجيل الحقيقية بالكامل (إنشاء طالب، ولي أمر، تسجيل، فواتير، إلخ)
 * 
 * @example php artisan db:seed --class=RealScenarioStudentSeeder
 */
class RealScenarioStudentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🎓 بدء تسجيل الطلاب...');

        /** @var RegisterStudentAction $registerAction */
        $registerAction = app(RegisterStudentAction::class);

        // الحصول على الصفوف والشعب
        $grades = Grade::with('sections')->get();
        $activeYearId = school()->activeYearId();

        if (!$activeYearId) {
            $this->command->error('❌ لا توجد سنة نشطة!');
            return;
        }

        $faker = \Faker\Factory::create('ar_SA');

        // سنقوم بتسجيل 5 طلاب في كل صف (للتجربة)
        foreach ($grades as $grade) {
            $sections = $grade->sections->where('academic_year_id', $activeYearId);

            if ($sections->isEmpty()) {
                $this->command->warn("⚠️ لا توجد شعب للصف: {$grade->name}");
                continue;
            }

            $this->command->info("   تسجيل طلاب في: {$grade->name}");

            for ($i = 1; $i <= 10; $i++) {
                // توزيع الطلاب على الشعب
                $section = $sections->random();
                $gender = $i % 2 == 0 ? Gender::Female : Gender::Male;
                $firstName = $gender === Gender::Male ? $faker->firstNameMale : $faker->firstNameFemale;

                try {
                    $data = StudentRegistrationData::fromArray([
                        'student' => [
                            'first_name_ar' => $firstName,
                            'family_name_ar' => $faker->lastName,
                            'date_of_birth' => $faker->date('Y-m-d', '-6 years'),
                            'gender' => $gender->value,
                            'national_id' => $faker->unique()->numerify('##########'),
                            'nationality_id' => 1, // سعودي افتراضياً
                        ],
                        'guardians' => [
                            [
                                'data' => [
                                    'first_name' => $faker->firstNameMale,
                                    'last_name' => $faker->lastName,
                                    'phone' => '05' . $faker->numerify('########'),
                                    'national_id' => $faker->unique()->numerify('##########'),
                                ],
                                'relationship' => GuardianRelationship::Father->value,
                                'is_financial_sponsor' => true,
                                'is_emergency_contact' => true,
                            ]
                        ],
                        'grade_id' => $grade->id,
                        'class_section_id' => $section->id,
                        'is_transfer' => false,
                        'create_invoice' => true, // إنشاء فاتورة رسوم
                        'address' => [
                            'city' => 'الرياض',
                            'district' => 'حي النرجس',
                            'street_name' => 'شارع الملك فهد',
                        ],
                        'health_data' => [], // بيانات صحية فارغة افتراضياً
                        'documents' => [], // وثائق فارغة افتراضياً
                    ]);

                    $student = $registerAction->execute($data);
                    // $this->command->info("      ✅ {$student->full_name_ar} ({$section->name})");

                } catch (\Exception $e) {
                    $this->command->error("      ❌ فشل تسجيل طالب: " . $e->getMessage());
                    Log::error('Student Seeding Failed', ['error' => $e->getMessage()]);
                }
            }
            $this->command->info("      ✅ تم تسجيل 5 طلاب");
        }

        $this->command->newLine();
        $this->command->info('🎉 تم اكتمال تسجيل الطلاب بنجاح!');
    }
}
