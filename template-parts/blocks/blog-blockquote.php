<?php
/**
 * Статья: цитата с автором (layout blockquote).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_blockquote' )
	: array();

$text   = trim( (string) ( $attrs['block_blog_blockquote_text'] ?? '' ) );
$cite   = trim( (string) ( $attrs['block_blog_blockquote_link'] ?? '' ) );
$img_id = isset( $attrs['block_blog_blockquote_image'] ) ? (int) $attrs['block_blog_blockquote_image'] : 0;
$author = trim( (string) ( $attrs['block_blog_blockquote_author'] ?? '' ) );
$under  = trim( (string) ( $attrs['block_blog_blockquote_author_under'] ?? '' ) );
$btn_t  = trim( (string) ( $attrs['block_blog_blockquote_btn_text'] ?? '' ) );
$btn_u  = trim( (string) ( $attrs['block_blog_blockquote_btn_url'] ?? '' ) );

if ( $text === '' ) {
	$text = (string) ( $defaults['text'] ?? '' );
}
if ( $cite === '' ) {
	$cite = (string) ( $defaults['link'] ?? '' );
}
if ( $img_id <= 0 ) {
	$img_id = (int) ( $defaults['image'] ?? 0 );
}
if ( $author === '' ) {
	$author = (string) ( $defaults['author'] ?? '' );
}
if ( $under === '' ) {
	$under = (string) ( $defaults['author_under'] ?? '' );
}
if ( $btn_t === '' ) {
	$btn_t = (string) ( $defaults['btn_text'] ?? '' );
}
if ( $btn_u === '' ) {
	$btn_u = (string) ( $defaults['btn_url'] ?? '' );
}

$show = array_key_exists( 'block_blog_blockquote_show_author', $attrs )
	? ! empty( $attrs['block_blog_blockquote_show_author'] )
	: ! empty( $defaults['show_author'] );

if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
	return;
}

$quote_svg = '<svg viewBox="0 0 35 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M22.3571 29.7308C29.866 24.7733 34.0377 19.0027 34.872 12.4193C36.1713 2.17096 27.2564 -2.84506 22.739 1.66146C18.2217 6.16798 20.9291 11.8893 24.1999 13.4537C27.4708 15.018 29.4711 14.4726 29.1221 16.5629C28.7731 18.6532 24.1202 24.4462 20.0316 27.1444C19.887 27.2712 19.7947 27.4499 19.7736 27.6441C19.7524 27.8383 19.8041 28.0336 19.9179 28.1901L20.9291 29.5422C21.3679 30.1286 21.7887 30.106 22.3571 29.7319M2.58657 29.7308C10.0955 24.7733 14.2671 19.0027 15.1015 12.4193C16.4018 2.17096 7.48683 -2.84506 2.96951 1.66146C-1.54782 6.16798 1.15958 11.8893 4.4314 13.4537C7.70322 15.018 9.70261 14.4726 9.35359 16.5629C9.00457 18.6532 4.35063 24.4462 0.2621 27.1444C0.117573 27.2713 0.0254824 27.4501 0.00455495 27.6443C-0.0163763 27.8385 0.0354196 28.0337 0.149414 28.1901L1.15958 29.5422C1.59835 30.1286 2.01917 30.106 2.58657 29.7319"/></svg>';
?>
<blockquote class="single-blog__content-block single-blog__content-block--blockquote br-30 blockquote"<?php echo $cite !== '' ? ' cite="' . esc_url( $cite ) . '"' : ''; ?>>
	<div class="blockquote__content">
		<?php echo $quote_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<p class="paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
		<?php echo $quote_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<?php if ( $show && ( $img_id || $author !== '' || $under !== '' || $btn_t !== '' ) ) : ?>
		<div class="blockquote__right">
			<?php if ( $img_id > 0 ) : ?>
				<?php
				$img_url    = (string) wp_get_attachment_image_url( $img_id, 'medium' );
				$img_srcset = (string) wp_get_attachment_image_srcset( $img_id, 'medium' );
				?>
				<?php if ( $img_url !== '' ) : ?>
					<img
						class="blockquote__right-img"
						src="<?php echo esc_url( $img_url ); ?>"
						<?php echo $img_srcset !== '' ? 'srcset="' . esc_attr( $img_srcset ) . '"' : ''; ?>
						alt="<?php echo esc_attr( $author ); ?>"
						loading="lazy"
						decoding="async"
					>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( $author !== '' || $under !== '' ) : ?>
				<div class="blockquote__right-wrapper">
					<?php if ( $author !== '' ) : ?>
						<cite class="line-caps-bold-16-15"><?php echo esc_html( $author ); ?></cite>
					<?php endif; ?>
					<?php if ( $under !== '' ) : ?>
						<span><?php echo esc_html( $under ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $btn_t !== '' ) : ?>
				<?php
				if ( $btn_u === '' || $btn_u === '#modal' ) {
					$btn_u = '#modal';
				}
				$btn_is_modal = ( $btn_u === '#modal' );
				?>
				<a
					class="blockquote__right-btn default-btn"
					href="<?php echo esc_url( $btn_u ); ?>"><?php echo esc_html( $btn_t ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</blockquote>
<?php
