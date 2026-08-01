## Decision: Monthly Category Matching and Attendance Term Key

Status: Proposed (requires stakeholder sign-off)
Date: 2026-01-30
Owner: Grading Domain
Scope: Monthly aggregation + Attendance sync guardrails
Non-negotiables: SSOT, Term Isolation, No silent defaults, No DB change without Stop Report

### Context

We have two correctness risks affecting term isolation and SSOT:

1) Monthly aggregation matches categories by name, which is fragile if names are editable or duplicated.
2) Attendance → StudentMark sync uses an update key without term_id, which is safe only if course_offering_id is term-unique.

Evidence (exact locations):
- Name matching:
  - app/Domains/Academic/Grading/Actions/AggregateGradebookToTemplateMarksAction.php
  - app/Domains/Academic/Grading/Services/GradeSyncService.php:418
- Attendance update key lacks term_id:
  - app/Domains/Academic/Grading/Services/GradeSyncService.php:310

### MCP Gate (Required before deciding)

Goal: prevent decisions based on assumptions.

Required proofs:
- TemplateCategory stable key exists? (mapping_type or a dedicated flag)
- course_offering uniqueness relative to term exists? (constraint or invariant)
- Monthly category name is editable in admin UI? (code evidence)

Deliverable:
- Record evidence in a facts file (see Appendix).

### Decision Needed (Two independent decisions)

#### 1) Monthly category matching

Option A (Fix, no DB changes)
- Allowed only if a stable key already exists and is used consistently.
- Match by stable key, never by name.
- Fail loud if zero or multiple candidates exist.
- No fallback to "first match".

Required tests:
- Renaming the category does not break aggregation.
- Duplicate candidates triggers a stop (no silent writes).

Option B (Stop Report)
- If names are editable and no stable key exists, do not patch.
- Propose schema change: store template_category_id on monthly_grades.
- Include migration/backfill strategy.

#### 2) Attendance sync term isolation

Option A (Document invariant, no code change)
- Allowed only if course_offering_id is guaranteed unique per term.
- Document the invariant and add a guard test to prevent regressions.

Option B (Stop Report / DB change)
- If course_offering_id can repeat across terms, the key must include term_id.
- Requires migration + backfill strategy.

### Temporary Safety Rule (Until decision is signed)

Keep current behavior, but:
- If monthly category matching returns zero or multiple candidates:
  - record warning metadata
  - do not write aggregation results (fail fast for that offering)

### Sign-off Checklist

Monthly categories:
- [ ] Is renaming allowed in UI? (Yes/No with evidence)
- [ ] Stable key exists? (Yes/No with evidence)

Attendance sync:
- [ ] course_offering uniqueness invariant exists? (Yes/No with evidence)

### Follow-ups (After sign-off)

- If Option A: implement minimal fix + tests.
- If Option B: write Stop Report + plan schema change.

### Related

- docs/grading/stop_report_monthly_category_key.md
- docs/grading/stop_report_attendance_term_key.md
- docs/grading/stop_report_student_marks_term_key.md

### Appendix: MCP Facts Template

Create: docs/grading/PR?_mcp_facts.md

Include:
- Table/column evidence (DatabaseSchema output or code references)
- UI evidence (form field for monthly category name)
- Constraints/invariants for course_offering and term
