<?php
/**
 * Выпадающая панель «Услуги» (marketing-разметка, данные из WP-меню / таксономии).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tolstenko_get_header_services_columns_html' ) ) {
	return;
}

$html = tolstenko_get_header_services_columns_html();
if ( $html === '' ) {
	return;
}

echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built with esc_* in helpers.
