<?php
/**
 * Статья: два изображения (layout imgs).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_imgs' )
	: array();

$left_id  = isset( $attrs['block_blog_imgs_left'] ) ? (int) $attrs['block_blog_imgs_left'] : 0;
$right_id = isset( $attrs['block_blog_imgs_right'] ) ? (int) $attrs['block_blog_imgs_right'] : 0;
if ( $left_id <= 0 ) {
	$left_id = (int) ( $defaults['left'] ?? 0 );
}
if ( $right_id <= 0 ) {
	$right_id = (int) ( $defaults['right'] ?? 0 );
}
if ( $left_id <= 0 && $right_id <= 0 ) {
	return;
}
?>
<div class="single-blog__content-imgs">
	<?php if ( $left_id > 0 ) : ?>
		<?php
		$left_url = (string) wp_get_attachment_image_url( $left_id, 'large' );
		$left_alt = (string) get_post_meta( $left_id, '_wp_attachment_image_alt', true );
		?>
		<?php if ( $left_url !== '' ) : ?>
			<img src="<?php echo esc_url( $left_url ); ?>" alt="<?php echo esc_attr( $left_alt ); ?>" loading="lazy" decoding="async">
		<?php endif; ?>
	<?php endif; ?>
	<?php if ( $right_id > 0 ) : ?>
		<?php
		$right_url = (string) wp_get_attachment_image_url( $right_id, 'large' );
		$right_alt = (string) get_post_meta( $right_id, '_wp_attachment_image_alt', true );
		?>
		<?php if ( $right_url !== '' ) : ?>
			<img src="<?php echo esc_url( $right_url ); ?>" alt="<?php echo esc_attr( $right_alt ); ?>" loading="lazy" decoding="async">
		<?php endif; ?>
	<?php endif; ?>
</div>
<?php
