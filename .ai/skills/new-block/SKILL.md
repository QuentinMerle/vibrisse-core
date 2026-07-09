---
name: new-block
description: Generates a complete new ACF block (block.json, render.php, style.css, acf-json) following all quality standards of the vibrisse-core theme (A11y, SEO, Performance, Web-First).
---

# Skill: Creating a New Block

You are a WordPress expert (FSE + ACF Pro) and a frontend developer with a strong focus on accessibility, performance, and SEO. Your mission is to create a complete ACF block for the `vibrisse-core` theme.

## Step 1 — Understand the requirement

Before writing a single line, ask yourself:
- **What is the business name of the block?** (e.g. "Product Card", "Team Section")
- **What data does this block display?** (text, images, links, repeatable lists?)
- **Does this block have visual variants?** (e.g. reversed layout, dark background)
- **Does this block contain structured data?** → If so, which schema.org type? (FAQPage, Product, Event, Person…)

## Step 2 — The 4 files to create

The block will be named `vibrisse/[block-name]` and will live in:
- `blocks/custom/[block-name]/block.json`
- `blocks/custom/[block-name]/render.php`
- `blocks/custom/[block-name]/style.css`
- `acf-json/group_vibrisse_[block_name].json`

If you are working in a **child theme**, replace `blocks/custom/` with the `blocks/custom/` folder of your child theme. The scanner in `inc/acf.php` of the parent will automatically detect them.

## Step 3 — Reference template to follow

### `block.json` (Metadata)
```json
{
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "name": "vibrisse/[nom]",
  "title": "[Titre lisible]",
  "description": "[Une phrase décrivant le bloc]",
  "category": "layout",
  "icon": "[icône WordPress dashicons]",
  "keywords": ["[mot-clé1]", "[mot-clé2]", "vibrisse"],
  "acf": {
    "mode": "preview",
    "renderTemplate": "render.php"
  },
  "supports": {
    "align": ["wide", "full"],
    "anchor": true,
    "jsx": true
  },
  "style": "file:./style.css"
}
```

### `render.php` — Mandatory quality contract

```php
<?php
/**
 * [Nom du Bloc] Block Template.
 *
 * @package VibrisseCore (ou NomDuThèmeEnfant)
 */

// 1. RÉCUPÉRATION DES DONNÉES
// Toujours prévoir un fallback pour éviter un bloc blanc en mode preview.
$title  = get_field( 'title' ) ?: 'Titre par défaut';
$items  = get_field( 'items' ) ?: []; // Pour les Repeaters

// 2. CLASSES CSS
// Combiner les classes du bloc avec les classes Tailwind du design system.
$classes = 'vibrisse-[nom] py-16 lg:py-24 px-6';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
?>

<!-- 3. STRUCTURE HTML : Respecter la sémantique -->
<!-- section > h2 pour les blocs standards -->
<!-- Toujours fournir un id pour les ancres (#) -->
<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
    <div class="max-w-6xl mx-auto">

        <?php if ( $title ) : ?>
            <!-- h2 dans les blocs (le h1 est réservé au bloc Hero) -->
            <h2 class="font-serif text-3xl lg:text-5xl font-bold">
                <?php echo esc_html( $title ); ?>
            </h2>
        <?php endif; ?>

        <!-- ... contenu du bloc ... -->

    </div>
</section>

<!-- 4. JSON-LD (Si le bloc contient des données structurées) -->
<?php if ( ! empty( $items ) && ! $is_preview ) : ?>
<script type="application/ld+json">
<?php echo wp_json_encode( [
    '@context' => 'https://schema.org',
    '@type'    => 'ItemList', // Adapter selon le type de données
    // ...
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
</script>
<?php endif; ?>
```

## Step 4 — The Quality Checklist (to validate before delivery)

### Performance
- [ ] No external JavaScript libraries imported (slider, modal, accordion…). Use native APIs (`<details>`, `<dialog>`, `scroll-snap`, `IntersectionObserver`).
- [ ] Images: `loading="lazy"` attribute on all images that are not above the fold. `loading="eager"` attribute on Hero images only.
- [ ] No inline `style=""` values. Everything goes through Tailwind or CSS custom properties.

### Accessibility (A11y)
- [ ] Semantic HTML: `<section>`, `<figure>`, `<blockquote>`, `<details>`… rather than `<div>`.
- [ ] Every `<img>` tag has an `alt` attribute. If decorative: `alt=""` + `aria-hidden="true"`.
- [ ] Decorative SVG icons have `aria-hidden="true"`.
- [ ] `<a>` links have visible text or an explicit `aria-label`.
- [ ] Focus is visible on all interactive elements (do not remove `outline` without a replacement).
- [ ] Text/background contrast complies with WCAG 2.1 AA (guaranteed by the design system tokens).

### SEO & GEO
- [ ] Only one `<h2>` per block (unless a specific business need requires otherwise). Never a `<h1>` in any block other than the Hero.
- [ ] If the block contains structured data (FAQ, Products, Events, Reviews), add the corresponding schema.org JSON-LD.
- [ ] Texts are informative and factual (avoid generic "lorem ipsum" or "showcase" content).

### WordPress Code
- [ ] All PHP outputs are escaped: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses_post()`.
- [ ] All user inputs are sanitised: `sanitize_text_field()`, `absint()`, etc.
- [ ] The block has a visual fallback if ACF fields are empty (no blank block).
- [ ] The `acf-json/group_vibrisse_[name].json` file is created with the corresponding field keys.

### Design System
- [ ] Only Tailwind classes from the design system: `bg-base`, `bg-muted`, `bg-contrast`, `bg-accent`, `text-contrast`, `font-sans`, `font-serif`.
- [ ] No arbitrary Tailwind values (`bg-[#123456]`). If a colour is missing, add it to `theme.json` first.
- [ ] Spacing uses the design system scale (`py-16`, `gap-8`, etc.).

## Full Example: "Team Card" Block

To see an example of the complete architecture, refer to the code in `blocks/custom/testimonials/` which follows this exact pattern (ACF Repeater, `<figure>` semantics, empty fallback, design system classes).
