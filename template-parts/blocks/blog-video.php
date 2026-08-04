<?php
/**
 * Статья: видео (layout video) — preview + rutube iframe / video_url.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = tolstenko_block_attributes();
$defaults = tolstenko_blog_content_block_defaults( 'blog_video' );

$preview_id = isset( $attrs['block_blog_video_preview'] ) ? (int) $attrs['block_blog_video_preview'] : 0;
$video_url  = isset( $attrs['block_blog_video_url'] ) ? trim( (string) $attrs['block_blog_video_url'] ) : '';
$iframe     = isset( $attrs['block_blog_video_iframe'] ) ? trim( (string) $attrs['block_blog_video_iframe'] ) : '';

if ( $preview_id <= 0 ) {
	$preview_id = (int) ( $defaults['preview'] ?? 0 );
}
if ( $video_url === '' ) {
	$video_url = trim( (string) ( $defaults['url'] ?? '' ) );
}
if ( $iframe === '' ) {
	$iframe = trim( (string) ( $defaults['iframe'] ?? '' ) );
}

if ( $iframe === '' && $video_url === '' ) {
	return;
}

$preview_url    = $preview_id > 0 ? (string) wp_get_attachment_image_url( $preview_id, 'large' ) : '';
$preview_alt    = $preview_id > 0 ? (string) get_post_meta( $preview_id, '_wp_attachment_image_alt', true ) : '';
$preview_srcset = $preview_id > 0 ? (string) wp_get_attachment_image_srcset( $preview_id, 'large' ) : '';
?>
<div class="single-blog__content-video video" data-tolstenko-blog-video>
	<?php if ( $iframe !== '' ) : ?>
		<div class="video__embed" hidden>
			<?php echo wp_kses( $iframe, tolstenko_blog_video_iframe_allowed_html() ); ?>
		</div>
	<?php elseif ( $video_url !== '' ) : ?>
		<video class="video__iframe" src="<?php echo esc_url( $video_url ); ?>" loop playsinline controls hidden></video>
	<?php endif; ?>

	<?php if ( $preview_url !== '' ) : ?>
		<img
			class="video__img"
			src="<?php echo esc_url( $preview_url ); ?>"
			<?php echo $preview_srcset !== '' ? 'srcset="' . esc_attr( $preview_srcset ) . '"' : ''; ?>
			alt="<?php echo esc_attr( $preview_alt ); ?>"
			loading="lazy"
			decoding="async"
		>
	<?php endif; ?>

	<button class="video__btn" type="button" aria-label="<?php esc_attr_e( 'Смотреть видео', 'tolstenko-theme' ); ?>" <?php echo $video_url !== '' ? 'data-video="' . esc_url( $video_url ) . '"' : ''; ?>>
		<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M3.82263 0.0285661C3.40033 0.0281482 2.98488 0.135329 2.61549 0.339995C2.23667 0.533082 1.91754 0.82562 1.6923 1.18625C1.46706 1.54688 1.34421 1.96201 1.33691 2.38714V17.64C1.34421 18.0651 1.46706 18.4803 1.6923 18.8409C1.91754 19.2015 2.23667 19.4941 2.61549 19.6871C2.99158 19.896 3.41545 20.0038 3.84565 19.9998C4.27584 19.9957 4.69764 19.8802 5.06977 19.6643L17.3983 12.0386C17.7788 11.8468 18.0985 11.5532 18.3219 11.1904C18.5452 10.8276 18.6634 10.4099 18.6633 9.98384C18.6631 9.5578 18.5446 9.14017 18.321 8.77753C18.0974 8.41488 17.7775 8.12146 17.3969 7.92999L5.06834 0.361423L5.04549 0.348566C4.67225 0.138309 4.25101 0.0280782 3.82263 0.0285661Z" />
		</svg>
	</button>
</div>
<?php
