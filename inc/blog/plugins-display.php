<?php
/**
 * WP ULike / Post Views Counter: вывод в stats, подсчёт для CPT blog.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp', 'tolstenko_disable_blog_auto_views_likes', 20 );
add_action( 'init', 'tolstenko_pvc_enable_blog_post_type', 20 );
add_filter( 'pvc_display_views_count', 'tolstenko_hide_blog_post_views_auto_display' );
add_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );

/**
 * Число просмотров без разметки плагина (только цифра).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_post_views_count( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return 0;
	}

	if ( function_exists( 'pvc_get_post_views' ) ) {
		return (int) pvc_get_post_views( $post_id );
	}

	return 0;
}

/**
 * Добавляет CPT blog в список типов Post Views Counter.
 *
 * @param mixed $opts General settings.
 * @return mixed
 */
function tolstenko_pvc_option_add_blog_type( $opts ) {
	if ( ! is_array( $opts ) ) {
		return $opts;
	}

	$types = isset( $opts['post_types_count'] ) && is_array( $opts['post_types_count'] )
		? $opts['post_types_count']
		: array();

	if ( ! in_array( 'blog', $types, true ) ) {
		$types[]                    = 'blog';
		$opts['post_types_count'] = $types;
	}

	return $opts;
}

/**
 * Патчит runtime-опции PVC + сохраняет blog в настройках плагина (один раз).
 */
function tolstenko_pvc_enable_blog_post_type() {
	if ( ! function_exists( 'Post_Views_Counter' ) ) {
		return;
	}

	$pvc = Post_Views_Counter();
	if ( ! $pvc || empty( $pvc->options['general'] ) || ! is_array( $pvc->options['general'] ) ) {
		return;
	}

	$types = isset( $pvc->options['general']['post_types_count'] ) && is_array( $pvc->options['general']['post_types_count'] )
		? $pvc->options['general']['post_types_count']
		: array();

	if ( ! in_array( 'blog', $types, true ) ) {
		$types[] = 'blog';
		$pvc->options['general']['post_types_count'] = $types;
	}

	if ( get_option( 'tolstenko_pvc_blog_enabled' ) === '1' ) {
		return;
	}

	// Читаем сырой option без нашего фильтра.
	remove_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );
	$opts = get_option( 'post_views_counter_settings_general', array() );
	add_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );

	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	$saved_types = isset( $opts['post_types_count'] ) && is_array( $opts['post_types_count'] )
		? $opts['post_types_count']
		: array();
	if ( ! in_array( 'blog', $saved_types, true ) ) {
		$saved_types[]            = 'blog';
		$opts['post_types_count'] = $saved_types;
		update_option( 'post_views_counter_settings_general', $opts, false );
	}
	update_option( 'tolstenko_pvc_blog_enabled', '1', false );
}

/**
 * Убираем авто-кнопку лайков из the_content на записи блога.
 */
function tolstenko_disable_blog_auto_views_likes() {
	$is_body = function_exists( 'tolstenko_is_content_body_singular' )
		? tolstenko_is_content_body_singular()
		: is_singular( array( 'blog', 'actions' ) );
	if ( ! $is_body ) {
		return;
	}

	remove_filter( 'the_content', 'wp_ulike_put_posts', 15 );
	remove_filter( 'the_excerpt', 'wp_ulike_put_posts', 15 );
}

/**
 * Скрываем авто-счётчик просмотров на single blog (показываем в stats).
 *
 * @param mixed $display Display flag from Post Views Counter.
 * @return mixed
 */
function tolstenko_hide_blog_post_views_auto_display( $display ) {
	$is_body = function_exists( 'tolstenko_is_content_body_singular' )
		? tolstenko_is_content_body_singular()
		: is_singular( array( 'blog', 'actions' ) );
	if ( $is_body ) {
		return false;
	}

	return $display;
}
