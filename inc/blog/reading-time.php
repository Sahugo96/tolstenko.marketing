<?php
/**
 * Reading Time WP: отключаем автовывод, оставляем шорткод для stats.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'tolstenko_disable_reading_time_wp_auto_output', 20 );

/**
 * Убираем авто-вставку времени чтения в контент/excerpt.
 */
function tolstenko_disable_reading_time_wp_auto_output() {
	global $reading_time_wp;

	if ( ! isset( $reading_time_wp ) || ! is_object( $reading_time_wp ) ) {
		return;
	}

	remove_filter( 'the_content', array( $reading_time_wp, 'rt_add_reading_time_before_content' ) );
	remove_filter( 'get_the_excerpt', array( $reading_time_wp, 'rt_add_reading_time_before_excerpt' ), 1000 );
}

/**
 * Текст времени чтения через шорткод плагина.
 *
 * @param int $post_id Post ID.
 * @return string
 */
function tolstenko_get_reading_time_text( $post_id = 0 ) {
	if ( ! shortcode_exists( 'rt_reading_time' ) ) {
		return '';
	}

	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return '';
	}

	$text = wp_strip_all_tags(
		do_shortcode(
			sprintf(
				'[rt_reading_time post_id="%d" postfix="мин" postfix_singular="мин"]',
				$post_id
			)
		)
	);

	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) ) );
}
