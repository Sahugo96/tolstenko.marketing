<?php
/**
 * Данные блока «Шапка и подвал»: телефон, соцсети, текст футера.
 * Один блок с типом header-footer — данные подставляются в header.php и footer.php.
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
		'phone'       => '8 (800) 500-71-48',
		'phone_href'  => '88005007148',
		'telegram'    => '',
		'whatsapp'    => '',
		'vk'          => '',
		'rutube'      => '',
		'email'       => 'sale@rpkleon.ru',
		'footer_html'       => '',
		'promo_notice_html' => '',
		'socials_header_1'  => array(),
		'socials_header_2'  => array(),
		'socials_footer_1'  => array(),
		'socials_footer_2'  => array(),
	);
	if ( ! function_exists( 'get_field' ) ) {
		tolstenko_fill_legacy_social_groups( $data );
		tolstenko_normalize_phone_href( $data );
		tolstenko_apply_contact_data_overrides( $data );
		return $data;
	}
	$block_id = 0;
	$blocks   = get_posts(
		array(
			'post_type'      => 'block',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'meta_key'       => 'block_type',
			'meta_value'     => 'header-footer',
		)
	);
	if ( ! empty( $blocks ) ) {
		$block_id = (int) $blocks[0]->ID;
	}
	if ( ! $block_id ) {
		tolstenko_fill_legacy_social_groups( $data );
		tolstenko_normalize_phone_href( $data );
		tolstenko_apply_contact_data_overrides( $data );
		return $data;
	}
	$v = get_field( 'site_phone', $block_id );
	if ( $v !== null && $v !== '' ) {
		$data['phone'] = (string) $v;
	}
	tolstenko_normalize_phone_href( $data );
	foreach ( array( 'telegram' => 'site_social_telegram', 'whatsapp' => 'site_social_whatsapp', 'vk' => 'site_social_vk', 'rutube' => 'site_social_rutube' ) as $key => $name ) {
		$v = get_field( $name, $block_id );
		$data[ $key ] = ( $v !== null && $v !== '' ) ? (string) $v : '';
	}
	$data['socials_header_1'] = tolstenko_get_social_items_from_meta( $block_id, 'header_1' );
	$data['socials_header_2'] = tolstenko_get_social_items_from_meta( $block_id, 'header_2' );
	$data['socials_footer_1'] = tolstenko_get_social_items_from_meta( $block_id, 'footer_1' );
	$data['socials_footer_2'] = tolstenko_get_social_items_from_meta( $block_id, 'footer_2' );
	if ( empty( $data['socials_header_1'] ) && ! metadata_exists( 'post', $block_id, '_tolstenko_hf_socials_header_1' ) ) {
		$data['socials_header_1'] = tolstenko_get_social_items_from_fields( $block_id, 'site_socials_header_1' );
	}
	if ( empty( $data['socials_header_2'] ) && ! metadata_exists( 'post', $block_id, '_tolstenko_hf_socials_header_2' ) ) {
		$data['socials_header_2'] = tolstenko_get_social_items_from_fields( $block_id, 'site_socials_header_2' );
	}
	if ( empty( $data['socials_footer_1'] ) && ! metadata_exists( 'post', $block_id, '_tolstenko_hf_socials_footer_1' ) ) {
		$data['socials_footer_1'] = tolstenko_get_social_items_from_fields( $block_id, 'site_socials_footer_1' );
	}
	if ( empty( $data['socials_footer_2'] ) && ! metadata_exists( 'post', $block_id, '_tolstenko_hf_socials_footer_2' ) ) {
		$data['socials_footer_2'] = tolstenko_get_social_items_from_fields( $block_id, 'site_socials_footer_2' );
	}
	$v = get_field( 'site_email', $block_id );
	$data['email'] = ( $v !== null && $v !== '' ) ? (string) $v : '';
	$v = get_field( 'site_footer_html', $block_id );
	$data['footer_html'] = ( $v !== null && $v !== '' ) ? (string) $v : '';
	$v = get_field( 'site_promo_notice_html', $block_id );
	$data['promo_notice_html'] = ( $v !== null && $v !== '' ) ? (string) $v : '';
	tolstenko_fill_legacy_social_groups( $data, $block_id );
	tolstenko_apply_contact_data_overrides( $data );
	return $data;
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
	$phone = isset( $cd['phone'] ) ? trim( (string) $cd['phone'] ) : '';
	$email = isset( $cd['email'] ) ? trim( (string) $cd['email'] ) : '';
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

function tolstenko_get_social_items_from_meta( $block_id, $key ) {
	$rows = get_post_meta( $block_id, '_tolstenko_hf_socials_' . $key, true );
	if ( ! is_array( $rows ) ) {
		return array();
	}
	$out = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		if ( isset( $row['enabled'] ) && empty( $row['enabled'] ) ) {
			continue;
		}
		$icon_raw = isset( $row['icon'] ) ? trim( (string) $row['icon'] ) : '';
		$link = isset( $row['link'] ) ? trim( (string) $row['link'] ) : '';
		$text = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
		$icon = '';
		if ( $icon_raw !== '' && ctype_digit( $icon_raw ) ) {
			$icon_url = wp_get_attachment_image_url( (int) $icon_raw, 'full' );
			$icon = $icon_url ? (string) $icon_url : '';
		} else {
			$icon = $icon_raw;
		}
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

function tolstenko_get_social_items_from_fields( $block_id, $prefix ) {
	$items = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$icon_raw = get_field( $prefix . '_' . $i . '_icon', $block_id );
		$link_raw = get_field( $prefix . '_' . $i . '_link', $block_id );
		$text_raw = get_field( $prefix . '_' . $i . '_text', $block_id );

		$link = ( $link_raw !== null && $link_raw !== '' ) ? trim( (string) $link_raw ) : '';
		$text = ( $text_raw !== null && $text_raw !== '' ) ? trim( (string) $text_raw ) : '';
		$icon = '';
		if ( is_numeric( $icon_raw ) ) {
			$icon_url = wp_get_attachment_image_url( (int) $icon_raw, 'full' );
			$icon = $icon_url ? (string) $icon_url : '';
		} elseif ( is_string( $icon_raw ) && trim( $icon_raw ) !== '' ) {
			$icon = trim( $icon_raw );
		}

		if ( $icon === '' && $link === '' && $text === '' ) {
			continue;
		}
		$items[] = array(
			'icon' => $icon,
			'link' => $link,
			'text' => $text,
		);
	}
	return $items;
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
