<?php

namespace Tests\Feature\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\PaymentStatus;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentAfterLockUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_payment_on_closed_year()
    {
        \Illuminate\Support\Facades\Gate::define('finance.cancel_payment', fn() => true);
        \Illuminate\Support\Facades\Gate::define('finance.record_payment', fn() => true);
        
        $user = User::factory()->create();
        $year = AcademicYear::factory()->create(['financial_status' => 'closed']);
        $student = Student::factory()->create();
        $guardian = \App\Domains\Academic\Student\Models\Guardian::factory()->create();
        $invoice = Invoice::factory()->create([
            'academic_year_id' => $year->id,
            'student_id' => $student->id,
            'payer_guardian_id' => $guardian->id,
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => InvoiceStatus::Unpaid,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Finance\Invoices\RecordPaymentModal::class, ['invoiceId' => $invoice->id])
            ->assertSee('تنبيه') // Warning banner
            ->set('amount', '500')
            ->set('method', PaymentMethod::Cash->value)
            ->call('record')
            ->assertDispatched('invoiceUpdated')
            ->assertHasNoErrors();

        $this->assertEquals(500, $invoice->fresh()->paid_amount);
    }

    public function test_can_cancel_payment_on_closed_year()
    {
        \Illuminate\Support\Facades\Gate::define('finance.cancel_payment', fn() => true);

        $user = User::factory()->create();
        $year = \App\Domains\Academic\AcademicYear\Models\AcademicYear::factory()->create(['financial_status' => 'closed']);
        $student = Student::factory()->create();
        $invoice = Invoice::factory()->create([
            'academic_year_id' => $year->id,
            'student_id' => $student->id,
            'total_amount' => 1000,
            'paid_amount' => 500,
            'status' => InvoiceStatus::PartiallyPaid,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 500,
            'status' => PaymentStatus::Posted,
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Finance\Invoices\CancelPaymentModal::class, ['payment_id' => $payment->id])
            ->assertSee('سنة مالية مغلقة') // Warning banner
            ->set('reason', 'Mistake in entry')
            ->call('cancel')
            ->assertDispatched('payment-cancelled') // Should exist if implemented
            ->assertHasNoErrors();

        $this->assertEquals(PaymentStatus::Cancelled, $payment->fresh()->status);
    }
}
