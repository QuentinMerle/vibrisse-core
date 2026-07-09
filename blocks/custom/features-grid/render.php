<?php
/**
 * Features Grid Block Template.
 *
 * @package VibrisseCore
 */

$section_title = get_field( 'section_title' );
$items         = get_field( 'items' ) ?: [];

$classes = 'vibrisse-features-grid py-16 lg:py-24 px-6';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>

<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
	<div class="max-w-6xl mx-auto">

		<?php if ( $section_title ) : ?>
			<h2 class="font-serif text-3xl lg:text-5xl font-bold text-center mb-14">
				<?php echo esc_html( $section_title ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
				<?php foreach ( $items as $item ) : ?>
					<div class="vibrisse-features-grid__card flex flex-col gap-4 p-8 bg-muted rounded-2xl">

						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<div class="w-10 h-10 text-accent" aria-hidden="true">
								<?php echo wp_kses( $item['icon'], [ 'svg' => [ 'xmlns' => [], 'viewbox' => [], 'fill' => [], 'class' => [], 'aria-hidden' => [] ], 'path' => [ 'd' => [], 'fill' => [], 'fill-rule' => [], 'clip-rule' => [] ] ] ); ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="font-serif text-xl font-semibold text-contrast">
								<?php echo esc_html( $item['title'] ); ?>
							</h3>
						<?php endif; ?>

						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="font-sans text-base leading-relaxed text-contrast/70">
								<?php echo esc_html( $item['description'] ); ?>
							</p>
						<?php endif; ?>

					</div>
				<?php endforeach; ?>
			</div>

		<?php else : ?>
			<p class="text-center text-contrast/40 font-sans py-12">[ Ajoutez vos arguments via ACF ]</p>
		<?php endif; ?>

	</div>
</section>
