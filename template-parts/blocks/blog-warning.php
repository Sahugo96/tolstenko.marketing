<?php
/**
 * Статья: предупреждения / заметки (layout warning).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_warning' )
	: array();

$items = isset( $attrs['block_blog_warning_items'] ) && is_array( $attrs['block_blog_warning_items'] )
	? $attrs['block_blog_warning_items']
	: array();

/**
 * @param array $raw Raw items.
 * @return array<int, array{type:string,text:string,icon:int}>
 */
$normalize_warning_items = static function ( $raw ) {
	$clean = array();
	if ( ! is_array( $raw ) ) {
		return $clean;
	}
	foreach ( $raw as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$text = trim( (string) ( $it['text'] ?? '' ) );
		if ( $text === '' ) {
			continue;
		}
		$type = sanitize_key( (string) ( $it['type'] ?? 'warn' ) );
		if ( ! in_array( $type, array( 'warn', 'pin', 'ide', 'custom' ), true ) ) {
			$type = 'warn';
		}
		$clean[] = array(
			'type' => $type,
			'text' => $text,
			'icon' => isset( $it['icon'] ) ? (int) $it['icon'] : 0,
		);
	}
	return $clean;
};

$clean = $normalize_warning_items( $items );
if ( ! $clean ) {
	$clean = $normalize_warning_items( $defaults['items'] ?? array() );
}
if ( ! $clean ) {
	return;
}

/**
 * @param string $type Icon type.
 * @param int    $icon Attachment ID for custom.
 */
