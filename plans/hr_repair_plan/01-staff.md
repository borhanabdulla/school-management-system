# Phase 1 - Staff Domain

Scope
- Staff model parity, safe delete, and delete orchestration.
- Actions consistency and guardrails.

Must-fix mismatches
- app/Domains/HR/Staff/Models/Staff.php
  - Casts use join_date but DB has joining_date (fix cast).
  - Remove birth_date cast if the column does not exist.
  - protectedRelations references attendances but the relation is staffAttendances.
- database/migrations/2025_11_19_185200_create_staff_table.php
  - Verify columns align with model (joining_date, etc.).

Delete policy (critical)
- app/Domains/HR/Staff/Actions/DeleteStaffAction.php
  - Must not delete teacher directly.
  - Orchestrate deletion via DeleteTeacherAction or block if Teacher cannot be deleted.
  - Use transaction to avoid partial deletes.

Safe delete guard
- app/Infrastructure/Traits/HandlesSafeDelete.php
- app/Domains/HR/Staff/Models/Staff.php
  - Ensure protectedRelations lists actual relation method names.
  - Optional: add alias relation attendances() -> staffAttendances().

Actions and DTO boundaries
- app/Domains/HR/Staff/Actions/CreateStaffAction.php
- app/Domains/HR/Staff/Actions/UpdateStaffAction.php
  - Use DTOs only; no UI/Form types in domain.
  - Validate uniqueness via DB constraints and guard in action.

DB constraints
- Add unique index for employee_number within school/tenant scope if applicable.
- Add FK constraints for user_id and school/tenant scope if applicable.

Tests (minimum)
- Create staff with joining_date -> DB matches and cast returns date.
- Delete staff with attendances -> blocked by safe delete.
- Delete staff with teacher who has substitutions -> blocked by teacher policy.
- Delete staff with teacher and no dependencies -> allowed via DeleteTeacherAction.
- Unique employee_number enforced.

Deliverable
- No silent mismatches between DB and model.
- Staff deletion cannot bypass teacher rules.
- Safe delete is effective for attendance and other linked records.
