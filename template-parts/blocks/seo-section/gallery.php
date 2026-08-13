<?php
/**
 * SEO продвижение: раскладка «галерея» + Fancybox.
 *
 * @var array $args Аргументы get_template_part(): block — нормализованная строка.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row = tolstenko_get_seo_section_partial_row( $args ?? array() );
if ( null === $row || empty( $row['gallery'] ) ) {
	return;
}

$fancybox_group = 'seo-gallery-' . wp_unique_id();
?>
<div class="seo-section__block seo-section__block--gallery">
	<?php tolstenko_seo_section_block_title( $row ); ?>

	<div class="seo-section__gallery">
		<?php
		foreach ( $row['gallery'] as $image_id ) :
			$image_url = (string) wp_get_attachment_image_url( $image_id, 'large' );
			if ( $image_url === '' ) {
				continue;
			}
			$full_url  = (string) wp_get_attachment_image_url( $image_id, 'full' );
			if ( $full_url === '' ) {
				$full_url = $image_url;
			}
			$image_alt = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			?>
			<div class="seo-section__gallery-item">
				<img
					class="seo-section__gallery-image"
					src="<?php echo esc_url( $image_url ); ?>"
					alt="<?php echo esc_attr( $image_alt ); ?>"
					data-fancybox="<?php echo esc_attr( $fancybox_group ); ?>"
					data-src="<?php echo esc_url( $full_url ); ?>"
					<?php if ( $image_alt !== '' ) : ?>
						data-caption="<?php echo esc_attr( $image_alt ); ?>"
					<?php endif; ?>
					loading="lazy"
					decoding="async"
				>
			</div>
		<?php endforeach; ?>
	</div>
</div>
