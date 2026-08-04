<?php
/**
 * SEO продвижение: раскладка «две колонки» (+ список пунктов под колонками).
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

$columns = array();
foreach ( $row['columns'] as $column ) {
	if ( trim( wp_strip_all_tags( $column ) ) === '' ) {
		continue;
	}
	$columns[] = $column;
}
?>
<div class="seo-section__block seo-section__block--two-columns">
	<?php tolstenko_seo_section_block_title( $row ); ?>

	<?php if ( ! empty( $columns ) ) : ?>
		<div class="seo-section__columns">
			<?php foreach ( $columns as $column ) : ?>
				<div class="seo-section__column redactor paragraph-15-25"><?php echo tolstenko_kses_redactor( $column ); ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $row['items'] ) ) : ?>
		<ul class="seo-section__items">
			<?php foreach ( $row['items'] as $item ) : ?>
				<li class="seo-section__item">
					<?php if ( $item['title'] !== '' ) : ?>
						<div class="seo-section__item-title line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item['title'] ); ?></div>
					<?php endif; ?>
					<?php if ( trim( wp_strip_all_tags( $item['text'] ) ) !== '' ) : ?>
						<div class="seo-section__item-text redactor paragraph-15-25"><?php echo tolstenko_kses_redactor( $item['text'] ); ?></div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
