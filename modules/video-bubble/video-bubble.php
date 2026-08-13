<?php
/**
 * Плавающий видео-пузырь на всех страницах.
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Преобразует URL YouTube / Rutube / VK Video в embed URL (с autoplay).
 *
 * @param string $url Raw URL.
 * @return string Embed URL or empty.
 */
function tolstenko_video_bubble_embed_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	// Already embed.
	if ( preg_match( '#youtube\.com/embed/([A-Za-z0-9_-]+)#i', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&mute=1&rel=0&playsinline=1';
	}
	if ( preg_match( '#rutube\.ru/play/embed/([A-Za-z0-9]+)#i', $url, $m ) ) {
		return 'https://rutube.ru/play/embed/' . $m[1] . '?autoplay=1&muted=1';
	}
	if ( preg_match( '#(?:vkvideo\.ru|vk\.com|vk\.ru)/video_ext\.php#i', $url ) ) {
		$parts = wp_parse_url( $url );
		$query = array();
		if ( ! empty( $parts['query'] ) ) {
			parse_str( $parts['query'], $query );
		}
		$oid = isset( $query['oid'] ) ? (string) $query['oid'] : '';
		$id  = isset( $query['id'] ) ? (string) $query['id'] : '';
		if ( $oid !== '' && $id !== '' ) {
			$embed = 'https://vkvideo.ru/video_ext.php?oid=' . rawurlencode( $oid ) . '&id=' . rawurlencode( $id ) . '&hd=2&autoplay=1';
			if ( ! empty( $query['hash'] ) ) {
				$embed .= '&hash=' . rawurlencode( (string) $query['hash'] );
			}
			return $embed;
		}
	}

	// youtube.com/watch?v=ID
	if ( preg_match( '#[?&]v=([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&mute=1&rel=0&playsinline=1';
	}
	// youtu.be/ID
	if ( preg_match( '#youtu\.be/([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&mute=1&rel=0&playsinline=1';
	}
	// youtube shorts
	if ( preg_match( '#youtube\.com/shorts/([A-Za-z0-9_-]{6,})#', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&mute=1&rel=0&playsinline=1';
	}
	// rutube.ru/video/ID/
	if ( preg_match( '#rutube\.ru/video/([A-Za-z0-9]+)#i', $url, $m ) ) {
		return 'https://rutube.ru/play/embed/' . $m[1] . '?autoplay=1&muted=1';
	}

	// VK Video / VK: video-OWNER_ID or videoOWNER_ID
	// https://vkvideo.ru/video-96880569_456239092
	// https://vk.com/video123_456
	// https://vkvideo.ru/clip-123_456
	if ( preg_match( '#(?:vkvideo\.ru|vk\.com|vk\.ru)/(?:video|clip)(-?\d+)_(\d+)#i', $url, $m ) ) {
		$oid = $m[1];
		$id  = $m[2];
		return 'https://vkvideo.ru/video_ext.php?oid=' . rawurlencode( $oid ) . '&id=' . rawurlencode( $id ) . '&hd=2&autoplay=1';
	}
	// z=video-OWNER_ID in query
	if ( preg_match( '#[?&]z=(?:video|clip)(-?\d+)_(\d+)#i', $url, $m ) ) {
		$oid = $m[1];
		$id  = $m[2];
		return 'https://vkvideo.ru/video_ext.php?oid=' . rawurlencode( $oid ) . '&id=' . rawurlencode( $id ) . '&hd=2&autoplay=1';
	}

	return '';
}

if ( is_admin() ) {
	return;
}

$cfg = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'video_bubble' ) : array();
if ( empty( $cfg['enabled'] ) ) {
	return;
}

$source = sanitize_key( (string) ( $cfg['source'] ?? 'file' ) );
if ( ! in_array( $source, array( 'file', 'iframe' ), true ) ) {
	$source = 'file';
}

$video_id  = (int) ( $cfg['video'] ?? 0 );
$video_url = $video_id ? (string) wp_get_attachment_url( $video_id ) : '';
$embed_url = tolstenko_video_bubble_embed_url( (string) ( $cfg['iframe_url'] ?? '' ) );

$has_file   = ( $source === 'file' && $video_url !== '' );
$has_iframe = ( $source === 'iframe' && $embed_url !== '' );
if ( ! $has_file && ! $has_iframe ) {
	if ( $video_url !== '' ) {
		$has_file = true;
		$source   = 'file';
	} elseif ( $embed_url !== '' ) {
		$has_iframe = true;
		$source     = 'iframe';
	} else {
		return;
	}
}

$btn_text = trim( (string) ( $cfg['btn_text'] ?? '' ) );
if ( $btn_text === '' ) {
	$btn_text = __( 'Консультация', 'tolstenko-theme' );
}
$btn_url = function_exists( 'tolstenko_url_or_modal' )
	? tolstenko_url_or_modal( (string) ( $cfg['btn_url'] ?? '' ) )
	: ( trim( (string) ( $cfg['btn_url'] ?? '' ) ) !== '' ? (string) $cfg['btn_url'] : '#modal' );

$position = sanitize_key( (string) ( $cfg['position'] ?? 'left' ) );
if ( ! in_array( $position, array( 'left', 'right' ), true ) ) {
	$position = 'left';
}

$delay = isset( $cfg['delay_seconds'] ) ? (int) $cfg['delay_seconds'] : 5;
if ( $delay < 0 ) {
	$delay = 0;
}
if ( $delay > 120 ) {
	$delay = 120;
}

$classes = array(
	'video-bubble',
	'video-bubble--' . $position,
);
?>

<div
	class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
	id="video-bubble"
	class="video-bubble"
	hidden
	aria-hidden="true"
	data-source="<?php echo esc_attr( $source ); ?>"
	data-delay="<?php echo esc_attr( (string) $delay ); ?>"
	<?php if ( $has_iframe ) : ?>
		data-embed="<?php echo esc_url( $embed_url ); ?>"
	<?php endif; ?>
>
	<button class="video-bubble__close" type="button" aria-label="<?php esc_attr_e( 'Закрыть', 'tolstenko-theme' ); ?>">
		<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path d="M3 3L11 11M11 3L3 11" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
		</svg>
	</button>

	<div class="video-bubble__media" data-video-bubble-media>
		<span class="video-bubble__time" data-video-bubble-time hidden>0:00</span>

		<?php if ( $has_file ) : ?>
			<video
				class="video-bubble__video"
				data-video-bubble-player
				playsinline
				muted
				loop
				preload="auto"
				src="<?php echo esc_url( $video_url ); ?>"
			></video>
		<?php else : ?>
			<div class="video-bubble__iframe-wrap" data-video-bubble-iframe></div>
		<?php endif; ?>

		<button
			class="video-bubble__hit"
			type="button"
			data-video-bubble-hit
			aria-label="<?php esc_attr_e( 'Показать кнопку консультации', 'tolstenko-theme' ); ?>"
		></button>
	</div>

	<a
		class="video-bubble__btn default-btn"
		href="<?php echo esc_url( $btn_url ); ?>"
		data-video-bubble-btn
		hidden
	><?php echo esc_html( $btn_text ); ?></a>
</div>
