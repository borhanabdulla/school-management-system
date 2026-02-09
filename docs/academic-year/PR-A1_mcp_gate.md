# PR-A1 MCP Gate Report

Scope: Active year and active term transitions (PR-A1).

## Laravel Docs Evidence (MCP Search)

1) Pessimistic Locking
- `lockForUpdate()` acquires a "for update" lock and prevents the selected rows from being modified.
- Laravel recommends wrapping pessimistic locks inside `DB::transaction(...)` so locks are held until commit or rollback.

Source: Laravel documentation (Query Builder > Pessimistic Locking).

## Decision

All active year / active term transitions must:
- run inside `DB::transaction(...)`, and
- use `lockForUpdate()` for the rows they rely on.

This satisfies the correctness requirement for read-heavy but write-sensitive flows.
