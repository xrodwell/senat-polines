# ARCHITECTURAL RULES: Senat Akademik POLINES (Laravel 11 Frontend-First)
(Adopted from worldflowai/everything-claude-code)

## 1. Modular Decoupling (Future-Proof for PostgreSQL & External APIs)
- All Controllers MUST depend strictly on Repository Interfaces defined in `app/Contracts/`.
- No direct hardcoded data inside Blade templates or Controllers.
- Phase 1 Implementations reside in `app/Repositories/Mock/` reading structured JSON files in `storage/mock/`.
- Binding configuration is centralized in `app/Providers/AppServiceProvider.php`.

## 2. Controller Responsibility
- Controllers must remain "Thin".
- Methods handle HTTP requests, invoke repository methods, and return Blade views.
- Route naming convention:
  - `home` (`/`)
  - `profile` (`/profil`)
  - `regulations.index` (`/produk-hukum`)
  - `regulations.show` (`/produk-hukum/{slug}`)
  - `aspirations.index` & `aspirations.store` (`/aspirasi`)
  - `aspirations.track` (`/aspirasi/lacak`)
  - `archives.index` (`/arsip`)
  - `member.dashboard` (`/portal-anggota`)
  - `member.attendance` (`/portal-anggota/absensi`)
  - `admin.meetings` (`/portal-admin/sidang`)

## 3. Blade & UI Conventions
- Use standard Blade layouts: `resources/views/layouts/public.blade.php` and `resources/views/layouts/portal.blade.php`.
- Shared components in `resources/views/components/`:
  - `navbar.blade.php`
  - `footer.blade.php`
  - `news-card.blade.php`
  - `agenda-item.blade.php`
  - `stat-card.blade.php`
  - `ticket-status-badge.blade.php`
- Interactivity handled via Alpine.js (modals, countdown timers, dropdowns, code copy).
