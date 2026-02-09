# 11 Database Practices (Migrations)

Goal
- Remove driver-specific branching and standardize schema changes using Laravel Schema Builder.

Scope
- Migrations that manipulate indexes or constraints with DB driver conditionals.

Findings (verified)
- monthly_grades unique index migration used driver-specific DROP INDEX logic.
- academic_years partial unique index migration skipped sqlite.
- dashboard overdue-day SQL used driver branching.

Tasks
- [x] Replace driver-specific DROP INDEX logic with Schema::dropUnique.
- [x] Remove sqlite bypass in academic_years partial index migration.
- [x] Audit migrations for getDriverName/driver branching (none remaining).
- [ ] Verify academic_years partial index behavior under sqlite tests and decide fallback if it fails.
- [x] Remove driver branching from overdue-days SQL expression (use sqlite expression).

Acceptance Criteria
- Migrations do not branch by database driver.
- Index changes use Schema builder where supported.

Files Updated
- database/migrations/2026_02_10_000001_update_monthly_grades_unique_index.php
- database/migrations/2026_01_31_154655_add_unique_active_year_constraint_to_academic_years.php
- app/Domains/Shared/Services/Dashboard/Concerns/HasDashboardQueries.php
