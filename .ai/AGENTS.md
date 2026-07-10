# Global Instructions — vibrisse-core Project

> **Target audience for this file:** Any AI model (Cursor, Antigravity, Claude, etc.) working on this project.

## Context & Mission

`vibrisse-core` is a **WordPress Starter Theme for agencies**. The AI is a workflow tool for the **developer**, not for the end client. Never introduce "client/admin"-oriented code into the Core.

**Multiple developers may work simultaneously on a project based on this theme.** Guardrails (Husky, lint, phpcs) are in place to ensure consistency. The AI must generate code that passes these checks.

## Tech Stack
| Tool | Usage |
|---|---|
| WordPress FSE | Global layout (Site Editor) |
| ACF Pro + Local JSON | Engine for custom business blocks |
| Tailwind CSS v4 + Vite | CSS/JS compilation (default — replaceable) |
| `.ai/` | Project AI context |

## CSS — Tailwind or Vanilla

Tailwind is the default. The design system is defined in `theme.json` and exposed via **CSS custom properties** (`--wp--preset--color--accent`, etc.) — framework-agnostic.

**Option 1 — Tailwind (default)**
`@theme` in `src/css/main.css` maps tokens to utility classes.
Classes are used directly in `render.php` files (`py-16`, `font-serif`, `bg-accent`...).

**Option 2 — Vanilla CSS / SCSS / other framework**
1. Remove `@import "tailwindcss"` from `src/css/main.css`.
2. Uninstall: `npm uninstall tailwindcss`.
3. Style each block in its co-located `style.css` using BEM + custom properties:
   ```css
   .vibrisse-hero {
     background-color: var(--wp--preset--color--base);
     color: var(--wp--preset--color--contrast);
     padding-block: var(--wp--preset--spacing--80);
   }
   ```
4. Replace utility classes in `render.php` files with BEM classes.

> **V2 Note:** The target architecture uses style-agnostic blocks — only BEM classes in `render.php`, all visual styling in `style.css`. Tailwind via `@apply` or vanilla CSS depending on the project.

## Absolute Rules (Never break)

1. **Zero Native Gutenberg React**: For any custom component, use **exclusively** the ACF Blocks architecture (`block.json` + PHP `render.php`). Never use a React `save()` function.

2. **Zero hard-coded colors/fonts**: Every design token goes through `theme.json`. Tailwind via `@theme`. Never use a hex value in CSS or PHP.

3. **Zero modifications to `functions.php`**: Block auto-registration is handled by `inc/acf.php`. Adding a new block = creating a folder in `blocks/custom/`.

4. **Zero additions to `inc/` without structural justification**: Business logic belongs in blocks, not in the theme core.

5. **Local JSON required**: Every ACF field group must exist as a `.json` file in `acf-json/` before it can be considered "deliverable".

6. **Tailwind PHP scan**: Verify that the Tailwind configuration scans `blocks/**/*.php` to avoid purging classes from `render.php` files in production.

7. **`IS_VITE_DEVELOPMENT` = `false` in production**: This flag in `inc/assets.php` controls asset loading (Vite HMR vs. compiled build) AND styles in the Gutenberg editor. Never commit this flag set to `true`. Tailwind styles are injected into the editor automatically — do not duplicate them manually via `add_editor_styles()`.


## Web-First Principle: Prefer Native APIs

Before introducing an external dependency (npm or CDN), ask yourself: **does the browser or WordPress not already handle this natively?**

Preferred native replacements:
| External lib ❌ | Native alternative ✅ |
|---|---|
| JS accordion lib | `<details>` / `<summary>` HTML |
| Modal lib | `<dialog>` + `.showModal()` method |
| ScrollReveal, AOS | `IntersectionObserver` API + CSS |
| jQuery `.animate()` | CSS `transition` / `animation` |
| Sticky header lib | CSS `position: sticky` |
| JS smooth scroll | CSS `scroll-behavior: smooth` |
| Slider/carousel lib | CSS `scroll-snap` |
| Moment.js | Native `Intl.DateTimeFormat` |

This rule also applies to blocks: if an interaction can be solved with pure HTML/CSS, that is the preferred approach (e.g. FAQ block using `<details>/<summary>`, zero JS).

## Accessibility Rules (A11y)

All generated code must comply with WCAG 2.1 AA principles:
- **Semantic HTML**: `<header>`, `<main>`, `<nav>`, `<section>`, `<article>`, `<figure>`, etc. Never use a `<div>` where a semantic element exists.
- **Alternative text**: Every `<img>` must have an `alt` attribute. If the image is decorative, use `alt=""` + `aria-hidden="true"`.
- **Minimal ARIA**: Only add ARIA attributes when native HTML is insufficient. Use `aria-hidden="true"` on decorative elements (SVG icons, etc.).
- **Contrast**: Never create text/background combinations that fail the minimum 4.5:1 ratio (managed by the `theme.json` design system).
- **Visible focus**: Never remove `outline` without replacing it with an equivalent visible focus style.
- **Landmarks**: Every page must have a unique `<main>` with `id="main-content"` for the skip link. Blocks are `<section>` elements.
- **Skip link**: Already implemented in `inc/setup.php` via `wp_body_open`. Do not remove it.

