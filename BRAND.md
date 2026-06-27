# Shelvi — Brand Guidelines

Financial / receipt-tracking app. Warm, trustworthy, India-first (INR). Orange-amber
identity on a deep indigo base.

---

## 1. Logo

`public/logo.svg` — circular emblem, serif **S** monogram.

| Element | Spec |
|---|---|
| Outer ring | Orange gradient, top-left `#F9A01B` → bottom-right `#ED6626` (135°) |
| Inner mark + "S" | Indigo `#2C2359` |
| Monogram font | Times New Roman / Georgia serif, weight normal |
| Viewbox | `0 0 240 240`, mark inset `translate(10,10)` |
| Favicon | Same mark on white `#ffffff` rounded square, `rx=48` |

Clear space: keep ≥ 10% of mark width clear on all sides. Don't recolor the gradient,
don't swap the serif S for a sans glyph.

---

## 2. Color

All values **oklch** (design-system mandate). Hex shown for reference only.

### Brand core
| Token | oklch | ~Hex | Use |
|---|---|---|---|
| Primary | `oklch(0.59 0.22 27)` | ~#E0532A orange-red | Buttons, links, active, focus ring |
| Primary fg | `oklch(0.99 0.002 260)` | ~#FCFCFD | Text on primary |
| Secondary | `oklch(0.94 0.025 25)` | ~#F7E4DE soft peach | Chips, avatars, subtle fills |
| Secondary fg | `oklch(0.49 0.19 27)` | ~#B33E1E | Text on secondary |
| Logo indigo | `#2C2359` | — | Logo only / sidebar base |

### Light theme (`:root`)
| Token | oklch |
|---|---|
| background | `oklch(0.975 0.004 260)` |
| foreground | `oklch(0.25 0.035 260)` |
| card / surface | `oklch(1 0 0)` |
| muted | `oklch(0.968 0.007 247.896)` |
| muted-foreground | `oklch(0.554 0.046 257.417)` |
| accent | `oklch(0.95 0.01 260)` |
| border | `oklch(0.91 0.01 260)` |
| input | `oklch(0.89 0.012 260)` |
| ring | `oklch(0.59 0.22 27)` (= primary) |
| destructive | `oklch(0.577 0.245 27.325)` |
| overlay | `oklch(0.12 0.03 260 / 55%)` |

### Sidebar (dark indigo, both themes in light mode)
| Token | oklch |
|---|---|
| sidebar | `oklch(0.25 0.052 259)` deep indigo |
| sidebar-foreground | `oklch(0.97 0.005 260)` |
| sidebar-primary | `oklch(0.63 0.22 27)` orange |
| sidebar-accent | `oklch(0.34 0.065 258)` |
| sidebar-hover | `oklch(0.29 0.055 259)` |
| sidebar-muted | `oklch(0.75 0.025 260)` |
| sidebar-border | `oklch(0.38 0.045 260)` |

### Charts
1 `oklch(0.59 0.22 27)` orange · 2 `oklch(0.47 0.13 251)` blue ·
3 `oklch(0.72 0.15 170)` teal · 4 `oklch(0.72 0.15 75)` amber ·
5 `oklch(0.63 0.16 310)` purple

### Signature shadow
`--shadow-accent: 0 8px 24px oklch(0.59 0.22 27 / 22%)` — orange-tinted lift on primary CTAs.

Dark theme (`.dark`) flips to indigo background `oklch(0.129 0.042 264.695)`, near-white
primary, and translucent borders. Full set in `src/styles.css`.

---

## 3. Typography

Two Google fonts, loaded via `<link>` in `src/routes/__root.tsx`.

| Role | Family | Weights | Token |
|---|---|---|---|
| Body / UI | **Manrope** | 400 500 600 700 | `--font-sans` → `font-sans` (default) |
| Display / headings / numbers | **Plus Jakarta Sans** | 600 700 800 | `--font-display` → `font-display` |

```
family=Manrope:wght@400;500;600;700
family=Plus+Jakarta+Sans:wght@600;700;800
```

### Rules
- Headings, page titles, KPI numbers, currency amounts → `font-display font-bold`
  (often with `tracking-tight` on big titles).
- Body text, labels, table cells → Manrope (default), 400–600.
- Money values always `font-display font-bold` (e.g. `₹4,85,000`), right-aligned in tables.
- Logo wordmark "SHELVI" → `font-display text-sm font-bold`.

### Observed scale (Tailwind)
| Use | Class |
|---|---|
| Hero KPI | `font-display text-3xl font-bold` |
| Page title | `font-display text-xl md:text-2xl font-bold tracking-tight` |
| Section heading | `font-display text-lg font-bold` |
| Card heading | `font-display font-bold` |
| Body | `text-sm` Manrope |

---

## 4. Shape & elevation

| Token | Value |
|---|---|
| `--radius` | `0.75rem` (12px) base |
| sm / md / lg | `radius-4` / `radius-2` / `radius` |
| xl … 4xl | `+4 / +8 / +12 / +16 px` |

- Cards, inputs, buttons use `rounded-lg`/`rounded-xl`; avatars `rounded-full` or `rounded-xl`.
- Primary CTA elevation = `--shadow-accent` (orange glow), not neutral gray.
- Borders 1px, `--border` token; focus outline = ring (primary) `offset 2px`.

---

## 5. Stack context

shadcn/ui **new-york** style · Lucide icons · base color slate · Tailwind v4
CSS-variable theming · `.dark` class strategy. To add a semantic color: define in
`:root` + `.dark`, register in `@theme inline` as `--color-<name>`.
