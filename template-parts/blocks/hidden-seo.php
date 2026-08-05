<?php
/**
 * Блок «Скрытый seo»: контент из InnerBlocks, на фронте скрыт классом .hide.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$content = get_query_var( 'tolstenko_block_inner_content', '' );
$content = is_string( $content ) ? trim( $content ) : '';

if ( $content === '' ) {
	return;
}
?>
<div class="hide">
	<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inner blocks HTML. ?>
</div>
