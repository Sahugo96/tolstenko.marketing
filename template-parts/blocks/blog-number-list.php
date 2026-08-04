<?php
/**
 * Статья: нумерованный список (layout number_list).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_number_list' )
	: array();

$items = isset( $attrs['block_blog_number_list_items'] ) && is_array( $attrs['block_blog_number_list_items'] )
	? $attrs['block_blog_number_list_items']
	: array();

$clean = array();
foreach ( $items as $it ) {
	$text = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $text !== '' ) {
		$clean[] = $text;
	}
}

if ( ! $clean && ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	foreach ( $defaults['items'] as $it ) {
		$text = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
		if ( $text !== '' ) {
			$clean[] = $text;
		}
	}
}

if ( ! $clean ) {
	return;
}
?>
<div class="single-blog__content-block single-blog__content-block--number br-30">
	<ul class="single-blog__content-list">
		<?php foreach ( $clean as $text ) : ?>
			<li><span></span><?php echo tolstenko_kses_html( $text ); ?></li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
