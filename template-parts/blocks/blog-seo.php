<?php
/**
 * Статья: SEO / CTA блок (layout seo).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = tolstenko_block_attributes();
$defaults = tolstenko_blog_content_block_defaults( 'blog_seo' );

$title    = trim( (string) ( $attrs['block_blog_seo_title'] ?? '' ) );
$btn_text = trim( (string) ( $attrs['block_blog_seo_btn'] ?? '' ) );
$btn_url  = trim( (string) ( $attrs['block_blog_seo_btn_url'] ?? '' ) );

if ( $title === '' ) {
	$title = (string) ( $defaults['title'] ?? '' );
}
if ( $btn_text === '' ) {
	$btn_text = (string) ( $defaults['btn'] ?? '' );
}
if ( $btn_url === '' ) {
	$btn_url = trim( (string) ( $defaults['btn_url'] ?? '' ) );
}

if ( $title === '' && $btn_text === '' ) {
	return;
}

if ( $btn_url === '' || $btn_url === '#modal' ) {
	$btn_url = '#modal';
}
$is_modal = ( $btn_url === '#modal' );
?>
<div class="single-blog__content-block single-blog__content-block--seo br-30">
	<?php if ( $title !== '' ) : ?>
		<h2 class="single-blog__content-title h2"><?php echo tolstenko_kses_html( $title ); ?></h2>
	<?php endif; ?>
	<?php if ( $btn_text !== '' ) : ?>
		<a
			class="single-blog__content-btn default-btn line-caps-bold-16-15"
			href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
	<?php endif; ?>
</div>
<?php
