<?php
/**
 * Статья: видео (layout video) — preview + rutube/youtube iframe / mp4.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_video' )
	: array();

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

$embed_src = '';
$file_url  = '';

if ( $iframe !== '' && function_exists( 'tolstenko_parse_video_embed_src' ) ) {
	$embed_src = tolstenko_parse_video_embed_src( $iframe );
}
if ( $embed_src === '' && $iframe !== '' && preg_match( '#src=(["\'])([^"\']+)\1#i', $iframe, $m ) ) {
	$embed_src = html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' );
}
if ( $embed_src === '' && $video_url !== '' && function_exists( 'tolstenko_blog_video_to_embed_url' ) ) {
	$embed_src = tolstenko_blog_video_to_embed_url( $video_url );
}
if ( $embed_src === '' && $video_url !== '' ) {
	if ( function_exists( 'tolstenko_blog_video_is_file_url' ) && tolstenko_blog_video_is_file_url( $video_url ) ) {
		$file_url = $video_url;
	} elseif ( preg_match( '#^https?://#i', $video_url ) && ! preg_match( '#(youtube|youtu\.be|rutube|vimeo)\.#i', $video_url ) ) {
		// Прямая ссылка на файл без расширения в URL (CDN и т.п.).
		$file_url = $video_url;
	}
}

if ( $embed_src === '' && $file_url === '' ) {
	return;
}

$preview_url    = $preview_id > 0 ? (string) wp_get_attachment_image_url( $preview_id, 'large' ) : '';
$preview_alt    = $preview_id > 0 ? (string) get_post_meta( $preview_id, '_wp_attachment_image_alt', true ) : '';
$preview_srcset = $preview_id > 0 ? (string) wp_get_attachment_image_srcset( $preview_id, 'large' ) : '';

if ( $preview_url === '' && $embed_src !== '' && function_exists( 'tolstenko_get_video_embed_poster' ) ) {
	$preview_url = (string) tolstenko_get_video_embed_poster( $embed_src );
}
?>
<div class="single-blog__content-video video" data-tolstenko-blog-video>
	<?php if ( $embed_src !== '' ) : ?>
		<div class="video__embed" hidden>
			<iframe
				class="video__iframe"
				data-src="<?php echo esc_url( $embed_src ); ?>"
				src="about:blank"
				title="<?php esc_attr_e( 'Видео', 'tolstenko-theme' ); ?>"
				allow="autoplay; fullscreen; picture-in-picture; encrypted-media; clipboard-write"
				allowfullscreen
				loading="lazy"
				referrerpolicy="strict-origin-when-cross-origin"
			></iframe>
		</div>
	<?php elseif ( $file_url !== '' ) : ?>
		<video class="video__iframe" src="<?php echo esc_url( $file_url ); ?>" playsinline controls preload="metadata" hidden></video>
	<?php endif; ?>

	<?php if ( $preview_url !== '' ) : ?>
		<img
			class="video__img"
			src="<?php echo esc_url( $preview_url ); ?>"
			<?php echo $preview_srcset !== '' ? 'srcset="' . esc_attr( $preview_srcset ) . '"' : ''; ?>
			alt="<?php echo esc_attr( $preview_alt !== '' ? $preview_alt : __( 'Превью видео', 'tolstenko-theme' ) ); ?>"
			loading="lazy"
			decoding="async"
		>
	<?php endif; ?>

	<button class="video__btn" type="button" aria-label="<?php esc_attr_e( 'Смотреть видео', 'tolstenko-theme' ); ?>">
		<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path fill-rule="evenodd" clip-rule="evenodd" d="M3.82263 0.0285661C3.40033 0.0281482 2.98488 0.135329 2.61549 0.339995C2.23667 0.533082 1.91754 0.82562 1.6923 1.18625C1.46706 1.54688 1.34421 1.96201 1.33691 2.38714V17.64C1.34421 18.0651 1.46706 18.4803 1.6923 18.8409C1.91754 19.2015 2.23667 19.4941 2.61549 19.6871C2.99158 19.896 3.41545 20.0038 3.84565 19.9998C4.27584 19.9957 4.69764 19.8802 5.06977 19.6643L17.3983 12.0386C17.7788 11.8468 18.0985 11.5532 18.3219 11.1904C18.5452 10.8276 18.6634 10.4099 18.6633 9.98384C18.6631 9.5578 18.5446 9.14017 18.321 8.77753C18.0974 8.41488 17.7775 8.12146 17.3969 7.92999L5.06834 0.361423L5.04549 0.348566C4.67225 0.138309 4.25101 0.0280782 3.82263 0.0285661Z" />
		</svg>
	</button>
</div>
<?php
