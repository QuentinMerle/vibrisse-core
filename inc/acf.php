<?php
/**
 * ACF Pro advanced configuration.
 * Handles Local JSON paths and auto-registration of custom Gutenberg blocks.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Guard: this entire file requires ACF Pro. Exit silently if the plugin is missing.
if ( ! class_exists( 'ACF' ) ) {
	return;
}


/**
 * 1. ACF Local JSON save path.
 * Saves to the acf-json folder of the parent theme (Vibrisse Core) by default.
 */
add_filter( 'acf/settings/save_json', function( $path ) {
	$path = VIBRISSE_THEME_DIR . '/acf-json';
	return $path;
} );

/**
 * 2. ACF Local JSON load paths.
 * Loads fields from the child theme FIRST, then from the parent theme.
 */
add_filter( 'acf/settings/load_json', function( $paths ) {
	// Remove the default path.
	unset( $paths[0] );

	// Add child theme path if it exists.
	if ( get_stylesheet_directory() !== get_template_directory() ) {
		$paths[] = get_stylesheet_directory() . '/acf-json';
	}

	// Add parent theme path as fallback (Core Blocks).
	$paths[] = VIBRISSE_THEME_DIR . '/acf-json';

	return $paths;
} );

/**
 * 3. Dynamic auto-registration of custom ACF blocks.
 *
 * Deduplication strategy:
 * - Read the `name` from each block.json to build an indexed registry.
 * - Parent theme is scanned first, child theme second.
 * - If both have a block with the same `name`, the child wins (real override).
 * - The final registry is registered once → zero double registration.
 */
add_action( 'init', function() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	// Registry indexed by block name: ['vibrisse/hero' => '/path/to/block.json']
	$blocks_registry = [];

	$dirs_to_scan = array_unique( [
		VIBRISSE_THEME_DIR . '/blocks/custom/',          // Parent (Core)
		get_stylesheet_directory() . '/blocks/custom/',  // Child (Client business)
	] );

	foreach ( $dirs_to_scan as $blocks_dir ) {
		if ( ! is_dir( $blocks_dir ) ) {
			continue;
		}

		$block_folders = array_diff( scandir( $blocks_dir ), [ '..', '.' ] );

		foreach ( $block_folders as $folder ) {
			$block_json_path = $blocks_dir . $folder . '/block.json';

			if ( ! file_exists( $block_json_path ) ) {
				continue;
			}

			// Read block name from block.json for deduplication
			$block_data = json_decode( file_get_contents( $block_json_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( empty( $block_data['name'] ) ) {
				continue;
			}

			// Child overrides parent thanks to scan order (last value wins)
			$blocks_registry[ $block_data['name'] ] = $block_json_path;
		}
	}

	// Single registration — never the same name twice
	foreach ( $blocks_registry as $block_json_path ) {
		register_block_type( $block_json_path );
	}
} );
