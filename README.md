# Infrion Admin Theme

A clean, theme-aware admin panel built with **Laravel 13**, **Tailwind CSS v4**, and a
shadcn-inspired component set — **Blade only, no React**. Includes server-side **DataTables**
(Yajra) with a reusable base class, light/dark theming, and a collapsible sidebar.

## Stack

- Laravel 13 (PHP 8.3)
- Tailwind CSS v4 (Vite)
- Blade components (shadcn-styled, no JS framework)
- Lucide icons
- Yajra Laravel DataTables (server-side)

## Features

- Light / dark theme with no-flash boot and `localStorage` persistence
- Collapsible sidebar (icon rail + hover flyouts, nested menus), responsive off-canvas on mobile
- shadcn-style Blade UI kit: button, card, input, badge, avatar, dropdown, combobox, table
- Server-side DataTables (Orders, Customers, Products) and dashboard widgets, all via a shared
  `BaseDataTable` — page-size + status comboboxes, action column, shadcn styling
- Fixed app-shell layout with independently scrolling sidebar and content

## Getting started

```bash
composer install
cp .env.example .env
php artisan key:generate

npm install
npm run build        # or: npm run dev
php artisan serve
```

## Build & deploy (shared hosting, no npm at runtime)

Frontend assets are compiled locally with Vite and the output (`public/build/`) is committed, so
the server only needs PHP/Composer. Rebuild locally and re-upload `public/build/` after any
Blade/CSS/JS change. See `DEPLOY.md`.

## License

Proprietary — Infrion Technolab.
