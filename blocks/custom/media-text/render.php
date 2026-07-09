<?php
/**
 * Media & Text Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during backend preview render.
 * @param   int $post_id The post ID the block is rendering content against.
 * @param   array $context The context provided to the block by the post or it's parent block.
 */

// Récupération des champs ACF (avec fallback pour le mode preview/vide)
$title = get_field('title') ?: 'Titre du bloc généré par IA';
$description = get_field('description') ?: 'Un paragraphe descriptif qui vient soutenir le titre, généré automatiquement par notre modèle LLM selon le contexte de la page.';
$media = get_field('media');
$layout_reverse = get_field('layout_reverse'); // true = image à droite, false = image à gauche

// Support des classes natives générées par Gutenberg (alignements, couleurs, classes persos)
$classes = 'vibrisse-media-text flex flex-col md:flex-row gap-8 lg:gap-12 items-center py-12';
if ( ! empty( $block['className'] ) ) {
    $classes .= ' ' . $block['className'];
}
if ( ! empty( $block['align'] ) ) {
    $classes .= ' align' . $block['align'];
}
if ( $layout_reverse ) {
	$classes .= ' md:flex-row-reverse';
}
?>

<section class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $block['anchor'] ?? '' ); ?>">
    
    <div class="vibrisse-media-text__media w-full md:w-1/2">
        <?php if ( $media ) : ?>
            <img src="<?php echo esc_url( $media['url'] ); ?>" alt="<?php echo esc_attr( $media['alt'] ); ?>" class="w-full h-auto rounded-xl shadow-lg object-cover aspect-video" />
        <?php else : ?>
            <!-- Placeholder "IA-friendly" si pas d'image définie -->
            <div class="w-full aspect-video bg-muted rounded-xl flex items-center justify-center text-contrast/50 border border-contrast/10">
                <span class="font-sans text-sm font-medium">[ Espace Média ]</span>
            </div>
        <?php endif; ?>
    </div>

    <div class="vibrisse-media-text__content w-full md:w-1/2 flex flex-col gap-5">
        <h2 class="font-serif text-3xl lg:text-5xl font-bold leading-tight"><?php echo esc_html( $title ); ?></h2>
        <div class="font-sans text-lg leading-relaxed text-contrast/80">
            <?php echo wp_kses_post( $description ); ?>
        </div>
        
        <?php if ( get_field('button_link') && get_field('button_label') ) : ?>
            <div class="mt-2">
                <a href="<?php echo esc_url( get_field('button_link') ); ?>" class="inline-block bg-accent text-white px-8 py-3.5 rounded-full font-medium hover:opacity-90 transition-opacity">
                    <?php echo esc_html( get_field('button_label') ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

</section>