## SEO & GEO (Generative Engine Optimization)

Generative engines (SGE, Perplexity, ChatGPT Search) read structured HTML first.
- **Heading hierarchy**: A single `<h1>` per page (in the Hero block). Blocks use `<h2>`. Never skip heading levels.
- **JSON-LD FAQ**: The FAQ block automatically emits a `FAQPage` schema. For new blocks with structured data, add the corresponding schema.
- **JSON-LD Organization**: The child theme declares its type via the `vibrisse_organization_schema` filter in `inc/seo.php`. Always implement it (LocalBusiness, Restaurant, MedicalBusiness, etc.).
- **`llms.txt`**: Managed by `inc/seo.php`. It exposes the site structure to LLM crawlers.
- **Explicit content**: The AI generates factual, direct content. Avoid vague "showcase content".

## Internationalisation (i18n)

Every user-visible string in PHP must be translatable. Core text domain: `vibrisse-core`. Child theme text domain: its own slug.

```php
// Simple string
__( 'Text', 'vibrisse-core' )
_e( 'Text', 'vibrisse-core' )          // direct echo

// String in an HTML attribute
esc_attr__( 'Text', 'vibrisse-core' )  // always combined with esc_attr
esc_html__( 'Text', 'vibrisse-core' )  // always combined with esc_html

// String with context (for translators)
_x( 'Text', 'context', 'vibrisse-core' )

// Plural
_n( 'One item', '%d items', $count, 'vibrisse-core' )
```

Rule: if the string is hard-coded in PHP and visible to the user, it must go through an i18n function. No exceptions.

## Images — Optimisation & Best Practices

- **ACF return format**: Always `array` (not `url`, not `id`). The array contains `url`, `alt`, `width`, `height`, `sizes` — everything needed for a complete `<img>` tag.
- **Use `wp_get_attachment_image()`** instead of a manual `<img>` tag — automatically generates `srcset`, `sizes`, `loading`, and `decoding`.
- **`loading="eager"`** only for the LCP (Hero image above the fold). Everything else: `loading="lazy"`.
- **`decoding="async"`** on all lazy-loaded images.
- **No CSS `background-image`** for content images — use `<img>` with `object-fit: cover` inside a container. Better LCP and better accessibility.
- **`alt` required**: descriptive for content images, empty (`alt=""`) for decorative ones.

```php
// ✅ Correct
$image = get_field( 'image' ); // return_format = array
if ( $image ) {
    echo wp_get_attachment_image(
        $image['ID'],
        'large',
        false,
        [ 'loading' => 'lazy', 'decoding' => 'async' ]
    );
}

// ❌ Incorrect
echo '<img src="' . $image['url'] . '" alt="">';
```

## Quick Workflows

**New block** → Skill `new-block`
```
blocks/custom/[name]/block.json + render.php + style.css
acf-json/group_vibrisse_[name].json
→ Auto-registered. No further action needed.
```

**New CPT + Taxonomy** → Skill `new-cpt`
```
vibrisse-[client]/inc/post-types.php
vibrisse-[client]/acf-json/group_[slug]_fields.json
vibrisse-[client]/templates/archive-[slug].html
vibrisse-[client]/templates/single-[slug].html
→ require_once in the child theme's functions.php.
```

**LocalBusiness JSON-LD** → In the child theme's `functions.php`
```php
add_filter( 'vibrisse_organization_schema', function() {
    return [ '@context' => 'https://schema.org', '@type' => 'LocalBusiness', ... ];
} );
```

## Archives, Singles & FSE Templates

In FSE (Full Site Editing), templates live in `block-templates/` (or `templates/` in the child theme). WordPress selects the template according to its native hierarchy.

### Template naming (WordPress hierarchy)

| File | Page |
|---|---|
| `index.html` | Universal fallback (already in the Core) |
| `front-page.html` | Homepage |
| `single.html` | Blog post |
| `single-[slug].html` | Detail page for a specific CPT |
| `archive.html` | Generic blog archive |
| `archive-[slug].html` | Archive for a specific CPT |
| `taxonomy-[slug].html` | Taxonomy archive |
| `404.html` | Error page |
| `search.html` | Search results |
| `page.html` | Generic static page |

### Rules

- Templates in the **child theme** take priority over those in the Core.
- An FSE template is an HTML file that uses native Gutenberg blocks (Query Loop, Post Title, Post Content, etc.) — no PHP.
- The `<main>` element of the layout must have `id="main-content"` for the skip link.
- The 404 page and the search page are generated in the **child theme** (they are project-specific).

### Minimal pattern for a CPT archive template

