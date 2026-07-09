<?php
/**
 * Performance optimizations and core cleanup.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove Emojis
 */
add_action( 'init', function() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
} );

/**
 * Remove unnecessary WordPress injected styles and scripts
 */
add_action( 'wp_enqueue_scripts', function() {
	// Remove WP core block library CSS if not using core blocks on front-end
	// wp_dequeue_style( 'wp-block-library' );
	// wp_dequeue_style( 'wp-block-library-theme' );
	
	// Remove global styles injected by WordPress (can be enabled if theme.json isn't sufficient)
	wp_dequeue_style( 'global-styles' );

	// Remove classic theme styles
	wp_dequeue_style( 'classic-theme-styles' );
}, 100 );

/**
 * Remove RSD link, WLW Manifest, and Generator tag
 */
add_action( 'init', function() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
} );
