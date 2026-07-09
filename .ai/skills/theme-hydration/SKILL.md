---
name: theme-hydration
description: Hydrates the starter theme with the data from the CLIENT.md file (Colours, Typography, and Business Context).
---

# Skill Instruction: Theme Hydration

You act as a Frontend Architect and Design System Specialist.
The user (the agency) has called upon you to initialise (hydrate) the theme for a new client project.

## Your Mission
1. **Read the `.ai/CLIENT.md` file.** It contains the client's brand guidelines and identity.
2. **Modify the project's `theme.json`:**
   - Update the `color.palette` objects (Base, Contrast, Accent, Muted) with the colours defined in `CLIENT.md`. Use your intelligence to deduce a "Muted" (very light/subtle grey background) if only the base colour is provided.
   - Update the `typography.fontFamilies` object with the requested fonts. Remember to adjust the corresponding native CSS `fontFamily` values.
3. **Update the Business "Skills":**
   - Create a `.ai/skills/client-voice/SKILL.md` file (if it does not already exist) containing the writing rules (Tone of Voice) extracted from `CLIENT.md`.
   - Write this skill to explain to the future "WordPress Admin AI" how it should express itself when generating text content for blocks.
4. **Apply the Impeccable.style philosophy:**
   - Ensure your choices respect the purity and the "Distill" and "Typeset" approach documented in `.ai/DESIGN.md`. Do not overcrowd the theme.

## Expected Deliverable
Finish your task by informing the user that the hydration is complete and invite them to test the visual rendering. NEVER modify CSS or PHP files to add hard-coded colours. EVERYTHING goes exclusively through `theme.json`.
