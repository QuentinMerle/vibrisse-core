# Vision & Architecture — Vibrisse Core

Absolute source of truth for the project. Every developer (human or AI) working on this theme must read this document before starting.

## 1. The Ambition

`vibrisse-core` is a **tool for developers and agencies**, not for end clients.

**Goal:** enable an agency to kick off any WordPress client project with a clear process, automatic guardrails, and an operational AI copilot from the very first commit.

The value of AI lies in the developer workflow — design system hydration, block generation, brand consistency — not in the client's admin interface.

### What it is
- A **Parent Theme**: an immutable base, extended via a child theme per project.
- An **FSE + ACF Pro engine**: global layout via the Site Editor, structured data via ACF.
- A **structured agency workflow**: DA → Dev → Delivery, with defined entry points for each stage.

### What it is not
- A content-generation tool for the end client.
- A page builder.
- An "out-of-the-box" theme — the Core is neutral by design.

---

## 2. Architecture Decisions

### Why ACF and not native Gutenberg React?
In native Gutenberg, WordPress saves the generated HTML in the database. If a block changes, older posts display a "Block Validation" error. With ACF, only the **data** is saved; rendering happens server-side. An LLM generates ACF JSON without ever breaking the site.

### Why theme.json as the single source of truth?
No colour, font size, or spacing should ever be hard-coded in CSS or PHP. `theme.json` is the only source. Tailwind v4 is synchronised via `@theme`. Reskinning a client's brand = editing only their child `theme.json`.

### Why block colocation?
Each ACF block is an isolated folder inside `blocks/custom/` containing `block.json`, `render.php`, and `style.css`. Auto-registration scans both directories (parent + child). A client-specific block has zero impact on the Core.

---

## 3. Parent / Child Architecture

```
vibrisse-core/ (Parent — never modify after a project is initialised)
├── blocks/custom/           # Universal blocks (Hero, FAQ, CTA…)
├── acf-json/                # ACF field groups for Core blocks
├── .ai/                     # AI rules and Skills (shared across all projects)
├── inc/acf.php              # Parent + child scanner, deduplication by name
└── theme.json               # Neutral design system

vibrisse-[client]/ (Child — the project theme)
├── blocks/custom/           # Client-specific blocks (auto-detected, child overrides parent)
├── acf-json/                # ACF field groups for client-specific blocks
├── .ai/
│   ├── CLIENT.md            # Client context
│   └── skills/
│       └── client-voice/   # Business tone of voice
├── theme.json               # Design system override (colours, typography)
└── functions.php            # Enqueue child styles only
```

**Block priority rule:** `inc/acf.php` builds a deduplicated registry indexed by `name`. The child always overrides the parent. `register_block_type` is never called twice with the same identifier.

---

## 4. Process Entry Points

### Entry A — New project (init)
```
1. git clone vibrisse-core → npm install → composer install
2. Skill "design-import" or npm run design:import
   → theme.json + CLIENT.md populated from DA artefacts
3. Skill "theme-hydration"
   → Design system applied, client-voice Skill created
4. Skill "init-child-theme"
   → Child theme scaffold generated
5. Activate child theme → npm run dev
```

### Entry B — Day-to-day dev (blocks and content)
```
- New client block   → Skill "new-block"    (child theme)
- New plugin         → Skill "plugin-check"
- Block modification → Edit blocks/custom/  (child theme)
```

### Entry C — Rebrand
```
1. npm run design:import -- new-brand.json
   or Skill "design-import" on new assets
2. Skill "theme-hydration"
   → Zero PHP changes, zero manual CSS
```

### Entry D — Production deployment
```
npm run predeploy
→ Build + Lint + static llms.txt + SEO report + manual checklist
```

---

## 5. Available AI Skills

| Skill | Path | Trigger |
|---|---|---|
| `design-import` | `.ai/skills/design-import/` | DA delivers an artefact (image, PDF, CSS, JSON, URL) |
| `theme-hydration` | `.ai/skills/theme-hydration/` | After design-import or CLIENT.md is filled in |
| `init-child-theme` | `.ai/skills/init-child-theme/` | Start of a project |
| `new-block` | `.ai/skills/new-block/` | New block requirement |
| `new-cpt` | `.ai/skills/new-cpt/` | New recurring content type (CPT + taxonomy) |
| `plugin-check` | `.ai/skills/plugin-check/` | Identified functional requirement |

