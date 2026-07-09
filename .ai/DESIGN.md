# Design System - Vibrisse Core

This document serves as a reference to maintain the visual and architectural consistency of the project. The design system is centrally generated via `theme.json` and automatically synchronized with Tailwind CSS v4.

## 🎨 Visual Philosophy (Inspired by Impeccable.style)
Our goal is to avoid generic, "AI slop" rendering by applying a precise and intentional design vocabulary. When designing or generating components, apply these principles:

- **Distill**: Remove everything superfluous. A design must breathe. Keep only the essential.
- **Typeset**: Typography is the heart of the interface. Use `Inter` for utility, and `Playfair Display` for editorial. Do not settle for defaults.
- **Layout & Adapt**: Use relative (clamp) and fluid spacing so the interface elegantly adapts to any screen.
- **Quieter / Bolder**: Play with contrast to establish information hierarchy without overloading the interface with colors.
- **Delight & Animate**: Add subtle and elegant micro-interactions (no unnecessary "Overdrive").

## 🌈 Colors (Native Variables)
All colors must use the theme's native tokens, mapped to Tailwind.
- **Base** (`#ffffff`): `bg-base`, `text-base`
- **Contrast** (`#0a0a0a`): `bg-contrast`, `text-contrast`
- **Muted** (`#f5f5f5`): `bg-muted`, `text-muted`
- **Accent** (`#2563eb`): `bg-accent`, `text-accent`

*Rule: No hard-coded hex values should ever exist in component CSS files. Always use CSS variables.*

## 🖋️ Typography
- **Sans Serif (Inter)**: `font-sans` - For body text, navigation, and utility interfaces.
- **Serif (Playfair Display)**: `font-serif` - Exclusively for major headings (H1/H2) and hero text, offering a premium and editorial touch.

## 📐 Construction Rules
1. **Source of Truth:** If a global color, typography, or spacing needs to be added or modified, it must be done first in `theme.json`.
2. **Block Styling:** Use Tailwind CSS to style custom blocks (`blocks/custom/`), but keep `theme.json` as the global foundation.
3. **Native Elements:** The base style of the `body`, links (`a`), and headings is managed via the `styles.elements` object in `theme.json`. Avoid heavily overriding them in CSS, unless there is a specific need for a block.
