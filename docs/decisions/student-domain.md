# Student Domain Decision Log

This document captures the decisions finalized during PR6/PR7 so future
changes stay consistent with the domain contracts.

## Admission Number Policy
- Source: `AdmissionNumberService::generateFor`.
- Format: `<start_year><padded_student_id>`, where `start_year` is the active
  academic year start date (YYYY) and student id is left-padded to 4 digits.
- Requirement: student is created first; `TMP-` placeholder must not remain
  after registration.

## Academic Year Deletion Policy
- Deletion is allowed only for pending years with no related data.
- If the year has terms, sections, students, fee structures, or school events,
  deletion is blocked with a domain exception.
- This aligns with FK safety and avoids cascading deletes across domains.

## Attendance Year Context
- Attendance records carry `academic_year_id`.
- Timetables do not require an `academic_year_id` column; year context is
  derived from related records (class section or course offering).
- Auto-filling academic year is disabled for `Timetable` to match schema.

## User Model Compatibility Bridge
- The system user model is `App\Domains\Shared\Models\User` (per `auth.php`).
- A bridge class `App\Models\User` extends the shared model to maintain
  compatibility with existing tests and controllers.

## Student Lookup Cache Versioning
- Directory stats and general stats cache keys include a version suffix.
- `StudentLookupService::clearCache` bumps the version to invalidate all
  hashed keys without tags.
