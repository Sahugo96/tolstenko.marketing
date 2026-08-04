<?php
/**
 * SEO продвижение: раскладка «редактор» (произвольный HTML из редактора).
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

$html = tolstenko_kses_redactor( $row['redactor'] );
if ( $html === '' && $row['title'] === '' ) {
	return;
}
?>
<div class="seo-section__block seo-section__block--redactor">
	<?php tolstenko_seo_section_block_title( $row ); ?>
	<?php if ( $html !== '' ) : ?>
		<div class="seo-section__text redactor paragraph-15-25"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses. ?></div>
	<?php endif; ?>
</div>
