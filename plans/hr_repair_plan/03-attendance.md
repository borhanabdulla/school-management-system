# Phase 3 - Staff Attendance

Scope
- Fix DB/model parity, enums, and attendance write paths.
- Prevent history edits after payroll locks.

DB/model parity (must-fix)
- app/Domains/HR/Staff/Models/StaffAttendance.php
  - recorded_by relation uses created_by (wrong column).
  - status cast uses Academic AttendanceStatus (wrong domain).
  - check_in/check_out casted as datetime while DB is time.
- database/migrations/2025_11_19_185400_create_staff_attendance_table.php
- database/migrations/2025_12_17_164100_enhance_staff_attendance_table.php

Required changes
- Introduce StaffAttendanceStatus enum in HR domain.
- Align check_in/check_out casts with DB (time string or custom cast).
- Use recorded_by in relation (recordedBy()) and in write paths.

Write paths
- app/Domains/HR/Attendance/Services/AttendanceService.php
  - saveAttendance() and correctAttendance() must check PayrollPeriodLockService.
  - calculateStatus() must use HR enum and align with leave/holiday policy.

Data rules
- Enforce unique attendance per staff/date (or staff/date/shift).
- Do not overwrite manual attendance when leave is approved; define policy.

Tests
- recorded_by relation returns correct user.
- status enum accepts only HR values.
- time values round-trip without date drift.
- locked payroll period blocks corrections (or routes to Amendment).
