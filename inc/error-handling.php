<?php
/**
 * Общие хелперы обработки ошибок: логирование и проверяемое сохранение опций.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Записать ошибку темы в лог (только при включённом WP_DEBUG).
 *
 * @param string $context Контекст (функция/подсистема).
 * @param string $message Сообщение.
 * @param mixed  $data    Доп. данные (WP_Error, код ответа и т.п.).
 */
function tolstenko_log_error( $context, $message, $data = null ) {
	if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
		return;
	}

	if ( is_wp_error( $data ) ) {
		$data = $data->get_error_code() . ': ' . $data->get_error_message();
	} elseif ( is_array( $data ) || is_object( $data ) ) {
		$data = wp_json_encode( $data );
	}

	$line = sprintf( '[tolstenko-theme] %s: %s', (string) $context, (string) $message );
	if ( $data !== null && $data !== '' ) {
		$line .= ' | ' . (string) $data;
	}

	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- отладочное логирование темы.
	error_log( $line );
}

/**
 * Сохранить опцию и убедиться, что значение действительно записано.
 *
 * update_option() возвращает false и когда запись не удалась, и когда значение
 * не изменилось, поэтому результат дополнительно сверяется с БД.
 *
 * @param string $option   Имя опции.
 * @param mixed  $value    Значение.
 * @param bool   $autoload Автозагрузка.
 * @return bool true, если в БД лежит ожидаемое значение.
 */
function tolstenko_update_option_checked( $option, $value, $autoload = false ) {
	if ( update_option( $option, $value, $autoload ) ) {
		return true;
	}

	$stored = get_option( $option, null );
	if ( $stored == $value ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- значения из БД теряют типы.
		return true;
	}

	tolstenko_log_error( 'tolstenko_update_option_checked', 'Не удалось сохранить опцию', $option );

	return false;
}

/**
 * Вывести админ-уведомление.
 *
 * @param string $message Текст.
 * @param string $type    error|success|warning|info.
 */
function tolstenko_admin_notice( $message, $type = 'error' ) {
	$allowed = array( 'error', 'success', 'warning', 'info' );
	if ( ! in_array( $type, $allowed, true ) ) {
		$type = 'error';
	}

	printf(
		'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
		esc_attr( $type ),
		esc_html( $message )
	);
}

/**
 * Уведомление о неудачном сохранении (проверка nonce не прошла).
 */
function tolstenko_admin_notice_nonce_failed() {
	tolstenko_admin_notice(
		__( 'Не удалось сохранить: истёк срок действия формы. Обновите страницу и попробуйте снова.', 'tolstenko-theme' ),
		'error'
	);
}

/**
 * Уведомление о неудачной записи в БД.
 */
function tolstenko_admin_notice_save_failed() {
	tolstenko_admin_notice(
		__( 'Не удалось сохранить настройки: ошибка записи в базу данных.', 'tolstenko-theme' ),
		'error'
	);
}
