# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Design-only **finance/receivables** admin panel — Laravel 13 (PHP 8.3) + Tailwind CSS v4 + Blade.
**No React, no Alpine.** shadcn/ui visual language reproduced as native Blade components using Tailwind
utilities. No real DB/auth — pages render in-memory data behind a repository seam (see below), so a real
database can slot in without touching controllers or views. Brand name in UI is "Shelvi". Domain pages:
dashboard, bank accounts, party management, money received/paid, party ledger, cheque management, reports.

Money is formatted with `App\Support\Inr` (Indian lakh/crore grouping, `₹XX,XX,XXX`); dates are stored
ISO and rendered via `App\Support\Dates::human` (server) / `window.fmtDate` (client) so DataTables sort
chronologically rather than lexically.

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

### Routes → Controllers → DataTables (no Eloquent models)
`routes/web.php` wires resource-style routes to thin controllers (`app/Http/Controllers/`). There are
no Eloquent models beyond the skeleton `User`; data comes from the repository seam (below). Each list
view is a Yajra DataTable service (`app/DataTables/`) that `extends BaseDataTable` and supplies
`query()` (returns a `Collection` from an injected repository), `dataTable()` (column renderers),
`html()`, and `getColumns()`.

- A controller `index()` returns `$dataTable->render('pages.x')` — same URL serves the HTML page
  (GET) and the server-side JSON (DataTables ajax).
- The dashboard hosts widgets with their **own dedicated ajax route**
  (`/dashboard/recent-txns`) so an ajax request never receives the HTML page.
- Create/edit are **one shared form page** per resource (`pages.x-form`), mode-detected by whether the
  record var is null; the controller passes the record (or `abort(404)`) plus form options.

### Repository seam (swap-in point for a real DB)
Controllers and DataTables depend on **contracts** in `app/Repositories/Contracts/` (Party, Bank,
Cheque, Ledger, Money, Dashboard, Report), bound to in-memory implementations in
`app/Repositories/Mock/` via the `REPOSITORIES` map in `AppServiceProvider::register()`. To go live,
write Eloquent implementations of the same interfaces and flip the map — controllers, DataTables, and
views don't change. DataTables receive their repository through **constructor injection** (Yajra's base
has no constructor, so this is safe); the container resolves them because controllers type-hint them.

`App\Data\Mock` is now a **raw-fixture class only** (the seed data). Finders/aggregation/options were
moved out: static select lists live in `config/options.php`, and aggregates are typed readonly DTOs
(`App\Data\LedgerSummary`, `App\Data\ChequeStats`) returned by the Ledger/Cheque repositories. Views
read DTOs as objects (`$summary->opening`, `$stats->bounced`), not arrays.

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
`draw.dt` so ajax-injected rows (action buttons) get icons. Fonts (Manrope, Plus Jakarta Sans) are
self-hosted by the Vite build via `bunny()` in `vite.config.js` — no runtime CDN.

### Blade UI kit & shell
`resources/views/components/ui/*` — shadcn-styled components (button, card, input, badge, dropdown,
combobox, table primitives). Pages in `resources/views/pages/`. The shell
`components/layouts/admin.blade.php` is shell-only (head, no-flash script, title/subtitle/actions
slots) and composes extracted components: `components/{sidebar,navbar,confirm-dialog,toast}.blade.php`.
The sidebar reads its nav tree from **`config/navigation.php`** (not inline).

### Forms & client validation
Create/edit forms carry `data-validate`; `initFormValidation()` in `app.js` wires
[jquery-validation](https://jqueryvalidation.org/) (keyed by input `name` — every field needs a unique
`name`, not just `id`). Required combobox hidden inputs emit `data-rule-required`. Use `:user-invalid`
(not `:invalid`) for error styling so blank required fields don't flag on first paint. Client validation
is **not** security — add a server `FormRequest` when real write routes land. Deletes are wired to a
confirm dialog via `data-confirm` (handled in `app.js`); in this design-only build delete just removes
the row from the DOM.
