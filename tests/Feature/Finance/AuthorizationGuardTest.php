<?php

namespace Tests\Feature\Finance;

use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Models\Discount;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_discount_requires_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Deny by default (no permission defined or user doesn't have it)
        // Verify Check: Gate should forbid.

        $invoiceItem = new InvoiceItem(['invoice_id' => 1]);

        $feeType = \App\Domains\Finance\Models\FeeType::create([
            'name' => 'Tuition'
        ]);
        $invoice = Invoice::factory()->create();
        $invoiceItem = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'fee_type_id' => $feeType->id,
            'amount' => 100
        ]);

        $discount = Discount::create([
            'name' => 'Promo',
            'type' => 'fixed',
            'value' => 50
        ]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(\App\Domains\Finance\Actions\ApplyDiscountAction::class)->execute(
            $invoiceItem->id,
            $discount->id
        );
    }

    public function test_record_payment_requires_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // Deny by default

        $invoice = Invoice::factory()->create();
        // Since we hit Gate first, data validation doesn't matter much yet, but let's be safe
        // Actually execute uses strict data, but mocking data object is easier.
        // Wait, execute(PaymentData $data).

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        app(\App\Domains\Finance\Actions\RecordPaymentAction::class)->execute(
            \App\Domains\Finance\Data\PaymentData::fromArray([
                'invoice_id' => $invoice->id,
                'guardian_id' => 1,
                'amount' => 100,
                'method' => PaymentMethod::Cash,
            ])
        );
    }

    public function test_apply_discount_allows_with_permission()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Gate::define('finance.apply_discount', fn() => true);

        // Setup valid scenario
        $year = AcademicYear::factory()->create(['status' => 'active']);
        $student = Student::factory()->create();
        $invoice = Invoice::factory()->create([
            'academic_year_id' => $year->id,
            'student_id' => $student->id
        ]);

        $feeType = \App\Domains\Finance\Models\FeeType::create([
            'name' => 'Tuition'
        ]);
        $item = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'fee_type_id' => $feeType->id
        ]);

        $discount = Discount::create([
            'name' => 'Promo',
            'type' => 'fixed',
            'value' => 100
        ]);

        $result = app(\App\Domains\Finance\Actions\ApplyDiscountAction::class)->execute(
            $item->id,
            $discount->id
        );

        $this->assertNotNull($result);
    }
}
