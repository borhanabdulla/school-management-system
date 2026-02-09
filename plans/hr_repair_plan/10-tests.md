# Phase 10 - Tests (Guardrails)

Purpose
- Prevent regressions after each phase.

Staff
- Create staff -> joining_date cast matches DB.
- Delete staff with attendance -> blocked.
- Delete staff with teacher and substitutions -> blocked.

Teacher/Substitution
- scopeAvailableAt excludes busy teacher.
- Assign substitution twice for same timetable/date -> fails.

Attendance
- recorded_by relation returns correct user.
- check_in/check_out time round-trip.
- Locked payroll period blocks corrections.

Leave
- Overlap prevention for pending/approved requests.
- Approved leave does not overwrite manual attendance.
- Locked period blocks approval (or creates amendment).

Payroll
- Unique batch per period enforced at DB.
- Draft regenerate does not consume one-time items.
- Loan status transitions align with enum.

UI (smoke)
- HR attendance dashboard renders without fatal errors.
- Leave request form loads with correct model imports.

Status
- Implemented in `tests/Feature/HR/StaffBasicsTest.php`.
- Implemented in `tests/Feature/HR/TeacherAvailabilityTest.php`.
- Implemented in `tests/Feature/HR/SubstitutionAssignmentTest.php`.
- Implemented in `tests/Feature/HR/UiSmokeTest.php`.
- Implemented in `tests/Feature/Payroll/PayrollBatchUniquenessTest.php`.
- Implemented in `tests/Feature/Payroll/PayrollOneTimeConsumptionTest.php`.
