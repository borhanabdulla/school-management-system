# 08 Annual Results

Goal
- Aggregate term results into annual outcomes using consistent weights and decisions.

Scope
- AnnualResultService and AnnualResult model writes.

Inputs (code references)
- app/Domains/Academic/Promotion/Services/AnnualResultService.php
- app/Domains/Academic/Results/Models/AnnualResult.php
- app/Domains/Academic/Results/Models/TermResult.php

Tasks
- [x] Ensure term weights come from SystemSetting and are normalized.
- [x] Confirm grade_id source is enrollment-based (not current_grade_id).
- [x] Ensure annual results are blocked when year is closed.
- [x] Verify failed subject logic uses term results only.

Acceptance Criteria
- AnnualResult uses term-weighted totals and consistent pass/fail rules.
- AnnualResult is reproducible from TermResult data.

Verification
- Aggregate two terms with weights and verify annual_total matches expected value.
