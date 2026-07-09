<?php
/**
 * Call To Action Block Template.
 *
 * @package VibrisseCore
 */

$title       = get_field( 'title' ) ?: 'Prêt à passer à l\'étape suivante ?';
$description = get_field( 'description' );
$cta_label   = get_field( 'cta_label' ) ?: 'Contactez-nous';
$cta_link    = get_field( 'cta_link' ) ?: '#contact';
$style       = get_field( 'background_style' ) ?: 'accent'; // accent | muted | dark

$bg_classes = match ( $style ) {
	'muted' => 'bg-muted text-contrast',
	'dark'  => 'bg-contrast text-base',
	default => 'bg-accent text-base',
};

$btn_classes = match ( $style ) {
	'accent' => 'bg-base text-accent hover:bg-base/90',
	'muted'  => 'bg-accent text-base hover:opacity-90',
	'dark'   => 'bg-base text-contrast hover:bg-base/90',
	default  => 'bg-base text-accent hover:bg-base/90',
};

$classes = "vibrisse-cta {$bg_classes} py-20 px-6";
if ( ! empty( $block['className'] ) ) {
	$classes .= ' ' . $block['className'];
}
?>

<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
	<div class="max-w-3xl mx-auto text-center flex flex-col gap-6 items-center">

		<h2 class="font-serif text-3xl lg:text-5xl font-bold leading-tight">
			<?php echo esc_html( $title ); ?>
		</h2>

		<?php if ( $description ) : ?>
			<p class="font-sans text-lg leading-relaxed opacity-80 max-w-xl">
				<?php echo esc_html( $description ); ?>
			</p>
		<?php endif; ?>

		<a href="<?php echo esc_url( $cta_link ); ?>" class="inline-block <?php echo esc_attr( $btn_classes ); ?> px-10 py-4 rounded-full font-medium text-lg transition-all mt-2">
			<?php echo esc_html( $cta_label ); ?>
		</a>

	</div>
</section>
