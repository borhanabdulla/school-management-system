# 07 Term Results

Goal
- Ensure term results use validated configs and correct StudentMark inputs.

Scope
- CalculateTermGradesAction, TermResult, TermResultFailure.

Inputs (code references)
- app/Domains/Academic/Grading/Actions/CalculateTermGradesAction.php
- app/Domains/Academic/Results/Models/TermResult.php
- app/Domains/Academic/Results/Models/TermResultFailure.php
- app/Domains/Academic/Grading/Services/SubjectScorePolicyResolver.php

Tasks
- [x] Gate CalculateTermGradesAction with GradingHealthGate.
- [ ] Ensure StudentMark query is term-scoped and category relations are loaded.
- [ ] Confirm pass/fail decision logic (pass_score + threshold failures).
- [ ] Ensure TermResultFailure records all threshold failures.

Acceptance Criteria
- Term calculation is blocked when config health is invalid.
- Term results are reproducible from StudentMark + FinalExamScoreResolver.

Verification
- Run term calculation with missing mappings and confirm it aborts.
