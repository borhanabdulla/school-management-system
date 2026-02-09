# Timetable Data Audit Documentation

## Overview

This document describes the data audit process for the timetable system. The audit is performed via Laravel Artisan commands and does not leave permanent artifacts in the database schema.

## Best Practice Note

**Why no CREATE VIEW in migration?**

The `CREATE VIEW` statement was intentionally removed from the migration because:
- Views are permanent database objects, not temporary diagnostics
- Production environments may restrict CREATE VIEW permissions
- View syntax varies between database engines (MySQL/MariaDB/PostgreSQL)
- Audit queries should be transient, not permanent schema changes

The same diagnostic query is now executed via `timetable:audit` command as a SELECT statement.

## Audit Commands

### 1. Run Comprehensive Audit

```bash
php artisan timetable:audit
```

This command performs:
- **Phase 1**: Orphan record detection (time_slots, timetables, substitutions)
- **Phase 2**: Data integrity checks (duplicates, null fields, future dates)
- **Phase 3**: Phase 1 readiness check (column existence, backfill scope)
- **Phase 1.5**: Attendance-timetable mapping statistics

### 2. Backfill timetable_id

After migration, populate the `timetable_id` column:

```bash
# Dry run first
php artisan timetable:backfill-attendance-timetable-id --dry-run

# Execute backfill
php artisan timetable:backfill-attendance-timetable-id

# With smaller chunks
php artisan timetable:backfill-attendance-timetable-id --chunk=500
```

## Audit Phases

### Phase 1: Orphan Records

Checks for records referencing non-existent parent records:

| Check | Description | Severity |
|-------|-------------|----------|
| `attendance_time_slot` | Attendance records with invalid `time_slot_id` | Error |
| `substitution_timetable` | Substitution records with invalid `timetable_id` | Error |
| `timetable_template` | Timetable entries with invalid `timetable_template_id` | Warning |
| `timetable_time_slot` | Timetable entries with invalid `time_slot_id` | Warning |
| `timetable_section` | Timetable entries with invalid `class_section_id` | Error |
| `timetable_term` | Timetable entries with invalid `term_id` | Error |

### Phase 2: Data Integrity

Validates data quality and consistency:

| Check | Description | Severity |
|-------|-------------|----------|
| `duplicate_attendance` | Multiple records for same student/date/time_slot | Error |
| `null_required` | Records with NULL in required fields | Warning |
| `future_dates` | Attendance records with future dates | Info |
| `timetable_null_fields` | Timetable entries missing required fields | Warning |

### Phase 3: Phase 1 Readiness

Validates prerequisites for Phase 1 (migration):

| Check | Description | Severity |
|-------|-------------|----------|
| `has_timetable_id` | Whether `timetable_id` column exists | Info |
| `backfill_scope` | Number of records needing backfill | Warning |
| `foreign_key` | Whether FK constraint exists | Warning |

### Phase 1.5: Attendance-Timetable Mapping

Shows the relationship between attendances and timetables:

```sql
-- Equivalent query (executed as SELECT, not View)
SELECT 
    COUNT(*) as total_attendances,
    SUM(CASE WHEN timetable_id IS NOT NULL THEN 1 ELSE 0 END) as linked,
    SUM(CASE WHEN timetable_id IS NULL THEN 1 ELSE 0 END) as unlinked
FROM attendances
```

## Expected Results

### After Migration (before backfill)

```
✅ Passed: X
⚠️  Warnings: Y
❌ Errors: Z

📊 Attendance-Timetable Mapping Stats:
   - Total attendance records: N
   - Linked to timetable: 0
   - Unlinked (need backfill): N
   - Link percentage: 0%
```

### After Backfill

```
📊 Attendance-Timetable Mapping Stats:
   - Total attendance records: N
   - Linked to timetable: N
   - Unlinked (need backfill): 0
   - Link percentage: 100%
```

## Troubleshooting

### "Cannot proceed: Found records with multiple timetable matches"

This indicates duplicate data where one attendance could match multiple timetables. Resolution:

1. Identify conflicting records:
```sql
SELECT a.id, a.student_id, a.date, a.time_slot_id, COUNT(t.id) as match_count
FROM attendances a
JOIN timetables t ON t.class_section_id = a.class_section_id 
    AND t.time_slot_id = a.time_slot_id 
    AND t.term_id = a.term_id
GROUP BY a.id
HAVING COUNT(t.id) > 1
```

2. Manually resolve duplicates by updating timetable assignments

### "Ambiguous matches" during backfill

Same as above - multiple timetables match the same attendance criteria.

## Related Files

- **Migration**: `database/migrations/2026_02_04_225114_add_timetable_id_to_attendances.php`
- **Audit Command**: `app/Console/Commands/DataAudit/TimetableDataAuditCommand.php`
- **Backfill Command**: `app/Console/Commands/DataAudit/BackfillAttendanceTimetableIdCommand.php`
- **Exception Classes**: 
  - `CannotDeleteTimeSlotsInUseException`
  - `CannotDeleteTimetableWithAttendanceException`

## See Also

- [EXECUTION_PLAN.md](EXECUTION_PLAN.md) - Full implementation plan
- [DEEP_REVIEW.md](DEEP_REVIEW.md) - Detailed analysis of issues found
