<?php
/**
 * SEO, Accessibility & AI Discoverability.
 * Handles: JSON-LD by content type, dynamic llms.txt, basic Open Graph.
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1. Open Graph & Twitter Card basic meta tags.
 * Designed to be completed by an SEO plugin (Yoast, RankMath).
 * Only activates if no SEO plugin is present.
 */
add_action( 'wp_head', function() {
	if ( function_exists( 'wpseo_head' ) || function_exists( 'rank_math_head' ) ) {
		return; // Defer to the SEO plugin
	}

	$title       = wp_get_document_title();
	$description = get_bloginfo( 'description' );
	$url         = get_permalink() ?: home_url( '/' );
	$image       = get_the_post_thumbnail_url( null, 'large' ) ?: '';

	echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '" />' . "\n";
	echo '<meta property="og:type" content="website" />' . "\n";
	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '" />' . "\n";
	}
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '" />' . "\n";
	}
}, 5 );

/**
 * 2. Dynamic llms.txt endpoint.
 * Exposes /llms.txt to inform LLMs about the site content and structure.
 * Emerging standard — https://llmstxt.org
 */
add_action( 'init', function() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?vibrisse_llms_txt=1', 'top' );
} );

add_filter( 'query_vars', function( $vars ) {
	$vars[] = 'vibrisse_llms_txt';
	return $vars;
} );

add_action( 'template_redirect', function() {
	if ( ! get_query_var( 'vibrisse_llms_txt' ) ) {
		return;
	}

	$site_name = get_bloginfo( 'name' );
	$site_desc = get_bloginfo( 'description' );

	// Fetch latest published pages
	$pages = get_posts( [
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	] );

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'Cache-Control: public, max-age=86400' );

	echo "# {$site_name}\n";
	if ( $site_desc ) {
		echo "> {$site_desc}\n";
	}
	echo "\n";
	echo "## Pages\n\n";
	foreach ( $pages as $page ) {
		$excerpt = wp_strip_all_tags( get_the_excerpt( $page ) );
		echo '- [' . esc_html( $page->post_title ) . '](' . esc_url( get_permalink( $page ) ) . ')';
		if ( $excerpt ) {
			echo ': ' . esc_html( wp_trim_words( $excerpt, 20 ) );
		}
		echo "\n";
	}

	exit;
} );

/**
 * 3. Organization / LocalBusiness JSON-LD.
 *
 * The child theme declares its schema via the `vibrisse_organization_schema` filter.
 * The Core automatically injects the JSON-LD in <head> if the filter returns an array.
 *
 * Usage in the child theme (functions.php):
 *
 *   add_filter( 'vibrisse_organization_schema', function() {
 *       return [
 *           '@context' => 'https://schema.org',
 *           '@type'    => 'LocalBusiness',  // Restaurant, MedicalBusiness, LegalService, etc.
 *           'name'     => get_bloginfo( 'name' ),
 *           'url'      => home_url( '/' ),
 *           'telephone'   => '+33 1 23 45 67 89',
 *           'address'  => [
 *               '@type'           => 'PostalAddress',
 *               'streetAddress'   => '12 rue de la Paix',
 *               'addressLocality' => 'Paris',
 *               'postalCode'      => '75001',
 *               'addressCountry'  => 'FR',
 *           ],
 *           'openingHours' => [ 'Mo-Fr 09:00-18:00' ],
 *           'image'        => get_site_icon_url( 512 ),
 *       ];
 *   } );
 */
add_action( 'wp_head', function() {
	$schema = apply_filters( 'vibrisse_organization_schema', null );

	if ( empty( $schema ) || ! is_array( $schema ) ) {
		return;
	}

	echo '<script type="application/ld+json">' . "\n"
		. wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
		. "\n" . '</script>' . "\n";
}, 5 );

/**
 * 4. Flush rewrite rules on theme activation.
 */
add_action( 'after_switch_theme', function() {
	flush_rewrite_rules();
} );
