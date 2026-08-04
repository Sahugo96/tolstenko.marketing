<?php
/**
 * Настройки сайта → Контактные данные.
 * Телефон, почта и два списка соцсетей (моно / цветные).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOLSTENKO_CONTACT_DATA_OPTION', 'tolstenko_contact_data' );

/**
 * Схема / пустые значения.
 *
 * @return array{
 *   phone: string,
 *   email: string,
 *   whatsapp: string,
 *   telegram: string,
 *   footer_name: string,
 *   footer_inn: string,
 *   footer_ogrn: string,
 *   footer_address: string,
 *   footer_copyright: string,
 *   footer_links: array<int, array{title: string, url: string}>,
 *   socials: array<int, array{icon: string, link: string, text: string, enabled: int}>,
 *   socials_rgb: array<int, array{icon: string, link: string, text: string, enabled: int}>
 * }
 */
function tolstenko_contact_data_defaults() {
	return array(
		'phone'            => '',
		'email'            => '',
		'whatsapp'         => '',
		'telegram'         => '',
		'footer_name'      => '',
		'footer_inn'       => '',
		'footer_ogrn'      => '',
		'footer_address'   => '',
		'footer_copyright' => '',
		'footer_links'     => array(
			array( 'title' => '', 'url' => '' ),
			array( 'title' => '', 'url' => '' ),
			array( 'title' => '', 'url' => '' ),
		),
		'socials'          => array(),
		'socials_rgb'      => array(),
	);
}

/**
 * @param mixed $raw Raw links.
 * @return array<int, array{title: string, url: string}>
 */
function tolstenko_sanitize_contact_footer_links( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	for ( $i = 0; $i < 3; $i++ ) {
		$row   = isset( $raw[ $i ] ) && is_array( $raw[ $i ] ) ? $raw[ $i ] : array();
		$title = isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '';
		$url   = isset( $row['url'] ) ? esc_url_raw( trim( (string) $row['url'] ) ) : '';
		$out[] = array(
			'title' => $title,
			'url'   => $url,
		);
	}
	return $out;
}

/**
 * @param mixed $rows Raw rows.
 * @return array<int, array{icon: string, link: string, text: string, enabled: int}>
 */
function tolstenko_sanitize_contact_social_rows( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}
	$out = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$icon    = isset( $row['icon'] ) ? trim( (string) $row['icon'] ) : '';
		$link    = isset( $row['link'] ) ? esc_url_raw( trim( (string) $row['link'] ) ) : '';
		$text    = isset( $row['text'] ) ? sanitize_text_field( (string) $row['text'] ) : '';
		$enabled = ! isset( $row['enabled'] ) || ! empty( $row['enabled'] ) ? 1 : 0;
		if ( $icon === '' && $link === '' && $text === '' ) {
			continue;
		}
		$out[] = array(
			'icon'    => $icon,
			'link'    => $link,
			'text'    => $text,
			'enabled' => $enabled,
		);
	}
	return $out;
}

/**
 * @param mixed $raw Raw option.
 * @return array<string, mixed>
 */
function tolstenko_sanitize_contact_data( $raw ) {
	$base = tolstenko_contact_data_defaults();
	if ( ! is_array( $raw ) ) {
		return $base;
	}
	$base['phone']    = sanitize_text_field( (string) ( $raw['phone'] ?? '' ) );
	$base['email']    = sanitize_email( (string) ( $raw['email'] ?? '' ) );
	if ( $base['email'] === '' && ! empty( $raw['email'] ) ) {
		// sanitize_email может обнулить нестандартный ввод — сохраняем как текст.
		$base['email'] = sanitize_text_field( (string) $raw['email'] );
	}
	$base['whatsapp']         = esc_url_raw( trim( (string) ( $raw['whatsapp'] ?? '' ) ) );
	$base['telegram']         = esc_url_raw( trim( (string) ( $raw['telegram'] ?? '' ) ) );
	$base['footer_name']      = sanitize_text_field( (string) ( $raw['footer_name'] ?? '' ) );
	$base['footer_inn']       = sanitize_text_field( (string) ( $raw['footer_inn'] ?? '' ) );
	$base['footer_ogrn']      = sanitize_text_field( (string) ( $raw['footer_ogrn'] ?? '' ) );
	$base['footer_address']   = sanitize_textarea_field( (string) ( $raw['footer_address'] ?? '' ) );
	$base['footer_copyright'] = sanitize_text_field( (string) ( $raw['footer_copyright'] ?? '' ) );
	$base['footer_links']     = tolstenko_sanitize_contact_footer_links( $raw['footer_links'] ?? array() );
	$base['socials']          = tolstenko_sanitize_contact_social_rows( $raw['socials'] ?? array() );
	$base['socials_rgb']      = tolstenko_sanitize_contact_social_rows( $raw['socials_rgb'] ?? array() );
	return $base;
}

