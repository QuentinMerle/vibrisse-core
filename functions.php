<?php
/**
 * Vibrisse Core Theme Functions
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Theme Constants.
define( 'VIBRISSE_THEME_VERSION', wp_get_theme()->get( 'Version' ) );
define( 'VIBRISSE_THEME_DIR', get_template_directory() );
define( 'VIBRISSE_THEME_URI', get_template_directory_uri() );

// Headless Mode Toggle.
if ( ! defined( 'VIBRISSE_HEADLESS_MODE' ) ) {
	define( 'VIBRISSE_HEADLESS_MODE', false );
}

/**
 * Autoload all PHP files in the /inc/ directory.
 */
function vibrisse_core_autoload_inc() {
	$inc_dir = VIBRISSE_THEME_DIR . '/inc/';
	
	if ( ! is_dir( $inc_dir ) ) {
		return;
	}

	$files = glob( $inc_dir . '*.php' );
	
	if ( empty( $files ) ) {
		return;
	}

	foreach ( $files as $file ) {
		require_once $file;
	}
}

vibrisse_core_autoload_inc();
