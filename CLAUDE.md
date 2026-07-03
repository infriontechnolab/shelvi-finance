# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Finance/receivables** admin panel — Laravel 13 (PHP 8.3) + Tailwind CSS v4 + Blade.
**No React, no Alpine.** shadcn/ui visual language reproduced as native Blade components using Tailwind
utilities. Real database (SQLite in dev) + real auth (Breeze, login-only) + Spatie role/permission RBAC.
Data flows through a repository seam (see below) so controllers/views stay decoupled from Eloquent.
Brand name in UI is "Shelvi". Domain pages: dashboard, bank accounts, party management, money
received/paid, party ledger, cheque management, reports, plus users & roles administration.

Money is stored as **paise** (`bigInteger`) and formatted for display with `App\Support\Inr` (Indian
lakh/crore grouping, `₹XX,XX,XXX`); FormRequests/seeders convert rupees→paise (×100), repositories
convert paise→rupees so views are unchanged. Dates are stored ISO and rendered via
`App\Support\Dates::human` (server) / `window.fmtDate` (client) so DataTables sort chronologically.

## Commands

```bash
composer install && npm install        # npm: if registry unreachable, add --registry https://registry.npmjs.org
composer dev                           # all-in-one: php serve + queue + pail logs + vite (concurrently)
npm run dev                            # vite dev server only
npm run build                          # compile assets → public/build/ (committed; see "Deploy")
php artisan test                       # full suite (PHPUnit, sqlite :memory:)
php artisan test --filter ExampleTest  # single test
./vendor/bin/pint                      # format (Laravel Pint)
```

## Deploy (critical constraint)

Shared host has **no npm at runtime**. `public/build/` is compiled locally, committed (removed from
`.gitignore`), and uploaded. **After any Blade/CSS/JS change, run `npm run build` and re-upload
`public/build/`** — the server never builds. Server runs only `composer install --no-dev` +
`artisan config:cache/route:cache/view:cache`. See `DEPLOY.md`.

## Architecture

### Routes → Controllers → DataTables
`routes/web.php` wires resource-style routes to thin controllers (`app/Http/Controllers/`), all behind
`auth` + a per-route `permission:` middleware. Eloquent models live in `app/Models/` (Party, Bank,
Cheque, Transaction, LedgerEntry, User); list data flows through the repository seam (below). Each list
view is a Yajra DataTable service (`app/DataTables/`) that `extends BaseDataTable` and supplies
`query()` (returns a `Collection` from an injected repository), `dataTable()` (column renderers),
`html()`, and `getColumns()`.

- A controller `index()` returns `$dataTable->render('pages.x')` — same URL serves the HTML page
  (GET) and the server-side JSON (DataTables ajax).
- The dashboard hosts widgets with their **own dedicated ajax route**
  (`/dashboard/recent-txns`) so an ajax request never receives the HTML page.
- Create/edit are **one shared form page** per resource (`pages.x-form`), mode-detected by whether the
  record var is null; edit/update/destroy use route-model binding. Writes go through a `FormRequest`
  (`app/Http/Requests/`) with a `toModel()` mapper (rupees→paise, password hashing, etc.).
- Money Received/Paid are currently **list-only** (no create/edit routes yet).

### Repository seam
Controllers and DataTables depend on **contracts** in `app/Repositories/Contracts/` (Party, Bank,
Cheque, Ledger, Money, Dashboard, Report), bound to implementations via the `REPOSITORIES` map in
`AppServiceProvider::register()`. The six domain repos are now **Eloquent** (`app/Repositories/Eloquent/`);
**Report alone stays a Mock** (`MockReportRepository`, a static catalogue). The old
`app/Repositories/Mock/` impls are retained as reference — flip the map to swap any back. DataTables
receive their repository through **constructor injection** (Yajra's base has no constructor, so this is
safe); the container resolves them because controllers type-hint them.

Repos return plain arrays/collections shaped for the views, and typed readonly DTOs for aggregates
(`App\Data\LedgerSummary`, `App\Data\ChequeStats` — read as objects: `$summary->opening`,
`$stats->bounced`). Static select lists live in `config/options.php`. Dev/test rows are seeded by
`database/seeders/` (`FinanceDataSeeder` for domain data, `RolesAndPermissionsSeeder` + `UserSeeder`
for auth); `App\Data\Mock` holds the raw fixtures the seeder draws from.

### Auth & access control
Breeze (blade) stripped to **login/logout only** — login view rebuilt on the app's own UI kit
(`resources/views/auth/login.blade.php`); no register/forgot/OAuth. RBAC via **Spatie
laravel-permission**: every route carries `permission:<name>` (aliased in `bootstrap/app.php`), and Blade
gates with `@can`. Three roles seeded: **superadmin** (all perms — a *secret owner account*, kept out of
every UI surface: users list, roles list, role pickers, direct-edit URLs 404), **admin** (all except
`users.*`/`roles.*`), **accountant** (operate-only subset). One source of truth in `App\Support\Access`
(the hidden role name, hidden permission groups, `assignablePermissionNames()`). Users have an
`is_active` flag. The permission matrix (`pages/roles-form`) never shows `users.*`/`roles.*`, so no
visible role can be granted them even via a crafted request.