/**
 * Контактные данные сайта.
 *
 * @param bool $only_enabled Фильтровать выключенные элементы списков.
 * @return array{
 *   phone: string,
 *   phone_href: string,
 *   email: string,
 *   socials: array<int, array{icon: string, link: string, text: string, enabled?: int}>,
 *   socials_rgb: array<int, array{icon: string, link: string, text: string, enabled?: int}>
 * }
 */
function tolstenko_get_contact_data( $only_enabled = true ) {
	static $cache = null;
	if ( $cache !== null && $only_enabled ) {
		return $cache;
	}

	$saved = get_option( TOLSTENKO_CONTACT_DATA_OPTION, array() );
	$data  = tolstenko_sanitize_contact_data( $saved );

	$digits = preg_replace( '/\D/', '', $data['phone'] );
	$phone_href = '';
	if ( is_string( $digits ) && strlen( $digits ) >= 10 ) {
		if ( $digits[0] === '8' ) {
			$phone_href = '+7' . substr( $digits, 1 );
		} elseif ( $digits[0] !== '7' ) {
			$phone_href = '+7' . $digits;
		} else {
			$phone_href = '+' . $digits;
		}
	} else {
		$phone_href = is_string( $digits ) ? $digits : '';
	}
	$data['phone_href'] = $phone_href;

	if ( $only_enabled ) {
		foreach ( array( 'socials', 'socials_rgb' ) as $key ) {
			$filtered = array();
			foreach ( $data[ $key ] as $row ) {
				if ( empty( $row['enabled'] ) ) {
					continue;
				}
				$filtered[] = array(
					'icon' => (string) ( $row['icon'] ?? '' ),
					'link' => (string) ( $row['link'] ?? '' ),
					'text' => (string) ( $row['text'] ?? '' ),
				);
			}
			$data[ $key ] = $filtered;
		}
		$cache = $data;
	}

	return $data;
}

/**
 * URL иконки из ID вложения или готового URL.
 *
 * @param string $icon_raw ID или URL.
 * @param string $size     Image size.
 * @return string
 */
function tolstenko_contact_resolve_icon_url( $icon_raw, $size = 'full' ) {
	$icon_raw = trim( (string) $icon_raw );
	if ( $icon_raw === '' ) {
		return '';
	}
	if ( ctype_digit( $icon_raw ) ) {
		$url = wp_get_attachment_image_url( (int) $icon_raw, $size );
		return $url ? (string) $url : '';
	}
	return $icon_raw;
}

/**
 * Локальный путь к SVG для inline-вывода.
 *
 * @param string $icon_raw ID или URL.
 * @return string
 */
function tolstenko_contact_resolve_svg_path( $icon_raw ) {
	$icon_raw = trim( (string) $icon_raw );
	if ( $icon_raw === '' ) {
		return '';
	}
	if ( ctype_digit( $icon_raw ) ) {
		return tolstenko_validate_inline_svg_path( (string) get_attached_file( (int) $icon_raw ) );
	}
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();
	if ( strpos( $icon_raw, $theme_uri ) === 0 ) {
		$rel = wp_parse_url( substr( $icon_raw, strlen( $theme_uri ) ), PHP_URL_PATH );
		return tolstenko_validate_inline_svg_path( $theme_dir . '/' . ltrim( (string) $rel, '/' ) );
	}
	return '';
}

add_action( 'admin_menu', 'tolstenko_register_contact_data_admin_page', 11 );
add_action( 'admin_enqueue_scripts', 'tolstenko_contact_data_admin_assets' );
add_action( 'admin_post_tolstenko_save_contact_data', 'tolstenko_handle_save_contact_data' );

