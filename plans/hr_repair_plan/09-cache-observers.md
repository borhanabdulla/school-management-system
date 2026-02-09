# Phase 9 - Cache + Observers Alignment

HR dashboard cache
- app/Domains/HR/Shared/Services/HRDashboardService.php
  - CACHE_TTL_SHORT comment says 5 minutes but value is 24 hours.
  - Consider consistent TTLs or rely solely on event-driven invalidation.
  - Cache keys not scoped (school/tenant) if multi-school.

Observers
- app/Domains/HR/Staff/Observers/StaffAttendanceObserver.php
  - Directly clears HR dashboard cache (works, but not uniform).
- app/Domains/HR/Leave/Observers/LeaveRequestObserver.php
  - Dispatches LeaveRequestStatusChanged event, but no listener registered.
  - As a result, cache may never clear on leave changes.

Required actions
- Register LeaveRequestStatusChanged listener in AppServiceProvider (or use direct clear).
- Use a single invalidation approach (events or direct cache clear) for HR.
- Scope cache keys if multi-school/tenant or multi-year context exists.

Tests
- After leave approval, HR dashboard stats update without waiting for TTL.
- After attendance update, HR dashboard stats update without waiting for TTL.
