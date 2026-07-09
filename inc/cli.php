<?php
/**
 * Commandes WP-CLI pour Vibrisse Core.
 * Usage : wp vibrisse predeploy
 *
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ce fichier ne s'exécute que dans le contexte WP-CLI.
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Commandes Vibrisse pour WP-CLI.
 */
class Vibrisse_CLI {

	/**
	 * Prépare le site pour la mise en production.
	 *
	 * Génère un llms.txt statique, valide les données SEO des pages publiées,
	 * et produit un rapport avant déploiement.
	 *
	 * ## EXEMPLES
	 *
	 *     wp vibrisse predeploy
	 *
	 * @subcommand predeploy
	 */
	public function predeploy() {
		WP_CLI::log( '' );
		WP_CLI::log( '🚀 Vibrisse Core — Pre-deploy check' );
		WP_CLI::log( str_repeat( '─', 40 ) );

		$errors   = 0;
		$warnings = 0;

		// ─────────────────────────────────────────
		// 1. Génération du llms.txt statique
		// ─────────────────────────────────────────
		WP_CLI::log( '' );
		WP_CLI::log( '📄 Génération de llms.txt...' );

		$site_name = get_bloginfo( 'name' );
		$site_desc = get_bloginfo( 'description' );

		$pages = get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		] );

		$content  = "# {$site_name}\n";
		$content .= $site_desc ? "> {$site_desc}\n\n" : "\n";
		$content .= "## Pages\n\n";

		foreach ( $pages as $page ) {
			$excerpt = wp_strip_all_tags( get_the_excerpt( $page ) );
			$content .= '- [' . $page->post_title . '](' . get_permalink( $page ) . ')';
			if ( $excerpt ) {
				$content .= ': ' . wp_trim_words( $excerpt, 20 );
			}
			$content .= "\n";
		}

		$llms_path = ABSPATH . 'llms.txt';
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		if ( file_put_contents( $llms_path, $content ) !== false ) {
			WP_CLI::success( 'llms.txt généré → ' . $llms_path );
			WP_CLI::log( '  ' . count( $pages ) . ' page(s) indexée(s).' );
		} else {
			WP_CLI::warning( 'Impossible d\'écrire llms.txt (vérifier les permissions sur ' . ABSPATH . ')' );
			$warnings++;
		}

		// ─────────────────────────────────────────
		// 2. Validation SEO des pages publiées
		// ─────────────────────────────────────────
		WP_CLI::log( '' );
		WP_CLI::log( '🔍 Validation SEO des pages...' );

		$seo_issues = [];

		foreach ( $pages as $page ) {
			$title = $page->post_title;

			// Titre manquant ou trop court
			if ( strlen( $title ) < 10 ) {
				$seo_issues[] = "[⚠️  TITRE COURT] « {$title} » ({$page->ID}) — moins de 10 caractères.";
				$warnings++;
			}

			// Image à la une manquante
			if ( ! has_post_thumbnail( $page->ID ) ) {
				$seo_issues[] = "[ℹ️  OG IMAGE] « {$title} » — pas d'image à la une (Open Graph incomplet).";
			}

			// Excerpt manquant
			if ( empty( $page->post_excerpt ) && empty( wp_strip_all_tags( $page->post_content ) ) ) {
				$seo_issues[] = "[⚠️  CONTENU VIDE] « {$title} » ({$page->ID}) — page sans contenu.";
				$warnings++;
			}
		}

		if ( empty( $seo_issues ) ) {
			WP_CLI::success( count( $pages ) . ' page(s) validée(s) — aucun problème SEO détecté.' );
		} else {
			foreach ( $seo_issues as $issue ) {
				WP_CLI::log( '  ' . $issue );
			}
		}

		// ─────────────────────────────────────────
		// 3. Vérification ACF Local JSON
		// ─────────────────────────────────────────
		WP_CLI::log( '' );
		WP_CLI::log( '📦 Vérification ACF Local JSON...' );

		$acf_json_dir    = VIBRISSE_THEME_DIR . '/acf-json/';
		$child_acf_dir   = get_stylesheet_directory() . '/acf-json/';
		$json_files      = glob( $acf_json_dir . '*.json' ) ?: [];
		$child_json      = glob( $child_acf_dir . '*.json' ) ?: [];
		$total_json      = count( $json_files ) + count( $child_json );

		if ( $total_json > 0 ) {
			WP_CLI::success( $total_json . ' groupe(s) ACF trouvé(s) en Local JSON.' );
		} else {
			WP_CLI::warning( 'Aucun fichier ACF JSON trouvé. Les champs sont-ils bien sauvegardés ?' );
			$warnings++;
		}

		// ─────────────────────────────────────────
		// 4. Rappels manuels
		// ─────────────────────────────────────────
		WP_CLI::log( '' );
		WP_CLI::log( '📋 Checklist manuelle avant déploiement :' );
		WP_CLI::log( '  [ ] Plugin SEO configuré (Yoast ou RankMath) ?' );
		WP_CLI::log( '  [ ] Politique de confidentialité publiée ?' );
		WP_CLI::log( '  [ ] Favicon et icônes configurés ?' );
		WP_CLI::log( '  [ ] Google Search Console vérifié ?' );
		WP_CLI::log( '  [ ] assets/ compilé en prod (npm run build) ?' );

		// ─────────────────────────────────────────
		// 5. Résumé
		// ─────────────────────────────────────────
		WP_CLI::log( '' );
		WP_CLI::log( str_repeat( '─', 40 ) );

		if ( $errors > 0 ) {
			WP_CLI::error( "Pre-deploy terminé avec {$errors} erreur(s) et {$warnings} avertissement(s)." );
		} elseif ( $warnings > 0 ) {
			WP_CLI::warning( "Pre-deploy terminé avec {$warnings} avertissement(s). Vérifiez les points ci-dessus." );
		} else {
			WP_CLI::success( 'Pre-deploy OK — site prêt pour la mise en production. 🎉' );
		}

		WP_CLI::log( '' );
	}
}

WP_CLI::add_command( 'vibrisse', 'Vibrisse_CLI' );
