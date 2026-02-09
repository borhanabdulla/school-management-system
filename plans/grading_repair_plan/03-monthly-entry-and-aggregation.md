# 03 Monthly Entry and Aggregation

Goal
- Ensure monthly grades are written through guarded actions and aggregate deterministically into StudentMark.

Scope
- SmartGradeBook write path, MonthlyGrade persistence, MonthlyGradeSaved event, aggregation action.

Inputs (code references)
- app/Livewire/Teacher/Grading/SmartGradeBook.php
- app/Domains/Academic/Grading/Models/MonthlyGrade.php
- app/Domains/Academic/Grading/Events/MonthlyGradeSaved.php
- app/Domains/Academic/Grading/Listeners/SyncMonthlyToStudentMark.php
- app/Domains/Academic/Grading/Actions/AggregateGradebookToTemplateMarksAction.php
- app/Domains/Academic/Student/Models/StudentMark.php

Tasks
- [x] Replace direct MonthlyGrade writes in UI with a dedicated Action (RecordMonthlyGradeAction).
- [x] Enforce AcademicWriteGuard before saving MonthlyGrade.
- [x] Ensure category_key is always set and used for lookup.
- [ ] Make aggregation idempotent for repeated saves.
- [ ] Ensure StudentMark writes include term_id and academic_year_id.
- [ ] Document the monthly -> aggregate -> StudentMark flow in code comments or docs.

Acceptance Criteria
- MonthlyGrade cannot be written after term completion.
- MonthlyGradeSaved always triggers aggregation or a logged failure.
- StudentMark reflects term-to-date aggregated values for mapped categories.

Verification
- Save a monthly grade twice and confirm StudentMark remains correct and not duplicated.
