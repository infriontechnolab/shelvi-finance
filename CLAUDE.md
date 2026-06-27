# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Design-only admin theme — Laravel 13 (PHP 8.3) + Tailwind CSS v4 + Blade. **No React, no Alpine.**
shadcn/ui visual language reproduced as native Blade components using Tailwind utilities. No real
models/DB/auth — pages render demo data from in-memory Collections. Brand name in UI is "Shelvi".

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

### Routes → DataTables (no controllers/models)
`routes/web.php` wires routes directly to Yajra DataTable service classes (`app/DataTables/`) via
closures. There are no controllers or Eloquent models beyond the skeleton `User`. Each table class
`extends BaseDataTable` and supplies only its `query()` (returns a demo `Collection`), `dataTable()`
(column renderers), `html()`, and `getColumns()`.

- A DataTable page route returns `$dataTable->render('pages.x')` — same URL serves the HTML page
  (GET) and the server-side JSON (DataTables ajax).
- The dashboard hosts multiple table widgets; each widget has its **own dedicated ajax route**
  (`/dashboard/recent-orders`, etc.) so an ajax request never receives the HTML page.

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

### Blade UI kit
`resources/views/components/ui/*` — shadcn-styled components (button, card, input, badge, dropdown,
combobox, table primitives). Pages in `resources/views/pages/`; shell in `components/layouts/admin.blade.php`
(sidebar nav array is defined inline at top of that file).
