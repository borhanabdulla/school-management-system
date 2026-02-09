<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Academic\AcademicYear\Models\AcademicYear;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\InvoiceItem;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\Payment;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Finance\Enums\PaymentMethod;
use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Shared\Models\User;

class DemoFinanceSeeder extends Seeder
{
    public function run()
    {
        $year = AcademicYear::where('status', 'active')->first()
            ?? AcademicYear::factory()->create(['status' => 'active', 'financial_status' => 'open']);

        $tuition = FeeType::firstOrCreate(['name' => 'Tuition Fees']);
        $bus = FeeType::firstOrCreate(['name' => 'Bus Fees']);

        // 1. Paid Invoice
        $s1 = Student::factory()->create([
            'first_name_en' => 'Ahmed',
            'family_name_en' => 'Ali',
            'first_name_ar' => 'أحمد',
            'family_name_ar' => 'علي'
        ]);

        $u1 = User::firstOrCreate(['email' => 'parent1@test.com'], [
            'username' => 'parent1',
            'password' => bcrypt('password'),
            'phone' => '123456789'
        ]);

        $g1 = Guardian::firstOrCreate(['phone' => '123456789'], [
            'user_id' => $u1->id,
            'first_name' => 'Parent',
            'last_name' => 'One',
            'national_id' => '1000000001',
        ]);
        $s1->guardians()->syncWithoutDetaching([$g1->id => ['relationship' => 'father', 'is_financial_sponsor' => true]]);

        $inv1 = Invoice::create([
            'student_id' => $s1->id,
            'academic_year_id' => $year->id,
            'status' => InvoiceStatus::Paid,
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 5000,
            'paid_amount' => 5000,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv1->id,
            'fee_type_id' => $tuition->id,
            'amount' => 5000
        ]);
        Payment::create([
            'invoice_id' => $inv1->id,
            'guardian_id' => $g1->id,
            'amount' => 5000,
            'method' => PaymentMethod::Cash,
            'paid_at' => now(),
            'transaction_reference' => 'TRX-' . rand(1000, 9999),
            'created_by' => 1,
        ]);


        // 2. Partial Invoice
        $s2 = Student::factory()->create([
            'first_name_en' => 'Sara',
            'family_name_en' => 'Mohamed',
            'first_name_ar' => 'سارة',
            'family_name_ar' => 'محمد'
        ]);

        $u2 = User::firstOrCreate(['email' => 'parent2@test.com'], [
            'username' => 'parent2',
            'password' => bcrypt('password'),
            'phone' => '987654321'
        ]);

        $g2 = Guardian::firstOrCreate(['phone' => '987654321'], [
            'user_id' => $u2->id,
            'first_name' => 'Parent',
            'last_name' => 'Two',
            'national_id' => '1000000002',
        ]);
        $s2->guardians()->syncWithoutDetaching([$g2->id => ['relationship' => 'mother', 'is_financial_sponsor' => true]]);

        $inv2 = Invoice::create([
            'student_id' => $s2->id,
            'academic_year_id' => $year->id,
            'status' => InvoiceStatus::PartiallyPaid,
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 5000,
            'paid_amount' => 2000,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv2->id,
            'fee_type_id' => $tuition->id,
            'amount' => 5000
        ]);
        Payment::create([
            'invoice_id' => $inv2->id,
            'guardian_id' => $g2->id,
            'amount' => 2000,
            'method' => PaymentMethod::ManualTransfer,
            'paid_at' => now(),
            'transaction_reference' => 'TRX-' . rand(1000, 9999),
            'created_by' => 1,
        ]);

        // 3. Unpaid Invoice
        $s3 = Student::factory()->create([
            'first_name_en' => 'Omar',
            'family_name_en' => 'Khaled',
            'first_name_ar' => 'عمر',
            'family_name_ar' => 'خالد'
        ]);
        $inv3 = Invoice::create([
            'student_id' => $s3->id,
            'academic_year_id' => $year->id,
            'status' => InvoiceStatus::Unpaid,
            'invoice_number' => 'INV-' . rand(1000, 9999),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'total_amount' => 6000, // 5000 tuition + 1000 bus
            'paid_amount' => 0,
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv3->id,
            'fee_type_id' => $tuition->id,
            'amount' => 5000
        ]);
        InvoiceItem::create([
            'invoice_id' => $inv3->id,
            'fee_type_id' => $bus->id,
            'amount' => 1000
        ]);

        $this->command->info('Created 3 demo invoices (Paid, Partial, Unpaid)');
    }
}
