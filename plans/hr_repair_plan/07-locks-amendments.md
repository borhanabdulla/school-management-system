# Phase 7 - Period Locks + Amendments

Goal
- Prevent post-payroll edits from changing history.
- Route locked-period changes through Amendments and payroll adjustments.

Period lock service
- New: app/Domains/HR/Payroll/Services/PayrollPeriodLockService.php
  - isLockedForAttendance(date)
  - isLockedForLeave(range)
  - isLockedForContractChange(range)
  - Returns lock reason (batch status + id).

Apply locks in actions/services
- AttendanceService::saveAttendance/correctAttendance
- LeaveService::approveRequest/cancelRequest
- Contract/Loan actions that alter amounts within locked periods

Amendment flow (MVP)
- New HR amendment entity
- CreateAmendmentAction, ApproveAmendmentAction
- On approval, generate PayrollAdjustment for next period

Reproducibility
- Add calculation_context JSON to PayrollBatch/PayrollRecord
- Store policy snapshot used at generation (grace minutes, daily rate basis, etc.).

Tests
- Locked period blocks direct attendance/leave/contract edits.
- Amendment generates adjustment for next payroll period.
- Reproducibility: payroll results remain explainable after policy changes.
