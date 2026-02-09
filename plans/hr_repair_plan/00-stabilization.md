# Phase 0 - Stabilization (Stop the bleeding)

Goal
- Fix issues that cause runtime failures or silent data corruption before refactors.

Critical runtime/data fixes
- StaffAttendance model parity
  - recorded_by relation, status enum, check_in/out casts.
- Staff model parity
  - joining_date cast, remove birth_date if not in DB.
- Leave balance table mismatch
  - StaffLeaveBalance table name vs migration; fix model or migration.
- LeaveRequestStatus mismatch
  - Align enum values with DB (include cancelled or remove from enum).
- LeaveRequest approver relation
  - Add missing User import in LeaveRequest model.
- UI runtime errors
  - StaffAttendanceDashboard missing saveAttendance.
  - Wrong model imports in LeaveRequestForm/EmployeeLeaveDashboard/StaffEdit/LeaveRequestForm (Form object).

Guardrails to add immediately
- DB unique index for payroll period to prevent duplicate batches.
- Fix Loan status 'completed' vs enum 'paid'.
- Fix one-time consumption columns mismatch (add columns or stop updating them).