function tolstenko_register_contact_data_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Контактные данные', 'tolstenko-theme' ),
		__( 'Контактные данные', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-contact-data',
		'tolstenko_render_contact_data_admin_page'
	);
}

/**
 * @param string $hook Hook.
 */
function tolstenko_contact_data_admin_assets( $hook ) {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $page !== 'tolstenko-contact-data' ) {
		return;
	}
	wp_enqueue_media();
}

function tolstenko_handle_save_contact_data() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'tolstenko-theme' ) );
	}
	check_admin_referer( 'tolstenko_contact_data_save', 'tolstenko_contact_data_nonce' );

	$raw = isset( $_POST['tolstenko_contact_data'] ) && is_array( $_POST['tolstenko_contact_data'] )
		? wp_unslash( $_POST['tolstenko_contact_data'] )
		: array();

	update_option( TOLSTENKO_CONTACT_DATA_OPTION, tolstenko_sanitize_contact_data( $raw ), false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'tolstenko-contact-data',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

/**
 * @param string $group  socials|socials_rgb.
 * @param string $index  Row index.
 * @param string $icon   Icon.
 * @param string $link   Link.
 * @param string $text   Text.
 * @param bool   $enabled Enabled.
 */
function tolstenko_render_contact_social_row( $group, $index, $icon, $link, $text, $enabled = true ) {
	$name    = 'tolstenko_contact_data[' . $group . '][' . $index . ']';
	$preview = tolstenko_contact_resolve_icon_url( $icon, 'thumbnail' );
	$row_class = 'tolstenko-cd-row' . ( $enabled ? '' : ' is-disabled' );
	$icon_value_label = $icon !== ''
		? ( ctype_digit( $icon ) ? 'ID: ' . $icon : __( 'URL задан', 'tolstenko-theme' ) )
		: __( 'Не выбрана', 'tolstenko-theme' );
	?>
	<div class="<?php echo esc_attr( $row_class ); ?>">
		<div class="field tolstenko-cd-field-enabled">
			<label class="tolstenko-cd-enabled">
				<input type="hidden" name="<?php echo esc_attr( $name . '[enabled]' ); ?>" value="0">
				<input type="checkbox" data-cd-enabled name="<?php echo esc_attr( $name . '[enabled]' ); ?>" value="1" <?php checked( $enabled, true ); ?>>
				<?php esc_html_e( 'Показать', 'tolstenko-theme' ); ?>
			</label>
		</div>
		<div class="field tolstenko-cd-field-link">
			<label><?php esc_html_e( 'Ссылка', 'tolstenko-theme' ); ?></label>
			<input type="url" name="<?php echo esc_attr( $name . '[link]' ); ?>" value="<?php echo esc_attr( $link ); ?>" placeholder="https://...">
		</div>
		<div class="field tolstenko-cd-field-text">
			<label><?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $name . '[text]' ); ?>" value="<?php echo esc_attr( $text ); ?>" placeholder="<?php echo esc_attr__( 'Например Telegram', 'tolstenko-theme' ); ?>">
		</div>
		<div class="field tolstenko-cd-field-actions field-actions">
			<label>&nbsp;</label>
			<div class="tolstenko-cd-row-btns">
				<button type="button" class="button button-small" data-cd-up>↑</button>
				<button type="button" class="button button-small" data-cd-down>↓</button>
				<button type="button" class="button button-small" data-cd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
			</div>
		</div>
		<div class="field tolstenko-cd-field-icon">
			<label><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></label>
			<div class="tolstenko-cd-icon-controls">
				<img class="tolstenko-cd-icon-preview" data-cd-icon-preview src="<?php echo esc_url( $preview ); ?>" alt="">
				<input type="hidden" data-cd-icon-input name="<?php echo esc_attr( $name . '[icon]' ); ?>" value="<?php echo esc_attr( $icon ); ?>">
				<span class="tolstenko-cd-icon-value" data-cd-icon-value><?php echo esc_html( $icon_value_label ); ?></span>
				<button type="button" class="button button-small" data-cd-pick-icon><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
				<button type="button" class="button button-small" data-cd-set-url><?php esc_html_e( 'Вставить URL', 'tolstenko-theme' ); ?></button>
				<button type="button" class="button button-small" data-cd-clear-icon><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
			</div>
		</div>
	</div>
	<?php
}

