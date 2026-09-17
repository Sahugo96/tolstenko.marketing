<?php
/**
 * Статья: плюсы и минусы.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_pros_cons' )
	: array();

$sanitize_lines = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $row ) {
		$text = is_array( $row ) ? (string) ( $row['text'] ?? '' ) : (string) $row;
		if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
			continue;
		}
		$out[] = $text;
	}
	return $out;
};

$pros_title = trim( (string) ( $attrs['block_blog_pros_cons_pros_title'] ?? '' ) );
$cons_title = trim( (string) ( $attrs['block_blog_pros_cons_cons_title'] ?? '' ) );
$pros       = $sanitize_lines( $attrs['block_blog_pros_cons_pros'] ?? array() );
$cons       = $sanitize_lines( $attrs['block_blog_pros_cons_cons'] ?? array() );

if ( $pros_title === '' ) {
	$pros_title = trim( (string) ( $defaults['pros_title'] ?? '' ) );
}
if ( $cons_title === '' ) {
	$cons_title = trim( (string) ( $defaults['cons_title'] ?? '' ) );
}
if ( ! $pros ) {
	$pros = $sanitize_lines( $defaults['pros'] ?? array() );
}
if ( ! $cons ) {
	$cons = $sanitize_lines( $defaults['cons'] ?? array() );
}

if ( $pros_title === '' ) {
	$pros_title = __( 'Плюсы', 'tolstenko-theme' );
}
if ( $cons_title === '' ) {
	$cons_title = __( 'Минусы', 'tolstenko-theme' );
}

$show_pros = ( $pros_title !== '' || $pros );
$show_cons = ( $cons_title !== '' || $cons );
if ( ! $show_pros && ! $show_cons ) {
	return;
}

$plus_svg = '<svg class="pro" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 3.5v9M3.5 8h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
$minus_svg = '<svg class="con" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3.5 8h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
?>
<div class="single-blog__pros-cons">
	<?php if ( $show_pros ) : ?>
		<div class="single-blog__pros-cons-item single-blog__pros-cons-item--pro">
			<div class="single-blog__pros-cons-body">
				<?php if ( $pros_title !== '' ) : ?>
					<p class="single-blog__pros-cons-title">
					<span class="single-blog__pros-cons-icon" aria-hidden="true"><?php echo $plus_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						
					<?php echo esc_html( $pros_title ); ?>
				</p>
				<?php endif; ?>
				<?php if ( $pros ) : ?>
					<ul class="single-blog__pros-cons-list">
						<?php foreach ( $pros as $line ) : ?>
							<li><?php echo function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $line ) : wp_kses_post( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php if ( $show_cons ) : ?>
		<div class="single-blog__pros-cons-item single-blog__pros-cons-item--con">
			<div class="single-blog__pros-cons-body">
				<?php if ( $cons_title !== '' ) : ?>
					<p class="single-blog__pros-cons-title">
					<span class="single-blog__pros-cons-icon" aria-hidden="true"><?php echo $minus_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>

						<?php echo esc_html( $cons_title ); ?>
					</p>
				<?php endif; ?>
				<?php if ( $cons ) : ?>
					<ul class="single-blog__pros-cons-list">
						<?php foreach ( $cons as $line ) : ?>
							<li><?php echo function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $line ) : wp_kses_post( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
<?php
