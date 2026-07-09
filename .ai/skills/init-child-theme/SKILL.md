---
name: init-child-theme
description: Generates the complete scaffold of a WordPress child theme based on vibrisse-core. To be run from the wp-content/themes/ folder at the start of a project.
---

# Skill: Child Theme Initialisation

You act as a Senior WordPress Architect. Your mission is to create the complete scaffold of the child theme that will be the final client theme for the project, based on `vibrisse-core`.

## Before you start

You must read:
1. `.ai/CLIENT.md` — The client's name, colours, and typography.
2. `.ai/CONTEXT.md` — The parent/child architecture.
3. `.ai/DESIGN.md` — The visual principles.

If `CLIENT.md` is not filled in, ask for the minimum information before continuing: **Client Name**, **Primary Colour**, **Text Colour**, **Heading Font**, **Body Font**.

## What you will generate

The child theme will live in `wp-content/themes/vibrisse-[client-slug]/`. Replace `[client-slug]` with the client's slug (e.g. `vibrisse-aurelia` for "Hôtel Aurelia").

### Files to create

#### `style.css` — Mandatory WordPress header
```css
/*
 * Theme Name:  [Nom du Client]
 * Description: Thème enfant de vibrisse-core pour [Nom du Client].
 * Template:    vibrisse-core
 * Version:     1.0.0
 * Author:      [Nom de l'Agence]
 */
```
> ⚠️ The `Template: vibrisse-core` line is mandatory. Without it, WordPress will not recognise the child theme.

#### `functions.php` — Child asset loading
```php
<?php
/**
 * [Nom du Client] Child Theme Functions.
 *
 * @package Vibrisse[NomClient]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue child theme styles après les styles parent.
 */
add_action( 'wp_enqueue_scripts', function() {
    // Le style parent est chargé automatiquement par WordPress.
    // On n'enqueue que la surcharge enfant si elle existe.
    $child_style = get_stylesheet_directory() . '/assets/css/child.css';
    if ( file_exists( $child_style ) ) {
        wp_enqueue_style(
            'vibrisse-child-style',
            get_stylesheet_directory_uri() . '/assets/css/child.css',
            [ 'vibrisse-style' ],
            filemtime( $child_style )
        );
    }
}, 20 );
```

#### `theme.json` — Design System override
Generate only the tokens that differ from the parent. Use the values from `CLIENT.md`.
```json
{
    "$schema": "https://schemas.wp.org/trunk/theme.json",
    "version": 3,
    "settings": {
        "color": {
            "palette": [
                { "slug": "base",     "color": "[COULEUR DE FOND]",  "name": "Base" },
                { "slug": "contrast", "color": "[COULEUR DE TEXTE]", "name": "Contrast" },
                { "slug": "muted",    "color": "[FOND SECONDAIRE]",  "name": "Muted" },
                { "slug": "accent",   "color": "[COULEUR PRIMAIRE]", "name": "Accent" }
            ]
        },
        "typography": {
            "fontFamilies": [
                {
                    "slug": "sans",
                    "name": "[Police Texte]",
                    "fontFamily": "'[Police Texte]', system-ui, sans-serif",
                    "fontFace": [{
                        "fontFamily": "[Police Texte]",
                        "src": ["https://fonts.gstatic.com/..."],
                        "fontWeight": "400 700"
                    }]
                },
                {
                    "slug": "serif",
                    "name": "[Police Titres]",
                    "fontFamily": "'[Police Titres]', Georgia, serif",
                    "fontFace": [{
                        "fontFamily": "[Police Titres]",
                        "src": ["https://fonts.gstatic.com/..."],
                        "fontWeight": "700"
                    }]
                }
            ]
        }
    }
}
```

#### `.ai/CLIENT.md` — Copy from the parent
Copy and fill in the `vibrisse-core/.ai/CLIENT.md` file into the child theme under `.ai/CLIENT.md`.

#### `.ai/skills/client-voice/SKILL.md` — Business Tone of Voice
Write this skill based on the sector and tone of voice from `CLIENT.md`. It will be read by any AI that generates content for this project.
```markdown
---
name: client-voice
description: Règles de rédaction et ton de voix pour [Nom du Client].
---

# Ton de Voix : [Nom du Client]

## Contexte
[Nom du Client] est [description courte de l'activité et du positionnement].

## Règles de Rédaction
- **Registre :** [Vouvoiement / Tutoiement]
- **Ton :** [Ex: Chaleureux, expert, rassurant]
- **À faire :** [Ex: Utiliser un vocabulaire précis du secteur]
- **À éviter :** [Ex: Le jargon technique, les superlatifs vides]

## Exemple de Bonne Formulation
[Exemple concret de phrase qui correspond au ton]
```

### Empty directories to create
```
vibrisse-[slug-client]/
├── acf-json/         # Client-specific ACF fields (committed)
├── blocks/
│   └── custom/       # Client business blocks (auto-detected by vibrisse-core)
└── assets/
    └── css/          # Compiled CSS (child.css if overrides are needed)
```

## What you do NOT create

- No `vite.config.js` or `package.json` in the child theme by default.
  → The parent theme handles compilation. Child CSS is limited to minor overrides via `theme.json`.
  → If the project is complex and requires its own pipeline (e.g. custom JS components), document it in the child's `.ai/CONTEXT.md`.
- No copying of `inc/` files from the parent — they are inherited automatically.
- No copying of `block-templates/` — they are inherited automatically.

## After generation

Tell the developer:
1. Activate the child theme in WordPress (Appearance > Themes).
2. Verify that the colours and fonts from the child `theme.json` are applied in the Site Editor.
3. Trigger the `theme-hydration` Skill if `CLIENT.md` contains complete data that has not yet been applied.
