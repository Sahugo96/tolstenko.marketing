<?php
/**
 * Блок «Страница Спасибо»: заголовок и описание после успешной отправки формы.
 * Используется на странице с ярлыком thanks (или ok-thanks).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = __( 'Спасибо за заявку!', 'tolstenko-theme' );
$title_tag   = 'h2';
$description = __( 'Мы свяжемся с вами в ближайшее время.', 'tolstenko-theme' );

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! empty( $attrs['block_thanks_title'] ) ) {
	$title = $attrs['block_thanks_title'];
}
if ( ! empty( $attrs['block_thanks_title_tag'] ) ) {
	$title_tag = $attrs['block_thanks_title_tag'];
}
if ( ! empty( $attrs['block_thanks_description'] ) ) {
	$description = $attrs['block_thanks_description'];
}
?>
<div class="thanks">
	<div class="container thanks-inner">
		<?php $title_tag = tolstenko_normalize_heading_tag( $title_tag, 'h2' ); ?>
		<<?php echo esc_attr( $title_tag ); ?> class="thanks-title"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
		<p class="thanks-description"><?php echo tolstenko_kses_html( $description ); ?></p>
	</div>
</div>