Create `vibrisse-[client]/templates/archive-[slug].html`:
```html
<!-- wp:template-part {"slug":"header","theme":"vibrisse-core","area":"header"} /-->
<main id="main-content">
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:query {"queryId":1,"query":{"postType":"[slug]"}} -->
  <div class="wp-block-query">
    <!-- wp:post-template -->
      <!-- wp:post-title {"isLink":true} /-->
      <!-- wp:post-excerpt /-->
    <!-- /wp:post-template -->
    <!-- wp:query-pagination /-->
    <!-- wp:query-no-results -->
      <!-- wp:paragraph --><p>No results.</p><!-- /wp:paragraph -->
    <!-- /wp:query-no-results -->
  </div>
  <!-- /wp:query -->
</div>
<!-- /wp:group -->
</main>
<!-- wp:template-part {"slug":"footer","theme":"vibrisse-core","area":"footer"} /-->
```

## Client Hydration Workflow (For the agency)
```
1. Fill in .ai/CLIENT.md
2. Run the "theme-hydration" Skill
→ theme.json + .ai/skills/client-voice/ are updated automatically.
```

## Headless Mode (Next.js, Nuxt, Astro)

If `VIBRISSE_HEADLESS_MODE` is set to `true` in `functions.php`:
1. WordPress acts purely as a headless CMS.
2. The frontend is automatically redirected to `VIBRISSE_HEADLESS_FRONTEND_URL` (defined in `wp-config.php`).
3. The WP REST API automatically exposes the page's block structure and ACF data under the `vibrisse_blocks` property.

To generate a frontend component for an ACF block, use the **`export-headless`** AI Skill. It will read the `block.json` and `render.php` and generate the equivalent React/Vue/Svelte component using the same Tailwind classes.

## Out of Scope
- Content generation from the WordPress admin (→ see `experimental/`)
- Page builders or complex React components
- Node.js production dependencies (`dependencies` in `package.json`)
- Direct modifications to `functions.php` to register blocks

## Plugins: Selection Criteria

`vibrisse-core` does not prescribe any plugin. When a functional need arises (forms, SEO, caching...), use the `plugin-check` Skill to evaluate options.

**Non-negotiable criteria for any plugin added to the project:**
1. **FSE-compatible**: native Gutenberg blocks, no proprietary shortcodes.
2. **No lock-in**: data in a standard format, plugin deactivatable without breaking the site.
3. **Lightweight**: does not inject CSS/JS on pages where it is not used.
4. **Actively maintained**: last update < 6 months ago.

**Web-First rule applied to plugins:** Before looking for a plugin, check whether the need can be solved natively (HTML, ACF, WordPress REST API). One less plugin = one less dependency.


## ACF Options Page — Global Settings

A very common agency pattern: phone number, address, social networks, footer copyright.
These global settings — available site-wide — go through an **ACF Options Page**.

Create in `vibrisse-[client]/inc/options.php`:

```php
<?php
/**
 * ACF Options Page — Global site settings.
 * Requires ACF Pro.
 *
 * @package Vibrisse[ClientName]
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'acf_add_options_page' ) ) {
    return;
}

acf_add_options_page( [
    'page_title' => __( 'Site Settings', '[child-text-domain]' ),
    'menu_title' => __( 'Settings', '[child-text-domain]' ),
    'menu_slug'  => 'vibrisse-settings',
    'capability' => 'manage_options',
    'icon_url'   => 'dashicons-admin-generic',
    'position'   => 2,
] );
```

In the child theme's `functions.php`: `require_once get_stylesheet_directory() . '/inc/options.php';`

Create the ACF group `acf-json/group_[client]_options.json` with `location` set to `options_page == vibrisse-settings`.

Usage in templates:
```php
// Fields from an Options Page require the second argument 'option'
$phone = get_field( 'phone', 'option' );
$email = get_field( 'contact_email', 'option' );
```

## GDPR / Cookie Consent

Required for any site served to EU users. Recommended lock-in-free approach:

**Plugin choice:** Use the `plugin-check` Skill with the requirement "GDPR cookie consent". Options to evaluate: Complianz, CookieYes, Klaro (open source).

**Specific criteria to verify for this use case:**
- Does the plugin block Google Fonts before consent? (significant GDPR risk)
- Does it integrate Google Consent Mode v2?
- Does it block third-party scripts (Analytics, Facebook Pixel) before consent?
- Does it preserve the Lighthouse score (no heavy iframes for the banner)?

**Impact on the theme:** Google Fonts loaded via `theme.json` (`fontFace`) may be blocked before consent. The clean alternative: download fonts and serve them locally (no external requests, no GDPR issue).

```json
// theme.json — Self-hosted fonts (GDPR-friendly)
{
  "fontFace": [{
    "src": ["file:./assets/fonts/inter-regular.woff2"],
    "fontWeight": "400"
  }]
}
```

## Quality Rules (Guardrails)
All AI-generated code must comply with:
- **PHP:** WordPress Coding Standards (tab indentation, PHPDoc, `sanitize_*`, `esc_*`).
- **JS:** ESLint flat config (ES2024, `const`/`let` only, no `var`).
- **CSS:** Stylelint — no hard-coded values, everything goes through `--wp--preset--*` custom properties.
- **Tailwind:** Use only classes from the Design System (`bg-base`, `text-contrast`, `font-serif`, etc.). No arbitrary-value classes (`bg-[#123456]`).
