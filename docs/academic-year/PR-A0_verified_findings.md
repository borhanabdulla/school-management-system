# PR-A0: Verified Findings Report (Active Year SSOT)

**Status:** Draft  
**Scope:** Active Academic Year + Active Term only  
**Nature:** Findings only (no code changes)  
**Note:** Line references were captured before PR-A1; they may shift after implementation.

---

## 1) Cached SSOT used inside state transitions (High Risk)

### Evidence

**A. Cached reads used during transitions**

* `app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php:57`
  * Uses `school()->activeYearId()` **inside** `DB::transaction(...)`
* `app/Domains/Academic/Term/Actions/ActivateTermAction.php:17`
  * Uses `school()->activeYearId()` **inside** `DB::transaction(...)`
* `app/Domains/Academic/Term/Actions/ActivateTermAction.php:31`
  * Uses `school()->activeTermId()` **inside** `DB::transaction(...)`
* `app/Domains/Academic/Term/Actions/ReopenTermAction.php:23`
  * Uses `school()->activeYearId()` **inside** `DB::transaction(...)`

**B. Source of `activeYearId()`**

* `school()` returns `AcademicContextService::getInstance()`:
  * `app/Infrastructure/Support/helpers.php:48`
* `active_year_id()` delegates to `school()->activeYearId()`:
  * `app/Infrastructure/Support/helpers.php:65`
* `AcademicContextService::activeYearId()` delegates to `activeYear()`:
  * `app/Infrastructure/Context/AcademicContextService.php:141`
* `activeYear()` is cached via `Cache::remember(...)` using:
  * `app/Infrastructure/Context/AcademicContextService.php:124`

  ```php
  CACHE_TTL = 60 * 60 * 24; // 24 hours
  ```
  * `app/Infrastructure/Context/AcademicContextService.php:52`

This establishes that **cached state is consulted during write/transition operations**.

---

### Why this matters

State transitions (activate / close / reopen) must operate on the **authoritative, current DB state**.

Using a cached value inside a transaction introduces a **temporal inconsistency**:

* The transaction may observe a stale “active year” or “active term”.
* Decisions (validation, closure, activation) are then applied to the **wrong row**.

---

### Risk

* A stale cache read can cause:
  * Closing the wrong academic year.
  * Validating the wrong year/term.
* Under concurrency, this can lead to:
  * **Two academic years marked `active`**.
  * **Active term belonging to a non-active year**.

This is a **silent data integrity failure**, not a visible crash.

---

## 2) No DB-level guard for single active academic year (High Risk)

### Evidence

* `database/migrations/2025_11_19_184600_create_academic_years_table.php:18`
  * Column `status` is indexed.
  * **No uniqueness constraint** exists for rows where `status = 'active'`.

### Risk

* Even with correct application logic:
  * Concurrent requests can still persist **multiple active academic years**.
* Integrity depends entirely on application behavior, not enforced by the database.

---

## 3) No DB-level guard for single active term per academic year (Medium Risk)

### Evidence

* `database/migrations/2025_11_19_184700_create_terms_table.php:18`
  * Column `status` is indexed.
  * **No uniqueness constraint** exists for:
    * `(academic_year_id, status = 'active')`

### Risk

* Multiple active terms may coexist within the same academic year.
* This corrupts:
  * Term-scoped writes (grades, attendance, reports).
* Impact is localized but persistent.

---

## 4) Cache invalidation does not mitigate transition risk

### Evidence

* Cache invalidation occurs **after commit** in transitions:
  * `app/Domains/Academic/AcademicYear/Actions/ActivateAcademicYearAction.php:82`
  * `app/Domains/Academic/Term/Actions/ActivateTermAction.php:43`
  * `app/Domains/Academic/Term/Actions/ReopenTermAction.php:29`
* Invalidation targets the academic context cache:
  * `app/Infrastructure/Context/AcademicContextService.php:315` (`invalidateYear`)
  * `app/Infrastructure/Context/AcademicContextService.php:328` (`invalidateTerm`)
* During the transaction itself:
  * Cached values may still be stale.
  * Decisions are already made before invalidation occurs.

### Conclusion

Cache invalidation **does not make cached reads safe inside transitions**.

---

## Conclusion

The system currently violates a fundamental invariant:

> **Transitions depend on cached state rather than authoritative DB state.**

As a result:

* Active year and active term transitions are **not concurrency-safe**.
* The database does not enforce the invariants required to keep the model consistent.

---

## Required Next Step

### PR-A1: DB-Authoritative Transitions

* Remove all cached academic context reads from transition paths.
* All activate / close / reopen operations must:
  * Read state directly from the database.
  * Use `lockForUpdate()` inside `DB::transaction(...)`.
* Cache remains read-only and is invalidated **after commit**.

---

### Why this PR exists

PR-A0 establishes **facts only**:

* What is happening.
* Why it is dangerous.
* Where invariants are currently unenforced.

No refactor or fix should begin without this report being accepted.

---

## ✔︎ Status Check

* [x] No assumptions about future DB guards
* [x] No implementation details leaked into findings
* [x] Scope limited to active year / active term
* [x] Evidence-based, reproducible via grep
