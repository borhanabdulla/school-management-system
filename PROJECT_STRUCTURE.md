# Project Structure Documentation

## Overview
This repository implements a **School Management System** built with **Laravel**, **Livewire**, and **Tailwind CSS**. The application follows a clean **MVC** architecture with a **Service Layer** and **Livewire components** for interactive UI. The UI adopts a premium **Glassmorphism** design with Alpine.js for client‑side interactivity.

---

## Directory Layout

```
project-root/
├─ app/                     # Application core (PHP)
│  ├─ Actions/              # Single‑purpose actions (CUD operations)
│  ├─ Data/                 # Data Transfer Objects (DTOs)
│  ├─ Enums/                # Enum classes (e.g., Gender, StudentStatus)
│  ├─ Events/               # Domain events
│  ├─ Exceptions/           # Custom exception classes
│  ├─ Http/
│  │   ├─ Controllers/      # HTTP controllers (e.g., StudentController)
│  │   └─ Middleware/       # Request middleware
│  ├─ Livewire/             # Livewire components (UI logic)
│  │   └─ Student/          # Student‑related components (Directory, Profile)
│  ├─ Models/               # Eloquent models (Student, Guardian, etc.)
│  ├─ Observers/            # Model observers
│  ├─ Providers/            # Service providers (AppServiceProvider, etc.)
│  ├─ Services/             # Service layer (business logic)
│  │   └─ Student/          # StudentPerformanceService, etc.
│  ├─ Traits/               # Reusable traits
│  └─ View/                 # View composers (if any)
├─ database/
│  ├─ migrations/           # Schema migrations (creates all tables)
│  └─ seeders/             # Database seeders (e.g., StudentSeeder)
├─ public/                  # Public assets (JS, CSS, images)
├─ resources/
│  ├─ css/                  # Tailwind CSS entry point
│  ├─ js/                   # JavaScript entry point (Alpine.js, etc.)
│  ├─ views/                # Blade templates
│  │   ├─ components/       # Reusable UI components (buttons, cards, charts)
│  │   ├─ layouts/          # Layout files (app.blade.php, guest.blade.php)
│  │   ├─ livewire/         # Livewire component views
│  │   │   └─ student/      # student‑profile.blade.php, student‑directory.blade.php
│  │   ├─ students/         # Wrapper views for routes (index.blade.php, show.blade.php)
│  │   └─ vendor/           # Published package views (pagination, etc.)
│  └─ lang/                 # Localization files
├─ routes/
│  └─ web.php               # Route definitions (Controller → View → Livewire)
├─ storage/                 # Logs, cache, compiled views
├─ tests/                   # PHPUnit / Pest tests
├─ .env                     # Environment configuration
└─ artisan                  # CLI entry point
```

---

## Key Architectural Concepts

### 1. **Controller → View → Livewire**
* **Controller** (`StudentController`) receives the request and returns a Blade view (`students.show`).
* The view acts as a thin wrapper that mounts a **Livewire** component (`student-profile`).
* All interactive UI logic lives inside the Livewire component, keeping controllers lightweight.

### 2. **Service Layer**
* Business‑logic heavy operations (e.g., grade analysis) are placed in **Services** such as `StudentPerformanceService`.
* Controllers and Livewire components inject these services via method injection, promoting testability.

### 3. **Livewire Components**
* `StudentDirectory` – handles listing, filtering, bulk actions, and pagination.
* `StudentProfile` – displays the premium Glassmorphism profile page with tabbed navigation.
* Components use **Alpine.js** for client‑side state (active tabs, dropdowns) and **Tailwind CSS** for styling.

### 4. **Enums & DTOs**
* Enums (`Gender`, `StudentStatus`) provide type‑safe constants.
* DTOs (e.g., `StudentUpdateData`) encapsulate request data for actions and services.

---

## Database Reference (`bor.txt`)
A quick reference of all tables created by migrations is available in the file **`bor.txt`** at the project root. It lists tables such as `students`, `guardians`, `student_enrollments`, `invoices`, `fees`, `subjects`, etc.

---

## UI / Design Guidelines
* **Glassmorphism** – translucent cards, backdrop blur, soft shadows.
* **Tailwind CSS** – utility‑first styling, responsive design.
* **Alpine.js** – lightweight reactivity for tabs, dropdowns, and filter toggles.
* **Dark Mode** – all components respect the `dark:` variant for a seamless dark theme.

---

## How to Extend / Onboard a New Developer
1. **Clone the repository** and run `composer install` & `npm install`.
2. **Copy `.env.example` to `.env`**, generate an app key (`php artisan key:generate`).
3. **Run migrations**: `php artisan migrate` (SQLite is used in this project).
4. **Seed sample data** (optional): `php artisan db:seed`.
5. **Start the dev server**: `php artisan serve` and `npm run dev`.
6. Review the **`TECHNICAL_GUIDE.md`** (generated earlier) for deeper insights on each module.

---

## Useful Commands
* `php artisan route:list` – view all routes.
* `php artisan view:clear` – clear compiled Blade views.
* `php artisan migrate:fresh --seed` – reset DB with seed data.
* `npm run dev` – compile assets with Vite.

---

## Contact & Contribution
* Open a PR for new features or bug fixes.
* Follow the existing coding standards (PSR‑12, Laravel conventions).
* Ensure UI changes keep the Glassmorphism aesthetic and are responsive.

---

*Generated on 2025‑12‑01*
