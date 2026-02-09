<?php

namespace App\Livewire\Payroll;

use App\Domains\HR\Payroll\Models\PayrollBatch;
use App\Domains\HR\Payroll\Actions\GeneratePayrollAction;
use App\Domains\HR\Payroll\Actions\FreezePayrollAction;
use App\Domains\HR\Payroll\Actions\ApprovePayrollAction;
use App\Domains\HR\Payroll\Actions\MarkPayrollPaidAction;
use App\Domains\HR\Payroll\Enums\PayoutMethod;
use App\Domains\HR\Payroll\Services\PayrollCalculationService;
use App\Domains\HR\Payroll\Services\WPSValidator;
use App\Domains\HR\Payroll\Services\BankExportService;
use App\Domains\HR\Payroll\Data\PayrollGenerationData;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

#[Layout('layouts.app')]
class PayrollBatchManager extends Component
{
    use WithPagination;

    // Filters
    public string $statusFilter = '';
    public int $yearFilter = 0;

    // Generate Modal
    public bool $showGenerateModal = false;
    public int $generateYear;
    public int $generateMonth;

    // View Modal
    public bool $showViewModal = false;
    public ?PayrollBatch $viewingBatch = null;

    // Export Modal
    public $selectedBatchId;
    public $showExportModal = false;
    public $exportStep = 1; // 1: Validation, 2: Ready
    public $validationIssues = [];
    public $exportFilePath = null;

    // Pay Modal (PR4.1)
    public bool $showPayModal = false;
    public ?int $payingBatchId = null;
    public string $payoutMethod = 'Cash';
    public string $payoutReference = '';

    public function mount()
    {
        $this->generateYear = now()->year;
        $this->generateMonth = now()->month;
        $this->yearFilter = now()->year;
    }

    // ==================== Generate Actions ====================

    public function openGenerateModal()
    {
        $this->generateYear = now()->year;
        $this->generateMonth = now()->month;
        $this->showGenerateModal = true;
    }

    public function generate(GeneratePayrollAction $action)
    {
        try {
            $data = PayrollGenerationData::fromYearMonth($this->generateYear, $this->generateMonth);
            $batch = $action->execute($data, auth()->id());

            $this->dispatch('notify', message: "تم توليد الدفعة {$batch->name} بنجاح!", type: 'success');
            $this->showGenerateModal = false;
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    // ==================== Workflow Actions ====================

    public function freeze(int $id, FreezePayrollAction $action)
    {
        try {
            $batch = PayrollBatch::findOrFail($id);
            $action->execute($batch, auth()->id());
            $this->dispatch('notify', message: 'تم قفل الدفعة بنجاح. لا يمكن التعديل الآن.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function approve(int $id, ApprovePayrollAction $action)
    {
        try {
            $batch = PayrollBatch::findOrFail($id);
            $action->execute($batch, auth()->id());
            $this->dispatch('notify', message: 'تم اعتماد الدفعة بنجاح.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function openPayModal(int $id)
    {
        $this->payingBatchId = $id;
        $this->payoutMethod = 'Cash';
        $this->payoutReference = '';
        $this->showPayModal = true;
    }

    public function closePayModal()
    {
        $this->showPayModal = false;
        $this->payingBatchId = null;
    }

    public function confirmPay(MarkPayrollPaidAction $action)
    {
        $this->validate([
            'payoutMethod' => 'required|in:Cash,BankTransfer,Cheque',
            'payoutReference' => 'nullable|string|max:100',
        ]);

        try {
            $batch = PayrollBatch::findOrFail($this->payingBatchId);
            $method = PayoutMethod::from($this->payoutMethod);

            $action->execute($batch, auth()->id(), $method, $this->payoutReference ?: null);

            $this->dispatch('notify', message: 'تم تسجيل دفع الرواتب بنجاح.', type: 'success');
            $this->closePayModal();
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function delete(int $id)
    {
        try {
            $batch = PayrollBatch::findOrFail($id);
            $batch->ensureEditable();
            $batch->delete();
            $this->dispatch('notify', message: 'تم حذف الدفعة.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    // ==================== View ====================

    public function view(int $id)
    {
        $this->viewingBatch = PayrollBatch::with(['records.staff', 'records.items'])->findOrFail($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingBatch = null;
    }

    public function openExportModal($batchId)
    {
        $this->selectedBatchId = $batchId;
        $this->showExportModal = true;
        $this->exportStep = 1;
        $this->validateForExport();
    }

    public function validateForExport()
    {
        $batch = PayrollBatch::find($this->selectedBatchId);
        if (!$batch)
            return;

        $validator = new WPSValidator();
        $this->validationIssues = $validator->validateBatch($batch);

        // If no errors (warnings are ok), move to step 2 automatically? 
        // No, let user see "All Good" message first.
    }

    public function generateExport()
    {
        $batch = PayrollBatch::find($this->selectedBatchId);
        if (!$batch)
            return;

        $exporter = new BankExportService();
        $path = $exporter->exportToFile($batch);

        return response()->download(storage_path('app/' . $path))->deleteFileAfterSend(true);
    }

    public function closeExportModal()
    {
        $this->showExportModal = false;
        $this->validationIssues = [];
        $this->exportStep = 1;
    }

    public function render()
    {
        $batches = PayrollBatch::query()
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->yearFilter, fn($q) => $q->where('year', $this->yearFilter))
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(12);

        $years = PayrollBatch::selectRaw('DISTINCT year')->orderBy('year', 'desc')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('livewire.payroll.payroll-batch-manager', [
            'batches' => $batches,
            'years' => $years,
        ]);
    }
}
