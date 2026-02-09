# Timetable Domain - Comprehensive Architectural Review

## 📋 Table of Contents

1. [Executive Summary](#executive-summary)
2. [Data Flow Architecture](#data-flow-architecture)
3. [Code Cleanliness Assessment](#code-cleanliness-assessment)
4. [Architectural Violations](#architectural-violations)
5. [Laravel Best Practices Compliance](#laravel-best-practices-compliance)
6. [Detailed Layer Analysis](#detailed-layer-analysis)
7. [Recommendations](#recommendations)

---

## Executive Summary

This document provides a **comprehensive architectural review** of the Timetable domain, following Laravel best practices and clean code principles.

### Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────┐
│                         UI Layer                                      │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Livewire Component                                       │   │
│  │  - State Management                                      │   │
│  │  - User Interaction                                      │   │
│  │  - Event Dispatching                                      │   │
│  └────────────────────────┬──────────────────────────────────┘   │
│                           │                                        │
│                           ▼                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Livewire Form Object (Optional)                           │   │
│  │  - Validation Rules                                        │   │
│  │  - Form State                                             │   │
│  │  - Type Conversion                                        │   │
│  └────────────────────────┬──────────────────────────────────┘   │
└───────────────────────────┼────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Application Layer                                │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Action Classes                                            │   │
│  │  - Single Responsibility                                  │   │
│  │  - Business Logic                                         │   │
│  │  - Database Transactions                                  │   │
│  │  - Domain Rules & Guards                                  │   │
│  └────────────────────────┬──────────────────────────────────┘   │
│                           │                                        │
│                           ▼                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Service Classes (Read-Only)                               │   │
│  │  - Complex Queries                                        │   │
│  │  - Lookup Operations                                      │   │
│  │  - Aggregations                                           │   │
│  └────────────────────────┬──────────────────────────────────┘   │
└───────────────────────────┼────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      Domain Layer                                      │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Data Transfer Objects (DTOs)                               │   │
│  │  - Type Safety                                            │   │
│  │  - Immutability                                           │   │
│  │  - Validation at Boundaries                                │   │
│  └────────────────────────┬──────────────────────────────────┘   │
│                           │                                        │
│                           ▼                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Eloquent Models                                           │   │
│  │  - Relationships                                          │   │
│  │  - Scopes                                                 │   │
│  │  - Accessors/Mutators                                    │   │
│  │  - Traits                                                 │   │
│  └────────────────────────┬──────────────────────────────────┘   │
│                           │                                        │
│                           ▼                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Enums                                                    │   │
│  │  - Domain States                                          │   │
│  │  - Type Safety                                            │   │
│  └────────────────────────┬──────────────────────────────────┘   │
└───────────────────────────┼────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                    Infrastructure Layer                               │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Events                                                   │   │
│  │  - Domain Events                                          │   │
│  │  - Event Listeners                                        │   │
│  └────────────────────────┬──────────────────────────────────┘   │
│                           │                                        │
│                           ▼                                        │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │  Exceptions                                               │   │
│  │  - Domain Exceptions                                      │   │
│  │  - HTTP Exceptions                                        │   │
│  └────────────────────────┬──────────────────────────────────┘   │
└───────────────────────────┼────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────────────┐
│                       Database Layer                                  │
│  - MySQL/PostgreSQL                                                  │
│  - Foreign Keys                                                       │
│  - Indexes                                                           │
│  - Transactions                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

### Current Assessment

| Layer | Status | Score |
|-------|--------|-------|
| UI (Livewire) | ⚠️ Needs Work | 7/10 |
| Forms | ✅ Good | 9/10 |
| Actions | ✅ Good | 8/10 |
| Services | ⚠️ Needs Work | 6/10 |
| Data (DTOs) | ✅ Excellent | 9/10 |
| Models | ✅ Good | 8/10 |
| Events | ⚠️ Underutilized | 5/10 |
| Exceptions | ⚠️ Inconsistent | 6/10 |

---

## Data Flow Architecture

### Standard Flow: User Creates Session

```
1. User fills form in UI
   ↓
2. Livewire validates input (inline rules)
   ↓
3. Form object converts to DTO (TimetableTemplateData)
   ↓
4. Action receives DTO (type-safe)
   ↓
5. Action validates business rules (guards)
   ↓
6. Action executes in DB transaction
   ↓
7. Model saves to database
   ↓
8. Events dispatched
   ↓
9. Cache invalidated
   ↓
10. UI refreshed
```

### Issue: Direct DB Queries in Livewire

**Location:** `app/Livewire/Academic/TimetableBuilder.php:486-488`

```php
// ❌ PROBLEM: Bypasses entire architecture
Timetable::where('class_section_id', $this->selectedSectionId)
    ->where('time_slot_id', $slotId)
    ->where('term_id', $this->selectedTermId)
    ->delete();
```

**Impact:**
- No business rule validation
- No `HandlesSafeDelete` trait execution
- No model events fired
- No audit logging
- No transaction safety

**Recommendation:**
```php
// ✅ SOLUTION: Use Action
app(DeleteTimetableEntryAction::class)->execute(
    $timetable->id
);

// OR: Use Model properly
$timetable = Timetable::where(...)->first();
if ($timetable) {
    $timetable->delete(); // Triggers all hooks
}
```

---

## Detailed Layer Analysis

### 1. Livewire Components (Status: ⚠️ Needs Work)

#### ✅ Good Practices Found

1. **Separation of Concerns**
   - Component handles UI state
   - Delegates to Actions for business logic

2. **Validation**
   - Uses Livewire's `$rules` for inline validation
   - Custom validation messages in Arabic

3. **Computed Properties**
   - Uses `#[Computed]` attribute for cached data

#### ❌ Issues Found

1. **Unused Imports**
   ```php
   // TimetableBuilder.php:5-16
   use App\Domains\Shared\Enums\DayOfWeek;           // ❌ Not used
   use App\Domains\Academic\Timetable\Enums\TimeSlotType; // ❌ Not used
   use App\Domains\Academic\AcademicYear\Models\AcademicYear; // ❌ Not used
   use App\Domains\Academic\Grade\Models\Grade;     // ❌ Not used
   use App\Domains\Academic\Subject\Models\Subject;  // ❌ Not used
   use App\Domains\HR\Teacher\Models\Teacher;      // ❌ Not used
   ```

2. **Direct DB Queries**
   ```php
   // Line 486-488
   Timetable::where(...)->delete();
   ```

3. **Mixed Language Comments**
   ```php
   // التحقق من وجود الحصة والشعبة
   // ✅ PR0 Backend Guard
   // ✅ Ensure Offering belongs to the same term
   ```

4. **Magic Strings**
   ```php
   abort(403, 'Modification of non-active terms is restricted.');
   ```

#### Recommendations

```php
// ✅ Recommended Pattern
class TimetableBuilder extends Component
{
    // Use form object for complex data
    protected TimetableTemplateForm $form;

    // Use constants for messages
    public const ERROR_NON_ACTIVE_TERM = 'attendance.cannot_modify_non_active_term';

    // Delegate to actions
    public function deleteSession(int $slotId): void
    {
        try {
            app(DeleteTimetableEntryAction::class)->execute($slotId);
            $this->dispatch('notify', message: __('attendance.session_deleted'), type: 'success');
        } catch (CannotDeleteException $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }
}
```

---

### 2. Livewire Forms (Status: ✅ Excellent)

#### Structure Analysis

```
TimetableTemplateForm
├── Properties (snake_case for Blade compatibility)
│   ├── Basic: name, description, status
│   ├── Relations: grade_ids
│   └── Complex: slots (flattened array)
├── Validation: rules(), messages()
├── State: setModel(), resetForm()
├── Conversion: toData(), getGeneratorConfig()
└── Helpers: getSlotsForDay(), setSlotsForDay()
```

#### ✅ Good Practices

1. **Proper Property Naming**
   - Uses `snake_case` for Blade compatibility
   - Clear separation of concerns

2. **Type Conversion**
   - Converts to DTO for business layer
   - Handles nested data structures

3. **Validation Rules**
   - Comprehensive validation
   - Custom error messages

4. **Generator Configuration**
   - Separates concerns for smart generator

#### Recommendations

None - this layer is well-structured.

---

### 3. Data Transfer Objects (DTOs) (Status: ✅ Excellent)

#### Structure Analysis

```
BaseData (Abstract)
├── fromArray(array $data): static
├── fromRequest(Request $request): static
├── fromModel(Model $model): static
├── toArray(): array
├── toSnakeArray(): array
├── only(array $keys): array
├── except(array $keys): array
├── with(array $overrides): static
└── castValue(mixed $value, ReflectionParameter $param): mixed

TimetableTemplateData
├── Properties (readonly)
│   ├── id, name, description
│   ├── workingDays (array)
│   ├── isDefault (bool)
│   ├── status (Enum)
│   ├── academicYearId (int)
│   ├── educationalStageId (int|null)
│   ├── slots (TimeSlotData[])
│   └── gradeIds (int[])
└── Methods
    ├── fromLivewireForm()
    ├── fromModel()
    └── toModelArray()

TimeSlotData
├── Properties (readonly)
│   ├── id, dayOfWeek, label
│   ├── orderIndex, startTime, endTime
│   ├── type (Enum)
│   └── isAttendanceCheckpoint (bool)
└── Methods
    ├── fromArray()
    ├── fromModel()
    ├── toModelArray()
    ├── getDurationMinutes()
    └── getDayName()
```

#### ✅ Good Practices

1. **Immutability**
   - All properties are `readonly`
   - No setter methods

2. **Type Safety**
   - All properties have explicit types
   - Enum types for status fields

3. **Auto-casting**
   - `BaseData::castValue()` handles type conversion
   - Supports Enums, Dates, Nested DTOs

4. **Factory Methods**
   - `fromArray()`, `fromModel()`, `fromRequest()`

5. **Bidirectional Conversion**
   - `toModelArray()` for saving
   - `fromModel()` for loading

#### Recommendations

Consider adding **validation** in DTOs:

```php
class TimetableTemplateData extends BaseData
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        // ... other properties
    ) {
        // Validate required fields
        if (empty($this->name)) {
            throw new \InvalidArgumentException('Name is required');
        }

        // Validate time slots
        foreach ($this->slots as $slot) {
            if ($slot->startTime >= $slot->endTime) {
                throw new \InvalidArgumentException(
                    "Invalid time range for slot: {$slot->label}"
                );
            }
        }
    }
}
```

---

### 4. Actions (Status: ✅ Good)

#### Structure Analysis

```
AssignSessionAction
├── execute(yearId, termId, sectionId, subjectId, teacherId, slotId)
│   ├── DB::transaction()
│   ├── Validate inputs
│   ├── Validate business rules (guards)
│   ├── Create/Update CourseOffering
│   ├── Check attendance before changing course_offering_id
│   └── Create/Update Timetable
└── Return: Timetable

UpdateTimetableTemplateAction
├── execute(template, data)
│   ├── DB::transaction()
│   ├── Check if editable
│   ├── Validate grades
│   ├── GUARD: Check for linked timetables
│   ├── Update template
│   ├── Delete/Create timeSlots
│   └── Update grades
└── Return: TimetableTemplate
```

#### ✅ Good Practices

1. **Single Responsibility**
   - Each action does one thing

2. **Transactions**
   - All writes wrapped in `DB::transaction()`

3. **Guards**
   - Business rule validation before writes

4. **Type Hints**
   - Explicit parameter and return types

5. **Documentation**
   - PHPDoc comments with `@throws`

#### ❌ Issues Found

1. **Unused Import**
   ```php
   // AssignSessionAction.php:11
   use App\Domains\Academic\Timetable\Exceptions\CannotDeleteTimetableWithAttendanceException;
   // ❌ Not used - throw InvalidOperationException instead
   ```

2. **Exception Inconsistency**
   ```php
   // AssignSessionAction uses:
   throw InvalidOperationException::make(...);
   
   // But could use domain-specific exception:
   throw CannotChangeCourseOfferingException::withAttendance($count);
   ```

#### Recommendations

```php
// Create domain-specific exceptions
class CannotChangeCourseOfferingException extends \Exception
{
    public static function hasAttendance(int $count): self
    {
        return new self(
            "Cannot change course offering with {$count} attendance records."
        );
    }
}

// Usage in Action
if ($attendanceCount > 0) {
    throw CannotChangeCourseOfferingException::hasAttendance($attendanceCount);
}
```

---

### 5. Services (Status: ⚠️ Needs Work)

#### Current Structure

```
TimetableService
├── checkTeacherConflict(teacherId, timeSlotId, academicYearId, termId, sectionId)
└── Return: bool (true if conflict exists)

TimetableLookupService
├── getByTerm(int $termId)
├── getBySection(int $sectionId)
└── Search/Filter methods

TimetableTemplateService
├── getByStatus(TemplateStatus $status)
├── getDefaultForGrade(int $gradeId)
└── Other template-specific operations
```

#### ❌ Issues Found

1. **Mixed Responsibilities**
   - Some services have both read and write operations

2. **Missing Interfaces**
   - No service interfaces for testability

3. **Hard Dependencies**
   - Direct instantiation instead of dependency injection

#### Recommendations

```php
// Define interfaces
interface TimetableServiceInterface
{
    public function checkTeacherConflict(
        int $teacherId,
        int $timeSlotId,
        int $academicYearId,
        int $termId,
        ?int $excludeId = null
    ): bool;
}

// Implementation
class TimetableService implements TimetableServiceInterface
{
    public function __construct(
        private Timetable $timetable
    ) {}

    // ... implementation
}

// Usage with dependency injection
public function __construct(
    private TimetableServiceInterface $timetableService
) {}
```

---

### 6. Models (Status: ✅ Good)

#### Current Structure

```
Timetable
├── Relationships
│   ├── classSection() -> BelongsTo
│   ├── timeSlot() -> BelongsTo
│   ├── term() -> BelongsTo
│   └── courseOffering() -> BelongsTo
├── Accessors
│   ├── getSubjectAttribute()
│   └── getTeacherAttribute()
├── Scopes
│   └── scopeForTerm($query, int $termId)
└── Static Methods
    └── hasTeacherConflict(...)

TimeSlot
├── Relationships
│   ├── template() -> BelongsTo
│   └── timetableEntries() -> HasMany
├── Scopes
│   ├── scopeForDay($query, int $dayOfWeek)
│   ├── scopeAcademic($query)
│   ├── scopeBreaks($query)
│   ├── scopeAssignable($query)
│   └── scopeOrdered($query)
├── Accessors
│   ├── getTimeRangeAttribute()
│   ├── getDayNameAttribute()
│   └── getDurationMinutesAttribute()
└── Helpers
    ├── isAssignable()
    ├── countsForTeacherLoad()
    └── overlapsWithAnother(TimeSlot $other)

TimetableTemplate
├── Status Enum: draft, active, archived
├── Relationships
│   ├── timeSlots() -> HasMany
│   └── grades() -> BelongsToMany
├── Scopes
│   └── scopeActive($query)
└── Methods
    ├── isEditable()
    └── isDeletable()
```

#### ✅ Good Practices

1. **Proper Relationships**
   - Correct Eloquent relationship types
   - Proper foreign key naming

2. **Business Logic in Models**
   - `isEditable()`, `isDeletable()` methods
   - `hasTeacherConflict()` static method

3. **Attribute Accessors**
   - `getSubjectAttribute()` - delegated to courseOffering
   - `getTeacherAttribute()` - delegated to courseOffering

4. **Scopes**
   - `scopeForTerm()` for filtering
   - `scopeAcademic()`, `scopeBreaks()` for type filtering

#### ❌ Issues Found

1. **Unused Trait**
   ```php
   // Timetable.php:16-18
   use \App\Infrastructure\Traits\HandlesSafeDelete;
   use \App\Infrastructure\Traits\InvalidatesCache;
   
   // But deletion bypasses trait via Query Builder
   ```

2. **Missing Relationship Documentation**
   ```php
   public function timetableEntries(): HasMany
   {
       return $this->hasMany(Timetable::class);
   }
   // Should document: "Returns ALL entries, use timetableEntriesForTerm() for filtering"
   ```

3. **Magic Numbers**
   ```php
   // TimeSlot.php:136-141
   public function getDurationMinutesAttribute(): int
   {
       if (!$this->start_time || !$this->end_time) {
           return 0;
       }
       return $this->start_time->diffInMinutes($this->end_time);
   }
   ```

#### Recommendations

```php
// 1. Document relationship filtering
public function timetableEntries(): HasMany
{
    return $this->hasMany(Timetable::class);
}

/**
 * Get timetable entries for a specific term.
 *
 * @param int $termId
 * @return HasMany
 *
 * @example
 * $timeSlot->timetableEntriesForTerm(1)->get();
 */
public function timetableEntriesForTerm(int $termId): HasMany
{
    return $this->hasMany(Timetable::class)
        ->where('term_id', $termId);
}
```

---

### 7. Events (Status: ⚠️ Underutilized)

#### Current Structure

```
TimetableTemplateCreated (implements ShouldBroadcast?)
├── template: TimetableTemplate
└── timestamp: Carbon

TimetableTemplateUpdated
├── template: TimetableTemplate
└── timestamp: Carbon
```

#### ❌ Issues Found

1. **Events Not Dispatched**
   - Events defined but not fired in Actions

2. **No Event Listeners**
   - No listeners for cache invalidation
   - No listeners for audit logging

3. **Missing Events**
   - No `TimetableEntryCreated` event
   - No `TimetableEntryDeleted` event
   - No `SessionAssigned` event

#### Recommendations

```php
// In Action
use App\Domains\Academic\Timetable\Events\TimetableEntryCreated;

class AssignSessionAction
{
    public function execute(...): Timetable
    {
        return DB::transaction(function () use (...) {
            // ... create timetable
            
            event(new TimetableEntryCreated($timetable));
            
            return $timetable;
        });
    }
}

// Create listener for audit
class LogTimetableEntryCreated
{
    public function handle(TimetableEntryCreated $event): void
    {
        AuditLog::create([
            'action' => 'timetable_entry_created',
            'model_type' => 'Timetable',
            'model_id' => $event->timetable->id,
            'user_id' => auth()->id(),
            'metadata' => [
                'class_section_id' => $event->timetable->class_section_id,
                'time_slot_id' => $event->timetable->time_slot_id,
                'term_id' => $event->timetable->term_id,
            ],
        ]);
    }
}
```

---

### 8. Exceptions (Status: ⚠️ Inconsistent)

#### Current Structure

```
TemplateNotEditableException
├── templateId: int
├── currentStatus: TemplateStatus
└── Custom constructor with Arabic message

GradeAlreadyAssignedException
├── gradeId: int
├── existingTemplateId: int
├── existingTemplate: TimetableTemplate|null
└── Custom constructor

InvalidTimeSlotsException
├── errors: array
└── Static factories: overlappingSlots(), invalidTimeRange(), noSlotsProvided()

CannotDeleteTimeSlotsInUseException (NEW)
├── timeSlotIds: array
├── usageCount: int
└── Static factories: forSingleSlot(), forMultipleSlots()

CannotDeleteTimetableWithAttendanceException (NEW)
├── timetableId: int
├── attendanceCount: int
└── Static factories: forTimetable(), forMultipleTimetables()
```

#### ✅ Good Practices

1. **Domain-Specific Exceptions**
   - Exceptions match domain concepts

2. **Contextual Information**
   - Exceptions carry relevant data

3. **Custom Messages**
   - Arabic messages for user-facing errors

4. **Static Factories**
   - `forSingleSlot()`, `forMultipleTimetables()`

#### ❌ Issues Found

1. **Inconsistent Base Class**
   - Some extend `Exception` directly
   - No unified exception hierarchy

2. **Exception Throwing Inconsistency**
   ```php
   // Some places throw:
   throw InvalidOperationException::make(...);
   
   // Others dispatch:
   $this->dispatch('error', message: ...);
   
   // And others abort:
   abort(403, ...);
   ```

#### Recommendations

```php
// Create base exception
abstract class TimetableException extends \Exception
{
    protected array $context = [];

    public function getContext(): array
    {
        return $this->context;
    }
}

// All domain exceptions extend base
class CannotDeleteException extends TimetableException {}

// Unified error handling in Livewire
public function deleteSession(int $slotId): void
{
    try {
        app(DeleteTimetableEntryAction::class)->execute($slotId);
        $this->dispatch('notify', message: __('attendance.session_deleted'), type: 'success');
    } catch (CannotDeleteException $e) {
        $this->dispatch('error', message: $e->getMessage());
    } catch (\Throwable $e) {
        report($e); // Log for debugging
        $this->dispatch('error', message: __('attendance.unknown_error'));
    }
}
```

---

## Laravel Best Practices Compliance

### ✅ Following Laravel Conventions

1. **Domain-Driven Structure**
   - `app/Domains/{Domain}/` organization
   - Clear separation by business domain

2. **Action Pattern**
   - Business logic in Actions
   - Single responsibility

3. **DTOs with BaseData**
   - Type-safe data transfer
   - Automatic validation

4. **Eloquent Best Practices**
   - Relationships defined properly
   - Scopes for common queries

5. **Service Layer**
   - Lookup services for read operations

### ❌ Not Following Conventions

1. **Query Builder vs Model**
   - Using Query Builder when Model would be better

2. **Events**
   - Events defined but not dispatched

3. **Caching**
   - No consistent caching strategy

4. **Testing**
   - No tests visible in review

---

## Summary of Required Changes

### Priority 1: Critical (Security & Data Integrity)

| File | Change | Impact |
|------|--------|--------|
| `TimetableBuilder.php` | Fix `deleteSession` to use Model | Data integrity |
| `TimetableBuilder.php` | Remove unused imports | Code cleanliness |
| `AssignSessionAction.php` | Remove unused imports | Code cleanliness |

### Priority 2: Important (Architecture)

| File | Change | Impact |
|------|--------|--------|
| New | `DeleteTimetableEntryAction` | Consistency |
| `Exceptions/` | Create exception hierarchy | Error handling |
| `Events/` | Dispatch events from Actions | Audit & caching |
| `Services/` | Create interfaces | Testability |

### Priority 3: Nice to Have

| File | Change | Impact |
|------|--------|--------|
| `docs/` | `coding-standards.md` | Team consistency |
| `Models/` | Document relationships | Maintainability |
| All | Standardize comments | Readability |

---

## Appendix A: File Structure

```
app/Domains/Academic/Timetable/
├── Actions/
│   ├── ActivateTimetableTemplateAction.php ✅
│   ├── ArchiveTimetableTemplateAction.php
│   ├── AssignSessionAction.php ✅ (Guards added)
│   ├── CreateTimetableTemplateAction.php
│   ├── DeleteTimetableTemplateAction.php ✅ (Guards added)
│   ├── DuplicateTimetableTemplateAction.php
│   ├── GenerateTimetableAction.php
│   └── UpdateTimetableTemplateAction.php ✅ (Guards added)
├── Data/
│   ├── SlotGeneratorConfig.php
│   ├── TimeSlotData.php ✅
│   └── TimetableTemplateData.php ✅
├── Enums/
│   ├── TemplateStatus.php ✅
│   └── TimeSlotType.php ✅
├── Events/
│   ├── TimetableTemplateCreated.php
│   └── TimetableTemplateUpdated.php ⚠️ (Not dispatched)
├── Exceptions/
│   ├── CannotDeleteTimeSlotsInUseException.php ✅ (NEW)
│   ├── CannotDeleteTimetableWithAttendanceException.php ✅ (NEW)
│   ├── GradeAlreadyAssignedException.php
│   ├── InvalidTimeSlotsException.php
│   └── TemplateNotEditableException.php
├── Models/
│   ├── TimeSlot.php ✅ (Fixed casts)
│   ├── Timetable.php ✅
│   └── TimetableTemplate.php
├── Observers/
│   └── TimetableTemplateObserver.php
├── Services/
│   ├── TimetableLookupService.php
│   ├── TimetableService.php ✅
│   └── TimetableTemplateService.php
└── Validators/
    ├── TimetableGradeValidator.php
    └── TimetableSlotValidator.php
```

---

## Appendix B: Recommended Directory Structure for Future

```
app/
├── Console/
│   └── Commands/
│       └── DataAudit/
│           ├── TimetableDataAuditCommand.php ✅
│           └── BackfillAttendanceTimetableIdCommand.php ✅
├── Livewire/
│   ├── Academic/
│   │   └── TimetableBuilder.php ⚠️ (Needs cleanup)
│   └── Forms/
│       └── Timetable/
│           └── TimetableTemplateForm.php ✅
└── Domains/
    └── Academic/
        └── Timetable/
            └── [existing structure] ✅
```

---

**Document Version:** 2.0
**Last Updated:** 2026-02-04
**Author:** Kilo Code AI - Comprehensive Architectural Review
**Methodology:** Laravel Best Practices + Clean Code Principles + Domain-Driven Design
