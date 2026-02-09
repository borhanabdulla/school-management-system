<?php

namespace App\Livewire\Finance\Invoices;

use Livewire\Component;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Actions\RecordPaymentAction;
use App\Domains\Finance\Data\PaymentData;
use App\Domains\Finance\Enums\PaymentMethod;
use Illuminate\Validation\ValidationException;

class RecordPaymentModal extends Component
{
    public int $invoice_id;
    public string $amount = '';
    public string $method = 'cash';
    public ?string $notes = null;

    // UI Helpers
    public float $remainingAmount = 0;
    public bool $isLocked = false;

    public function mount(int $invoiceId)
    {
        $this->invoice_id = $invoiceId;
        $invoice = Invoice::findOrFail($invoiceId);

        $this->remainingAmount = $invoice->total_amount - $invoice->paid_amount;
        $this->isLocked = $invoice->academicYear->financial_status === 'closed';

        // Default amount to remaining (convenience)
        $this->amount = (string) $this->remainingAmount;
    }

    public function render()
    {
        return view('livewire.finance.invoices.record-payment-modal');
    }

    public function record()
    {
        // Warn if locked but allow proceeding
        // if ($this->isLocked) {
        //     $this->addError('general', 'لا يمكن تسجيل دفعات على سنة مالية مغلقة (إلا بصلاحيات خاصة لم تُفعل بعد).');
        //     return;
        // }

        $this->validate([
            'amount' => 'required|numeric|min:0.01|lte:remainingAmount',
            'method' => ['required', \Illuminate\Validation\Rule::enum(PaymentMethod::class)],
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            $invoice = Invoice::findOrFail($this->invoice_id);

            app(RecordPaymentAction::class)->execute(
                PaymentData::fromArray([
                    'invoice_id' => $this->invoice_id,
                    'guardian_id' => $invoice->payer_guardian_id, // Always use the invoice's payer
                    'amount' => (float) $this->amount,
                    'method' => PaymentMethod::from($this->method),
                    'notes' => $this->notes,
                ])
            );

            $this->dispatch('invoiceUpdated');
            $this->dispatch('closeModal');
            $this->dispatch('notify', message: 'تم تسجيل الدفعة بنجاح');

        } catch (ValidationException $e) {
            $this->addError('general', $e->getMessage());
        } catch (\Exception $e) {
            $this->addError('general', 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
