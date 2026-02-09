# PR-A4: Write-Path Audit for Active Year/Term Usage

Status: Planned  
Priority: Medium  
Depends On: PR-A1 (transitions DB-authoritative)

## 0. Purpose

Ensure write operations do not rely on cached active year/term values when they should be DB-authoritative or explicitly provided by the caller.

This is the read-heavy rule in practice:
- Reads may use cache.
- Writes must not silently trust cached context if correctness is required.

## 1. Audit Procedure

Run once to build the inventory:

```
rg -n "school\\(\\)->activeYearId\\(\\)|school\\(\\)->activeTermId\\(\\)" app
```

Classify each occurrence:
- Read-only (views, lookups, reports) -> OK.
- Transition (activate/close/reopen) -> must be DB-authoritative (covered in PR-A1).
- Write-path (create/update) -> must be reviewed and possibly refactored.

## 2. Scan Results (Current Codebase)

### 2.1 Write-path uses (create/update)

None remaining in this scope after remediation.

### 2.2 Read-only uses (UI / lookup / cache keys)

- `app/Infrastructure/Support/helpers.php:65-82`
  - Helper functions `active_year_id()` / `active_term_id()` return cached context.
- `app/Infrastructure/Traits/HasAcademicScope.php:65-78`
  - `scopeCurrentYear()` / `scopeCurrentTerm()` use cached context for filtering.
- `app/Infrastructure/Traits/HasAcademicScope.php:145-157`
  - `isCurrentYear()` / `isCurrentTerm()` compare against cached context.
- `app/Livewire/Admin/Promotion/AnnualResultsDashboard.php:49`
  - Default year selection from cached context.
- `app/Livewire/Admin/Promotion/PromotionManager.php:52`
  - Default year selection from cached context.
- `app/Livewire/Academic/TermManager.php:35-47`
  - Default year selection from cached context.
- `app/Domains/Finance/Services/FinanceLookupService.php:40-125`
  - Read-only stats + cache keys use cached active year id.
- `app/Domains/Academic/Timetable/Services/TimetableLookupService.php:28,50,70`
  - Read-only lookups use cached active year id.
- `app/Domains/Academic/Grading/Services/GradingLookupService.php:27,49`
  - Read-only lookups use cached active year id.
- `app/Domains/Academic/Term/Services/TermLookupService.php:33-71`
  - Read-only term lookups use cached active year id.
- `app/Livewire/Student/StudentRegistration.php`
  - Fee preview loads structures using cached active year (read-only UI).
- `app/Livewire/Forms/Finance/InvoiceForm.php`
  - Default academic year selection uses cached active year (read-only UI).
- `app/Livewire/Timetable/TimetableTemplateManager.php`
  - Default filter year uses cached active year (read-only UI).
- `app/Livewire/Dashboard/TeacherScheduleWidget.php`
  - Attendance settings read with cached active year (read-only UI).
- `app/Livewire/Teacher/DashboardStats.php`
  - Attendance settings read with cached active year (read-only UI).
- `app/Livewire/Teacher/Homework/TeacherHomeworkDashboard.php`
  - Default academic year selection uses cached active year (read-only UI).
- `app/Livewire/Teacher/WeeklyTimetable.php`
  - Active academic year read for schedule filtering (read-only UI).
- `app/Livewire/Attendance/AttendanceSettingsManager.php`
  - Settings load uses cached active year (read-only UI).
- `app/Domains/Academic/Student/Services/StudentLookupService.php`
  - Lookup read paths default to cached active year.

### 2.3 Transitions (PR-A1 scope)

- No cached reads remain inside transition actions (`ActivateAcademicYearAction`, `ActivateTermAction`, `ReopenTermAction`).

## 3. Decision Rules

For each write-path:

1) If the year/term is explicitly selected by the user or upstream service:
   - Pass the ID explicitly.
   - Do not default to cached active year/term.

2) If the operation must bind to the current active year/term:
   - Read the active year/term directly from DB (not cache).
   - Optionally lock if the operation is sensitive to concurrency.

3) If the write is an automatic helper (like `HasAcademicScope`):
   - Decide whether defaulting to active year is correct.
   - If required, consider switching to DB-authoritative reads or explicit injection.

## 4. Tests / Gates

Gate A:
- Force stale cache and perform a write-path operation.
- Verify it does not bind to the wrong year/term.

Gate B:
- Ensure transitions remain correct after adjusting write-path reads.

## 5. Definition of Done

- All write-path uses of active year/term are reviewed and classified.
- Any unsafe write-paths are refactored or explicitly documented as safe.
- Audit results recorded in this doc with line references.

## 6. Remediation Status

### 6.1 Fixed in this track

- `app/Domains/Academic/CourseOffering/Actions/AssignTeacherToCourseAction.php`
  - Now uses DB-authoritative active year/term (no cached reads).
  - Test: `tests/Feature/Domains/Academic/AssignTeacherToCourseActionTest.php`
- `app/Domains/Academic/Attendance/Actions/RecordStudentAttendanceAction.php`
  - Now sets `academic_year_id` from the timetable's class section (no cached auto-fill).
  - Test: `tests/Feature/Domains/Academic/AttendanceTest.php`
- `app/Domains/Academic/Control/Actions/CreateExamSessionAction.php`
  - Now sets `academic_year_id` from the selected term (no cached auto-fill).
- `app/Domains/Academic/Student/Actions/RegisterStudentAction.php`
  - Now reads active year from DB inside the transaction (no cached active year).
- `app/Domains/Academic/Student/Actions/AssignStudentToClassAction.php`
  - Now reads active year from DB (no cached active year).
- `app/Livewire/Admin/Events/EventManager.php`
  - Now reads active year from DB for write path (no cached active year).
  - Test: `tests/Feature/Livewire/Admin/Events/EventManagerTest.php`
- `app/Infrastructure/Traits/HasAcademicScope.php`
  - Auto-fill now uses DB-authoritative active year/term (no cached reads).
  - Test: `tests/Feature/Infrastructure/HasAcademicScopeTest.php`
- `app/Domains/Academic/Student/Services/AdmissionNumberService.php`
  - Now reads active year from DB when generating admission numbers.
  - Test: `tests/Unit/Domains/Academic/Student/AdmissionNumberServiceTest.php`
- `app/Domains/Academic/Student/Services/StudentPlacementSyncService.php`
  - Now reads active year from DB when syncing placement.
  - Test: `tests/Feature/Actions/Student/StudentPlacementSyncTest.php`
- `app/Domains/Academic/Student/Services/StudentService.php`
  - Now reads active year from DB inside the promotion transaction.
  - Test: `tests/Feature/Domains/Academic/StudentServiceTest.php`

### 6.2 Pending review

None.
