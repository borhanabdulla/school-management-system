# Phase 8 - Livewire UI Cleanup (HR)

Goal
- UI must not bypass domain rules or payroll locks.

Runtime fixes
- app/Livewire/HR/StaffAttendanceDashboard.php
  - Add missing saveAttendance() or remove calls and use AttendanceService only.
  - Remove duplicate write path in saveEditedAttendance().
- app/Livewire/HR/Leave/LeaveRequestForm.php
  - Fix LeaveType import (use domain model).
- app/Livewire/HR/Leave/EmployeeLeaveDashboard.php
  - Fix LeaveRequest/LeaveType imports.
  - Reset balances before filling.
- app/Livewire/Forms/HR/LeaveRequestForm.php
  - Fix setFromModel type hint (use domain LeaveRequest).
- app/Livewire/HR/StaffEdit.php
  - Fix WorkShift import (use domain model).
- app/Livewire/HR/StaffShow.php
  - Replace attendances() with staffAttendances() or correct relation alias.

Domain boundaries
- app/Livewire/HR/StaffAttendanceDashboard.php
  - Move checkTeacherSubstitution logic to a domain service.
  - UI should call service for substitutions availability.

Permissions alignment
- routes/web.php
  - Protect /hr/leave/request with can:leave.request.
  - Ensure /hr/leave/dashboard is accessible to staff with leave.request.
  - /hr/dashboard has write operations (assign substitution) but guarded by staff.view only.
    Re-evaluate permission requirements.

Policy vs UI
- Leave request backdating should be enforced in domain, not only UI rules.

Tests
- UI components handle PeriodLockedException gracefully.
- UI does not write models directly outside actions/services.