$render_icon = static function ( $type, $icon ) {
	if ( $type === 'custom' && $icon > 0 ) {
		if ( tolstenko_render_attachment_inline_svg( $icon ) ) {
			return;
		}
		$url = wp_get_attachment_image_url( $icon, 'thumbnail' );
		if ( $url ) {
			echo '<img src="' . esc_url( $url ) . '" alt="" width="55" height="55">';
			return;
		}
	}
	if ( $type === 'pin' ) {
		echo '<svg class="pin" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M27.5 0C42.6878 0 55 12.3122 55 27.5C55 42.6878 42.6878 55 27.5 55C12.3122 55 0 42.6878 0 27.5C0 12.3122 12.3122 0 27.5 0ZM29.5986 11.0898C29.2604 11.0536 28.9183 11.1011 28.6025 11.2275C28.2865 11.3541 28.0059 11.5565 27.7861 11.8164C27.5664 12.0763 27.4132 12.3862 27.3408 12.7188L25.7607 19.9932C24.4911 19.8691 23.2111 20.0908 22.0566 20.6338C20.9024 21.1767 19.9162 22.0211 19.2021 23.0781L17.4102 25.7246C17.2249 25.999 17.156 26.336 17.2188 26.6611C17.2816 26.9862 17.471 27.2733 17.7451 27.459L21.9287 30.2881L14.3525 41.4961C14.2573 41.632 14.1896 41.7852 14.1543 41.9473C14.119 42.1094 14.1163 42.2772 14.1465 42.4404C14.1767 42.6036 14.2391 42.7596 14.3301 42.8984C14.421 43.0372 14.5393 43.1562 14.6768 43.249C14.8143 43.3418 14.9691 43.407 15.1318 43.4395C15.2945 43.4718 15.4625 43.4715 15.625 43.4385C15.7875 43.4054 15.942 43.3403 16.0791 43.2471C16.2163 43.1537 16.3334 43.0337 16.4238 42.8945L24 31.6865L28.1816 34.5146C28.4561 34.6999 28.793 34.7689 29.1182 34.7061C29.4434 34.6432 29.7304 34.453 29.916 34.1787L31.709 31.5303C32.4238 30.473 32.8401 29.242 32.9131 27.9678C32.986 26.6938 32.7133 25.4238 32.124 24.292L38.2842 20.1123C38.5657 19.9213 38.7958 19.6639 38.9551 19.3633C39.1143 19.0627 39.1974 18.7279 39.1973 18.3877C39.1971 18.0475 39.1136 17.7126 38.9541 17.4121C38.7945 17.1116 38.564 16.8539 38.2822 16.6631L30.5439 11.4355C30.262 11.2449 29.9371 11.1261 29.5986 11.0898Z"/></svg>';
		return;
	}
	if ( $type === 'ide' ) {
		echo '<svg class="ide" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M27.5 0C12.3122 0 0 12.3122 0 27.5C0 42.6878 12.3122 55 27.5 55C42.6878 55 55 42.6878 55 27.5C55 12.3122 42.6878 0 27.5 0ZM26.5668 10.1648H28.4333V15.6668H26.5668V10.1648ZM18.9331 11.8164L22.0886 16.3214L20.5579 17.3956L17.4024 12.8906L18.9331 11.8164ZM36.0669 11.8164L37.5976 12.8906L34.4421 17.3956L32.9114 16.3214L36.0669 11.8164ZM27.5 17.1707C32.0721 17.1707 35.7782 19.9459 35.7782 23.371L31.0751 37.6145H23.9249L19.2218 23.371C19.2218 19.9458 22.9282 17.1707 27.5 17.1707ZM12.6321 18.8223L17.8018 20.7056L17.1606 22.4613L11.9943 20.578L12.6321 18.8223ZM42.3679 18.8223L43.0057 20.578L37.836 22.4613L37.1982 20.7056L42.3679 18.8223ZM17.5534 26.8555L18.0368 28.6615L12.7228 30.0849L12.2394 28.2788L17.5534 26.8555ZM37.4466 26.8555L42.7606 28.2788L42.2772 30.0849L36.9632 28.6615L37.4466 26.8555ZM23.8208 38.6551H31.1792V41.1291H23.8208V38.6551ZM23.8208 42.3611H31.1792V44.8352H23.8208V42.3611Z"/></svg>';
		return;
	}
	echo '<svg class="warn" viewBox="0 0 55 55" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M54.7822 27.5005C54.7822 12.4328 42.5682 0.21875 27.5005 0.21875C12.4328 0.21875 0.21875 12.4328 0.21875 27.5005C0.21875 42.5682 12.4328 54.7822 27.5005 54.7822C42.5682 54.7822 54.7822 42.5682 54.7822 27.5005ZM27.5005 13.8596C28.2241 13.8596 28.918 14.1471 29.4296 14.6587C29.9412 15.1703 30.2287 15.8642 30.2287 16.5878V30.2287C30.2287 30.9522 29.9412 31.6461 29.4296 32.1578C28.918 32.6694 28.2241 32.9568 27.5005 32.9568C26.7769 32.9568 26.083 32.6694 25.5714 32.1578C25.0598 31.6461 24.7723 30.9522 24.7723 30.2287V16.5878C24.7723 15.8642 25.0598 15.1703 25.5714 14.6587C26.083 14.1471 26.7769 13.8596 27.5005 13.8596ZM24.7723 38.4132C24.7723 37.6896 25.0598 36.9957 25.5714 36.4841C26.083 35.9725 26.7769 35.685 27.5005 35.685H27.5223C28.2459 35.685 28.9398 35.9725 29.4514 36.4841C29.9631 36.9957 30.2505 37.6896 30.2505 38.4132C30.2505 39.1368 29.9631 39.8307 29.4514 40.3423C28.9398 40.8539 28.2459 41.1414 27.5223 41.1414H27.5005C26.7769 41.1414 26.083 40.8539 25.5714 40.3423C25.0598 39.8307 24.7723 39.1368 24.7723 38.4132Z"/></svg>';
};
?>
<div class="single-blog__content-block single-blog__content-block--warning br-30">
	<ul class="single-blog__warnings">
		<?php foreach ( $clean as $item ) : ?>
			<li class="single-blog__warnings-item">
				<?php $render_icon( $item['type'], $item['icon'] ); ?>
				<span><?php echo tolstenko_kses_html( $item['text'] ); ?></span>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
