# 10 Tests

Goal
- Lock correctness with automated tests for the full grading pipeline.

Scope
- Feature and integration tests around monthly grades, attendance, homework, term and annual results.

Suggested Tests
- [x] MonthlyGrade save triggers StudentMark aggregation (term-scoped).
- [x] Attendance sync blocked after term completion.
- [x] Attendance month selection uses term_id.
- [x] Homework sync uses documented weight rule.
- [x] CalculateTermGradesAction blocked when health gate fails.
- [x] TermResultFailure records threshold failures.
- [x] AnnualResult aggregates term results with weights.
- [x] Queue failure surfaces warning in UI.

Verification
- Run focused test filters once implemented.
