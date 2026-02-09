# Theme Refactor Roadmap

Goal: stabilize the base theme and prevent visual drift while refactoring.

Primary references:
- docs/theme/design-inventory.md
- resources/css/app.css
- tailwind.config.js

## P0 — Theme Foundation (done)
Goal:
- establish a single source of truth for core theme values (colors/gradients/typography)
- remove duplicate definitions that cause drift

Touched files:
- resources/css/app.css
- tailwind.config.js
- resources/css/sidebar.css
- docs/theme/design-inventory.md

Rules:
- no duplicate gradient definitions
- tokens live in CSS variables, Tailwind maps to them

Success checklist:
- gradients defined once and referenced by variables
- sidebar styles use tokens (no raw hex values)

## P0.9 — Source of Truth Lockdown (done)
Goal:
- eliminate raw color values in core CSS in favor of tokens
- ensure build succeeds with current token setup

Touched files:
- resources/css/app.css
- resources/css/sidebar.css

Rules:
- use existing tokens or add minimal new tokens when required
- no visual change allowed

Success checklist:
- core CSS uses tokens for colors and effects
- `npm run build` passes
- Change: standardized core CSS colors on tokens (no raw values in app/sidebar CSS).
- Test: `npm run build` success.

## P1 — Component Tokenization (pending)
Goal:
- apply neutral/base tokens to shared components so theme changes are consistent everywhere

Scope (priority order):
- resources/views/components/input.blade.php
- resources/views/components/form/input.blade.php
- resources/views/components/form/select.blade.php
- resources/views/components/dialog-modal.blade.php

Rules:
- prefer Tailwind classes tied to tokens
- avoid inline styles and raw hex values

Success checklist:
- inputs/selects/modals use tokens for borders, backgrounds, text, and focus states
- no visual change compared to baseline
- Change (resources/views/components/input.blade.php): tokenized label and input border neutrals using form tokens.
- Test (resources/views/components/input.blade.php): not run here; please verify Light/Dark on a page with form + modal.
- Change (resources/views/components/form/select.blade.php): tokenized select border neutral using form tokens.
- Test (resources/views/components/form/select.blade.php): not run here; please verify Light/Dark on a page with form + modal.
- Change (resources/views/components/dialog-modal.blade.php): tokenized modal neutrals (overlay, surfaces, text) with modal tokens.
- Test (resources/views/components/dialog-modal.blade.php): not run here; please verify Light/Dark on a page with form + modal.
- Test (P1 verification): user confirmed Light/Dark form + modal has no visual difference.
- Status: P1 closed.

## P1.5 — Theme Stability (done)
Goal:
- fix any theme flipping (load order, Livewire hydration, dark mode toggles)

Likely areas:
- resources/views/layouts/app.blade.php
- resources/views/layouts/guest.blade.php
- resources/js/app.js

Success checklist:
- no flash of incorrect theme on load
- consistent theme after Livewire updates
- Change (resources/views/layouts/app.blade.php): single-theme source of truth (head init + applyTheme/toggleTheme) with Livewire rebind.
- Test (P1.5): `npm run build` passed after theme logic change.
- Test (P1.5): no matches for `storedTheme|shouldUseDark|getStoredTheme|livewire:navigated.*livewire:navigated` in `resources/views/layouts/app.blade.php`.
- Change (resources/css/app.css): add theme-preload/theme-switching guards to suppress transitions during load/toggle.
- Change (resources/views/layouts/app.blade.php): add theme-preload/theme-switching classes and avoid reapplying theme when unchanged.
- Test (P1.5.1): pending visual check for reload flash and toggle flicker.
- Change (resources/css/sidebar.css): disable sidebar/main-content transitions on desktop to prevent layout shift on reload.
- Test (P1.5.2): pending visual check for reload flash and sidebar/header movement.
- Change (resources/css/app.css): hide body during theme-preload to avoid first-paint flash.
- Change (resources/views/layouts/app.blade.php): remove theme-preload after window load for stable first render.
- Test (P1.5.3): Light/Dark reload shows no flash (verified).

