# Figma → Code Integration Rules (CLAUDE.md)

> Rules doc for using the **Figma MCP server** against this codebase: *Inventaire
> des artistes neuchâtelois·es (SCNE)* — a Laravel 13 + Livewire 4 + Filament 5 +
> Flux UI + Tailwind CSS v4 application. See also the dedicated skill at
> [.github/skills/figma-to-blade/SKILL.md](.github/skills/figma-to-blade/SKILL.md)
> and the Boost guidelines in [AGENTS.md](AGENTS.md).

---

## 0. TL;DR — Golden Rules

1. **Never paste the React/Tailwind output of `get_design_context` as-is.** It is a
   *reference only*. Translate it into Blade + Flux UI + project tokens.
2. **Use project design tokens** (`bg-brand`, `text-brand-muted`, etc.) — never
   raw hex codes. The mapping table lives in `.github/skills/figma-to-blade/SKILL.md`.
3. **Prefer Flux UI** components, fall back to Blade + Tailwind utilities, and use
   `<x-picto>` for the project's local SVG icon set.
4. **Preview every new component** in the gallery at `/dev/composants` before
   shipping.
5. **Run `vendor/bin/sail composer run dev`** so Vite picks up CSS/Tailwind
   changes — otherwise design tokens won't be available.

---

## 1. Token Definitions

### 1.1 Where tokens live

All design tokens are declared in a single Tailwind v4 `@theme` block:

- [resources/css/app.css](resources/css/app.css) — colors, fonts, accent
  aliases, plus Flux/`register-form` overrides.

There is **no separate `tokens.json` / Style Dictionary / token transformation
pipeline**. Tokens are CSS custom properties consumed directly by Tailwind v4 to
generate utility classes (e.g. `--color-brand-mint` → `bg-brand-mint`,
`text-brand-mint`, `border-brand-mint`).

### 1.2 Token format

```css
/* resources/css/app.css */
@theme {
    --font-sans: 'Public Sans', ui-sans-serif, system-ui, sans-serif, …;
    --font-serif: 'Lora', ui-serif, Georgia, 'Times New Roman', serif;

    --color-brand: #2e3d3c;             /* text-dark / bg-dark */
    --color-brand-teal: #477e7b;        /* accents, active borders */
    --color-brand-teal-hover: #3d6f6c;
    --color-brand-teal-light: #6dafae;
    --color-brand-cream: #fefcf7;
    --color-brand-paper: #fefefe;
    --color-brand-mint: #bfeceb;        /* CTA primary */
    --color-brand-mint-hover: #a9e3e2;
    --color-brand-muted: #5f6665;       /* form borders / helper text */
    --color-brand-track: #f3f4f4;
    --color-brand-hairline: #e1eeed;

    /* Neutral zinc scale (used by Filament admin) */
    --color-zinc-50 … --color-zinc-950

    /* Semantic aliases */
    --color-accent: var(--color-brand);
    --color-accent-content: var(--color-brand);
    --color-accent-foreground: var(--color-brand-paper);
}
```

### 1.3 Figma → project token map

Authoritative table is maintained in
[.github/skills/figma-to-blade/SKILL.md §2](.github/skills/figma-to-blade/SKILL.md).
Highlights:

| Figma variable                       | Project utility / variable |
| ------------------------------------ | -------------------------- |
| `structure/bg-dark` `#2e3d3c`        | `bg-brand` / `text-brand`  |
| `structure/bg-white` `#fefefe`       | `bg-brand-paper`           |
| `structure/bg-cream` `#fefcf7`       | `bg-brand-cream`           |
| `structure/text-gray` `#5f6665`      | `text-brand-muted`         |
| `forms/input/border-default`         | `border-brand-muted`       |
| `color-brand-hairline` `#e1eeed`     | `border-brand-hairline`    |
| `color-brand-mint` `#bfeceb`         | `bg-brand-mint`            |
| `color-brand-teal` `#477e7b`         | `text-brand-teal`          |

When a Figma token is missing here, **add the CSS variable to the `@theme` block
first**, then add the row to the skill mapping table — do not inline hex values
in Blade files.

---

## 2. Component Library

### 2.1 Architecture

Three layers, in order of preference:

1. **Flux UI** (`livewire/flux` v2 free + stubs published under
   `resources/views/flux/`) — primary component library.
   - Available free components: `avatar`, `badge`, `brand`, `breadcrumbs`,
     `button`, `callout`, `checkbox`, `dropdown`, `field`, `heading`, `icon`,
     `input`, `modal`, `navbar`, `otp-input`, `profile`, `radio`, `select`,
     `separator`, `skeleton`, `switch`, `text`, `textarea`, `tooltip`.
   - `<flux:button>` valid sizes: `xs`, `sm`, `base`. **`lg`/`xl` do not exist.**
2. **Project Blade components** — anonymous components under
   [resources/views/components/](resources/views/components/) (e.g.
   `<x-picto>`, `<x-search-bar>`, `<x-register.stepper>`).
