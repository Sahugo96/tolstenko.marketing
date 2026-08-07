<?php
/**
 * Хелперы Gutenberg-блоков тела статьи (из flexible content Tolstenko).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Разрешённый HTML для rutube/iframe в блоке видео.
 *
 * @return array
 */
function tolstenko_blog_video_iframe_allowed_html() {
	return array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'style'           => true,
			'class'           => true,
			'title'           => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'data-src'        => true,
		),
		'div'    => array(
			'class' => true,
			'style' => true,
		),
	);
}

/**
 * URL страницы/ссылки → embed URL (YouTube / Rutube / Vimeo).
 *
 * @param string $url Raw URL.
 * @return string Embed URL or empty.
 */
function tolstenko_blog_video_to_embed_url( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}

	if ( function_exists( 'tolstenko_get_rutube_video_id' ) ) {
		$rutube_id = tolstenko_get_rutube_video_id( $url );
		if ( $rutube_id !== '' ) {
			return 'https://rutube.ru/play/embed/' . rawurlencode( $rutube_id );
		}
	}

	if ( preg_match( '#(?:youtube\.com/embed/|youtube\.com/watch\?(?:[^#]*&)?v=|youtu\.be/)([a-zA-Z0-9_-]{6,})#i', $url, $m ) ) {
		return 'https://www.youtube.com/embed/' . $m[1];
	}

	if ( preg_match( '#(?:player\.)?vimeo\.com/(?:video/)?(\d+)#i', $url, $m ) ) {
		return 'https://player.vimeo.com/video/' . $m[1];
	}

	// Уже embed-ссылка.
	if ( preg_match( '#/(?:embed|play/embed)/#i', $url ) ) {
		return $url;
	}

	return '';
}

/**
 * Прямой файл видео (mp4 и т.п.).
 *
 * @param string $url URL.
 * @return bool
 */
function tolstenko_blog_video_is_file_url( $url ) {
	return (bool) preg_match( '#\.(mp4|webm|ogg|ogv|mov|m4v)(\?|#|$)#i', (string) $url );
}

/**
 * Слайги блоков тела статьи (flexible content → Gutenberg).
 *
 * @return string[]
 */
function tolstenko_get_blog_content_block_slugs() {
	return array(
		'blog-large-img',
		'blog-video',
		'blog-blockquote',
		'blog-number-list',
		'blog-warning',
		'blog-seo',
	);
}

/**
 * Полные имена блоков тела статьи.
 *
 * @return string[]
 */
function tolstenko_get_blog_content_block_names() {
	$out = array();
	foreach ( tolstenko_get_blog_content_block_slugs() as $slug ) {
		$out[] = 'tolstenko/' . $slug;
	}
	return $out;
}
