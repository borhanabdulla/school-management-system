# Current Findings (Verified)

Status legend
- Resolved: fix applied in code
- Pending: fix required

Findings
1) CRITICAL/BLOCKER: SQLite partial unique index on `academic_years.status`
- Evidence: `database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php`
- Impact: SQLite partial index can misbehave in tests using `database.sqlite`.
- Status: Pending (verify on sqlite; migration now runs without driver branching).

2) HIGH: SmartGradeBook bypassed domain Actions and wrote MonthlyGrade directly
- Evidence: `app/Livewire/Teacher/Grading/SmartGradeBook.php`
- Status: Resolved (RecordMonthlyGradeAction introduced and used).

3) HIGH: ensureMonthsForTerm generated months without AcademicWriteGuard
- Evidence: `app/Livewire/Teacher/Grading/SmartGradeBook.php`
- Status: Resolved (guard added before generation; service-level guard also added).

4) HIGH: Attendance month selection not scoped by term_id
- Evidence: `app/Domains/Academic/Grading/Listeners/SyncAttendanceToMonthlyGrade.php`
- Status: Resolved (term_id scope added).

5) HIGH: Health gate missing before term result calculation
- Evidence: `app/Domains/Academic/Grading/Actions/CalculateTermGradesAction.php`
- Status: Resolved (GradingHealthGate enforced).

6) MEDIUM: Homework weighting policy mismatch
- Evidence: `app/Domains/Academic/Grading/Services/GradeSyncService.php`
- Status: Resolved (uses assessment.weight, falls back to max_score).
