# 04 Attendance Sync

Goal
- Keep attendance-derived grades term-correct, guarded, and deterministic.

Scope
- AttendanceBatchSaved listener, month resolution, GradeSyncService attendance path.

Inputs (code references)
- app/Domains/Academic/Grading/Listeners/SyncAttendanceToMonthlyGrade.php
- app/Domains/Academic/Grading/Services/GradeSyncService.php
- app/Domains/Academic/Grading/Models/MonthlyGrade.php
- app/Domains/Academic/Grading/Models/GradebookSettings.php

Tasks
- [x] Scope month lookup by term_id, not just academic_year_id.
- [x] Enforce AcademicWriteGuard before writing MonthlyGrade.
- [ ] Ensure attendance category mapping uses category_key + label safely.
- [ ] Handle unsupported AttendanceMode with explicit warnings.
- [ ] Ensure attendance sync does not loop (MonthlyGrade::withoutEvents).

Acceptance Criteria
- Attendance sync never writes grades for a closed term.
- Attendance sync cannot target the wrong term.
- Missing attendance category results in a clear failure log.

Verification
- Simulate attendance save in an inactive term and confirm write is blocked.
