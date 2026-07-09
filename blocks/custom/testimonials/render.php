<?php
/**
 * Testimonials Block Template.
 *
 * @package VibrisseCore
 */

$section_title = get_field( 'section_title' );
$items         = get_field( 'items' ) ?: [];

$classes = 'vibrisse-testimonials py-16 lg:py-24 px-6 bg-muted';
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
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
				<?php foreach ( $items as $item ) : ?>
					<figure class="vibrisse-testimonials__card flex flex-col gap-5 bg-base p-8 rounded-2xl shadow-sm">

						<blockquote class="font-serif text-xl leading-relaxed text-contrast flex-1">
							"<?php echo esc_html( $item['quote'] ); ?>"
						</blockquote>

						<figcaption class="flex items-center gap-4 border-t border-contrast/10 pt-5">
							<?php if ( ! empty( $item['avatar'] ) ) : ?>
								<img
									src="<?php echo esc_url( $item['avatar']['url'] ); ?>"
									alt="<?php echo esc_attr( $item['author_name'] ); ?>"
									class="w-12 h-12 rounded-full object-cover flex-shrink-0"
									loading="lazy"
								/>
							<?php else : ?>
								<div class="w-12 h-12 rounded-full bg-accent/20 flex-shrink-0"></div>
							<?php endif; ?>
							<div>
								<p class="font-sans font-semibold text-contrast text-sm">
									<?php echo esc_html( $item['author_name'] ); ?>
								</p>
								<?php if ( ! empty( $item['author_role'] ) ) : ?>
									<p class="font-sans text-sm text-contrast/50">
										<?php echo esc_html( $item['author_role'] ); ?>
									</p>
								<?php endif; ?>
							</div>
						</figcaption>

					</figure>
				<?php endforeach; ?>
			</div>

		<?php else : ?>
			<p class="text-center text-contrast/40 font-sans py-12">[ Ajoutez vos témoignages via ACF ]</p>
		<?php endif; ?>

	</div>
</section>
