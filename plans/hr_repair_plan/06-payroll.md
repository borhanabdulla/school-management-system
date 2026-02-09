# Phase 6 - Payroll

Scope
- Payroll batch workflow, calculation engine, and financial sources (contracts/loans).

Batch generation
- app/Domains/HR/Payroll/Actions/GeneratePayrollAction.php
  - Duplicate check must be protected by DB unique index (period uniqueness).
  - employees_count should reflect actual records created (not staff count).
  - Do not consume one-time items during generate (reserve/consume at Freeze/Approve).

Payroll batch workflow
- app/Domains/HR/Payroll/Models/PayrollBatch.php
  - Keep Draft/Frozen/Approved/Paid invariant enforcement.

Calculation engine
- app/Domains/HR/Payroll/Services/PayrollCalculationService.php
  - Remove direct StaffAttendance queries; use AttendanceSummaryService.
  - Split-month deductions should be policy-based (segment vs last contract).

Contracts
- app/Domains/HR/Payroll/Models/Contract.php
  - ContractStatus enum missing draft while DB allows it.
- app/Domains/HR/Payroll/Services/ContractService.php
  - Must enforce ensureEditable/period locks when creating new contracts.
- app/Livewire/Payroll/ContractManager.php
  - Direct updates bypass domain; must move to Actions.

Contract items (one-time)
- database/migrations/2025_12_22_000002_create_contract_items_table.php
  - Missing consumed_by_payroll_batch_id and consumed_by_payroll_record_id.
  - Either add columns or stop updating them in Generate/Approve.

Loans
- app/Domains/HR/Payroll/Actions/ApprovePayrollAction.php
  - Writes loan status 'completed' but enum has 'paid'.
- app/Livewire/Payroll/LoanManager.php
  - No transaction; rounding errors; status set directly in UI.

Tests
- Prevent duplicate batch for same period (DB unique).
- Generate/Regenerate does not consume one-time items.
- Loan status transitions respect enum.
- Contract edits blocked when period is locked.
