<?php

namespace Tests\Feature;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Term\Models\Term;
use App\Domains\Shared\Models\User;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\AcademicYear\Services\AcademicYearService;
use App\Domains\Academic\AcademicYear\Data\AcademicYearData;
use App\Domains\Academic\AcademicYear\Exceptions\DateOverlapException;
use App\Domains\Academic\AcademicYear\Exceptions\InvalidDateRangeException;
use App\Domains\Academic\AcademicYear\Exceptions\InsufficientTermsException;
use App\Domains\Academic\AcademicYear\Exceptions\YearNotDeletableException;
use App\Domains\Academic\AcademicYear\Actions\CreateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\UpdateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ActivateAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\DeleteAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\CloseAcademicYearAction;
use App\Domains\Academic\AcademicYear\Actions\ArchiveAcademicYearAction;
use App\Domains\Academic\Term\Enums\TermStatus;
use App\Infrastructure\Exceptions\InvalidOperationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Carbon\Carbon;

class AcademicYearLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    /** @test */
    public function it_follows_the_full_academic_year_lifecycle_story()
    {
        // 🎭 المرحلة 1: اختبار الحراس (Validation & Constraints)

        // الخطوة 1: محاولة خرق قوانين الفيزياء (التواريخ المعكوسة)
        try {
            app(CreateAcademicYearAction::class)->execute(new AcademicYearData(
                name: '2025-2026',
                start_date: Carbon::parse('2025-09-01'),
                end_date: Carbon::parse('2025-08-01'), // Error
                status: AcademicYearStatus::Pending,
                terms: []
            ));
            $this->fail('Step 1 Failed: Should have thrown InvalidDateRangeException');
        } catch (InvalidDateRangeException $e) {
            $this->assertTrue(true, 'Step 1 Passed: Invalid dates rejected');
        }

        // إعداد: وجود سنة سابقة (2023-2024)
        $oldYear = AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        // الخطوة 2: محاولة احتلال سنة موجودة (التداخل الزمني)
        try {
            app(CreateAcademicYearAction::class)->execute(new AcademicYearData(
                name: '2024-2025',
                start_date: Carbon::parse('2024-03-01'), // Overlap
                end_date: Carbon::parse('2025-03-01'),
                status: AcademicYearStatus::Pending,
                terms: []
            ));
            $this->fail('Step 2 Failed: Should have thrown DateOverlapException');
        } catch (DateOverlapException $e) {
            $this->assertTrue(true, 'Step 2 Passed: Overlapping dates rejected');
        }

        // 🎭 المرحلة 2: الميلاد والتجهيز (Creation & Setup)

        // الخطوة 3: الولادة الطبيعية (سنة سليمة)
        Cache::flush();

        $newYear = app(CreateAcademicYearAction::class)->execute(new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-09-01'),
            end_date: Carbon::parse('2026-06-30'),
            status: AcademicYearStatus::Pending,
            terms: [] // No terms yet
        ));

        $this->assertDatabaseHas('academic_years', ['id' => $newYear->id, 'status' => 'pending']);

        // الخطوة 4: محاولة التفعيل المبكر (The Structure Check)
        try {
            app(ActivateAcademicYearAction::class)->execute($newYear);
            $this->fail('Step 4 Failed: Should have thrown InsufficientTermsException');
        } catch (InsufficientTermsException $e) {
            $this->assertTrue(true, 'Step 4 Passed: Activation without terms rejected');
        }

        // الخطوة 5: التجهيز (إضافة الفصول)
        $updateData = new AcademicYearData(
            name: '2025-2026',
            start_date: Carbon::parse('2025-09-01'),
            end_date: Carbon::parse('2026-06-30'),
            status: AcademicYearStatus::Pending,
            terms: [
                ['name' => 'Term 1', 'start_date' => '2025-09-01', 'end_date' => '2026-01-01', 'order_index' => 1],
                ['name' => 'Term 2', 'start_date' => '2026-01-15', 'end_date' => '2026-06-30', 'order_index' => 2],
            ]
        );
        app(UpdateAcademicYearAction::class)->execute($newYear, $updateData);
        $this->assertCount(2, $newYear->fresh()->terms);

        // 🎭 المرحلة 3: التفعيل والتبديل (Activation & Event-Driven Caching)

        // الخطوة 6: لحظة التفعيل (Activation)
        app(ActivateAcademicYearAction::class)->execute($newYear);

        $this->assertEquals(AcademicYearStatus::Active, $newYear->fresh()->status, 'Step 6: New year should be active');
        $this->assertEquals(AcademicYearStatus::Closed, $oldYear->fresh()->status, 'Step 6: Old year should be closed');

        // 🎭 المرحلة 4: الحماية والنهاية (Security & Lifecycle)

        // الخطوة 7: اختبار الدرع (حماية السنة النشطة)
        try {
            app(DeleteAcademicYearAction::class)->execute($newYear->id);
            $this->fail('Step 7 Failed: Should have thrown YearNotDeletableException');
        } catch (YearNotDeletableException $e) {
            $this->assertTrue(true, 'Step 7 Passed: Active year deletion prevented');
        }

        // الخطوة 8: الوداع (الإغلاق اليدوي)
        $newYear->refresh();
        Term::where('academic_year_id', $newYear->id)
            ->update(['status' => TermStatus::Completed->value]);
        app(CloseAcademicYearAction::class)->execute($newYear);
        $this->assertEquals(AcademicYearStatus::Closed, $newYear->fresh()->status, 'Step 8: Year closed successfully');

        // 🎭 المرحلة 5: الأرشفة والقيود الصارمة (Archiving & Strict Rules)

        // الخطوة 9: محاولة تعديل التواريخ لسنة مغلقة (يجب أن تفشل)
        try {
            app(UpdateAcademicYearAction::class)->execute($newYear, new AcademicYearData(
                name: '2030-2031',
                start_date: Carbon::parse('2030-01-01'), // Changed date
                end_date: Carbon::parse('2031-01-01'),
                status: AcademicYearStatus::Closed,
                terms: []
            ));
            $this->fail('Step 9 Failed: Should have thrown YearNotEditableException for dates');
        } catch (\App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException $e) {
            $this->assertTrue(true, 'Step 9 Passed: Date modification prevented for closed year');
        }

        // الخطوة 10: تعديل الاسم فقط (يجب أن ينجح)
        app(UpdateAcademicYearAction::class)->execute($newYear, new AcademicYearData(
            name: '2031-2032',
            start_date: $newYear->start_date, // Same date
            end_date: $newYear->end_date,     // Same date
            status: AcademicYearStatus::Closed,
            terms: []
        ));
        $this->assertEquals('Final Name', $newYear->fresh()->name, 'Step 10: Name update allowed for closed year');

        // الخطوة 11: الأرشفة
        app(ArchiveAcademicYearAction::class)->execute($newYear);
        $this->assertEquals(AcademicYearStatus::Archived, $newYear->fresh()->status, 'Step 11: Year archived successfully');

        // الخطوة 12: محاولة تعديل سنة مؤرشفة (يجب أن تفشل تماماً)
        try {
            $newYear->refresh();
            app(UpdateAcademicYearAction::class)->execute($newYear, new AcademicYearData(
                name: '2032-2033',
                start_date: $newYear->start_date,
                end_date: $newYear->end_date,
                status: AcademicYearStatus::Archived,
                terms: []
            ));
            $this->fail('Step 12 Failed: Should have thrown YearNotEditableException for archived year');
        } catch (\App\Domains\Academic\AcademicYear\Exceptions\YearNotEditableException $e) {
            $this->assertTrue(true, 'Step 12 Passed: Modification prevented for archived year');
        }
    }

    /** @test */
    public function it_cannot_close_year_with_incomplete_terms()
    {
        $year = AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'status' => AcademicYearStatus::Active,
        ]);

        Term::create([
            'academic_year_id' => $year->id,
            'name' => 'Term 1',
            'start_date' => '2024-09-01',
            'end_date' => '2024-12-31',
            'order_index' => 1,
            'status' => TermStatus::Pending,
        ]);

        $this->expectException(InvalidOperationException::class);
        app(CloseAcademicYearAction::class)->execute($year);
    }
}
