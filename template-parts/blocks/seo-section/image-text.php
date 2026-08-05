<?php
/**
 * SEO продвижение: раскладка «фото + текст» / «текст + фото».
 *
 * @var array $args Аргументы get_template_part(): block — нормализованная строка.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$row = tolstenko_get_seo_section_partial_row( $args ?? array() );
if ( null === $row ) {
	return;
}

$image_url = $row['image'] > 0 ? (string) wp_get_attachment_image_url( $row['image'], 'large' ) : '';
$image_alt = $row['image'] > 0 ? (string) get_post_meta( $row['image'], '_wp_attachment_image_alt', true ) : '';
if ( $image_alt === '' ) {
	$image_alt = $row['title'];
}

$classes = array( 'seo-section__block' );
$classes[] = $row['reverse'] ? 'seo-section__block--text-image' : 'seo-section__block--image-text';
if ( $row['reverse'] ) {
	$classes[] = 'seo-section__block--image-right';
}

$btn_classes = 'seo-section__btn default-btn line-caps-bold-13-15';
if ( ! empty( $row['btn_wide'] ) ) {
	$btn_classes .= ' seo-section__btn--wide';
}
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $image_url !== '' ) : ?>
		<div class="seo-section__media">
			<img class="seo-section__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy" decoding="async">
		</div>
	<?php endif; ?>

	<div class="seo-section__content">
		<?php tolstenko_seo_section_block_title( $row ); ?>
		<?php if ( trim( wp_strip_all_tags( $row['text'] ) ) !== '' ) : ?>
			<div class="seo-section__text redactor paragraph-15-25"><?php echo tolstenko_kses_redactor( $row['text'] ); ?></div>
		<?php endif; ?>
		<?php if ( $row['btn_text'] !== '' ) : ?>
			<a class="<?php echo esc_attr( $btn_classes ); ?>" href="<?php echo esc_url( tolstenko_url_or_modal( $row['btn_url'] ) ); ?>">
				<?php echo esc_html( $row['btn_text'] ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
