<?php

namespace App\Livewire\Finance\Invoices;

use App\Domains\Finance\Actions\CancelPaymentAction;
use App\Domains\Finance\Models\Payment;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CancelPaymentModal extends Component
{
    public int $payment_id;
    public string $reason = '';
    public bool $isLocked = false;

    public ?Payment $payment = null;

    public function mount(int $payment_id)
    {
        $this->payment_id = $payment_id;
        $this->payment = Payment::with('invoice.academicYear')->findOrFail($payment_id);
        $this->isLocked = $this->payment->invoice->academicYear->financial_status === 'closed';
    }

    public function rules()
    {
        return [
            'reason' => 'required|string|min:3|max:255',
        ];
    }

    public function cancel()
    {
        $this->validate();

        try {
            app(CancelPaymentAction::class)->execute($this->payment_id, $this->reason);

            $this->dispatch('closeModal');
            $this->dispatch('payment-cancelled'); // Optional: refresh parent
            $this->dispatch('refreshComponent'); // If parent listens to this

        } catch (ValidationException $e) {
            // Map validation errors (like double cancellation) to 'base' so they are visible
            $msg = implode(' ', $e->validator->errors()->all());
            $this->addError('base', $msg);
        } catch (\Exception $e) {
            $this->addError('base', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.finance.invoices.cancel-payment-modal');
    }
}
