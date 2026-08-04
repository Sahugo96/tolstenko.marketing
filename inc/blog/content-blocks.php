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
		),
		'div'    => array(
			'class' => true,
			'style' => true,
		),
	);
}

/**
 * Слайги блоков тела статьи (flexible content → Gutenberg).
 *
 * @return string[]
 */
function tolstenko_get_blog_content_block_slugs() {
	return array(
		'blog-large-img',
		'blog-imgs',
		'blog-video',
		'blog-blockquote',
		'blog-number-list',
		'blog-warning',
		'blog-seo',
		'blog-table',
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
