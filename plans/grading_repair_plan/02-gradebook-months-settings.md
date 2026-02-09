# 02 Gradebook Months and Settings

Goal
- Make gradebook months and monthly categories stable, term-aware, and validated.

Scope
- GradebookMonth generation, GradebookSettings monthly categories, monthly mapping integrity.

Inputs (code references)
- app/Domains/Academic/Grading/Services/GradebookMonthService.php
- app/Domains/Academic/Grading/Models/GradebookMonth.php
- app/Domains/Academic/Grading/Models/GradebookSettings.php
- app/Domains/Academic/Grading/Models/MonthlyCategoryMapping.php
- app/Domains/Academic/Grading/Services/Validators/MonthlyCategoryMappingValidator.php

Tasks
- [x] Ensure GradebookMonth generation is blocked after term closure.
- [x] Ensure GradebookMonth queries always scope by term_id.
- [x] Normalize monthly category keys and prevent duplicates.
- [x] Validate monthly categories are non-empty.
- [x] Ensure monthly category mapping exists for every category_key.
- [x] Define the missing-months policy defaults per term.

Acceptance Criteria
- No GradebookMonth created/updated after term completion.
- Monthly category keys are stable and unique per year.
- Missing monthly mappings are flagged by the health gate.

Verification
- Create a term, generate months, confirm term_id and academic_year_id are consistent.
