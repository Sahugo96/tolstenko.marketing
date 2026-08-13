<?php
/**
 * Заглушка изображения для карточек (статьи, услуги, кейсы).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ID вложения-заглушки (assets/img/default-card.jpg). Один раз кладётся в медиатеку.
 *
 * @return int Attachment ID or 0.
 */
function tolstenko_get_card_placeholder_attachment_id() {
	$option_key = 'tolstenko_default_card_placeholder_id';
	$cached     = (int) get_option( $option_key, 0 );
	if ( $cached && wp_attachment_is_image( $cached ) ) {
		return $cached;
	}

	$path = trailingslashit( get_template_directory() ) . 'assets/img/default-card.jpg';
	if ( ! is_readable( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( 'default-card.jpg' );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		return 0;
	}

	$file_array = array(
		'name'     => 'default-card.jpg',
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, __( 'Заглушка карточки по умолчанию', 'tolstenko-theme' ) );
	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return 0;
	}

	update_option( $option_key, (int) $attachment_id, false );
	return (int) $attachment_id;
}

/**
 * URL заглушки для карточки.
 *
 * @param string $size Image size.
 * @return string
 */
function tolstenko_get_card_placeholder_image_url( $size = 'large' ) {
	$attachment_id = tolstenko_get_card_placeholder_attachment_id();
	if ( $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, $size );
		if ( is_string( $url ) && $url !== '' ) {
			return $url;
		}
	}

	return trailingslashit( get_template_directory_uri() ) . 'assets/img/default-card.jpg';
}

/**
 * HTML img / wp_get_attachment_image для заглушки.
 *
 * @param string               $size Image size.
 * @param string               $alt Alt text.
 * @param array<string, mixed> $attr Extra attributes.
 * @return string
 */
function tolstenko_get_card_placeholder_image_html( $size = 'large', $alt = '', $attr = array() ) {
	$attachment_id = tolstenko_get_card_placeholder_attachment_id();
	$attr          = is_array( $attr ) ? $attr : array();

	if ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	if ( $alt !== '' ) {
		$attr['alt'] = $alt;
	} elseif ( ! isset( $attr['alt'] ) ) {
		$attr['alt'] = '';
	}

	if ( $attachment_id ) {
		return (string) wp_get_attachment_image( $attachment_id, $size, false, $attr );
	}

	$url = tolstenko_get_card_placeholder_image_url( $size );
	return sprintf(
		'<img src="%1$s" alt="%2$s"%3$s>',
		esc_url( $url ),
		esc_attr( (string) ( $attr['alt'] ?? '' ) ),
		isset( $attr['loading'] ) ? ' loading="' . esc_attr( (string) $attr['loading'] ) . '"' : ''
	);
}
