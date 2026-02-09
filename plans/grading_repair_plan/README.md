# Grading Repair Plan (Fresh)

Purpose: replace all prior grading reports with a single, task-based repair plan.
This plan is organized by domain segments with explicit tasks and acceptance criteria.

Principles
- SSOT: TermResult/AnnualResult are the only sources for final decisions.
- Term/year awareness: every write is scoped by term_id and academic_year_id.
- Guards first: no writes after term/year closure.
- UI never writes aggregate truth directly; writes go through Actions/Services.
- Queue reliability is part of correctness.

Flow Map (reference only)
- Admin setup: Grading settings UI -> Actions/Services -> template + subject config
- Monthly entry: SmartGradeBook -> MonthlyGrade -> MonthlyGradeSaved -> SyncMonthlyToStudentMark -> AggregateGradebookToTemplateMarksAction -> StudentMark
- Attendance: AttendanceBatchSaved -> SyncAttendanceToMonthlyGrade -> GradeSyncService -> MonthlyGrade -> AggregateGradebookToTemplateMarksAction -> StudentMark
- Homework: HomeworkSubmissionObserver -> GradeSyncService -> StudentMark
- Term result: CalculateTermGradesAction -> TermResult (+ failures)
- Annual result: AnnualResultService -> AnnualResult

Task Docs
0) 00-findings.md
1) 01-template-and-config.md
2) 02-gradebook-months-settings.md
3) 03-monthly-entry-and-aggregation.md
4) 04-attendance-sync.md
5) 05-homework-sync.md
6) 06-final-exam-and-thresholds.md
7) 07-term-results.md
8) 08-annual-results.md
9) 09-guards-audit-queue.md
10) 10-tests.md
11) 11-database-practices.md

Execution Order
- Start with 09 (guards) + 03 (monthly entry) to close write bypasses.
- Then 02, 04, 05 to stabilize data flow.
- Then 06, 07, 08 to stabilize result computation.
- Finish with 10 (tests) and re-run health checks.

Progress Snapshot
- Completed: template/config validations; gradebook month/mapping validation; monthly write guard + RecordMonthlyGradeAction; attendance term scoping + guard; term health gate; homework weight policy; final exam/threshold validation; annual result rules; grading queue heartbeat + UI warning; GradebookMonthService guard; DB migrations standardized (no driver branching); key grading tests added.
- Pending: sqlite partial index verification; run full test suite.