---

## 6. Automatic Guardrails

### Commit-time (Husky + lint-staged)
Every `git commit` automatically lints the modified files. A commit containing errors is rejected.

### Manual
| Command | Target |
|---|---|
| `npm run lint` | ESLint (JS) + Stylelint (CSS) |
| `composer run phpcs` | WordPress Coding Standards (PHP) |
| `npm run predeploy` | Full pre-delivery pipeline |

### Critical AI rules
- **Tailwind PHP scan**: `@source` in `src/css/main.css` — classes from `render.php` files are not purged in production.
- **ACF Pro Guard**: `inc/acf.php` silently disables itself if ACF Pro is absent.
- **acf-json/ is committed**: Source of truth for field groups, just like database migrations.
- **Child block override**: Registry deduplicated by `name` — never a double `register_block_type()`.

---

## 7. Out of Scope (v1)

- Content generation from the WordPress admin → see `experimental/`
- Page Builder or native Gutenberg React components
- Node.js dependencies in production
- Direct modifications to `functions.php` to register blocks

---

## 8. V2 Roadmap — Identified topics, not yet implemented

These topics have been evaluated and deliberately left out of Core v1. They are documented here so that a future contributor understands the decisions.

### Staging → Production (database migration)
**Problem:** Every WP developer loses time migrating URLs between environments (`http://local.test` → `https://client.com`).
**Recommended approach (manual for now):**
```bash
# On the staging/prod server, via WP-CLI:
wp db export backup-before-migration.sql
wp search-replace 'https://local.test' 'https://client.com' --all-tables
wp cache flush
```
**V2:** Integrate these commands into `wp vibrisse predeploy` with a `--migrate-from=URL` flag.

### Dark Mode
**Problem:** Increasingly expected by end users.
**Recommended approach:**
- Define alternative tokens in the child `theme.json` via `settings.custom`.
- Use `@media (prefers-color-scheme: dark)` to override CSS custom properties.
- Never store a preference in JS (dependency to avoid) — let the browser decide.
**V2:** Add `--color-base-dark`, `--color-contrast-dark` tokens to the design system.

### Security: HTTP Headers (CSP, X-Frame-Options…)
**Problem:** Security headers significantly improve the security score (securityheaders.com).
**Recommended approach (server-side, outside the theme):**
```nginx
# nginx.conf or .htaccess
add_header X-Frame-Options "SAMEORIGIN";
add_header X-Content-Type-Options "nosniff";
add_header Referrer-Policy "strict-origin-when-cross-origin";
```
**WordPress side (if no server access):**
```php
add_filter( 'wp_headers', function( $headers ) {
    $headers['X-Frame-Options']        = 'SAMEORIGIN';
    $headers['X-Content-Type-Options'] = 'nosniff';
    $headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
    return $headers;
} );
```
**V2:** Add this filter to `inc/performance.php` with a configurable CSP.

### Multilingualism (WPML / Polylang)
**Problem:** Multilingual sites are common in agency work.
**Recommended approach:**
- All PHP strings are already wrapped in i18n (✅ done in v1).
- For WPML or Polylang: add the text domain declaration in the child `functions.php` and prepare `.pot`/`.po`/`.mo` files.
- ACF blocks are natively WPML-compatible via the WPML String Translation module.
**V2:** Add an `init-multilingual` Skill that configures WPML or Polylang according to the agency's preference.

### Automated Testing
**Problem:** No safety net for visual or functional regressions.
**Recommended approach:**
- **PHP tests:** WP Browser (Codeception) or PHPUnit with WP_Mock for unit functions.
- **E2E tests:** Playwright on key pages (homepage, contact page, 404).
- **Visual tests:** Playwright screenshot comparison to detect CSS regressions.
**V2:** Add a `tests/` folder with basic Playwright tests (smoke tests on the 6 Core blocks).