3. **Livewire 4 components** for stateful UI — class + view pairs under
   [app/Livewire/](app/Livewire/) and
   [resources/views/livewire/](resources/views/livewire/), grouped by feature
   (`Artist/`, `Public/`, `Dev/`).

Filament 5 powers the back-office (`app/Filament/Resources/…`). Do **not** push
public-facing Figma designs through Filament resources.

### 2.2 Component gallery (= our "storybook")

Live preview at **`/dev/composants`** (route only available in non-production
environments).

- View: [resources/views/livewire/dev/component-gallery.blade.php](resources/views/livewire/dev/component-gallery.blade.php)
- Class: [app/Livewire/Dev/ComponentGallery.php](app/Livewire/Dev/ComponentGallery.php)
- Wrapper: `<x-dev.gallery-card label="…">…</x-dev.gallery-card>`

**Every new component implemented from Figma MUST get a gallery section** plus
an entry in the `$sections` array on the Livewire class.

---

## 3. Frameworks & Libraries

| Concern        | Stack                                                     |
| -------------- | --------------------------------------------------------- |
| Backend        | PHP 8.4, Laravel 13                                       |
| Server UI      | Livewire 4 (+ Alpine.js bundled)                          |
| Component kit  | Flux UI v2 (free tier)                                    |
| Admin panel    | Filament 5                                                |
| Styling        | Tailwind CSS **v4** (`@theme` + `@source` in CSS)         |
| Build / bundle | Vite 8 + `laravel-vite-plugin` + `@tailwindcss/vite`      |
| Auth           | Laravel Fortify v1 (passkeys, 2FA)                        |
| Testing        | Pest 4 (Feature + Browser)                                |
| Container      | Laravel Sail (Docker) — **all commands prefix `vendor/bin/sail`** |

Build entry points (declared in [vite.config.js](vite.config.js)):

```js
laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
tailwindcss(),
```

---

## 4. Asset Management

### 4.1 Storage

- **Bundled static assets** (logos, decorative SVGs): `resources/svg/`,
  imported via the `<x-picto>` component (inlined into HTML — see §5).
- **Public static files** (favicon, robots): `public/`.
- **User uploads** (artist portraits, attachments): Laravel filesystem under
  `storage/app/public/` exposed via `php artisan storage:link`.
- **Built JS/CSS**: emitted to `public/build/` by Vite (manifest-driven).

### 4.2 Optimization

- Tailwind v4 oxide engine tree-shakes utility classes by scanning `@source`
  globs in [resources/css/app.css](resources/css/app.css).
- SVGs are inlined (not `<img>`) so they can be styled via `class`/`fill="currentColor"`.
- Bunny Fonts CDN supplies `Public Sans` and `Lora` (loaded from the layout
  `<head>`). Do not self-host or re-add Google Fonts.

### 4.3 CDN

No bespoke CDN. Production is served via Laravel Cloud / standard Sail-built
artifacts. Vite manifest handles cache-busting.

---

## 5. Icon System

### 5.1 Three sources

1. **Project SVG set** — [resources/svg/icons/](resources/svg/icons/),
   `resources/svg/logos/`, `resources/svg/social/`. Hand-curated to match the
   Figma icon library.
2. **Heroicons** via Flux: `<flux:icon.pencil-square />`,
   `<flux:icon.x-mark />`, etc. Verify names on heroicons.com.
3. **Lucide** also available through Flux when a Heroicon is missing.

### 5.2 Usage

```blade
{{-- Project SVG (inlined, can be styled with text-* / size utilities) --}}
<x-picto name="search" class="h-4 w-4 text-brand-muted" />
<x-picto name="instagram" set="social" class="h-5 w-5" />

{{-- Heroicon via Flux --}}
<flux:icon.arrow-right class="size-4" />

{{-- Flux button + leading icon --}}
<flux:button icon="check" variant="primary">Valider</flux:button>
```

### 5.3 Naming convention

- Kebab-case filenames matching Figma layer names where possible:
  `arrow-left.svg`, `caret-down.svg`, `external-link.svg`.
- Sets: `icons` (default), `logos`, `social`.
- When importing a new icon from Figma:
  1. Export as SVG, run through SVGO (or strip width/height + add
     `fill="currentColor"`).
  2. Drop it in the matching `resources/svg/<set>/` folder.
  3. Reference via `<x-picto name="<filename-without-ext>">`.

---

## 6. Styling Approach

### 6.1 Methodology

- **Utility-first Tailwind v4** in Blade templates — primary styling method.
- **Scoped overrides** for Flux controls live in [resources/css/app.css](resources/css/app.css)
  under the `.register-form` class to avoid bleeding into Filament/admin
  surfaces. Follow the same pattern when restyling Flux for a new public surface
  (wrap in a scoping class).
- **No CSS Modules / no styled-components / no SCSS / no PostCSS plugins** apart
  from `@tailwindcss/vite` and `autoprefixer`.

### 6.2 Global styles

