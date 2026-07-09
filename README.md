# vibrisse-core

Agency WordPress starter theme. Built for a DA → Dev → Delivery workflow, not for shipping fast.

---

## Philosophy

### The Core is foundational. The child theme is business-specific.

`vibrisse-core` is deliberately incomplete. That's its strength.

**The Core provides:**
- The engine — FSE, [ACF Pro](https://www.advancedcustomfields.com/), [Vite](https://github.com/vitejs/vite), [Tailwind CSS](https://github.com/tailwindlabs/tailwindcss) v4, guardrails.
- Universal blocks — Hero, Media & Text, Features Grid, CTA, FAQ, Testimonials.
- A neutral design system — generic `theme.json`, ready to be overridden.
- AI context — `AGENTS.md`, `CONTEXT.md`, `DESIGN.md` that your IDE will read.

**The child theme provides:**
- The client's brand — child `theme.json`, without touching the Core.
- Business blocks — project-specific, non-portable.
- Business Skills — `client-voice`, sector rules, client memory.

**The process, not the shortcut.**
The DA produces mockups. The dev hydrates the design system. The AI generates blocks respecting the brand. The client receives a technically flawless site.

---

## Init Prompt — Start your project with AI

WordPress installed. `vibrisse-core` activated. Copy-paste this prompt into your AI (Cursor, Claude, Antigravity...) and let it guide you.

**New project (first launch):**

```
You are a senior WordPress expert working on vibrisse-core, an agency starter theme.

Start by reading these files in order:
1. .ai/AGENTS.md — absolute rules, stack, guardrails
2. .ai/CONTEXT.md — parent/child architecture, entry points, available Skills
3. .ai/CLIENT.md — client project context (may be empty)

Once read, tell me:
- What you understood about the project and its current state
- The next concrete step to take

Then guide me step by step to initialize this client project.
```

**Resume an ongoing project:**

```
You are working on a vibrisse-core project.
Read .ai/AGENTS.md, .ai/CONTEXT.md and .ai/CLIENT.md.
Identify where we are in the cycle (design, dev, delivery) and propose the next action.
```

**The DA just delivered the mockups:**

```
The DA just delivered the project assets (attached / at this URL / described below).
Read .ai/AGENTS.md and trigger the design-import Skill to analyze these assets
and update theme.json and CLIENT.md.
```

> Add `Respond in [your language].` to any prompt to get answers in your preferred language.

---

## Entry Points

Depending on where you are in the project cycle, here is the path to follow.

---

### 1. Start a new project

```bash
# Clone into wp-content/themes/
git clone ... vibrisse-core
cd vibrisse-core
nvm use               # Node 22 (.nvmrc)
npm install           # Activates Husky automatically
composer install      # phpcs + WordPress Coding Standards
cp .env.example .env  # Configure VITE_DEV_URL
```

Edit `.env` and set `VITE_DEV_URL` (your local WordPress site URL).

Then start the dev server:

```bash
npm run dev
```

Vite dev mode is detected automatically — no manual flag needed.

> **Editor styles:** Tailwind styles are automatically injected into the Gutenberg preview — via Vite in dev, via `add_editor_styles()` in production.

Then → **[2. The DA has delivered the design](#2-the-da-has-delivered-the-design)** or fill `.ai/CLIENT.md` manually.

---

### 2. The DA has delivered the design

Whatever the format, there is an entry point for each case.

**Structured format** (W3C JSON tokens or CSS variables):
```bash
npm run design:import -- brand.json     # W3C Design Tokens
npm run design:import -- styles.css     # CSS Custom Properties
npm run design:import:dry -- brand.json # Preview without writing
```

**Visual format** (screenshot, PDF, URL, text description):
```
→ AI Skill "design-import" in your IDE
  "Analyze this mockup and update the design system."
```

Both result in an updated `theme.json` and `CLIENT.md`. Then:

```
→ AI Skill "theme-hydration"
  Applies client tokens, creates the client-voice Skill (Tone of Voice).
```

---

### 3. Initialize the child theme

Once the design system is hydrated, generate the client project's child theme:

```
→ AI Skill "init-child-theme"
  Generates vibrisse-[client]/ with style.css, functions.php,
  child theme.json, .ai/CLIENT.md and .ai/skills/client-voice/.
```

Activate the child theme in WordPress (Appearance → Themes). The Core stays intact.

---

### 4. Add a business block

Core blocks cover 80% of brochure sites. For the rest (product card, team member, timeline...):

```
→ AI Skill "new-block" in your IDE
  "Create a [name] block with the fields [description]."
```

The Skill automatically generates `block.json`, `render.php`, `style.css` and `acf-json/`, with the built-in A11y/SEO/Perf quality contract. The block is auto-registered with no additional configuration.

Create business blocks in the **child theme** (`blocks/custom/`), not in the Core.

---

### 5. Add a Custom Post Type

For recurring structured content (Portfolio, Team, News, Services...):

```
→ AI Skill "new-cpt" in your IDE
  "Create a 'Project' CPT with a 'Sector' taxonomy."
```

The Skill generates `inc/post-types.php`, the ACF group in Local JSON, and FSE templates `archive-[slug].html` and `single-[slug].html` in the child theme.

---

### 6. Choose a plugin

`vibrisse-core` prescribes no plugins. When a functional need arises:

```
→ AI Skill "plugin-check"
  "We need a contact form, evaluate the options."
```

The Skill analyzes candidates on 5 criteria (FSE-compatible, weight, lock-in, maintenance, conflicts) and produces a structured, argued recommendation.

**Common needs:**

| Need | Common options | Avoid |
|---|---|---|
| Forms | Gravity Forms, Fluent Forms, WP Forms | Proprietary shortcodes |
| SEO | Yoast, RankMath | Duplicates with `inc/seo.php` |
| Cache | WP Rocket, LiteSpeed Cache | Plugins that modify HTML |
| Conditional content | JetEngine (ACF-compatible) | Elementor dependencies |

> Yoast and RankMath are auto-detected by `inc/seo.php` — native Open Graph tags are disabled to avoid duplicates.

---

### 7. Deploy to production

```bash
npm run predeploy
```

Chains in order:
1. `npm run build` — compiles and optimizes assets
2. `npm run lint` — ESLint + Stylelint pass
3. `wp vibrisse predeploy` — WP-CLI report that:
   - Generates a static `llms.txt` in the webroot
   - Validates titles and Open Graph of all published pages
   - Verifies ACF fields are in Local JSON
   - Displays a manual pre-delivery checklist

---

### 8. Graphic redesign (existing client)

```
1. Update .ai/CLIENT.md (new colors, new fonts)
   or re-run: npm run design:import -- new-brand.json
2. AI Skill "theme-hydration"
   → Child theme.json updated
   → Zero PHP modification, zero manual CSS
```

---

## Stack

| | |
|---|---|
| CMS | [WordPress 6.4+](https://github.com/WordPress/wordpress-develop) (FSE) |
| Blocks | [ACF Pro](https://www.advancedcustomfields.com/) + Local JSON |
| CSS | [Tailwind CSS v4](https://github.com/tailwindlabs/tailwindcss) |
| Build | [Vite 6](https://github.com/vitejs/vite) |
| PHP | 8.1+ |
| Node | 22 (`.nvmrc`) |

---

## Core Blocks

| Block | Usage |
|---|---|
| `vibrisse/hero` | Page introduction — centered or split |
| `vibrisse/media-text` | Text + image 50/50 presentation |
| `vibrisse/features-grid` | Arguments / services grid |
| `vibrisse/call-to-action` | Conversion banner |
| `vibrisse/faq` | Native HTML accordion (zero JS) + automatic JSON-LD |
| `vibrisse/testimonials` | Client reviews |

---

## AI Skills

| Skill | When | What it does |
|---|---|---|
| `design-import` | DA delivers an asset | Analyzes any format → `theme.json` + `CLIENT.md` |
| `theme-hydration` | After design-import or CLIENT.md filled | Applies tokens, creates `client-voice` Skill |
| `init-child-theme` | Project start | Full child theme scaffold |
| `new-block` | New block needed | Generates block with A11y/SEO/Perf quality contract |
| `new-cpt` | New recurring content type | CPT + taxonomy + ACF + FSE templates |
| `plugin-check` | Functional need identified | Evaluates plugin options |

---

## Commands

```bash
npm run dev            # Dev + HMR (Vite) — auto-enables editor styles
npm run build          # Production build — auto-disables dev mode
npm run lint           # ESLint + Stylelint
npm run lint:fix       # Auto-fix
composer run phpcs     # WordPress Coding Standards
composer run phpcbf    # PHP auto-fix
npm run predeploy      # Full pre-production pipeline
npm run design:import -- [file]     # Import design tokens (JSON or CSS)
npm run design:import:dry -- [file] # Preview without writing
```

---

## Quality & Collaboration

Husky activates on install. Every `git commit` lints modified files automatically.

| Tool | Trigger | Target |
|---|---|---|
| Husky + lint-staged | `git commit` | Modified files only |
| ESLint | `npm run lint` | `src/js/` |
| Stylelint | `npm run lint` | `src/css/` |
| phpcs (WPCS) | `composer run phpcs` | `inc/`, `blocks/`, `functions.php` |
| `.editorconfig` | Code editor | All files |

---

## Structure

```
vibrisse-core/
├── .ai/                        # AI context for language models
│   ├── AGENTS.md               # Rules and constraints (read before coding)
│   ├── CONTEXT.md              # Vision, architecture, workflows
│   ├── DESIGN.md               # Art direction
│   ├── CLIENT.md               # ← Fill in per project
│   └── skills/
│       ├── design-import/      # Design asset import
│       ├── theme-hydration/    # Design system hydration
│       ├── init-child-theme/   # Child theme scaffold
│       ├── new-block/          # Block creation
│       ├── new-cpt/            # Custom Post Type creation
│       └── plugin-check/       # Plugin evaluation
├── acf-json/                   # ACF fields (committed — source of truth)
├── block-template-parts/       # Header, Footer FSE
├── block-templates/            # FSE page layouts
├── blocks/custom/              # Collocated ACF blocks (auto-registered)
├── experimental/               # Paused features (AI Admin Bridge)
├── scripts/
│   └── design-import.js        # W3C Tokens / CSS vars parser
├── inc/
│   ├── acf.php                 # Auto-registration + Local JSON (parent + child)
│   ├── assets.php              # Vite HMR / Production (auto-detected)
│   ├── cli.php                 # WP-CLI commands (predeploy)
│   ├── performance.php         # Core WP cleanup
│   ├── seo.php                 # JSON-LD, llms.txt, Open Graph
│   └── setup.php               # Editor lock + skip nav
├── src/
│   ├── css/main.css            # Tailwind v4 + @source PHP scan
│   └── js/main.js              # Vanilla JS
├── .editorconfig
├── .env.example
├── .gitignore
├── composer.json               # phpcs + WordPress Coding Standards
├── eslint.config.js
├── .stylelintrc.json
├── functions.php               # PHP autoload only
├── phpcs.xml
└── theme.json                  # Design system source of truth
```

---

## Prerequisites

- WordPress >= 6.4
- ACF Pro (silent degradation without the plugin)
- PHP >= 8.1
- Node.js >= 22
- Composer

---

## Maintainers & Origins

**vibrisse-core** is the internal production standard built and actively maintained by [Vibrisse Studio](https://vibrisse-studio.dev/), a premium Web & AI Engineering agency. 

*Proudly coded by [Quentin Merle](https://dev.to/quentin_merle) in Beauce, Québec 🇨🇦.*

---

## License

MIT
