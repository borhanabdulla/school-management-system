# 05 Homework Sync

Goal
- Ensure homework grading maps correctly to StudentMark with a clear weight policy.

Scope
- HomeworkSubmissionObserver and GradeSyncService homework path.

Inputs (code references)
- app/Domains/Academic/Homework/Observers/HomeworkSubmissionObserver.php
- app/Domains/Academic/Grading/Services/GradeSyncService.php
- app/Domains/Academic/Grading/Models/Assessment.php
- app/Domains/Academic/Student/Models/StudentMark.php

Tasks
- [x] Define and document the weight source (assessment.weight vs assessment.max_score).
- [x] Enforce AcademicWriteGuard before StudentMark write.
- [x] Handle homework submissions without assessment_id (log or skip reason).
- [x] Ensure term_id and academic_year_id are always set in StudentMark.
- [x] Avoid re-summing when only feedback changes (score not dirty).

Acceptance Criteria
- Homework grading always uses a single, documented weight rule.
- Missing assessment linkage is visible (log or UI warning).

Verification
- Grade linked homework and confirm StudentMark matches expected weight rule.
