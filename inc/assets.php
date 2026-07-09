<?php
/**
 * Enqueue theme assets (CSS and JS).
 * Supports Vite HMR during development and compiled assets in production.
 *
 * Dev mode is detected automatically via the `.vite-dev` flag file:
 * - Created by `npm run dev` when the Vite server starts.
 * - Deleted by `npm run build` when the production bundle is compiled.
 * No manual flag needed.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Auto-detect Vite dev server via flag file (created/deleted by vite.config.js).
define( 'IS_VITE_DEVELOPMENT', file_exists( VIBRISSE_THEME_DIR . '/.vite-dev' ) );


add_action( 'wp_enqueue_scripts', function() {
	if ( IS_VITE_DEVELOPMENT ) {
		// Assets en mode Développement (Vite HMR)
		wp_enqueue_script( 'vibrisse-vite-client', 'http://localhost:3000/@vite/client', [], null, false );
		wp_enqueue_script( 'vibrisse-main', 'http://localhost:3000/src/js/main.js', ['vibrisse-vite-client'], null, true );
		wp_enqueue_style( 'vibrisse-style', 'http://localhost:3000/src/css/main.css', [], null );
	} else {
		// Assets en mode Production (Build)
		$css_path = VIBRISSE_THEME_DIR . '/assets/css/style.css';
		$js_path  = VIBRISSE_THEME_DIR . '/assets/js/main.js';

		if ( file_exists( $css_path ) ) {
			wp_enqueue_style( 'vibrisse-style', VIBRISSE_THEME_URI . '/assets/css/style.css', [], filemtime( $css_path ) );
		}
		if ( file_exists( $js_path ) ) {
			wp_enqueue_script( 'vibrisse-main', VIBRISSE_THEME_URI . '/assets/js/main.js', [], filemtime( $js_path ), true );
		}
	}
} );

/**
 * Charge les styles compilés dans l'éditeur Gutenberg (aperçu admin).
 * En production : add_editor_styles() suffit (WordPress l'injecte dans l'iframe de l'éditeur).
 * En développement : on passe par wp_enqueue_block_editor_assets pour accéder au serveur Vite.
 */
add_action( 'after_setup_theme', function() {
	if ( ! IS_VITE_DEVELOPMENT ) {
		$css_path = VIBRISSE_THEME_DIR . '/assets/css/style.css';
		if ( file_exists( $css_path ) ) {
			add_editor_styles( 'assets/css/style.css' );
		}
	}
} );

add_action( 'enqueue_block_editor_assets', function() {
	if ( IS_VITE_DEVELOPMENT ) {
		// En dev : injecte le client Vite et le CSS dans l'éditeur
		wp_enqueue_script( 'vibrisse-vite-client-editor', 'http://localhost:3000/@vite/client', [], null, false );
		wp_enqueue_style( 'vibrisse-editor-style', 'http://localhost:3000/src/css/main.css', [], null );
	}
} );


/**
 * Ajoute l'attribut type="module" pour les scripts chargés depuis Vite
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
	if ( in_array( $handle, ['vibrisse-vite-client', 'vibrisse-main'], true ) ) {
		return '<script type="module" src="' . esc_url( $src ) . '"></script>';
	}
	return $tag;
}, 10, 3 );
