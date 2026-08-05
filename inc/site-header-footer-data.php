<?php
/**
 * Данные шапки и подвала: телефон, соцсети, промо-плашка.
 * Источник — «Настройки сайта → Контактные данные» (+ запасные значения темы).
 *
 * @return array{
 *   phone: string,
 *   phone_href: string,
 *   telegram: string,
 *   whatsapp: string,
 *   vk: string,
 *   rutube: string,
 *   email: string,
 *   footer_html: string,
 *   promo_notice_html: string,
 *   socials_header_1: array<int, array{icon: string, link: string, text: string}>,
 *   socials_header_2: array<int, array{icon: string, link: string, text: string}>,
 *   socials_footer_1: array<int, array{icon: string, link: string, text: string}>,
 *   socials_footer_2: array<int, array{icon: string, link: string, text: string}>
 * }
 */
function tolstenko_get_site_header_footer_data() {
	static $data = null;
	if ( $data !== null ) {
		return $data;
	}

	$data = array(
		'phone'             => '8 (800) 500-71-48',
		'phone_href'        => '88005007148',
		'telegram'          => '',
		'whatsapp'          => '',
		'vk'                => '',
		'rutube'            => '',
		'email'             => 'sale@rpkleon.ru',
		'footer_html'       => '',
		'promo_notice_html' => '',
		'socials_header_1'  => array(),
		'socials_header_2'  => array(),
		'socials_footer_1'  => array(),
		'socials_footer_2'  => array(),
	);

	tolstenko_normalize_phone_href( $data );
	tolstenko_apply_contact_data_overrides( $data );
	tolstenko_apply_contact_socials_to_header_footer( $data );
	tolstenko_fill_legacy_social_groups( $data );

	return $data;
}

/**
 * Соцсети из «Контактных данных» → группы шапки/подвала.
 *
 * @param array<string, mixed> $data Header/footer data by ref.
 */
function tolstenko_apply_contact_socials_to_header_footer( &$data ) {
	if ( ! function_exists( 'tolstenko_get_contact_data' ) ) {
		return;
	}

	$cd   = tolstenko_get_contact_data( true );
	$mono = tolstenko_hf_social_rows_from_contact( $cd['socials'] ?? array() );
	$rgb  = tolstenko_hf_social_rows_from_contact( $cd['socials_rgb'] ?? array() );

	if ( empty( $data['socials_header_1'] ) && ! empty( $mono ) ) {
		$data['socials_header_1'] = $mono;
	}
	if ( empty( $data['socials_header_2'] ) && ! empty( $mono ) ) {
		$data['socials_header_2'] = array_slice( $mono, 0, 2 );
	}
	if ( empty( $data['socials_footer_1'] ) && ! empty( $rgb ) ) {
		$data['socials_footer_1'] = array_slice( $rgb, 0, 2 );
	}
	if ( empty( $data['socials_footer_2'] ) && ! empty( $rgb ) ) {
		$data['socials_footer_2'] = $rgb;
	}
}

/**
 * @param mixed $rows Contact social rows.
 * @return array<int, array{icon: string, link: string, text: string}>
 */
function tolstenko_hf_social_rows_from_contact( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$out = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$icon_raw = isset( $row['icon'] ) ? trim( (string) $row['icon'] ) : '';
		$icon     = function_exists( 'tolstenko_contact_resolve_icon_url' )
			? tolstenko_contact_resolve_icon_url( $icon_raw )
			: $icon_raw;
		$link = isset( $row['link'] ) ? trim( (string) $row['link'] ) : '';
		$text = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
		if ( $icon === '' && $link === '' && $text === '' ) {
			continue;
		}
		$out[] = array(
			'icon' => $icon,
			'link' => $link,
			'text' => $text,
		);
	}

	return $out;
}

/**
 * Приоритет: «Настройки сайта → Контактные данные» для телефона и почты.
 *
 * @param array<string, mixed> $data Header/footer data by ref.
 */
