<?php

declare(strict_types=1);

use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Actions\CreateInvoiceAction;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Data\InvoiceData;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Exceptions\PaymentExceedsInvoiceException;
use App\Domains\Finance\Exceptions\GuardianNotFinancialSponsorException;
use Spatie\Permission\Models\Permission;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\AcademicYear\Enums\AcademicYearStatus;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\InvoiceItem;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $permission = Permission::firstOrCreate(['name' => 'finance.record_payment']);
    $user = \App\Domains\Shared\Models\User::factory()->create();
    $user->givePermissionTo($permission);
    $this->actingAs($user);

    // إنشاء سنة دراسية نشطة
    $this->academicYear = AcademicYear::factory()->create([
        'status' => AcademicYearStatus::Active,
    ]);

    // إنشاء ولي أمر
    $this->guardian = Guardian::factory()->create();

    // إنشاء طالب مع ربط الولي كمسؤول مالي
    $this->student = Student::factory()->create();
    $this->student->guardians()->attach($this->guardian->id, [
        'relationship' => 'father',
        'is_financial_sponsor' => true,
        'is_emergency_contact' => true,
        'lives_with' => true,
        'has_portal_access' => true,
    ]);

    // إنشاء فاتورة
    $this->invoice = Invoice::factory()->create([
        'student_id' => $this->student->id,
        'academic_year_id' => $this->academicYear->id,
        'total_amount' => 1000.00,
        'paid_amount' => 0,
        'status' => InvoiceStatus::Unpaid,
    ]);

    $feeType = FeeType::create(['name' => 'Tuition', 'is_active' => true]);
    InvoiceItem::create([
        'invoice_id' => $this->invoice->id,
        'fee_type_id' => $feeType->id,
        'label' => 'Tuition',
        'amount' => 1000.00,
    ]);
});

describe('Payment Flow', function () {

    it('correctly updates invoice status from unpaid to partially_paid to paid', function () {
        $action = app(RecordPaymentAction::class);

        // دفعة أولى: 200 ريال
        $payment1 = $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $this->guardian->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Cash,
        ]));

        $this->invoice->refresh();

        expect($payment1)->toBeInstanceOf(Payment::class);
        expect((float) $this->invoice->paid_amount)->toEqual(200.00);
        expect($this->invoice->status)->toBe(InvoiceStatus::PartiallyPaid);

        // دفعة ثانية: 800 ريال (المتبقي)
        $payment2 = $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $this->guardian->id,
            'amount' => 800.00,
            'method' => PaymentMethod::ManualTransfer,
            'transaction_reference' => 'TRX-001',
        ]));

        $this->invoice->refresh();

        expect((float) $this->invoice->paid_amount)->toEqual(1000.00);
        expect($this->invoice->status)->toBe(InvoiceStatus::Paid);
        expect($this->invoice->payments->count())->toBe(2);
    });

    it('rejects payment exceeding remaining amount', function () {
        $action = app(RecordPaymentAction::class);

        // محاولة دفع 1500 على فاتورة 1000
        $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $this->guardian->id,
            'amount' => 1500.00,
            'method' => PaymentMethod::Cash,
        ]));
    })->throws(PaymentExceedsInvoiceException::class);

    it('rejects payment from non-financial-sponsor guardian', function () {
        // إنشاء ولي غير مسؤول مالياً
        $nonSponsorGuardian = Guardian::factory()->create();
        $this->student->guardians()->attach($nonSponsorGuardian->id, [
            'relationship' => 'uncle',
            'is_financial_sponsor' => false,
            'is_emergency_contact' => false,
            'lives_with' => false,
            'has_portal_access' => false,
        ]);

        $action = app(RecordPaymentAction::class);

        $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $nonSponsorGuardian->id,
            'amount' => 500.00,
            'method' => PaymentMethod::Cash,
        ]));
    })->throws(GuardianNotFinancialSponsorException::class);

    it('allows payment on closed academic year', function () {
        // إغلاق السنة الدراسية
        $this->academicYear->update(['status' => AcademicYearStatus::Closed]);

        $action = app(RecordPaymentAction::class);

        $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $this->guardian->id,
            'amount' => 500.00,
            'method' => PaymentMethod::Cash,
        ]));

        $this->invoice->refresh();
        expect((float) $this->invoice->paid_amount)->toEqual(500.00);
    });

    it('links payment to the correct guardian', function () {
        $action = app(RecordPaymentAction::class);

        $payment = $action->execute(PaymentData::fromArray([
            'invoice_id' => $this->invoice->id,
            'guardian_id' => $this->guardian->id,
            'amount' => 300.00,
            'method' => PaymentMethod::Cash,
        ]));

        expect($payment->guardian_id)->toBe($this->guardian->id);
        expect($payment->guardian->id)->toBe($this->guardian->id);
    });
});
