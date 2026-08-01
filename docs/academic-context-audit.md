# Academic Context Audit (Active Year/Term)

Purpose
- Identify places that should use the active academic year/term but do not.
- Clarify the difference between calendar year (current year) vs active academic year.
- Recommend a single source of truth for year/term scoping.

Single source of truth (intended)
- `App\Infrastructure\Context\AcademicContextService` accessed via helper `school()` in `app/Infrastructure/Support/helpers.php`.
- `school()->activeYear()` / `school()->activeYearId()` for academic year scope.
- `school()->activeTerm()` / `school()->activeTermId()` for term scope.
- `HasAcademicScope` trait provides `currentYear()`/`currentTerm()` scopes based on `school()`.

Definitions
- Calendar year (current year): derived from `now()->year`. This is NOT the academic year.
- Active academic year: `academic_years.status = AcademicYearStatus::Active` (via `school()->activeYear()`), with cached context and term links.
- Active term: `terms.status = TermStatus::Active` AND belongs to active academic year (via `school()->activeTerm()`).

When to use which
- Use `school()->activeYearId()` for academic data scoped by academic year (enrollments, class sections, course offerings, attendance reports, grading, timetable).
- Use `school()->activeTermId()` for term-scoped data (timetable sessions, term-specific attendance, gradebooks).
- Use `now()->year` ONLY for calendar-based data (financial payroll periods, generic date filters, audit logging).

Findings (needs unification / risk)

1) Uses `AcademicYear::active()` directly instead of `school()->activeYear()` (mostly resolved)
- Updated in the student actions/services, dashboard, events, and payroll onboarding command.
- Remaining intentional exception: `app/Domains/Academic/AcademicYear/Actions/CreateAcademicYearAction.php` keeps a locked query to avoid cached context while creating a year.

Why this matters
- These bypass the centralized cache/invalidations in `AcademicContextService`.
- They can diverge if `school()->activeYear()` is cached or intentionally overridden.

Recommendation
- Replace direct `AcademicYear::active()->first()` with `school()->activeYear()` or inject `AcademicContextService`.

2) Uses calendar year for academic reporting (resolved)
- `app/Livewire/Attendance/AttendanceReport.php`
  - Now defaults `year` and `termId` from `school()->activeYear()` / `school()->activeTerm()`.

3) HR Leave dashboard uses calendar year (resolved)
- `app/Livewire/HR/Leave/EmployeeLeaveDashboard.php`
  - Now uses the active academic year start date for the balance year.

5) AttendanceService uses SchoolEvent::currentYear() (resolved)
- `app/Domains/HR/Attendance/Services/AttendanceService.php` now scopes holidays by `school()->activeYearId()`.

6) Payroll UI uses calendar year
- `app/Livewire/Payroll/PayrollProcessor.php`
- `app/Livewire/Payroll/PayrollBatchManager.php`
- `app/Livewire/Payroll/PayrollDashboard.php`

Note
- Payroll is monthly/financial and can be calendar-based. This is acceptable if HR/Finance policy is calendar-year-based.
- If payroll should align to academic year, it should use `school()->activeYear()` or allow user selection.

7) StudentLookupService uses `now()->year`
- `app/Domains/Academic/Student/Services/StudentLookupService.php` (created_at year filters).

Why this matters
- This is a calendar filter, not an academic-year filter.

Recommendation
- If the intended meaning is "students created in active academic year", use `school()->activeYear()` boundaries.

Compliant usages (good patterns)
- `app/Domains/Academic/Timetable/Services/TimetableLookupService.php` uses `school()->activeYearId()`.
- `app/Domains/Academic/Term/Services/TermLookupService.php` uses `school()->activeYearId()`.
- `app/Infrastructure/Traits/HasAcademicScope.php` uses `school()->activeYearId()` and `school()->activeTermId()`.
- `app/Domains/Academic/Grading/Services/GradingLookupService.php` uses `school()->activeYearId()`.

Execution status (applied changes)
- Switched to `school()->activeYear()` in:
  - `app/Domains/Academic/Student/Actions/RegisterStudentAction.php`
  - `app/Domains/Academic/Student/Actions/AssignStudentToClassAction.php`
  - `app/Domains/Academic/Student/Services/AdmissionNumberService.php`
  - `app/Domains/Academic/Student/Services/StudentService.php` (kept row lock via `AcademicYear::query()->lockForUpdate()` on the resolved active year)
  - `app/Domains/Academic/Student/Services/StudentPlacementSyncService.php`
  - `app/Livewire/Dashboard/MainDashboard.php` (active year/term resolution now uses `school()`)
  - `app/Livewire/Admin/Events/EventManager.php`
  - `app/Console/Commands/OnboardStaffToPayroll.php`
- Updated `app/Infrastructure/Traits/HasAcademicScope.php` to use `school()->activeYearId()` and `school()->activeTerm()` for auto-fill.
- Updated `app/Livewire/Attendance/AttendanceReport.php` to default to active term/year.
- Updated `app/Livewire/HR/Leave/EmployeeLeaveDashboard.php` to use active academic year start date for balance year.
- Updated `app/Domains/HR/Attendance/Services/AttendanceService.php` to scope holidays by `school()->activeYearId()`.
- Updated `app/Livewire/HR/Leave/EmployeeLeaveDashboard.php` to use active academic year start date for balance year.

Remaining exceptions (intentional / needs discussion)
- `app/Domains/Academic/AcademicYear/Actions/CreateAcademicYearAction.php` keeps `AcademicYear::active()->lockForUpdate()` to avoid cached context during year creation.

Unification plan (single-source enforcement)
1) Ensure remaining exceptions are documented and intentional.
2) Add a helper for mapping calendar dates to academic-year ranges when required by reports.
3) Where calendar year is intended (payroll), document it explicitly in code comments.
4) Add tests for active-year/term scoping in high-risk flows (enrollments, attendance reports).

Audit method (tools used)
- `rg -n "activeYear|activeTerm|AcademicContextService|now()->year|AcademicYear::active" app -g"*.php"`
- Direct file inspections for specific flows (attendance report, student actions, payroll screens).
