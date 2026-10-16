# Sutomo HR — Laravel + Filament module

Filament 3.2 admin panel for the Sutomo HR recruitment module
(candidates, vacancies, interviews, deposits, yayasan approvals, audit log).

## Stack
- PHP 8.2+ (developed on 8.5)
- Laravel 11
- Filament 3.2
- SQLite (default — file at `database/database.sqlite` is committed for demo data)
- Tailwind via Filament's bundled build (Vite for additional assets)

## Quick start (for new devs)
```bash
git clone -b laravel-main https://github.com/icannDevTeam/Sutomo_module_template-.git hr-laravel
cd hr-laravel
cp .env.example .env
composer install
npm install && npm run build      # only if you change assets
php artisan key:generate
php artisan migrate                # safe to re-run; SQLite file already shipped
php artisan serve
```
Then open http://127.0.0.1:8000/admin

Default admin login:
- email: `admin@sutomo.sch.id`
- password: `password`

## Branch convention (matches the rest of the template repo)
- `main`         → vanilla HTML/CSS/JS HR mockup (`hr/`)
- `svelte-main`  → SvelteKit version (`sutomo-svelte/`)
- `laravel-main` → this branch — Laravel + Filament implementation

## Notable pieces
- `app/Filament/Pages/Dashboard.php` — custom 3-col dashboard
- `app/Filament/Widgets/*` — KPI stats, charts, hero banner, approvals, activity
- `app/Filament/Resources/*` — Candidates, Teachers, Vacancies, Deposits, Interviews, AuditLog
- `app/Filament/Pages/*` — Buku Induk, Master Database, Verifications, Onboarding, Assessments, Yayasan Approvals, Pipeline, Inbox, Settings
- The Principal panel is the single-login simulation hub: **Approvals** contains the Yayasan Approval Inbox and Hiring Approvals; **HR Tasks** contains both HR Coordination and the HR Tasks Overview; **Overview** contains Yayasan Dashboard and decision history. Simulation actions are attributed to the signed-in Principal; contract preparation remains gated by approval and assignment.
- **Contract Workflow** (My Submissions) shows only submitter actions: withdraw, edit/resubmit, and review the prepared document, plus continuation Agreement handover and Buku Induk. Yayasan decisions remain in the Approval Inbox; HR preparation remains in HR Tasks, even when simulating all roles through the same login.
- LOI previews opened from Contract Workflow return to the linked request (or Ready to submit before submission). Embedded previews return through the main browser frame so the dashboard never opens inside the document preview.
- `app/Support/CsvExporter.php` — UTF-8 BOM streamed CSV helper used by every resource & page
- Topbar: profile, database notifications (30s polling), global search (⌘K), user menu shortcuts

## Notes
- The committed `database/database.sqlite` is for development/demo only.
  For real use, switch `.env` to `DB_CONNECTION=mysql` (or pgsql) and migrate.
- `vendor/` and `node_modules/` are git-ignored — run `composer install` after pulling.
- Laravel's default README has been kept as `README.laravel-default.md`.

## Architecture and module flows

The [Archify flow library](.archify/README.md) includes the system architecture
and individual enrollment, leave/substitution, contract and planning workflows.
After pulling, run `php -S 127.0.0.1:8085 -t .archify` from the repository root
and open http://127.0.0.1:8085/. No application dependencies are needed to view it.

The [product and UX design brief](docs/design-brief.md) covers enrollment,
supervision, leave/substitution, planning/timetable and contracts, separating
current behavior from proposed screens, acceptance criteria and policy decisions.
