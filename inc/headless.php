<?php
/**
 * Headless Mode Configuration.
 * Redirection of frontend and REST API block parsing.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'VIBRISSE_HEADLESS_MODE' ) || ! VIBRISSE_HEADLESS_MODE ) {
	return; // Headless mode is disabled
}

/**
 * 1. Redirect frontend to the Headless URL.
 */
add_action( 'template_redirect', function() {
	// Do not redirect REST API, AJAX, or Admin
	if ( is_admin() || wp_is_json_request() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	$headless_url = defined( 'VIBRISSE_HEADLESS_FRONTEND_URL' ) ? VIBRISSE_HEADLESS_FRONTEND_URL : '';
	
	if ( empty( $headless_url ) ) {
		// If URL is not defined, just return a simple JSON message instead of rendering the theme.
		wp_send_json( [ 'message' => 'Vibrisse Headless Mode is active. VIBRISSE_HEADLESS_FRONTEND_URL is not defined in wp-config.php.' ] );
		exit;
	}

	// Build the redirect URL based on the current request
	$redirect_url = rtrim( $headless_url, '/' ) . $_SERVER['REQUEST_URI'];

	wp_redirect( esc_url_raw( $redirect_url ), 301 );
	exit;
} );

/**
 * 2. Expose structured block data in REST API.
 * This parses the `post_content` and returns an array of blocks with their ACF data.
 */
function vibrisse_expose_blocks_in_rest( $response, $post, $request ) {
	if ( empty( $post->post_content ) ) {
		return $response;
	}

	$blocks = parse_blocks( $post->post_content );
	$structured_blocks = [];

	foreach ( $blocks as $block ) {
		// Ignore empty blocks or pure HTML (unless you want to parse core paragraphs)
		if ( empty( $block['blockName'] ) ) {
			continue;
		}

		$structured_blocks[] = [
			'name'       => $block['blockName'],
			'attributes' => $block['attrs'],
			// We avoid sending raw HTML 'innerHTML' to keep the API clean, 
			// but we keep 'innerBlocks' if there are nested blocks.
			'innerBlocks' => ! empty( $block['innerBlocks'] ) ? $block['innerBlocks'] : [],
		];
	}

	$response->data['vibrisse_blocks'] = $structured_blocks;

	return $response;
}

// Register the filter for all public post types that support the editor
add_action( 'init', function() {
	$post_types = get_post_types( [ 'public' => true, 'show_in_rest' => true ], 'names' );
	foreach ( $post_types as $post_type ) {
		if ( post_type_supports( $post_type, 'editor' ) ) {
			add_filter( "rest_prepare_{$post_type}", 'vibrisse_expose_blocks_in_rest', 10, 3 );
		}
	}
}, 100 );
