<?php
/**
 * Hero Block Template.
 *
 * @package VibrisseCore
 */

$title    = get_field( 'title' ) ?: 'Titre principal de la page';
$subtitle = get_field( 'subtitle' ) ?: 'Une accroche claire et directe qui donne envie d\'en savoir plus.';
$media    = get_field( 'media' );
$cta_label = get_field( 'cta_label' );
$cta_link  = get_field( 'cta_link' );
$layout   = get_field( 'layout' ) ?: 'centered'; // centered | split

$classes = 'vibrisse-hero relative overflow-hidden';
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>

<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">

	<?php if ( $media && 'centered' === $layout ) : ?>
		<div class="absolute inset-0 z-0">
			<img
				src="<?php echo esc_url( $media['url'] ); ?>"
				alt=""
				aria-hidden="true"
				class="w-full h-full object-cover"
			/>
			<div class="absolute inset-0 bg-contrast/60"></div>
		</div>
	<?php endif; ?>

	<div class="relative z-10 <?php echo 'split' === $layout ? 'flex flex-col md:flex-row items-center gap-12 px-6 py-20 lg:py-32 max-w-7xl mx-auto' : 'flex flex-col items-center text-center px-6 py-28 lg:py-40 max-w-4xl mx-auto'; ?>">

		<div class="flex flex-col gap-6 <?php echo 'split' === $layout ? 'md:w-1/2' : 'items-center'; ?>">
			<h1 class="font-serif text-4xl lg:text-7xl font-bold leading-tight <?php echo $media && 'centered' === $layout ? 'text-base' : 'text-contrast'; ?>">
				<?php echo esc_html( $title ); ?>
			</h1>

			<p class="font-sans text-xl leading-relaxed <?php echo $media && 'centered' === $layout ? 'text-base/80' : 'text-contrast/70'; ?>">
				<?php echo esc_html( $subtitle ); ?>
			</p>

			<?php if ( $cta_label && $cta_link ) : ?>
				<div class="flex gap-4 mt-2 <?php echo 'centered' === $layout ? 'justify-center' : ''; ?>">
					<a href="<?php echo esc_url( $cta_link ); ?>" class="inline-block bg-accent text-base px-8 py-4 rounded-full font-medium text-lg hover:opacity-90 transition-opacity">
						<?php echo esc_html( $cta_label ); ?>
					</a>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $media && 'split' === $layout ) : ?>
			<div class="md:w-1/2">
				<img
					src="<?php echo esc_url( $media['url'] ); ?>"
					alt="<?php echo esc_attr( $media['alt'] ); ?>"
					class="w-full h-auto rounded-2xl shadow-2xl object-cover aspect-square"
				/>
			</div>
		<?php endif; ?>

	</div>
</section>
