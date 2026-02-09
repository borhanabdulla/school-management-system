# Phase 2 - Teacher + Substitution

Scope
- Teacher model relations/scopes, teacher delete policy, and substitution creation rules.

Teacher model mismatches
- app/Domains/HR/Teacher/Models/Teacher.php
  - scopeAvailableAt uses relations substitutions/timetables that do not exist.
  - Fix by using substitutionsAsOriginal / substitutionsAsSubstitute and timetableSessions.
  - Optional: add alias relations if needed for backward compatibility.

Layering violation
- app/Domains/HR/Teacher/Services/TeacherService.php
  - Must not depend on Livewire Form objects.
  - Accept DTO only (e.g., TeacherOnboardingData) or arrays.

Teacher lookup caching
- app/Domains/HR/Teacher/Services/TeacherLookupService.php
  - Cache keys are unscoped (missing school/tenant/year/term where applicable).
  - rememberForever used for teacher_id_user mapping; must be invalidated on changes.

Substitution domain
- app/Domains/HR/Substitution/Services/SubstitutionService.php
  - Uses auth() inside domain service; pass created_by explicitly.
  - Missing concurrency protection (duplicate substitutes for same timetable/date).
- app/Domains/HR/Substitution/Observers/SubstitutionObserver.php
  - Cache keys unscoped; add school/tenant where applicable.

Hard rules to implement
- Substitution creation must be in an Action with transaction and duplicate guard.
- Enforce uniqueness (timetable_id + date) if policy allows only one substitution.

Tests
- scopeAvailableAt excludes teachers with sessions/substitutions on the same slot.
- Deleting teacher with substitutions/course offerings fails with clear exception.
- Assigning substitution twice for same timetable/date fails.
- Cache invalidation works after substitution changes.
