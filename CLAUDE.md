# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Shelvi Finance — a finance/receivables admin panel. Laravel 13 (PHP 8.3), Tailwind CSS v4, and a
shadcn-inspired component set — **Blade only, no React, no Alpine**. jQuery + Lucide icons +
Yajra Laravel DataTables (server-side) for lists; jquery-validation for client-side form checks.
SQLite in dev, MySQL in production.

## Commands

```bash
composer install
npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm run build          # or: npm run dev (Vite watch)
composer run dev       # serve + queue:listen + pail + vite, concurrently, port 8004
php artisan serve
```

Tests (PHPUnit, sqlite `:memory:` — see `phpunit.xml`):
```bash
composer test                                  # config:clear + php artisan test
php artisan test --filter=TestName             # single test
php artisan test tests/Feature/WritesTest.php  # single file
```

Lint/format: `./vendor/bin/pint` (Laravel Pint).

First-time data setup (roles/permissions + interactive account creation, **not** `db:seed`):
```bash
php artisan app:setup
```
Demo data (`FinanceDataSeeder`, `UserSeeder`) is loaded via `db:seed` for local dev only — never run
plain `migrate --seed` in production (see `DEPLOYMENT.md`).

Frontend assets are compiled locally and `public/build/` is committed — the production server has no
Node. After any Blade/CSS/JS change destined for prod, rebuild and re-upload `public/build/`. Full
deploy steps, `.env` template, and the GitHub Actions auto-deploy pipeline are in `DEPLOYMENT.md`.

## Architecture

**Routes → controllers → DataTables**, gated by permission on every route. `routes/web.php` wires
resource-style routes to thin controllers (`app/Http/Controllers/`); each list view is a Yajra
DataTable service (`app/DataTables/`) extending `BaseDataTable`. Every route inside the `auth`
middleware group also carries a `permission:{resource}.{action}` middleware — admin has the full set,
accountant a day-to-day subset (see `database/seeders/RolesAndPermissionsSeeder.php`). There is a
hidden `superadmin` owner role with every permission that must never surface in the UI (role pickers,
users list, edit URLs) — see `App\Support\Access`. Reflect this hidden-role rule in any new
role/permission or user-facing role-matrix code.

**Repository seam.** Controllers and DataTables depend on contracts in
`app/Repositories/Contracts/`, bound to Eloquent implementations in `app/Repositories/Eloquent/`
via a single map in `AppServiceProvider::REPOSITORIES`. Adding a new domain object means: contract →
Eloquent implementation → entry in that map.

**Domain model.** `Transaction` is the money-movement ledger (received/paid, amounts in paise,
soft-deletes) and drives bank statements, party ledgers, and dashboard aggregates via scopes
(`scopeReceived`/`scopePaid`). Every `Transaction` keeps a 1:1 `LedgerEntry` (the party's accounting
journal line) in sync — kept in sync by `MoneyController`, so when a transaction is
created/updated/soft-deleted/restored, update its `ledgerEntry` alongside it (see how
`TrashController::restore`/`forceDelete` cascade to `ledgerEntry()->withTrashed()`). Running
balance/DR-CR is derived at render time, ordered via `LedgerEntry::scopeChronological`, not stored.
All money amounts are integers (paise), never floats.

**Trash / recycle bin.** Soft-deletable modules have no dedicated trash page — each list has a
"Show deleted" toggle (`BaseDataTable::viewingTrash()`/`trashedAjaxData()`) that flips the DataTable
to its trashed feed and swaps row actions to Restore/Delete-forever (`BaseDataTable::trashActions()`,
routes `trash.restore`/`trash.destroy`, resolved via `App\Support\Trash::model($type)`). This whole
group (`trash.*`) is superadmin-only and hidden from the visible permission matrix.

**Typed/static data.** Static select lists live in `config/options.php`; sidebar nav in
`config/navigation.php`; aggregate DTOs live under `App\Data`.

**DataTable cells are Blade partials** in `resources/views/datatables/cells/`, rendered via
`BaseDataTable::cell()` and helpers (`money`, `signedMoney`, `statusPill`, `drCr`, `gatedActions`,
etc.) — never build HTML strings inline in a `DataTable` class. `gatedActions()` shows Edit/Delete
only if the current user holds the matching `{resource}.update`/`{resource}.delete` permission, so
row actions stay in sync with RBAC. Cell markup uses real Tailwind classes (a semantic tone palette)
so Tailwind's scanner picks them up — no `@source inline` safelist needed for badges/amounts/pills.

**Money formatting.** `App\Support\Inr::format()` renders Indian digit grouping (`₹XX,XX,XXX`,
lakh/crore). Use it (or the `money`/`signedMoney`/`amount` DataTable helpers) for any amount shown to
a user rather than hand-rolling number formatting.

**UI kit** in `resources/views/components/ui/`; app shell in
`resources/views/components/layouts/admin.blade.php` (sidebar built from `config/navigation.php`).
Brand tokens (colors as oklch, typography, radii) are documented in `BRAND.md` — check it before
introducing new UI colors/fonts rather than inventing values.

## Notes

- No React/Alpine/Vue — new interactivity is vanilla JS (`resources/js/`) or jQuery, consistent with
  the rest of the app.
- Light/dark theme uses a no-flash boot + `localStorage`; don't reintroduce a FOUC by moving that
  logic out of its current load order.
