<?php
/**
 * Общие помощники для шаблонов блоков (template-parts/blocks/*.php).
 *
 * Убирают повторяющуюся «шапку» шаблонов: чтение атрибутов Gutenberg,
 * дефолтов блока, нормализацию тега заголовка и данные шапки/подвала.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Атрибуты текущего блока из query var (всегда массив).
 *
 * @return array
 */
function tolstenko_block_attributes() {
	$attrs = get_query_var( 'tolstenko_block_attributes', array() );
	return is_array( $attrs ) ? $attrs : array();
}

/**
 * Дефолты блока темы (всегда массив).
 *
 * @param string $block Ключ блока.
 * @return array
 */
function tolstenko_block_defaults( $block ) {
	$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( $block ) : array();
	return is_array( $defaults ) ? $defaults : array();
}

/**
 * Дефолты блока тела статьи (всегда массив).
 *
 * @param string $block Ключ блока.
 * @return array
 */
function tolstenko_blog_content_block_defaults( $block ) {
	$defaults = function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( $block ) : array();
	return is_array( $defaults ) ? $defaults : array();
}

/**
 * Нормализованный тег заголовка из атрибутов блока.
 *
 * @param array  $attrs   Атрибуты блока.
 * @param string $key     Ключ атрибута с тегом.
 * @param string $default Тег по умолчанию.
 * @return string
 */
function tolstenko_block_heading_tag( $attrs, $key, $default = 'h2' ) {
	$tag = is_array( $attrs ) && isset( $attrs[ $key ] ) ? $attrs[ $key ] : $default;
	return function_exists( 'tolstenko_normalize_heading_tag' )
		? tolstenko_normalize_heading_tag( $tag, $default )
		: $default;
}

/**
 * Данные шапки/подвала сайта (всегда массив).
 *
 * @return array
 */
function tolstenko_site_data() {
	$data = tolstenko_site_data();
	return is_array( $data ) ? $data : array();
}
