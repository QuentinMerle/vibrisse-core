---
name: design-import
description: Transforms any design artifact (screenshot, PDF, CSS, JSON tokens, URL, text description) into the vibrisse-core design system (theme.json + CLIENT.md). Agnostic to the design tool used.
---

# Skill: Design Import

You are a Design Systems expert. Your mission is to analyse a design artifact provided by an Art Direction (AD) and translate it into precise tokens for `theme.json` and `CLIENT.md`.

**This skill has no tool dependency.** It works with Figma, Penpot, Sketch, XD, Framer, a PDF brand guide, a screenshot, a URL, or a text description.

---

## Step 1 — Identify the input format

| Received format | Analysis method |
|---|---|
| **Image / Screenshot** | Visual analysis (dominant colours, visible typography, background areas, buttons) |
| **PDF brand guide** | Document reading: palette, typography, defined usage rules |
| **CSS variables** | Parsing of custom properties (`--color-*`, `--font-*`, etc.) |
| **JSON W3C Design Tokens** | Structured parsing of the `$value` / `$type` schema |
| **Reference site URL** | Visual analysis + inspection of meta fonts, CSS colours |
| **Text description** | Semantic interpretation + lookup for exact values |

---

## Step 2 — Extract tokens

For every format, you must look for these 4 categories:

### Colours (minimum 4 tokens)

Map the found colours to the 4 vibrisse-core slots:

| Slot | Semantic role | What to look for in the AD |
|---|---|---|
| `base` | Main background | White, cream, lightest background |
| `contrast` | Main text | Black, charcoal, darkest colour |
| `muted` | Secondary background / surfaces | Light grey, beige, background variant |
| `accent` | Action / brand colour | Brand primary colour, CTA |

> If the AD provides more than 4 colours, choose the 4 most representative ones. Document the others in CLIENT.md under "Secondary Colours".

### Typography (minimum 2 tokens)

| Slot | Role | What to look for |
|---|---|---|
| `sans` | Body text, UI | Neutral, readable, sans-serif font |
| `serif` | Headings, editorial | Characterful font, often serif or display |

> If the AD uses a single font (mono-typeface), use it for both slots with different weights.

### Additional information for CLIENT.md
- Client's industry sector
- Tone of voice (luxury, accessible, technical, warm…)
- Dominant border radius (sharp, slightly rounded, very rounded)
- Overall visual tendency (minimalist, expressive, corporate, artisanal…)

---

## Step 3 — Analyse an image (if visual format)

If the input is an image or a screenshot:

1. **Colours**: Identify background areas, text, buttons, and accents. Note the approximate hex values you see. If a colour cannot be read with precision, give your best approximation and flag it as "to be validated".

2. **Typography**: Identify the visible font families (serif? sans-serif? display?). Look for font names in the metadata or deduce them visually. Suggest Google Fonts equivalents if the exact font cannot be identified.

3. **Hierarchy**: Note the overall structure (which element is dominant? what is the column width? is there an alternate background on some sections?).

---

## Step 4 — Generate the files

### `theme.json` patch (colours + typography only)

```json
{
    "$schema": "https://schemas.wp.org/trunk/theme.json",
    "version": 3,
    "settings": {
        "color": {
            "palette": [
                { "slug": "base",     "color": "[HEX extrait]", "name": "Base" },
                { "slug": "contrast", "color": "[HEX extrait]", "name": "Contrast" },
                { "slug": "muted",    "color": "[HEX extrait]", "name": "Muted" },
                { "slug": "accent",   "color": "[HEX extrait]", "name": "Accent" }
            ]
        },
        "typography": {
            "fontFamilies": [
                {
                    "slug": "sans",
                    "name": "[Nom de la police]",
                    "fontFamily": "'[Police]', system-ui, sans-serif",
                    "fontFace": [{
                        "fontFamily": "[Police]",
                        "src": ["https://fonts.gstatic.com/[URL réelle]"],
                        "fontWeight": "300 700",
                        "fontStyle": "normal"
                    }]
                },
                {
                    "slug": "serif",
                    "name": "[Nom de la police titres]",
                    "fontFamily": "'[Police titres]', Georgia, serif",
                    "fontFace": [{
                        "fontFamily": "[Police titres]",
                        "src": ["https://fonts.gstatic.com/[URL réelle]"],
                        "fontWeight": "700",
                        "fontStyle": "normal"
                    }]
                }
            ]
        }
    }
}
```

> For Google Fonts URLs, always use `fonts.gstatic.com` URLs (direct woff2 files) rather than `<link>` tags. Look up the exact URL via the Google Fonts CSS2 API.

### `CLIENT.md` update

Fill in or update the sections:
- `Colours` with the exact hex values and their roles
- `Typography` with the font names and their usages
- `Visual style` with observations on border radius, shadows, and density
- `Tone of voice` if deducible from the artifact

---

## Step 5 — Report ambiguities

At the end of your analysis, explicitly list:
- Values you have approximated (to be validated by the AD)
- Missing information needed to complete CLIENT.md
- Cases where the AD palette does not fit cleanly into 4 slots

**Do not guess silently.** An approximated but flagged value is preferable to a silently invented one.

---

## Invocation examples

```
"Here is a screenshot of the mockup, analyse it and update the design system."
"The AD exported this JSON tokens file, translate it for vibrisse-core."
"Here are the CSS variables from the previous project, let's start from those."
"We're drawing inspiration from example.com, analyse and adapt."
"Primary colour #D4A853, off-white background, Cormorant for headings, DM Sans for body text."
```

After generating the files, suggest triggering the `theme-hydration` Skill to apply the changes.
