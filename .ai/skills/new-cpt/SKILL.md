---
name: new-cpt
description: Generates a complete Custom Post Type (and optionally a Taxonomy) for WordPress, with naming conventions, ACF fields, FSE templates, and i18n best practices. To be created in the child theme.
---

# Skill: Creating a Custom Post Type

You are a WordPress expert. Your mission is to create a complete, maintainable, and i18n-ready CPT for the project's child theme.

> **Absolute rule:** CPTs belong to the **child** theme, never to the `vibrisse-core` Core. Always create the code in `vibrisse-[client]/`.

---

## Step 1 — Information to collect

Before generating anything, obtain:

| Information | Example |
|---|---|
| Singular name | "Testimonial", "Project", "Team Member" |
| Plural name | "Testimonials", "Projects", "Team Members" |
| Slug (URL) | `testimonial`, `project`, `member` |
| Admin icon | A dashicon: `dashicons-format-quote` |
| Visible in menu? | Yes / No |
| Supports what? | `title`, `editor`, `thumbnail`, `excerpt`… |
| Associated taxonomy? | Yes → name (e.g. "Project Category") / No |
| Rewrite URL | `/projects/` or leave the default slug |

---

## Step 2 — Files to create in the child theme

```
vibrisse-[client]/
├── inc/
│   ├── post-types.php      # CPT + Taxonomy registration
│   └── cpt-[slug].php      # Specific ACF fields (optional)
└── acf-json/
    └── group_[slug]_fields.json
```

And in the child theme's `functions.php`:
```php
require_once get_stylesheet_directory() . '/inc/post-types.php';
```

---

## Step 3 — `inc/post-types.php` template

```php
<?php
/**
 * Custom Post Types & Taxonomies — [Client Name].
 *
 * @package Vibrisse[NomClient]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enregistrement du CPT : [Nom Singulier].
 */
add_action( 'init', function() {
    $labels = [
        'name'               => _x( '[Nom Pluriel]', 'post type general name', '[text-domain-enfant]' ),
        'singular_name'      => _x( '[Nom Singulier]', 'post type singular name', '[text-domain-enfant]' ),
        'add_new'            => __( 'Ajouter', '[text-domain-enfant]' ),
        'add_new_item'       => __( 'Ajouter un(e) [Nom Singulier]', '[text-domain-enfant]' ),
        'edit_item'          => __( 'Modifier', '[text-domain-enfant]' ),
        'new_item'           => __( 'Nouveau', '[text-domain-enfant]' ),
        'view_item'          => __( 'Voir', '[text-domain-enfant]' ),
        'search_items'       => __( 'Rechercher', '[text-domain-enfant]' ),
        'not_found'          => __( 'Aucun résultat.', '[text-domain-enfant]' ),
        'not_found_in_trash' => __( 'Aucun élément dans la corbeille.', '[text-domain-enfant]' ),
        'menu_name'          => __( '[Nom Pluriel]', '[text-domain-enfant]' ),
    ];

    register_post_type( '[slug]', [
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => '[slug-url]' ],
        'supports'     => [ 'title', 'thumbnail', 'excerpt' ],
        'menu_icon'    => 'dashicons-[icone]',
        'show_in_rest' => true, // Obligatoire pour Gutenberg
        'menu_position' => 5,
    ] );
}, 0 ); // Priorité 0 pour que les rewrite rules soient disponibles dès init

/**
 * Flush des rewrite rules à l'activation du thème enfant.
 * Évite les 404 sur les archives du CPT après activation.
 */
add_action( 'after_switch_theme', function() {
    flush_rewrite_rules();
} );
```

---

## Step 4 — Template for an associated Taxonomy (if needed)

```php
/**
 * Taxonomie : [Nom de la Taxonomie] pour [Nom Singulier].
 */
add_action( 'init', function() {
    $labels = [
        'name'          => _x( '[Nom Taxonomie Pluriel]', 'taxonomy general name', '[text-domain-enfant]' ),
        'singular_name' => _x( '[Nom Taxonomie Singulier]', 'taxonomy singular name', '[text-domain-enfant]' ),
        'all_items'     => __( 'Toutes les catégories', '[text-domain-enfant]' ),
        'edit_item'     => __( 'Modifier', '[text-domain-enfant]' ),
        'add_new_item'  => __( 'Ajouter', '[text-domain-enfant]' ),
        'menu_name'     => __( '[Nom Taxonomie Pluriel]', '[text-domain-enfant]' ),
    ];

    register_taxonomy( '[slug-taxo]', '[slug-cpt]', [
        'labels'       => $labels,
        'hierarchical' => true,  // true = category, false = tag
        'public'       => true,
        'rewrite'      => [ 'slug' => '[slug-taxo-url]' ],
        'show_in_rest' => true,
    ] );
} );
```

---

## Step 5 — ACF fields (acf-json)

Create `acf-json/group_[slug]_fields.json` following the pattern of existing groups in `vibrisse-core/acf-json/`. Key points:

- `"location"`: `[{"param": "post_type", "operator": "==", "value": "[slug]"}]`
- Field keys prefixed: `field_[client]_[slug]_[name]`
- Group key: `group_[client]_[slug]`

---

## Step 6 — FSE templates for the archive and single view

Create in `vibrisse-[client]/templates/`:

- `archive-[slug].html` — entry list (Query Loop block)
- `single-[slug].html` — detail page

These templates automatically inherit the header/footer from the Core.

---

## Quality Checklist

- [ ] `show_in_rest: true` — mandatory for the Site Editor
- [ ] `flush_rewrite_rules()` on `after_switch_theme`
- [ ] All strings go through `__()` or `_x()` with the child theme's text-domain
- [ ] ACF group created as Local JSON in `acf-json/` (child)
- [ ] `require_once` added in the child's `functions.php`
- [ ] `archive-[slug].html` and `single-[slug].html` templates created
