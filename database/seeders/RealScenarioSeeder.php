<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * RealScenarioSeeder - إنشاء سيناريو حقيقي باستخدام Actions
 * 
 * هذا الـ Seeder يُنشئ بيانات بنفس الطريقة التي يعمل بها التطبيق الفعلي:
 * - يستخدم CreateAcademicYearAction بدلاً من إدخال مباشر للـ DB
 * - يُطبّق جميع الـ Business Rules والـ Validations
 * - يُطلق الـ Events ويُحدّث الـ Cache
 * 
 * @example php artisan db:seed --class=RealScenarioSeeder
 */
class RealScenarioSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 بدء إنشاء السيناريو الحقيقي...');

        // ═══════════════════════════════════════════════════════════════
        // 1️⃣ التحقق من عدم وجود السنة مسبقاً
        // ═══════════════════════════════════════════════════════════════
        $existingYear = AcademicYear::where('name', '2026/2027')->first();

        if ($existingYear) {
            $this->command->warn('⚠️ السنة 2026/2027 موجودة مسبقاً (ID: ' . $existingYear->id . ')');
            $this->command->info('   الحالة: ' . $existingYear->status->value);
            $this->command->info('   عدد الفصول: ' . $existingYear->terms()->count());
            return;
        }

        // ═══════════════════════════════════════════════════════════════
        // 2️⃣ تحضير البيانات باستخدام DTO
        // ═══════════════════════════════════════════════════════════════
        $data = AcademicYearData::fromArray([
            'name' => '2026/2027',
            'start_date' => Carbon::create(2026, 9, 1),   // 1 سبتمبر 2026
            'end_date' => Carbon::create(2027, 6, 30),    // 30 يونيو 2027
            'status' => AcademicYearStatus::Active,       // ✅ نشطة
            'terms' => [
                [
                    'name' => 'الفصل الدراسي الأول',
                    'start_date' => Carbon::create(2026, 9, 1),
                    'end_date' => Carbon::create(2027, 1, 15),
                    'order_index' => 1,
                    // سيكون Active تلقائياً لأن السنة Active وهو الأول
                ],
                [
                    'name' => 'الفصل الدراسي الثاني',
                    'start_date' => Carbon::create(2027, 1, 20),
                    'end_date' => Carbon::create(2027, 6, 30),
                    'order_index' => 2,
                    // سيكون Pending تلقائياً
                ],
            ],
        ]);

        // ═══════════════════════════════════════════════════════════════
        // 3️⃣ تنفيذ الـ Action (نفس كود التطبيق الحقيقي!)
        // ═══════════════════════════════════════════════════════════════
        try {
            /** @var CreateAcademicYearAction $action */
            $action = app(CreateAcademicYearAction::class);

            $this->command->info('📅 جاري إنشاء السنة الدراسية...');

            $year = $action->execute($data);

            // ═══════════════════════════════════════════════════════════════
            // 4️⃣ عرض النتائج
            // ═══════════════════════════════════════════════════════════════
            $this->command->newLine();
            $this->command->info('✅ تم إنشاء السنة الدراسية بنجاح!');
            $this->command->newLine();

            $this->command->table(
                ['الحقل', 'القيمة'],
                [
                    ['ID', $year->id],
                    ['الاسم', $year->name],
                    ['تاريخ البداية', $year->start_date->format('Y-m-d')],
                    ['تاريخ النهاية', $year->end_date->format('Y-m-d')],
                    ['الحالة', $year->status->value . ' ✅'],
                ]
            );

            $this->command->newLine();
            $this->command->info('📚 الفصول الدراسية:');

            $termsData = $year->terms->map(function ($term) {
                $statusIcon = $term->status->value === 'active' ? '✅' : '⏳';
                return [
                    $term->id,
                    $term->name,
                    $term->start_date->format('Y-m-d'),
                    $term->end_date->format('Y-m-d'),
                    $term->status->value . ' ' . $statusIcon,
                ];
            })->toArray();

            $this->command->table(
                ['ID', 'الاسم', 'البداية', 'النهاية', 'الحالة'],
                $termsData
            );

            // ═══════════════════════════════════════════════════════════════
            // 5️⃣ التحقق من تفاعل النظام
            // ═══════════════════════════════════════════════════════════════
            $this->command->newLine();
            $this->command->info('🔍 التحقق من السياق الأكاديمي (school() helper):');

            // مسح الكاش لإعادة تحميل البيانات الجديدة
            school()->invalidate();

            $activeYear = school()->activeYear();
            $activeTerm = school()->activeTerm();

            $this->command->table(
                ['الدالة', 'النتيجة'],
                [
                    ['school()->activeYear()', $activeYear ? $activeYear->name : 'لا يوجد'],
                    ['school()->activeYearId()', school()->activeYearId() ?? 'لا يوجد'],
                    ['school()->activeTerm()', $activeTerm ? $activeTerm->name : 'لا يوجد'],
                    ['school()->activeTermId()', school()->activeTermId() ?? 'لا يوجد'],
                ]
            );

            $this->command->newLine();
            $this->command->info('🎉 السيناريو جاهز! يمكنك الآن اختبار النظام.');

        } catch (\Exception $e) {
            $this->command->error('❌ فشل إنشاء السنة: ' . $e->getMessage());
            Log::error('RealScenarioSeeder failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