`resources/css/app.css` is the **only** global stylesheet. It:

1. Imports Tailwind and Flux defaults.
2. Declares `@source` globs (so Tailwind scans Blade views + Flux stubs +
   pagination views).
3. Defines `@custom-variant dark`.
4. Hides `[x-cloak]` until Alpine boots.
5. Declares the `@theme` token block (§1).
6. Provides scoped Flux + intl-tel-input overrides.

### 6.3 Responsive design

- Mobile-first; use Tailwind breakpoints (`sm:`, `md:`, `lg:`, `xl:`, `2xl:`).
- Public layouts use a content container approx. `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`.
- Match the Figma frame sizes: **375 (mobile)**, **768 (tablet)**, **1280
  (desktop)**. Cross-check each breakpoint against the corresponding Figma frame.

### 6.4 Dark mode

`@custom-variant dark (&:where(.dark, .dark *));` — apply variants as
`dark:bg-brand` etc. Currently only the Filament admin uses dark mode actively.

---

## 7. Project Structure (the bits that matter for Figma work)

```
resources/
├── css/app.css                          # Tokens + global styles (single source)
├── js/app.js                            # JS entry (Alpine ships with Livewire)
├── svg/{icons,logos,social}/            # Inlined SVG assets for <x-picto>
└── views/
    ├── layouts/{app,auth}.blade.php     # Top-level page chrome
    ├── components/                      # Anonymous Blade components (<x-*>)
    │   ├── picto.blade.php              # SVG inliner
    │   ├── register/                    # Public registration UI atoms
    │   └── dev/gallery-card.blade.php   # Gallery wrapper
    ├── livewire/
    │   ├── public/                      # Public-facing Livewire views
    │   ├── artist/                      # Artist portal views
    │   └── dev/component-gallery.blade.php
    └── flux/                            # Published Flux stubs (override here)

app/
├── Livewire/{Public,Artist,Dev,Actions}/ # Livewire 4 components, grouped by feature
├── Filament/Resources/                   # Admin (do NOT use for public Figma work)
└── View/Components/                      # PHP-backed Blade components (rare)
```

### Feature organization pattern

Each public feature is a triplet:

1. A Livewire class under `app/Livewire/Public/<Feature>.php`
2. A Blade view under `resources/views/livewire/public/<feature>.blade.php`
3. (Optional) Anonymous sub-components under `resources/views/components/<feature>/*.blade.php`

Mirror this when implementing a new Figma flow.

---

## 8. Figma MCP Workflow

### 8.1 Reading a design

Call `get_design_context` with:

- `fileKey` — segment after `/design/` in the Figma URL
- `nodeId` — the `node-id` query param with `-` replaced by `:`
  (e.g. `2439-38605` → `2439:38605`)
- `clientFrameworks`: `laravel,livewire,tailwind`
- `clientLanguages`: `php,html,css`

Use the **screenshot as visual truth** and the token names from the response —
ignore the React/Tailwind code body except as a structural hint.

### 8.2 Implementation checklist

1. Translate tokens via §1.3 (add new variables to `@theme` if missing).
2. Choose the lowest-cost layer: Flux > Blade component > Livewire component.
3. Use `<x-picto>` for project icons, `<flux:icon.*>` for Heroicons.
4. Use Alpine (`x-data`, `x-show`) for cosmetic interactivity; Livewire
   (`wire:model`, `wire:click`) for server state.
5. Add a `<x-dev.gallery-card>` preview in
   [component-gallery.blade.php](resources/views/livewire/dev/component-gallery.blade.php)
   and register the section in
   [ComponentGallery.php](app/Livewire/Dev/ComponentGallery.php).
6. Run `vendor/bin/sail composer run dev` (Vite) so token classes resolve.
7. Validate at `http://localhost/dev/composants` against the Figma screenshot
   (mobile + desktop frames).
8. Write a Pest test for any Livewire interaction
   (`vendor/bin/sail artisan make:test --pest <Name>Test`).
9. Format: `vendor/bin/sail bin pint --dirty --format agent`.

### 8.3 Code-to-design (pushing back to Figma)

Before calling `use_figma`, run `search_design_system` against the SCNE Figma
library and reuse existing components / variables. Do not invent new tokens on
the Figma side — propose additions to the design team instead.

---

## 9. Things NOT to do

- ❌ Don't inline hex colors in Blade — always go through `bg-brand-*` / `text-brand-*`.
- ❌ Don't use `<flux:button size="lg">` — it doesn't exist (max is `base`).
- ❌ Don't add `tailwind.config.js` — this project uses Tailwind v4 CSS-first
  config; tokens live in `@theme` inside `app.css`.
- ❌ Don't put public Figma designs through Filament resources.
- ❌ Don't introduce new global CSS files — extend `resources/css/app.css`.
- ❌ Don't apply Flux overrides without a scoping class (e.g. `.register-form`) —
  they will leak into Filament and break the admin UI.
- ❌ Don't run npm/node/composer directly — always prefix with `vendor/bin/sail`.
