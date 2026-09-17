<?php
/**
 * Статья: SEO / CTA блок (layout seo).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_seo' )
	: array();

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
?>
<div class="article-cta article-cta--seo">
	<?php if ( $title !== '' ) : ?>
		<div class="article-cta__content">
			<h2 class="article-cta__title"><?php echo tolstenko_kses_html( $title ); ?></h2>
		</div>
	<?php endif; ?>
	<?php if ( $btn_text !== '' ) : ?>
		<a class="article-cta__btn default-btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
	<?php endif; ?>
</div>
<?php