function tolstenko_apply_contact_data_overrides( &$data ) {
	if ( ! defined( 'TOLSTENKO_CONTACT_DATA_OPTION' ) ) {
		return;
	}
	$cd = get_option( TOLSTENKO_CONTACT_DATA_OPTION, array() );
	if ( ! is_array( $cd ) ) {
		return;
	}
	$phone    = isset( $cd['phone'] ) ? trim( (string) $cd['phone'] ) : '';
	$email    = isset( $cd['email'] ) ? trim( (string) $cd['email'] ) : '';
	$whatsapp = isset( $cd['whatsapp'] ) ? trim( (string) $cd['whatsapp'] ) : '';
	$telegram = isset( $cd['telegram'] ) ? trim( (string) $cd['telegram'] ) : '';
	if ( $phone !== '' ) {
		$data['phone'] = $phone;
		if ( function_exists( 'tolstenko_normalize_phone_href' ) ) {
			tolstenko_normalize_phone_href( $data );
		}
	}
	if ( $email !== '' ) {
		$data['email'] = $email;
	}
	if ( $telegram !== '' ) {
		$data['telegram'] = $telegram;
	}
	if ( $whatsapp !== '' ) {
		$data['whatsapp'] = $whatsapp;
	}
}

function tolstenko_fill_legacy_social_groups( &$data, $block_id = 0 ) {
	$theme_uri = get_template_directory_uri();

	$skip_legacy = static function ( $key ) use ( $block_id ) {
		return $block_id > 0 && metadata_exists( 'post', $block_id, '_tolstenko_hf_socials_' . $key );
	};

	if ( empty( $data['socials_header_1'] ) && ! $skip_legacy( 'header_1' ) ) {
		$items = array();
		if ( ! empty( $data['telegram'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/telegram-icon.svg',
				'link' => (string) $data['telegram'],
				'text' => 'Telegram',
			);
		}
		if ( ! empty( $data['vk'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/vk-icon.svg',
				'link' => (string) $data['vk'],
				'text' => 'VK',
			);
		}
		if ( ! empty( $data['rutube'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/rutube-icon.svg',
				'link' => (string) $data['rutube'],
				'text' => 'Rutube',
			);
		}
		$data['socials_header_1'] = $items;
	}

	if ( empty( $data['socials_header_2'] ) && ! $skip_legacy( 'header_2' ) ) {
		$items = array();
		if ( ! empty( $data['whatsapp'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/whatsapp-ion.svg',
				'link' => (string) $data['whatsapp'],
				'text' => 'WhatsApp',
			);
		}
		if ( ! empty( $data['telegram'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/telegram-icon.svg',
				'link' => (string) $data['telegram'],
				'text' => 'Telegram',
			);
		}
		$data['socials_header_2'] = $items;
	}

	if ( empty( $data['socials_footer_1'] ) && ! $skip_legacy( 'footer_1' ) ) {
		$items = array();
		if ( ! empty( $data['whatsapp'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/whatsapp-icon-white.svg',
				'link' => (string) $data['whatsapp'],
				'text' => 'WhatsApp',
			);
		}
		if ( ! empty( $data['telegram'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/telegram-icon-white.svg',
				'link' => (string) $data['telegram'],
				'text' => 'Telegram',
			);
		}
		$data['socials_footer_1'] = $items;
	}

	if ( empty( $data['socials_footer_2'] ) && ! $skip_legacy( 'footer_2' ) ) {
		$items = array();
		if ( ! empty( $data['telegram'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/telegram-icon.svg',
				'link' => (string) $data['telegram'],
				'text' => 'Telegram',
			);
		}
		if ( ! empty( $data['whatsapp'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/whatsapp-ion.svg',
				'link' => (string) $data['whatsapp'],
				'text' => 'WhatsApp',
			);
		}
		if ( ! empty( $data['vk'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/vk-icon.svg',
				'link' => (string) $data['vk'],
				'text' => 'VK',
			);
		}
		if ( ! empty( $data['rutube'] ) ) {
			$items[] = array(
				'icon' => $theme_uri . '/assets/img/rutube-icon.svg',
				'link' => (string) $data['rutube'],
				'text' => 'Rutube',
			);
		}
		$data['socials_footer_2'] = $items;
	}
}

function tolstenko_normalize_phone_href( &$data ) {
	$digits = preg_replace( '/\D/', '', $data['phone'] );
	if ( strlen( $digits ) >= 10 ) {
		if ( $digits[0] === '8' ) {
			$data['phone_href'] = '+7' . substr( $digits, 1 );
		} elseif ( $digits[0] !== '7' ) {
			$data['phone_href'] = '+7' . $digits;
		} else {
			$data['phone_href'] = '+' . $digits;
		}
	} else {
		$data['phone_href'] = $digits;
	}
}
