<?php
/**
 * SEO продвижение: раскладка «текст» (узкая колонка по центру).
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

$has_text = trim( wp_strip_all_tags( $row['text'] ) ) !== '';
if ( $row['title'] === '' && ! $has_text ) {
	return;
}
?>
<div class="seo-section__block seo-section__block--text">
	<?php tolstenko_seo_section_block_title( $row ); ?>
	<?php if ( $has_text ) : ?>
		<div class="seo-section__text redactor paragraph-15-25"><?php echo tolstenko_kses_redactor( $row['text'] ); ?></div>
	<?php endif; ?>
</div>
