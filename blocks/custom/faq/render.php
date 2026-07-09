<?php
/**
 * FAQ Block Template.
 * Utilise les éléments HTML natifs <details>/<summary> — zéro JavaScript.
 * Émet automatiquement un JSON-LD FAQPage pour le SEO structurel et le GEO.
 *
 * @package VibrisseCore
 */

$section_title = get_field( 'section_title' );
$items         = get_field( 'items' ) ?: [];

$classes = 'vibrisse-faq py-16 lg:py-24 px-6';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>

<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
	<div class="max-w-3xl mx-auto">

		<?php if ( $section_title ) : ?>
			<h2 class="font-serif text-3xl lg:text-5xl font-bold mb-12">
				<?php echo esc_html( $section_title ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="flex flex-col divide-y divide-contrast/10">
				<?php foreach ( $items as $item ) : ?>
					<details class="vibrisse-faq__item group py-5">
						<summary class="flex justify-between items-center gap-4 cursor-pointer list-none font-sans font-medium text-lg text-contrast select-none">
							<?php echo esc_html( $item['question'] ); ?>
							<span class="vibrisse-faq__icon flex-shrink-0 w-6 h-6 text-accent transition-transform duration-300 group-open:rotate-45" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
							</span>
						</summary>
						<div class="font-sans text-base leading-relaxed text-contrast/70 mt-4 pr-10">
							<?php echo wp_kses_post( $item['answer'] ); ?>
						</div>
					</details>
				<?php endforeach; ?>
			</div>

		<?php else : ?>
			<p class="text-center text-contrast/40 font-sans py-12">[ Ajoutez vos questions via ACF ]</p>
		<?php endif; ?>

	</div>
</section>

<?php if ( ! empty( $items ) && ! $is_preview ) : ?>
	<?php
	// JSON-LD FAQPage — SEO structurel + GEO (moteurs génératifs comme SGE, Perplexity)
	$faq_schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map( function( $item ) {
			return [
				'@type'          => 'Question',
				'name'           => wp_strip_all_tags( $item['question'] ),
				'acceptedAnswer' => [
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $item['answer'] ),
				],
			];
		}, $items ),
	];
	?>
	<script type="application/ld+json">
		<?php echo wp_json_encode( $faq_schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
	</script>
<?php endif; ?>
