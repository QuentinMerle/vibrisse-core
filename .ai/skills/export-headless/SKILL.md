---
name: export-headless
description: Generates a frontend framework component (React/Vue/Svelte) from an ACF block (block.json, render.php, acf-json) to be used in Headless mode.
---

# Skill: Export Block to Headless Component

You act as a Frontend Architect. The agency works in Headless mode with `vibrisse-core` (WordPress as a headless CMS, exposing structured blocks via REST API). Your mission is to convert a PHP ACF block into a modern frontend component.

## Step 1 — Context Gathering

Ask the user:
1. **Target Framework:** React (Next.js), Vue (Nuxt), or Svelte?
2. **Block Name:** Which block in `blocks/custom/` should be converted?

## Step 2 — Analysis of the PHP Block

Read these files from the provided block:
1. `block.json`: to get the block name (`vibrisse/[name]`).
2. `render.php`: to extract the exact HTML structure and Tailwind classes.
3. `acf-json/group_*.json`: to understand the data model (text, image arrays, repeaters).

## Step 3 — Generate the Component

You must generate a component that mimics the exact HTML/CSS structure of `render.php`, but consumes the data as JSON props.

### Rules for the generated code:
1. **Props Structure:** The component will receive a `data` object (corresponding to `attributes.data` in the REST API response).
2. **Tailwind Classes:** Keep the exact same Tailwind utility classes from `render.php`.
3. **Images:** The REST API returns the image as an array (ID, url, alt, sizes). Generate a native `<img>` or framework-specific image component (`<Image>` in Next.js) using these fields.
4. **Iterators:** Convert PHP `foreach` (for ACF repeaters) into JS `.map()`.

### Example (React / Next.js) for a "Hero" block:

```tsx
import React from 'react';
import Image from 'next/image';

interface HeroProps {
  data: {
    title: string;
    text?: string;
    image?: {
      url: string;
      alt: string;
      width: number;
      height: number;
    };
  };
  className?: string;
}

export default function VibrisseHero({ data, className = '' }: HeroProps) {
  const { title, text, image } = data;

  return (
    <section className={`vibrisse-hero py-16 lg:py-24 px-6 ${className}`}>
      <div className="max-w-6xl mx-auto flex flex-col md:flex-row gap-8 items-center">
        <div className="flex-1">
          {title && <h1 className="font-serif text-4xl lg:text-6xl font-bold mb-6 text-contrast">{title}</h1>}
          {text && <p className="text-lg text-muted mb-8">{text}</p>}
        </div>
        
        {image && (
          <div className="flex-1">
            <Image 
              src={image.url} 
              alt={image.alt || ''} 
              width={image.width} 
              height={image.height} 
              className="rounded-lg shadow-xl"
              priority
            />
          </div>
        )}
      </div>
    </section>
  );
}
```

## Step 4 — Deliver

Provide the generated code to the user, and remind them that the data passed to this component comes from the `vibrisse_blocks` array in the WP REST API response.
