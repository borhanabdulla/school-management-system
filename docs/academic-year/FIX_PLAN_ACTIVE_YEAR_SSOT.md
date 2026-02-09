# Active Academic Year SSOT Roadmap

Scope: active academic year and active term transitions only. No grading, promotion, finance, or student enrollment changes.

Status: Drafted from code review and MCP-backed Laravel guidance.

## 0. Purpose

Guarantee a single source of truth (SSOT) for the active academic year and active term in a read-heavy system:

- Reads may use cache for performance.
- Transitions (activate/close/reopen) must be DB-authoritative with locks.

## 0.1 Read/Write Rule (No Grey Area)

**Read Path (allowed):**
- `school()->activeYearId()` and `school()->activeTermId()` are **read-only** helpers (cached, TTL 24h).

**Write/Transition Path (forbidden):**
- Any state change or sensitive write must **not** read from cached context.
- Use DB-authoritative reads inside `DB::transaction(...)` with `lockForUpdate()`.

**One-line guard for all transitions:**
> Do not use cached academic context inside transitions. Use DB authoritative reads with locks inside transactions.

## 1. Invariants (Non-negotiable)

1) At most one `academic_years` row can be `status = active` at any time.  
2) At most one `terms` row can be `status = active` per academic year.  
3) Any state transition must read the current state directly from the database with locks.

## 2. Evidence (Current Code)

Active year and term are cached for 24h:
- `app/Infrastructure/Context/AcademicContextService.php`
  - `CACHE_TTL = 60 * 60 * 24`
  - `activeYear()` uses `where('status', 'active')`
  - `activeTerm()` uses `where('academic_year_id', activeYearId)` + `where('status', 'active')`

Transitions currently read active year/term from cache:
- `app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php`
  - uses `school()->activeYearId()` inside `DB::transaction(...)`
- `app/Domains/Academic/Term/Actions/ActivateTermAction.php`
  - uses `school()->activeYearId()` and `school()->activeTermId()` inside `DB::transaction(...)`
- `app/Domains/Academic/Term/Actions/ReopenTermAction.php`
  - uses `school()->activeYearId()` inside `DB::transaction(...)`

No DB-level uniqueness guard exists:
- `database/migrations/2025_11_19_184600_create_academic_years_table.php` (status indexed only)
- `database/migrations/2025_11_19_184700_create_terms_table.php` (status indexed only)

## 3. MCP Gate (Laravel Docs Best Practices)

Laravel docs recommend using pessimistic locks inside database transactions for correctness:
- `lockForUpdate()` should be executed within `DB::transaction(...)` to prevent race conditions and stale reads.
  - Source: Laravel docs (pessimistic locking and transactions).

This is the basis for PR-A1 (DB-authoritative transitions).

## 4. Roadmap (PR Series)

- PR-A0: Verified findings report (evidence + risks).
- PR-A1: Active year/term transitions become DB-authoritative (lockForUpdate).
- PR-A2: DB guard for single active year (partial unique index or runtime singleton).
- PR-A3: Active term guard (single active term per year).
- PR-A4: Write-path audit (remove cache usage from write operations).

## 5. Stop Report Triggers

Create a stop report before proceeding if:

- A migration or DB-level constraint is required (PR-A2/PR-A3).
- A write path depends on cache when it should be DB-authoritative.
- Behavior changes are required beyond active year/term transitions.

## 6. Execution Order

PR-A0 -> PR-A1 -> PR-A2 -> PR-A3 -> PR-A4

## 7. Files in This Track

- `docs/academic-year/PR-A0_verified_findings.md`
- `docs/academic-year/PR-A1_active-year-transitions.md`
- `docs/academic-year/PR-A1_mcp_gate.md`
- `docs/academic-year/PR-A2_active-year-db-guard.md`
- `docs/academic-year/PR-A3_active-term-guard.md`
- `docs/academic-year/PR-A4_write-path-audit.md`
