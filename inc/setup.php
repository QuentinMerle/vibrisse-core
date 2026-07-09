<?php
/**
 * Minimal theme setup (FSE Zero-Config Approach).
 *
 * The vast majority of supports (titles, thumbnails, HTML5) are enabled
 * automatically by the mere presence of theme.json.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function() {
	// 1. Remove remote "Core Patterns" from WordPress.org.
	// Prevents injection of generic, uncontrolled layouts (anti-slop).
	remove_theme_support( 'core-block-patterns' );

	// 2. Lock the editor palette to the agency's choices (theme.json).
	// Prevents the client from picking neon green if it's not in the brand guidelines.
	add_theme_support( 'editor-color-palette', [] );
	add_theme_support( 'disable-custom-colors' );

	// 3. Lock font sizes to the fluid scale (Clamp) defined in theme.json.
	add_theme_support( 'editor-font-sizes', [] );
	add_theme_support( 'disable-custom-font-sizes' );
}, 20 );

/**
 * Skip Navigation link — Accessibility WCAG 2.1 AA.
 * Injected immediately after <body> via the wp_body_open hook.
 * Visible on keyboard focus only, invisible otherwise.
 */
add_action( 'wp_body_open', function() {
	echo '<a class="vibrisse-skip-link" href="#main-content">'
		. esc_html__( 'Skip to main content', 'vibrisse-core' )
		. '</a>';
} );
