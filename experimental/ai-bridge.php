<?php
/**
 * Routeur IA pour l'interface d'administration WordPress (Gutenberg).
 * Approche "Zero Trust" : Lit la clé API depuis le Header HTTP.
 * 
 * @package VibrisseCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Vibrisse_AI_Bridge {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );
	}

	public function register_rest_routes() {
		register_rest_route( 'vibrisse/v1', '/generate-block', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'handle_generation_request' ],
			'permission_callback' => [ $this, 'check_admin_permissions' ],
		] );
	}

	public function check_admin_permissions() {
		return current_user_can( 'edit_posts' );
	}

	public function handle_generation_request( WP_REST_Request $request ) {
		// 1. Récupération de la clé API Zero Trust
		$api_key = $request->get_header( 'x_vibrisse_api_key' );
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_api_key', 'Clé API manquante dans les Headers.', [ 'status' => 401 ] );
		}

		$block_type = sanitize_text_field( $request->get_param( 'block_type' ) );
		$current_data = $request->get_param( 'current_data' );

		// 2. Compilation RAG (Lecture de la bible métier)
		$system_prompt = "Tu es un Copywriter Expert. Ta mission est de générer du contenu pour un composant web (Bloc: $block_type).\n";
		
		$client_file = VIBRISSE_THEME_DIR . '/.ai/CLIENT.md';
		if ( file_exists( $client_file ) ) {
			$system_prompt .= "\n--- CONTEXTE CLIENT ---\n" . file_get_contents( $client_file );
		}

		$system_prompt .= "\n--- INSTRUCTIONS STRICTES ---
- Tu DOIS répondre EXCLUSIVEMENT en JSON valide.
- Pour ce bloc ($block_type), génère les clés suivantes (ajuste selon ton intuition du bloc) :
  - 'title' : Un titre accrocheur.
  - 'description' : Un paragraphe de texte au format HTML (balises <p>, <strong>).
  - 'button_label' : Un texte de bouton d'appel à l'action.
  - 'button_link' : Une URL (ex: #contact).
- Si tu estimes qu'une illustration est nécessaire pour ce bloc, inclus une clé 'image_prompt' contenant une description très détaillée en anglais pour DALL-E 3. Sinon, omet la clé.";

		// 3. Appel à OpenAI (Texte)
		$text_response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', [
			'timeout' => 30,
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type'  => 'application/json',
			],
			'body' => wp_json_encode( [
				'model' => 'gpt-4o',
				'response_format' => [ 'type' => 'json_object' ],
				'messages' => [
					[ 'role' => 'system', 'content' => $system_prompt ],
					[ 'role' => 'user', 'content' => "Génère les données pour le bloc $block_type. Données actuelles (optionnel) : " . wp_json_encode($current_data) ]
				]
			] )
		] );

		if ( is_wp_error( $text_response ) ) {
			return new WP_Error( 'api_error', $text_response->get_error_message(), [ 'status' => 500 ] );
		}

		$body = json_decode( wp_remote_retrieve_body( $text_response ), true );
		if ( isset( $body['error'] ) ) {
			return new WP_Error( 'openai_error', $body['error']['message'], [ 'status' => 500 ] );
		}

		$generated_data = json_decode( $body['choices'][0]['message']['content'], true );

		// 4. Génération d'Image (DALL-E) si demandée par l'IA
		if ( ! empty( $generated_data['image_prompt'] ) ) {
			$image_prompt = $generated_data['image_prompt'];
			unset( $generated_data['image_prompt'] ); // On nettoie le JSON final

			$image_response = wp_remote_post( 'https://api.openai.com/v1/images/generations', [
				'timeout' => 30,
				'headers' => [
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				],
				'body' => wp_json_encode( [
					'model'  => 'dall-e-3',
					'prompt' => $image_prompt,
					'n'      => 1,
					'size'   => '1024x1024'
				] )
			] );

			if ( ! is_wp_error( $image_response ) ) {
				$img_body = json_decode( wp_remote_retrieve_body( $image_response ), true );
				if ( ! empty( $img_body['data'][0]['url'] ) ) {
					$image_url = $img_body['data'][0]['url'];
					
					// Sideload de l'image dans la médiathèque WP
					require_once( ABSPATH . 'wp-admin/includes/media.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					$attachment_id = media_sideload_image( $image_url, 0, 'Image générée par IA : ' . $image_prompt, 'id' );

					if ( ! is_wp_error( $attachment_id ) ) {
						// On lie l'image générée au champ ACF "media"
						$generated_data['media'] = $attachment_id;
					}
				}
			}
		}

		return rest_ensure_response( [
			'success' => true,
			'data'    => $generated_data,
		] );
	}
}

Vibrisse_AI_Bridge::get_instance();
