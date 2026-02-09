# 01 Template and Subject Config

Goal
- Ensure grading templates and subject configs are valid, consistent, and term-aware.

Scope
- Template creation, category integrity, subject config binding per grade/subject/term.

Inputs (code references)
- app/Domains/Academic/Grading/Actions/CreateGradingTemplateAction.php
- app/Domains/Academic/Grading/Actions/ApplyTemplateToGradeAction.php
- app/Domains/Academic/Grading/Services/SubjectGradingConfigResolver.php
- app/Domains/Academic/Grading/Services/Validators/*
- app/Livewire/Admin/Grading/GradingSettings.php

Tasks
- [x] Verify template category tree always includes root categories.
- [x] Enforce root weight sum = 100 and final-exam uniqueness.
- [x] Confirm template category flags: is_final_exam, mapping_type, pass_required.
- [x] Ensure SubjectGradingConfig is term-scoped and grade-scoped.
- [x] Ensure invalid configs are blocked by GradingConfigHealthChecker.
- [x] Define canonical defaults for max_score and pass_score.
- [x] Document the template -> subject config binding path in code.

Acceptance Criteria
- Invalid templates are rejected before any grade entry.
- SubjectGradingConfig is required for every course offering in the term.
- Health checker flags missing or invalid templates/configs.

Verification
- Run GradingHealthGate for an active term and confirm missing/invalid counts.
