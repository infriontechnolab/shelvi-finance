# Shelvi Finance

Finance & receivables admin panel built with **Laravel 13**, **Tailwind CSS v4**, and a
shadcn-inspired component set — **Blade only, no React, no Alpine**. Design-only demo: pages
render in-memory data behind a repository seam, so a real database can slot in without touching
controllers or views.

## Stack

- Laravel 13 (PHP 8.3)
- Tailwind CSS v4 (Vite, CSS-config)
- Blade components (shadcn-styled, no JS framework)
- jQuery + Lucide icons + Yajra Laravel DataTables (server-side)
- jquery-validation for client-side form checks

## Features

- **Dashboard** — KPIs, weekly chart, pending verifications, recent-transactions widget (own ajax route)
- **Bank accounts** — account cards + statement DataTable (credit/debit, running balance)
- **Party management** — customers / vendors / finance companies / agencies, with create + edit
- **Money received / paid** — inbound & outbound entries with method, bank, reference
- **Party ledger** — chronological debit/credit with running balance and a typed summary
- **Cheque management** — issue → deposit → due dates, clearing status, status tiles
- **Reports** — report-type catalogue
- INR formatting (Indian lakh/crore grouping, `₹XX,XX,XXX`), ISO-date storage with chronological sort
- Light / dark theme (no-flash boot, `localStorage`), collapsible sidebar (icon rail + hover flyouts)
- Shared create/edit form pages with jquery-validation and a confirm dialog for deletes

## Architecture

- **Routes → controllers → DataTables.** `routes/web.php` wires resource-style routes to thin
  controllers (`app/Http/Controllers/`); each list view is a Yajra DataTable service
  (`app/DataTables/`) extending `BaseDataTable`.
- **Repository seam.** Controllers and DataTables depend on contracts (`app/Repositories/Contracts/`),
  bound to in-memory mock implementations (`app/Repositories/Mock/`) in `AppServiceProvider`. Swap the
  binding map for Eloquent implementations to go live — nothing else changes.
- **Typed data.** Static select lists live in `config/options.php`; aggregates are readonly DTOs
  (`App\Data\LedgerSummary`, `App\Data\ChequeStats`); raw fixtures live in `App\Data\Mock`.
- **DataTable cells are Blade partials** (`resources/views/datatables/cells/`) using a semantic tone
  palette, so Tailwind scans every class — no `@source inline` safelist for badges/amounts/pills.
- **UI kit** in `resources/views/components/ui/`; app shell in `components/layouts/admin.blade.php`
  (sidebar nav from `config/navigation.php`).

See `CLAUDE.md` for the full architecture notes and `BRAND.md` for brand tokens.

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build        # or: npm run dev
php artisan serve
```

No database is required to run the demo — all pages render from in-memory data.

## Build & deploy (shared hosting, no npm at runtime)

Frontend assets are compiled locally with Vite and the output (`public/build/`) is committed, so
the server only needs PHP/Composer. Rebuild locally and re-upload `public/build/` after any
Blade/CSS/JS change. See `DEPLOYMENT.md`.

## License

Proprietary — Infrion Technolab.