## P2 — Charts (done)
Goal:
- tokenise repeated chart colors and opacities

Scope:
- resources/views/components/*-chart.blade.php

Change:
- Added chart tokens (`--color-black`, `--chart-title`, `--chart-center`) and applied to chart components.

Test:
- Visual check passed (titles, grid, tooltip, pie border).

## P3 — Print & Errors (pending)
Goal:
- align print and error pages with core tokens

Scope:
- resources/views/layouts/print.blade.php
- resources/views/errors/*

Change:
- Added print/error tokens and applied them to print layout + error layout (excluding errors/minimal).

Test:
- Visual check passed: print preview (borders/header/button/hover) and error layout text/background.

## P3.5 — Errors/minimal decision (pending)
Goal:
- decide whether to keep `resources/views/errors/minimal.blade.php` as-is or tokenise later with a strict token cap

Options:
- keep as-is (rarely shown, acceptable)
- tokenise partially later with a capped token budget

## P6 — Top 4 Pages Fix (pending)
Goal:
- replace raw neutral colors with tokens on four pages without changing structure/spacing/behavior

Scope:
- resources/views/academic-directory/index.blade.php
- resources/views/livewire/academic/academic-directory-manager.blade.php
- resources/views/livewire/academic/subject-manager.blade.php
- resources/views/livewire/academic/class-section-manager.blade.php
- resources/views/livewire/academic/structure-manager.blade.php

Audit (raw neutrals found):
- academic-directory/index: `bg-white/50`, `dark:bg-slate-800/50`
- academic-directory-manager: `bg-white/*`, `border-white/10`, `bg-white`, `border-gray-100`, `text-gray-*`, `bg-gray-*`, `placeholder-gray-*`
- subject-manager: heavy use of `bg-white`, `border-gray-*`, `text-gray-*`, `bg-gray-*`, `hover:bg-gray-*`
- class-section-manager: `bg-white`, `border-gray-300`, `text-gray-*`, `hover:bg-gray-50`
- structure-manager: heavy use of `bg-white`, `border-gray-*`, `text-gray-*`, `bg-gray-*`, `hover:bg-gray-*`

Change:
- resources/views/academic-directory/index.blade.php: `bg-white/50 dark:bg-slate-800/50` -> `bg-surface/50`.
- resources/views/livewire/academic/academic-directory-manager.blade.php: replaced neutral grays/whites with base tokens; kept semantic accents unchanged.
- resources/views/livewire/academic/subject-manager.blade.php: replaced neutral grays/whites with base tokens; added dark accent variants for subject cards, grade selector, and menu states to reduce glare.
- resources/views/livewire/academic/class-section-manager.blade.php: replaced neutral grays/whites with base tokens for modals, labels, and controls.
- resources/views/livewire/academic/class-section-manager.blade.php: switched header to `variant="soft"` with `size="tiny"` and toned down the copy action button.
- resources/views/components/academic/section-filters.blade.php: tokenized filter bar neutrals so it respects dark mode (bg/labels/inputs/icon).
- resources/views/components/academic/grade-section-group.blade.php: tokenized card/group neutrals so the list section respects dark mode.
- resources/views/components/academic/section-card.blade.php: tokenized section card neutrals so the grid respects dark mode.
- resources/views/livewire/academic/academic-directory-manager.blade.php: adjusted background gradient to use dark tokens for consistency with academic-year page.
- resources/views/components/academic/grade-card.blade.php: added dark-mode variants while keeping the light theme appearance.
- resources/views/layouts/navigation.blade.php: added `app-topbar` class so the header respects sidebar width.
- resources/css/sidebar.css: constrained top bar width on desktop to avoid overlap with sidebar.
- resources/views/livewire/academic/structure-manager.blade.php: replaced neutral grays/whites with base tokens; adjusted neutral overlays to `bg-foreground/*`.

Test:
- P6 visual checks pending (Light/Dark reload + interactions) for each page in scope.

## P4 — Welcome Page (pending)
Goal:
- decide whether to tokenise or treat as a separate marketing palette

Scope:
- resources/views/welcome.blade.php