### BaseDataTable (`app/DataTables/BaseDataTable.php`)
Holds all shared config and cell renderers so concrete tables stay thin:
- `commonParameters()` — server-side/paging/layout/language + an `initComplete` JS that swaps the
  native length `<select>` for the shadcn page-size combobox. Convention: each page provides
  `<div id="{tableId}-pagelen" hidden>` and it's relocated into the `.dt-length` slot + bound to
  `page.len()` centrally.
- Renderers (`avatar`, `statusPill`, `amount`, `mono`, `bold`, `muted`, `drCr`, `actions`) each render
  a Blade partial in `resources/views/datatables/cells/` via `view()->render()` and return the HTML —
  any column using them MUST be listed in `->rawColumns([...])`. Markup + Tailwind classes live in the
  partials, not in PHP strings.
- `statusPill($value, $tones)` / `amount($n, $tone)` take a **semantic tone key** (`success`, `warning`,
  `danger`, `info`, `accent`, `indigo`, `neutral` / `positive`, `negative`, `muted`, `plain`), not raw
  classes. The tone→class map lives in the partial.

### Tailwind v4 + DataTable cells
Tailwind v4 is configured in CSS, not a JS config. DataTable cell classes are scanned normally because
the cells are Blade partials under `resources/views` (covered by `@source '../views'`) — **no
`@source inline(...)` safelist needed** for badges/amounts/pills. The only remaining safelist entry is
for jquery-validation classes emitted from `app.js` (the scanner can't see JS strings).

### Theming
Tokens (`:root` / `.dark`, oklch, shadcn zinc + Shelvi orange-red primary) live in
`resources/css/app.css`. Dark mode = `.dark` on `<html>`. A no-flash inline script in
`components/layouts/admin.blade.php` `<head>` applies the theme before paint; `resources/js/app.js`
owns the toggle + `localStorage` (key `shelvi-theme`). Tailwind dark variant is `@custom-variant dark`.

### JS bundle (`resources/js/app.js`)
jQuery + `datatables.net` core (no default DataTables theme — styled via app.css tokens) + Lucide
icons. Icons render `<i data-lucide="...">` placeholders into SVG; `renderIcons()` re-runs on every
`draw.dt` so ajax-injected rows (action buttons) get icons. **Gotcha:** `createIcons()` uses a *curated*
`lucideIcons` set — a new icon name must be added to both the `import {…} from 'lucide'` and the
`lucideIcons` map in `app.js`, or it silently fails to render. Fonts (Manrope, Plus Jakarta Sans) are
self-hosted by the Vite build via `bunny()` in `vite.config.js` — no runtime CDN.

### Blade UI kit & shell
`resources/views/components/ui/*` — shadcn-styled components (button, card, input, badge, dropdown,
combobox, table primitives). Pages in `resources/views/pages/`. The shell
`components/layouts/admin.blade.php` is shell-only (head, no-flash script, title/subtitle/actions
slots) and composes extracted components: `components/{sidebar,navbar,confirm-dialog,toast}.blade.php`.
The sidebar reads its nav tree from **`config/navigation.php`** (not inline). Reports is a single leaf →
the `/reports` cards grid is the organized report menu.

### Forms & client validation
Create/edit forms carry `data-validate`; `initFormValidation()` in `app.js` wires
[jquery-validation](https://jqueryvalidation.org/) (keyed by input `name` — every field needs a unique
`name`, not just `id`). Required combobox hidden inputs emit `data-rule-required`. Use `:user-invalid`
(not `:invalid`) for error styling so blank required fields don't flag on first paint. On a valid form
the `submitHandler` calls `form.submit()` to perform the real POST (client validation is **not**
security — every write is guarded by a server `FormRequest`). Deletes are wired to a confirm dialog via
`data-confirm` (handled in `app.js`); on confirm it submits the enclosing method-spoofed DELETE form,
which hits the controller.

### Soft deletes
Party, Bank, Cheque, Transaction, LedgerEntry use the `SoftDeletes` trait (`deleted_at` column). A
delete sets `deleted_at` — the row stays (recoverable, FK-safe, no cascade crash) and is auto-hidden by
the global scope. Child lists that show a parent's name (cheques→party/bank, money→party/bank, dashboard
widgets, bank statement) eager-load the parent with `withTrashed()` so names survive a parent's
soft-delete. Aggregates (dashboard totals, ledger party pick) correctly exclude trashed rows. No restore
UI yet — recover via `Model::withTrashed()->restore()`.

### Flash toasts
Any controller flash (`->with('success'|'error'|'warning'|'info', …)`) is surfaced as a colored toast
top-right by `components/toast.blade.php` + `app.js`. One `<template>` per variant keeps all Tailwind
classes in scanned Blade (JS clones, never composes class strings). `window.showToast(message, type)` is
exposed for client-side use.

### Reports & export
Reports are data-only (slug → `{columns, rows}`) built by `EloquentReportRepository::generate($slug,
$period)`. Nine reports; `outstanding` is a live snapshot (ignores period), the rest honour a period
pill (all/today/week/month/quarter/year). The report-show page offers **CSV** and **PDF** export
(`reports.export` route, `{format}` in `csv|pdf`), both reusing the same `generate()` data and carrying
the active period. CSV is a streamed download with a UTF-8 BOM (Excel renders `₹`). PDF uses
**barryvdh/laravel-dompdf** rendering `resources/views/reports/pdf.blade.php` — a standalone template
with **inline CSS only** (dompdf ignores Tailwind; no flex/grid), A4 landscape.
