# 06 Final Exam and Thresholds

Goal
- Make final exam source and threshold rules consistent and auditable.

Scope
- Final exam score resolution and category thresholds in grading.

Inputs (code references)
- app/Domains/Academic/Results/Services/FinalExamScoreResolver.php
- app/Domains/Academic/Grading/Services/GradingCalculatorService.php
- app/Domains/Academic/Grading/Models/TemplateCategory.php

Tasks
- [x] Verify final exam category flag (is_final_exam) is set in templates.
- [x] Ensure FinalExamScoreResolver picks the correct source order.
- [x] Ensure pass_thresholds are validated (0..100) and enforced.
- [x] Ensure final exam max_raw_score is required when is_final_exam.

Acceptance Criteria
- A missing final exam max score is flagged as invalid.
- Threshold failures are recorded in TermResultFailure.

Verification
- Create a template with final exam category and confirm threshold enforcement.
