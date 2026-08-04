<?php
/**
 * Статья: крупное изображение (layout large_img).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = tolstenko_block_attributes();
$defaults = tolstenko_blog_content_block_defaults( 'blog_large_img' );

$image_id = isset( $attrs['block_blog_large_img_id'] ) ? (int) $attrs['block_blog_large_img_id'] : 0;
if ( $image_id <= 0 ) {
	$image_id = (int) ( $defaults['image'] ?? 0 );
}
if ( $image_id <= 0 ) {
	return;
}

$url = (string) wp_get_attachment_image_url( $image_id, 'full' );
if ( $url === '' ) {
	return;
}
$alt    = (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true );
$srcset = (string) wp_get_attachment_image_srcset( $image_id, 'full' );
?>
<img
	class="single-blog__content-img"
	src="<?php echo esc_url( $url ); ?>"
	<?php echo $srcset !== '' ? 'srcset="' . esc_attr( $srcset ) . '"' : ''; ?>
	alt="<?php echo esc_attr( $alt ); ?>"
	loading="lazy"
	decoding="async"
>
<?php
