# 00X. Student Domain Policies & Architecture Decisions

Date: 2026-01-27
Status: Accepted

## Context
The Student Domain is a core component of the School Dashboard. Recent refactoring (PR6, PR7, PR8) has established strict patterns for data access, caching, and consistency. This document records the architectural decisions and policies that must be adhered to.

## Decisions

### 1. Admission Number Generation
**Policy:** Admission numbers must be unique, sequential, and prefixed with the admission year.
**Implementation:**
- **Format:** `YYYY-ID` (e.g., `2024-0001`).
- **Generation:** Handled by `AdmissionNumberService`.
- **Concurrency:** The service must handle concurrent registration requests safely (e.g., using atomic locks or retries, though currently relying on unique constraints and retries).
- **Temporary IDs:** Temporary IDs (e.g., `TMP-UUID`) are allowed during the initial transaction but must be replaced with the final format before the transaction commits.

### 2. Academic Year Deletion
**Policy:** Academic Years cannot be deleted if they contain auxiliary data (Terms, Sections, Enrollments, Financial Records).
**Rationale:** Preserving historical data integrity is paramount. Deleting a year with associated data would cause cascading data loss or orphaned records.
**Implementation:**
- `DeleteAcademicYearAction` enforces this check.
- Attempting to delete a year with data throws a `YearNotDeletableException`.
- Users must manually archive or clean up data if they truly intend to delete a year (rare operation).

### 3. Attendance & Timetable Schema
**Policy:** The `timetables` table does NOT require a direct `academic_year_id` column.
**Rationale:** Attendance is inherently linked to an Academic Year through the `ClassSection` or `CourseOffering`. Adding a redundant column creates a risk of data inconsistency.
**Implementation:**
- **Linkage:** `Attendance` -> `ClassSection` -> `AcademicYear`.
- **Testing:** Tests must not assume `timetables.academic_year_id` exists. They should set up the necessary relationships (Year -> Section -> Timetable) to establish the context.

### 4. User Model Compatibility Bridge
**Policy:** The system uses a domain-specific User model (`App\Domains\Shared\Models\User`), but legacy tests and external packages may expect `App\Models\User`.
**Implementation:**
- A bridge class `App\Models\User` extends `App\Domains\Shared\Models\User`.
- This ensures compatibility without duplicating logic or breaking the domain structure.
- New code should prefer the domain-specific model, but the bridge remains for backward compatibility.

### 5. Student Data Access (Single Source of Truth)
**Policy:** All read operations for Student data (Directory, Profile, Stats) must go through `StudentLookupService`.
**Rationale:** Centralizes caching logic, eager loading optimization, and filtering rules.
**Implementation:**
- **Directory:** `StudentLookupService::paginateForDirectory` (uses `StudentDirectoryFilterData`).
- **Show:** `StudentLookupService::findForShow`.
- **Stats:** `StudentLookupService::getDirectoryStats` (cached with versioning).
- **Direct Queries:** `Student::query()`, `Student::where()`, etc., are FORBIDDEN in UI components (Livewire/Controllers).

### 6. Query Builder Usage (No Inline Domain Rules)
**Policy:** Do NOT use inline string conditions (e.g., `where('status', 'active')`) inside service query builders.
**Rationale:** This leaks business logic into queries, violates the Single Source of Truth, and creates silent bugs if rules change.
**Implementation:**
- Use **Scopes** (e.g., `scopeActiveForYear`) or **Enums** to encapsulate logic.
- Prefer returning `Collection` of IDs over open `Builder` instances to prevent misuse.

## Consequences
- **Strictness:** Developers must use the provided services and actions. Direct model manipulation is restricted.
- **Consistency:** Data integrity is guaranteed by these policies.
- **Maintainability:** Refactoring and updates are safer due to centralized logic.