/**
 * @param string                                                                 $group socials|socials_rgb.
 * @param string                                                                 $title Title.
 * @param string                                                                 $hint  Hint.
 * @param array<int, array{icon?: string, link?: string, text?: string, enabled?: int}> $rows Rows.
 */
function tolstenko_render_contact_social_group( $group, $title, $hint, $rows ) {
	if ( ! is_array( $rows ) ) {
		$rows = array();
	}
	?>
	<div class="tolstenko-cd-group" data-cd-group>
		<h2 class="tolstenko-cd-group-title"><?php echo esc_html( $title ); ?></h2>
		<?php if ( $hint !== '' ) : ?>
			<p class="description"><?php echo esc_html( $hint ); ?></p>
		<?php endif; ?>
		<div class="tolstenko-cd-list" data-cd-list>
			<?php
			foreach ( $rows as $i => $row ) {
				$icon    = isset( $row['icon'] ) ? (string) $row['icon'] : '';
				$link    = isset( $row['link'] ) ? (string) $row['link'] : '';
				$text    = isset( $row['text'] ) ? (string) $row['text'] : '';
				$enabled = ! isset( $row['enabled'] ) || ! empty( $row['enabled'] );
				tolstenko_render_contact_social_row( $group, (string) $i, $icon, $link, $text, $enabled );
			}
			?>
		</div>
		<p class="tolstenko-cd-empty"><?php esc_html_e( 'Пока нет элементов.', 'tolstenko-theme' ); ?></p>
		<p class="tolstenko-cd-actions">
			<button type="button" class="button" data-cd-add><?php esc_html_e( 'Добавить элемент', 'tolstenko-theme' ); ?></button>
		</p>
		<template>
			<?php tolstenko_render_contact_social_row( $group, '__INDEX__', '', '', '', true ); ?>
		</template>
	</div>
	<?php
}

