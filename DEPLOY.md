# Shelvi Admin — Build & Deploy

Theme-aware admin layout. Laravel 13 + Tailwind v4 + Blade. **No React, no Alpine.** shadcn/ui
visual language reproduced as native Blade components (`resources/views/components/ui/*`) using
Tailwind utilities only — shadcn proper is React, which this stack excludes.

## Why assets are committed

The shared host has **no npm at runtime**. Frontend assets are compiled locally with Vite and the
output (`public/build/`) is committed/uploaded. `public/build` is deliberately removed from
`.gitignore`.

## Local build (run before every deploy)

```bash
npm install --registry https://registry.npmjs.org   # registry override: default npm config points at an unreachable mirror
npm run build                                        # writes public/build/ (CSS, JS, self-hosted fonts)
```

Then commit `public/build/` (and `public/fonts-manifest.json`) or upload it with the rest of the app.
Fonts (Instrument Sans) are self-hosted by the build — no runtime CDN call.

## Deploy to shared host

Upload the project (or `git pull`). Server only runs PHP/Composer:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Do **not** run any npm command on the server. If you change Blade/CSS/JS, rebuild locally and
re-upload `public/build/`.

## Theming

- Tokens: `resources/css/app.css` (shadcn zinc palette, oklch) under `:root` / `.dark`.
- Dark mode = `.dark` class on `<html>`, toggled by `resources/js/app.js`, persisted to
  `localStorage`. A tiny inline script in the layout `<head>` applies it before first paint (no flash).
- Light/dark/system: follows system when the user hasn't chosen; explicit toggle wins.

## Routes (design-only, no controllers/models)

`/` dashboard · `/customers` · `/products` · `/orders` · `/analytics` · `/settings`
