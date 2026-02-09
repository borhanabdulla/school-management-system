# Phase 5 - Leave

Scope
- Leave requests, balances, and interaction with attendance/payroll.

DB/model mismatches
- app/Domains/HR/Leave/Models/StaffLeaveBalance.php
  - Table name mismatch: migration creates leave_balances, model expects staff_leave_balances.
  - Fix by setting protected $table = 'leave_balances' or rename migration/table.
- database/migrations/2025_12_18_170000_create_leave_management_tables.php
  - down() drops staff_leave_balances (wrong table).
- app/Domains/HR/Leave/Enums/LeaveRequestStatus.php
  - Enum includes cancelled but DB status enum does not.
- app/Domains/HR/Leave/Models/LeaveRequest.php
  - approver relation lacks User import.

Domain rules
- app/Domains/HR/Leave/Services/LeaveService.php
  - submitRequest() must prevent overlap with pending/approved requests.
  - approveRequest() must check PayrollPeriodLockService.
  - updateAttendanceRecords() must not overwrite manual attendance and must use HR enum.
  - No auth() inside domain services; pass user_id explicitly.

Policy decisions to lock in
- Backdated requests allowed only if period not locked.
- If attendance exists for a day, policy decides whether to block leave approval or create amendment.

Tests
- Overlap prevention for pending/approved requests.
- Approved leave does not overwrite manual attendance (policy enforced).
- Period lock blocks approval in frozen/paid payroll periods.