function tolstenko_render_contact_data_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$saved = get_option( TOLSTENKO_CONTACT_DATA_OPTION, null );
	$data  = is_array( $saved )
		? tolstenko_sanitize_contact_data( $saved )
		: tolstenko_contact_data_defaults();

	// Первичный заход: подставить телефон/почту из шапки для удобства.
	if ( ! is_array( $saved ) && function_exists( 'tolstenko_get_site_header_footer_data' ) ) {
		$hf = tolstenko_get_site_header_footer_data();
		if ( $data['phone'] === '' && ! empty( $hf['phone'] ) ) {
			$data['phone'] = (string) $hf['phone'];
		}
		if ( $data['email'] === '' && ! empty( $hf['email'] ) ) {
			$data['email'] = (string) $hf['email'];
		}
		if ( empty( $data['socials'] ) && ! empty( $hf['socials_header_1'] ) && is_array( $hf['socials_header_1'] ) ) {
			$data['socials'] = tolstenko_sanitize_contact_social_rows( $hf['socials_header_1'] );
		}
		if ( empty( $data['socials_rgb'] ) && ! empty( $hf['socials_footer_2'] ) && is_array( $hf['socials_footer_2'] ) ) {
			$data['socials_rgb'] = tolstenko_sanitize_contact_social_rows( $hf['socials_footer_2'] );
		}
	}

	$updated = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap tolstenko-cd">
		<h1><?php esc_html_e( 'Контактные данные', 'tolstenko-theme' ); ?></h1>
		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Сохранено.', 'tolstenko-theme' ); ?></p></div>
		<?php endif; ?>
		<p class="description">
			<?php esc_html_e( 'Телефон, почта, реквизиты и низ футера. Меню «Услуги» и «О нас» — в Внешний вид → Меню (области footer_menu_1 / footer_menu_2).', 'tolstenko-theme' ); ?>
		</p>

		<style>
			.tolstenko-cd .tolstenko-cd-card{max-width:920px;background:#fff;border:1px solid #dcdcde;padding:16px 18px;margin:16px 0;border-radius:4px}
			.tolstenko-cd .row{margin:12px 0}
			.tolstenko-cd .row label{display:block;font-weight:600;margin:0 0 6px}
			.tolstenko-cd .row input[type="text"],
			.tolstenko-cd .row input[type="email"],
			.tolstenko-cd .row input[type="url"],
			.tolstenko-cd .row input[type="tel"],
			.tolstenko-cd .row textarea{width:100%;max-width:640px}
			.tolstenko-cd .row textarea{min-height:72px}
			.tolstenko-cd .tolstenko-cd-links{display:flex;flex-direction:column;gap:10px;max-width:640px}
			.tolstenko-cd .tolstenko-cd-link-row{display:grid;grid-template-columns:1fr 1.4fr;gap:10px}
			.tolstenko-cd-group{border:1px solid #dcdcde;background:#fff;padding:14px;margin:18px 0;border-radius:4px;max-width:920px}
			.tolstenko-cd-group-title{margin:0 0 8px;font-size:15px}
			.tolstenko-cd-list{display:flex;flex-direction:column;gap:8px}
			.tolstenko-cd-row{border:1px solid #e2e4e7;background:#f9f9f9;padding:10px 12px;display:grid;grid-template-columns:92px minmax(0,1fr) minmax(0,1fr) auto;grid-template-areas:"enabled link text actions" "icon icon icon icon";gap:10px 12px;align-items:end}
			.tolstenko-cd-row.is-disabled{opacity:.55}
			.tolstenko-cd-field-enabled{grid-area:enabled;align-self:center}
			.tolstenko-cd-field-icon{grid-area:icon}
			.tolstenko-cd-field-link{grid-area:link}
			.tolstenko-cd-field-text{grid-area:text}
			.tolstenko-cd-field-actions{grid-area:actions}
			.tolstenko-cd-enabled{display:flex;align-items:center;gap:6px;margin:0;white-space:nowrap;font-size:12px;color:#50575e;font-weight:400;cursor:pointer}
			.tolstenko-cd-enabled input{margin:0}
			.tolstenko-cd-icon-controls{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
			.tolstenko-cd-icon-preview{width:28px;height:28px;flex:0 0 28px;border:1px solid #dcdcde;background:#fff;object-fit:contain}
			.tolstenko-cd-icon-value{flex:0 1 auto;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#666}
			.tolstenko-cd-row .field{margin:0;min-width:0}
			.tolstenko-cd-row .field>label{display:block;font-size:12px;color:#50575e;margin:0 0 4px;font-weight:600}
			.tolstenko-cd-row .field input[type="url"],
			.tolstenko-cd-row .field input[type="text"]{width:100%;max-width:100%;box-sizing:border-box}
			.tolstenko-cd-row-btns{display:flex;flex-wrap:wrap;gap:6px}
			.tolstenko-cd-actions{display:flex;gap:8px;align-items:center}
			.tolstenko-cd-empty{color:#777;font-style:italic}
			.tolstenko-cd .submit{margin-top:8px}
			@media (max-width:960px){
				.tolstenko-cd-row{grid-template-columns:1fr auto;grid-template-areas:"enabled actions" "link link" "text text" "icon icon"}
			}
		</style>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tolstenko_save_contact_data">
			<?php wp_nonce_field( 'tolstenko_contact_data_save', 'tolstenko_contact_data_nonce' ); ?>

			<div class="tolstenko-cd-card">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Контакты', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label for="tolstenko-cd-phone"><?php esc_html_e( 'Телефон', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-phone" name="tolstenko_contact_data[phone]" value="<?php echo esc_attr( $data['phone'] ); ?>" placeholder="8 (800) 500-71-48">
				</div>
				<div class="row">
					<label for="tolstenko-cd-email"><?php esc_html_e( 'Почта', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-email" name="tolstenko_contact_data[email]" value="<?php echo esc_attr( $data['email'] ); ?>" placeholder="sale@example.ru">
				</div>
				<div class="row">
					<label for="tolstenko-cd-telegram"><?php esc_html_e( 'Telegram (кнопка «Написать ТГ» в футере)', 'tolstenko-theme' ); ?></label>
					<input type="url" id="tolstenko-cd-telegram" name="tolstenko_contact_data[telegram]" value="<?php echo esc_attr( $data['telegram'] ); ?>" placeholder="https://t.me/...">
				</div>
				<div class="row">
					<label for="tolstenko-cd-whatsapp"><?php esc_html_e( 'WhatsApp (для блоков на сайте)', 'tolstenko-theme' ); ?></label>
					<input type="url" id="tolstenko-cd-whatsapp" name="tolstenko_contact_data[whatsapp]" value="<?php echo esc_attr( $data['whatsapp'] ); ?>" placeholder="https://wa.me/79...">
				</div>
			</div>

			<div class="tolstenko-cd-card">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Футер — левая колонка', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label for="tolstenko-cd-footer-name"><?php esc_html_e( 'Название компании', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-footer-name" name="tolstenko_contact_data[footer_name]" value="<?php echo esc_attr( $data['footer_name'] ); ?>" placeholder='ООО "Компания"'>
				</div>
				<div class="row">
					<label for="tolstenko-cd-footer-inn"><?php esc_html_e( 'ИНН', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-footer-inn" name="tolstenko_contact_data[footer_inn]" value="<?php echo esc_attr( $data['footer_inn'] ); ?>" placeholder="ИНН 0000000000">
				</div>
				<div class="row">
					<label for="tolstenko-cd-footer-ogrn"><?php esc_html_e( 'ОГРН', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-footer-ogrn" name="tolstenko_contact_data[footer_ogrn]" value="<?php echo esc_attr( $data['footer_ogrn'] ); ?>" placeholder="ОГРН 0000000000000">
				</div>
				<div class="row">
					<label for="tolstenko-cd-footer-address"><?php esc_html_e( 'Адрес', 'tolstenko-theme' ); ?></label>
					<textarea id="tolstenko-cd-footer-address" name="tolstenko_contact_data[footer_address]" rows="3"><?php echo esc_textarea( $data['footer_address'] ); ?></textarea>
				</div>
			</div>

			<div class="tolstenko-cd-card">
				<h2 style="margin-top:0;"><?php esc_html_e( 'Футер — нижняя полоса', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label for="tolstenko-cd-footer-copyright"><?php esc_html_e( 'Копирайт', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cd-footer-copyright" name="tolstenko_contact_data[footer_copyright]" value="<?php echo esc_attr( $data['footer_copyright'] ); ?>" placeholder="© 2026 Компания">
				</div>
				<div class="row">
					<label><?php esc_html_e( 'Ссылки (до 3)', 'tolstenko-theme' ); ?></label>
					<div class="tolstenko-cd-links">
						<?php
						$links = is_array( $data['footer_links'] ) ? $data['footer_links'] : array();
						for ( $i = 0; $i < 3; $i++ ) :
							$link = isset( $links[ $i ] ) && is_array( $links[ $i ] ) ? $links[ $i ] : array( 'title' => '', 'url' => '' );
							?>
							<div class="tolstenko-cd-link-row">
								<input type="text" name="tolstenko_contact_data[footer_links][<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( (string) ( $link['title'] ?? '' ) ); ?>" placeholder="<?php echo esc_attr( sprintf( __( 'Название ссылки %d', 'tolstenko-theme' ), $i + 1 ) ); ?>">
								<input type="url" name="tolstenko_contact_data[footer_links][<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( (string) ( $link['url'] ?? '' ) ); ?>" placeholder="https://">
							</div>
						<?php endfor; ?>
					</div>
				</div>
			</div>

			<?php
			tolstenko_render_contact_social_group(
				'socials',
				__( 'Социальные сети', 'tolstenko-theme' ),
				__( 'Монохромные / SVG. Шапка (иконки), футер («Соц.сети»), кнопка рядом с телефоном в футере берёт первые 2.', 'tolstenko-theme' ),
				$data['socials']
			);
			tolstenko_render_contact_social_group(
				'socials_rgb',
				__( 'Социальные сети (цветные)', 'tolstenko-theme' ),
				__( 'Цветные иконки для сайдбаров. Модуль: modules/socials/socials-rgb.php', 'tolstenko-theme' ),
				$data['socials_rgb']
			);
			?>

			<p class="submit">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить', 'tolstenko-theme' ); ?></button>
			</p>
		</form>
	</div>
	<script>
	(function(){
		function updateEmpty(list){
			var empty = list.parentElement.querySelector('.tolstenko-cd-empty');
			if (!empty) return;
			empty.style.display = list.children.length ? 'none' : '';
		}
		function init(){
			var wrap = document.querySelector('.tolstenko-cd');
			if (!wrap || wrap.dataset.init === '1') return;
			wrap.dataset.init = '1';
			wrap.querySelectorAll('[data-cd-list]').forEach(updateEmpty);
			wrap.addEventListener('click', function(e){
				var addBtn = e.target.closest('[data-cd-add]');
				if (addBtn) {
					var group = addBtn.closest('[data-cd-group]');
					if (!group) return;
					var list = group.querySelector('[data-cd-list]');
					var tpl = group.querySelector('template');
					if (!list || !tpl) return;
					var idx = Date.now().toString() + Math.floor(Math.random() * 1000).toString();
					list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, idx));
					updateEmpty(list);
					return;
				}
				var enabledToggle = e.target.closest('[data-cd-enabled]');
				if (enabledToggle) {
					var erow = enabledToggle.closest('.tolstenko-cd-row');
					if (erow) erow.classList.toggle('is-disabled', !enabledToggle.checked);
					return;
				}
				var removeBtn = e.target.closest('[data-cd-remove]');
				if (removeBtn) {
					var row = removeBtn.closest('.tolstenko-cd-row');
					var list = removeBtn.closest('[data-cd-group]') && removeBtn.closest('[data-cd-group]').querySelector('[data-cd-list]');
					if (row) row.remove();
					if (list) updateEmpty(list);
					return;
				}
				var up = e.target.closest('[data-cd-up]');
				if (up) {
					var urow = up.closest('.tolstenko-cd-row');
					if (urow && urow.previousElementSibling) {
						urow.parentNode.insertBefore(urow, urow.previousElementSibling);
					}
					return;
				}
				var down = e.target.closest('[data-cd-down]');
				if (down) {
					var drow = down.closest('.tolstenko-cd-row');
					if (drow && drow.nextElementSibling) {
						drow.parentNode.insertBefore(drow.nextElementSibling, drow);
					}
					return;
				}
				var pick = e.target.closest('[data-cd-pick-icon]');
				if (pick) {
					var prow = pick.closest('.tolstenko-cd-row');
					if (!prow || !window.wp || !wp.media) return;
					var input = prow.querySelector('[data-cd-icon-input]');
					var preview = prow.querySelector('[data-cd-icon-preview]');
					var valueText = prow.querySelector('[data-cd-icon-value]');
					var frame = wp.media({ title: 'Выбор иконки', multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						if (!att) return;
						if (input) input.value = String(att.id || '');
						if (preview && att.url) preview.src = att.url;
						if (valueText) valueText.textContent = input && input.value ? ('ID: ' + input.value) : 'Не выбрана';
					});
					frame.open();
					return;
				}
				var fromUrl = e.target.closest('[data-cd-set-url]');
				if (fromUrl) {
					var frow = fromUrl.closest('.tolstenko-cd-row');
					if (!frow) return;
					var finput = frow.querySelector('[data-cd-icon-input]');
					var fpreview = frow.querySelector('[data-cd-icon-preview]');
					var fvalue = frow.querySelector('[data-cd-icon-value]');
					var currentVal = finput && finput.value ? finput.value : '';
					var url = window.prompt('Вставьте URL изображения', currentVal);
					if (url === null) return;
					var clean = String(url).trim();
					if (finput) finput.value = clean;
					if (fpreview) fpreview.src = clean;
					if (fvalue) fvalue.textContent = clean ? 'URL задан' : 'Не выбрана';
					return;
				}
				var clear = e.target.closest('[data-cd-clear-icon]');
				if (clear) {
					var crow = clear.closest('.tolstenko-cd-row');
					if (!crow) return;
					var cinput = crow.querySelector('[data-cd-icon-input]');
					var cpreview = crow.querySelector('[data-cd-icon-preview]');
					var cvalue = crow.querySelector('[data-cd-icon-value]');
					if (cinput) cinput.value = '';
					if (cpreview) cpreview.src = '';
					if (cvalue) cvalue.textContent = 'Не выбрана';
				}
			});
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	})();
	</script>
	<?php
}
