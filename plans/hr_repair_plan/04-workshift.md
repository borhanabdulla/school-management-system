# Phase 4 - WorkShift

Scope
- WorkShift as policy reference; ensure edit/delete guards and cache correctness.

Model and policies
- app/Domains/HR/WorkShift/Models/WorkShift.php
  - Decide if overnight shifts are supported.
  - If not supported, keep end_time > start_time validation in action and UI.
  - If supported, add is_overnight and update calculations.

Actions
- app/Domains/HR/WorkShift/Actions/CreateWorkShiftAction.php
- app/Domains/HR/WorkShift/Actions/UpdateWorkShiftAction.php
- app/Domains/HR/WorkShift/Actions/DeleteWorkShiftAction.php
  - Prevent delete when shift has staff/attendance usage.
  - Consider soft delete or is_active false.

Cache/Lookup
- app/Domains/HR/WorkShift/Services/WorkShiftLookupService.php
  - Cache keys must be scoped (school/tenant/year if applicable).
  - Ensure invalidation on create/update/delete.

UI
- app/Livewire/HR/WorkShiftManager.php
  - Already uses actions; keep as read/write orchestrator only.

Tests
- Shift cannot be deleted if linked to staff/attendance.
- Lookup returns updated shift after update (cache invalidation works).
