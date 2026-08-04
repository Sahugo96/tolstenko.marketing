<?php
/**
 * Настройки сайта → Страница контактов.
 * Дефолты блоков Контакты / Реквизиты / Карты в tolstenko_block_defaults.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOLSTENKO_CONTACTS_PAGE_OPTION', 'tolstenko_contacts_page' );
define( 'TOLSTENKO_CONTACTS_PAGE_MIGRATED_OPTION', 'tolstenko_contacts_page_migrated' );

/**
 * Ключи дефолтов страницы контактов.
 *
 * @return string[]
 */
function tolstenko_contacts_defaults_keys() {
	return array( 'contacts_page', 'contacts_details', 'contacts_maps' );
}

/**
 * Однократная миграция из tolstenko_contacts_page → tolstenko_block_defaults.
 */
function tolstenko_maybe_migrate_contacts_page_option() {
	if ( get_option( TOLSTENKO_CONTACTS_PAGE_MIGRATED_OPTION, false ) ) {
		return;
	}

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$has_any = ! empty( $saved['contacts_page'] ) || ! empty( $saved['contacts_details'] ) || ! empty( $saved['contacts_maps'] );
	$old     = get_option( TOLSTENKO_CONTACTS_PAGE_OPTION, array() );

	if ( ! $has_any && is_array( $old ) && ! empty( $old ) ) {
		$flat = tolstenko_sanitize_contacts_page_flat_data( $old );
		if ( empty( $saved['contacts_page'] ) ) {
			$saved['contacts_page'] = array(
				'title'     => $flat['contacts_title'],
				'items'     => $flat['contacts_items'],
				'addresses' => $flat['addresses'],
			);
		}
		if ( empty( $saved['contacts_details'] ) ) {
			$saved['contacts_details'] = array(
				'title'      => $flat['details_title'],
				'items'      => $flat['details_items'],
				'form_title' => $flat['details_form_title'],
				'form_text'  => $flat['details_form_text'],
			);
		}
		if ( empty( $saved['contacts_maps'] ) ) {
			$saved['contacts_maps'] = array(
				'title' => $flat['maps_title'],
				'items' => $flat['maps_items'],
			);
		}
		update_option( 'tolstenko_block_defaults', $saved, false );
	}

	update_option( TOLSTENKO_CONTACTS_PAGE_MIGRATED_OPTION, true, false );
}

/**
 * Плоская структура по умолчанию (для совместимости).
 *
 * @return array<string, mixed>
 */
function tolstenko_contacts_page_flat_defaults() {
	return array(
		'contacts_title'         => '',
		'contacts_items'         => array(),
		'addresses'              => array(),
		'details_title'          => '',
		'details_items'          => array(),
		'details_form_title' => __( 'Свяжитесь с нами', 'tolstenko-theme' ),
		'details_form_text'  => __( 'Оставьте заявку и мы свяжемся с вами', 'tolstenko-theme' ),
		'maps_title'         => '',
		'maps_items'         => array(),
	);
}

/**
 * @param mixed $raw Raw.
 * @return array<string, mixed>
 */
function tolstenko_sanitize_contacts_page_flat_data( $raw ) {
	$base = tolstenko_contacts_page_flat_defaults();
	if ( ! is_array( $raw ) ) {
		return $base;
	}

	$base['contacts_title'] = sanitize_text_field( (string) ( $raw['contacts_title'] ?? '' ) );
	$base['details_title']  = sanitize_text_field( (string) ( $raw['details_title'] ?? '' ) );
	$base['maps_title']     = sanitize_text_field( (string) ( $raw['maps_title'] ?? '' ) );
	$base['details_form_title'] = sanitize_text_field( (string) ( $raw['details_form_title'] ?? '' ) );
	$base['details_form_text']  = sanitize_text_field( (string) ( $raw['details_form_text'] ?? '' ) );

	$items = array();
	if ( ! empty( $raw['contacts_items'] ) && is_array( $raw['contacts_items'] ) ) {
		foreach ( $raw['contacts_items'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
			$icon  = sanitize_text_field( (string) ( $row['icon'] ?? '' ) );
			$links = array();
			if ( ! empty( $row['links'] ) && is_array( $row['links'] ) ) {
				foreach ( $row['links'] as $link_row ) {
					if ( ! is_array( $link_row ) ) {
						continue;
					}
					$text = sanitize_text_field( (string) ( $link_row['text'] ?? '' ) );
					$href = esc_url_raw( trim( (string) ( $link_row['link'] ?? '' ) ) );
					if ( $text === '' && $href === '' ) {
						continue;
					}
					$links[] = array(
						'text' => $text,
						'link' => $href,
					);
				}
			}
			if ( $title === '' && $icon === '' && empty( $links ) ) {
				continue;
			}
			$items[] = array(
				'title' => $title,
				'icon'  => $icon,
				'links' => $links,
			);
		}
	}
	$base['contacts_items'] = $items;

	$addresses = array();
	if ( ! empty( $raw['addresses'] ) && is_array( $raw['addresses'] ) ) {
		foreach ( $raw['addresses'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$address = sanitize_text_field( (string) ( $row['address'] ?? '' ) );
			$gallery = array();
			if ( ! empty( $row['gallery'] ) && is_array( $row['gallery'] ) ) {
				foreach ( $row['gallery'] as $img ) {
					$id = absint( $img );
					if ( $id > 0 ) {
						$gallery[] = $id;
					}
				}
			} elseif ( ! empty( $row['gallery_ids'] ) ) {
				foreach ( preg_split( '/\s*,\s*/', (string) $row['gallery_ids'] ) as $part ) {
					$id = absint( $part );
					if ( $id > 0 ) {
						$gallery[] = $id;
					}
				}
			}
			$addr_items = array();
			if ( ! empty( $row['items'] ) && is_array( $row['items'] ) ) {
				foreach ( $row['items'] as $item_row ) {
					if ( ! is_array( $item_row ) ) {
						continue;
					}
					$title = sanitize_text_field( (string) ( $item_row['title'] ?? '' ) );
					$icon  = sanitize_text_field( (string) ( $item_row['icon'] ?? '' ) );
					$links = array();
					if ( ! empty( $item_row['links'] ) && is_array( $item_row['links'] ) ) {
						foreach ( $item_row['links'] as $link_row ) {
							if ( ! is_array( $link_row ) ) {
								continue;
							}
							$text = sanitize_text_field( (string) ( $link_row['text'] ?? '' ) );
							$href = esc_url_raw( trim( (string) ( $link_row['link'] ?? '' ) ) );
							if ( $text === '' && $href === '' ) {
								continue;
							}
							$links[] = array(
								'text' => $text,
								'link' => $href,
							);
						}
					}
					if ( $title === '' && $icon === '' && empty( $links ) ) {
						continue;
					}
					$addr_items[] = array(
						'title' => $title,
						'icon'  => $icon,
						'links' => $links,
					);
				}
			}
			if ( $address === '' && empty( $gallery ) && empty( $addr_items ) ) {
				continue;
			}
			$addresses[] = array(
				'address' => $address,
				'gallery' => $gallery,
				'items'   => $addr_items,
			);
		}
	}
	$base['addresses'] = $addresses;

	$details = array();
	if ( ! empty( $raw['details_items'] ) && is_array( $raw['details_items'] ) ) {
		foreach ( $raw['details_items'] as $row ) {
			$text = '';
			if ( is_array( $row ) ) {
				$text = (string) ( $row['text'] ?? '' );
			} else {
				$text = (string) $row;
			}
			$text = wp_kses_post( $text );
			if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
				continue;
			}
			$details[] = array( 'text' => $text );
		}
	}
	$base['details_items'] = $details;

	$maps = array();
	if ( ! empty( $raw['maps_items'] ) && is_array( $raw['maps_items'] ) ) {
		foreach ( $raw['maps_items'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$address = sanitize_text_field( (string) ( $row['address'] ?? '' ) );
			$map     = wp_kses(
				(string) ( $row['map'] ?? '' ),
				array(
					'iframe' => array(
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'style'           => true,
						'frameborder'     => true,
						'allowfullscreen' => true,
						'loading'         => true,
						'referrerpolicy'  => true,
						'title'           => true,
						'class'           => true,
					),
					'div'    => array( 'class' => true, 'style' => true, 'id' => true ),
					'script' => array( 'type' => true, 'src' => true, 'async' => true, 'defer' => true, 'charset' => true ),
				)
			);
			if ( $address === '' && trim( $map ) === '' ) {
				continue;
			}
			$maps[] = array(
				'address' => $address,
				'map'     => $map,
			);
		}
	}
	$base['maps_items'] = $maps;

	return $base;
}

/**
 * Санитизация дефолтов contacts_page / contacts_details / contacts_maps из POST.
 *
 * @param array<string, mixed> $raw tolstenko_block_defaults из формы.
 * @return array<string, array>
 */
function tolstenko_sanitize_contacts_defaults_from_raw( $raw ) {
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	$patch = array();

	$cp_raw = isset( $raw['contacts_page'] ) && is_array( $raw['contacts_page'] ) ? $raw['contacts_page'] : array();
	$cp     = array(
		'title'     => sanitize_text_field( (string) ( $cp_raw['title'] ?? '' ) ),
		'items'     => array(),
		'addresses' => array(),
	);

	$sanitize_contact_items = static function ( $rows ) {
		$out = array();
		if ( ! is_array( $rows ) ) {
			return $out;
		}
		foreach ( $rows as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$title = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
			$icon  = sanitize_text_field( (string) ( $row['icon'] ?? '' ) );
			$links = array();
			if ( ! empty( $row['links'] ) && is_array( $row['links'] ) ) {
				foreach ( $row['links'] as $link_row ) {
					if ( ! is_array( $link_row ) ) {
						continue;
					}
					$text = sanitize_text_field( (string) ( $link_row['text'] ?? '' ) );
					$href = esc_url_raw( trim( (string) ( $link_row['link'] ?? '' ) ) );
					if ( $text === '' && $href === '' ) {
						continue;
					}
					$links[] = array(
						'text' => $text,
						'link' => $href,
					);
				}
			}
			if ( $title === '' && $icon === '' && empty( $links ) ) {
				continue;
			}
			$out[] = array(
				'title' => $title,
				'icon'  => $icon,
				'links' => $links,
			);
		}
		return $out;
	};

	if ( ! empty( $cp_raw['addresses'] ) && is_array( $cp_raw['addresses'] ) ) {
		foreach ( $cp_raw['addresses'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$address = sanitize_text_field( (string) ( $row['address'] ?? '' ) );
			$gallery = array();
			if ( ! empty( $row['gallery'] ) && is_array( $row['gallery'] ) ) {
				foreach ( $row['gallery'] as $img ) {
					$id = is_array( $img ) ? absint( $img['id'] ?? ( $img['image'] ?? 0 ) ) : absint( $img );
					if ( $id > 0 ) {
						$gallery[] = $id;
					}
				}
			} elseif ( ! empty( $row['gallery_ids'] ) ) {
				foreach ( preg_split( '/\s*,\s*/', (string) $row['gallery_ids'] ) as $part ) {
					$id = absint( $part );
					if ( $id > 0 ) {
						$gallery[] = $id;
					}
				}
			}
			$items = $sanitize_contact_items( $row['items'] ?? array() );
			if ( $address === '' && empty( $gallery ) && empty( $items ) ) {
				continue;
			}
			$cp['addresses'][] = array(
				'address' => $address,
				'gallery' => $gallery,
				'items'   => $items,
			);
		}
	}
	$patch['contacts_page'] = $cp;

	$cd_raw = isset( $raw['contacts_details'] ) && is_array( $raw['contacts_details'] ) ? $raw['contacts_details'] : array();
	$cd     = array(
		'title'      => sanitize_text_field( (string) ( $cd_raw['title'] ?? '' ) ),
		'items'      => array(),
		'form_title' => sanitize_text_field( (string) ( $cd_raw['form_title'] ?? '' ) ),
		'form_text'  => sanitize_text_field( (string) ( $cd_raw['form_text'] ?? '' ) ),
	);
	if ( ! empty( $cd_raw['items'] ) && is_array( $cd_raw['items'] ) ) {
		foreach ( $cd_raw['items'] as $row ) {
			$text = is_array( $row ) ? (string) ( $row['text'] ?? '' ) : (string) $row;
			$text = wp_kses_post( $text );
			if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
				continue;
			}
			$cd['items'][] = array( 'text' => $text );
		}
	}
	$patch['contacts_details'] = $cd;

	$cm_raw = isset( $raw['contacts_maps'] ) && is_array( $raw['contacts_maps'] ) ? $raw['contacts_maps'] : array();
	$cm     = array(
		'title' => sanitize_text_field( (string) ( $cm_raw['title'] ?? '' ) ),
		'items' => array(),
	);
	if ( ! empty( $cm_raw['items'] ) && is_array( $cm_raw['items'] ) ) {
		foreach ( $cm_raw['items'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$address = sanitize_text_field( (string) ( $row['address'] ?? '' ) );
			$map     = wp_kses(
				(string) ( $row['map'] ?? '' ),
				array(
					'iframe' => array(
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'style'           => true,
						'frameborder'     => true,
						'allowfullscreen' => true,
						'loading'         => true,
						'referrerpolicy'  => true,
						'title'           => true,
						'class'           => true,
					),
					'div'    => array( 'class' => true, 'style' => true, 'id' => true ),
					'script' => array( 'type' => true, 'src' => true, 'async' => true, 'defer' => true, 'charset' => true ),
				)
			);
			if ( $address === '' && trim( $map ) === '' ) {
				continue;
			}
			$cm['items'][] = array(
				'address' => $address,
				'map'     => $map,
			);
		}
	}
	$patch['contacts_maps'] = $cm;

	return $patch;
}

/**
 * Merge-save contacts defaults into tolstenko_block_defaults.
 */
function tolstenko_save_contacts_defaults_keys_from_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$raw   = isset( $_POST['tolstenko_block_defaults'] ) ? wp_unslash( $_POST['tolstenko_block_defaults'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$patch = tolstenko_sanitize_contacts_defaults_from_raw( is_array( $raw ) ? $raw : array() );
	$out   = array();
	foreach ( tolstenko_contacts_defaults_keys() as $key ) {
		if ( isset( $patch[ $key ] ) ) {
			$out[ $key ] = $patch[ $key ];
		}
	}

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$saved = array_merge( $saved, $out );
	update_option( 'tolstenko_block_defaults', $saved, false );
}

/**
 * Плоская структура для совместимости (маппинг трёх ключей block_defaults).
 *
 * @return array<string, mixed>
 */
function tolstenko_get_contacts_page_data() {
	static $cache = null;
	if ( $cache !== null ) {
		return $cache;
	}

	tolstenko_maybe_migrate_contacts_page_option();

	$cp = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'contacts_page' ) : array();
	$cd = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'contacts_details' ) : array();
	$cm = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'contacts_maps' ) : array();

	$cache = array(
		'contacts_title'         => (string) ( $cp['title'] ?? '' ),
		'contacts_items'         => ! empty( $cp['items'] ) && is_array( $cp['items'] ) ? $cp['items'] : array(),
		'addresses'              => ! empty( $cp['addresses'] ) && is_array( $cp['addresses'] ) ? $cp['addresses'] : array(),
		'details_title'          => (string) ( $cd['title'] ?? '' ),
		'details_items'          => ! empty( $cd['items'] ) && is_array( $cd['items'] ) ? $cd['items'] : array(),
		'details_form_title' => (string) ( $cd['form_title'] ?? '' ),
		'details_form_text'  => (string) ( $cd['form_text'] ?? '' ),
		'maps_title'         => (string) ( $cm['title'] ?? '' ),
		'maps_items'         => ! empty( $cm['items'] ) && is_array( $cm['items'] ) ? $cm['items'] : array(),
	);

	return $cache;
}

add_action( 'admin_init', 'tolstenko_maybe_migrate_contacts_page_option' );
add_action( 'admin_menu', 'tolstenko_register_contacts_page_admin', 21 );
add_action( 'admin_enqueue_scripts', 'tolstenko_contacts_page_admin_assets' );
add_action( 'admin_post_tolstenko_save_contacts_page', 'tolstenko_handle_save_contacts_page' );

function tolstenko_register_contacts_page_admin() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Страница контактов', 'tolstenko-theme' ),
		__( 'Страница контактов', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-contacts-page',
		'tolstenko_render_contacts_page_admin'
	);
}

/**
 * @param string $hook Hook.
 */
function tolstenko_contacts_page_admin_assets( $hook ) {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $page !== 'tolstenko-contacts-page' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_editor();
}

function tolstenko_handle_save_contacts_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'tolstenko-theme' ) );
	}
	check_admin_referer( 'tolstenko_contacts_page_save', 'tolstenko_contacts_page_nonce' );

	tolstenko_save_contacts_defaults_keys_from_request();

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'tolstenko-contacts-page',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

/**
 * @param string $icon Attachment ID or URL.
 * @return array{url: string, id: int}
 */
function tolstenko_contacts_page_icon_preview( $icon ) {
	$icon = trim( (string) $icon );
	if ( $icon === '' ) {
		return array( 'url' => '', 'id' => 0 );
	}
	if ( ctype_digit( $icon ) ) {
		$id  = (int) $icon;
		$url = $id > 0 ? (string) wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
		return array( 'url' => $url, 'id' => $id );
	}
	return array( 'url' => esc_url_raw( $icon ), 'id' => 0 );
}

function tolstenko_render_contacts_page_admin() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	tolstenko_maybe_migrate_contacts_page_option();

	$all = function_exists( 'tolstenko_get_merged_defaults_for_keys' )
		? tolstenko_get_merged_defaults_for_keys( tolstenko_contacts_defaults_keys() )
		: array();

	$cp = $all['contacts_page'] ?? array();
	$cd = $all['contacts_details'] ?? array();
	$cm = $all['contacts_maps'] ?? array();

	$updated = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	$legacy_items = ! empty( $cp['items'] ) && is_array( $cp['items'] ) ? $cp['items'] : array();
	$empty_item   = array(
		'title' => '',
		'icon'  => '',
		'links' => array( array( 'text' => '', 'link' => '' ) ),
	);
	$addresses = ! empty( $cp['addresses'] ) && is_array( $cp['addresses'] ) ? $cp['addresses'] : array();
	if ( empty( $addresses ) ) {
		$addresses = array(
			array(
				'address' => '',
				'gallery' => array(),
				'items'   => $legacy_items ? $legacy_items : array( $empty_item ),
			),
		);
	} else {
		foreach ( $addresses as $ai => $addr ) {
			if ( empty( $addr['items'] ) || ! is_array( $addr['items'] ) ) {
				$addresses[ $ai ]['items'] = $legacy_items ? $legacy_items : array( $empty_item );
			}
		}
	}
	$details_items = ! empty( $cd['items'] ) && is_array( $cd['items'] ) ? $cd['items'] : array();
	if ( empty( $details_items ) ) {
		$details_items = array( array( 'text' => '' ) );
	}
	$maps_items = ! empty( $cm['items'] ) && is_array( $cm['items'] ) ? $cm['items'] : array();
	if ( empty( $maps_items ) ) {
		$maps_items = array(
			array(
				'address' => '',
				'map'     => '',
			),
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Страница контактов', 'tolstenko-theme' ); ?></h1>
		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Сохранено.', 'tolstenko-theme' ); ?></p></div>
		<?php endif; ?>
		<p class="description">
			<?php esc_html_e( 'Дефолты блоков «Контакты», «Реквизиты» и «Карты». Сохраняются в общие настройки сайта и не затирают остальные дефолты.', 'tolstenko-theme' ); ?>
		</p>

		<?php
		if ( function_exists( 'tolstenko_print_defaults_admin_styles' ) ) {
			tolstenko_print_defaults_admin_styles();
		}
		?>
		<style>
			.tolstenko-cp .row>label{display:block;font-weight:600;margin:0 0 6px}
			.tolstenko-cp .row input[type="text"],
			.tolstenko-cp .row input[type="url"],
			.tolstenko-cp .row textarea,
			.tolstenko-cp .nested input[type="text"],
			.tolstenko-cp .nested input[type="url"],
			.tolstenko-cp .nested textarea{
				width:100%;
				max-width:none;
				box-sizing:border-box;
			}
			.tolstenko-cp .repeater-item .cols{
				display:flex;
				gap:8px;
				flex-wrap:nowrap;
				align-items:center;
				width:100%;
			}
			.tolstenko-cp .repeater-item .cols input[type="text"],
			.tolstenko-cp .repeater-item .cols input[type="url"]{
				flex:1 1 0;
				width:auto;
				min-width:0;
				max-width:none;
			}
			.tolstenko-cp .repeater-item .cols .button{flex:0 0 auto}
			.tolstenko-cp .nested{margin-top:10px;padding-left:8px;border-left:3px solid #dcdcde}
			.tolstenko-cp .icon-preview img,
			.tolstenko-cp .gallery-image-preview img{max-width:64px;max-height:64px;display:block;object-fit:cover;background:#fff;border:1px solid #ddd}
			.tolstenko-cp .addr-items,
			.tolstenko-cp .addr-gallery{margin-top:12px;padding-top:10px;border-top:1px solid #dcdcde}
			.tolstenko-cp .addr-item,
			.tolstenko-cp .gallery-item{margin:10px 0;padding:10px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px}
		</style>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tolstenko_save_contacts_page">
			<?php wp_nonce_field( 'tolstenko_contacts_page_save', 'tolstenko_contacts_page_nonce' ); ?>

			<div class="tolstenko-df tolstenko-cp">
				<div class="tolstenko-df-tabs-group is-active" data-group="contacts-page">
					<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Страница контактов', 'tolstenko-theme' ); ?></div>
					<div class="tolstenko-df-tabs">
						<button type="button" class="tolstenko-df-tab active" data-panel="contacts" data-group="contacts-page"><?php esc_html_e( 'Контакты', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="details" data-group="contacts-page"><?php esc_html_e( 'Реквизиты', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="maps" data-group="contacts-page"><?php esc_html_e( 'Карты', 'tolstenko-theme' ); ?></button>
					</div>
					<div class="tolstenko-df-group-panels" data-group-panels="contacts-page">

			<div class="tolstenko-df-panel active" data-panel="contacts" data-group="contacts-page">
				<div class="row">
					<label for="tolstenko-cp-contacts-title"><?php esc_html_e( 'Заголовок блока', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cp-contacts-title" name="tolstenko_block_defaults[contacts_page][title]" value="<?php echo esc_attr( $cp['title'] ?? '' ); ?>" style="width:100%">
				</div>

				<h3><?php esc_html_e( 'Адреса (вкладки + галерея)', 'tolstenko-theme' ); ?></h3>
				<p class="muted"><?php esc_html_e( 'У каждого адреса свои контактные пункты слева и список фото справа. При переключении вкладки меняются и данные, и галерея.', 'tolstenko-theme' ); ?></p>
				<div data-repeater-list="addresses">
					<?php foreach ( $addresses as $i => $addr ) :
						$gallery_raw = ! empty( $addr['gallery'] ) && is_array( $addr['gallery'] ) ? $addr['gallery'] : array();
						$gallery     = array();
						foreach ( $gallery_raw as $gid ) {
							$id = is_array( $gid ) ? absint( $gid['id'] ?? ( $gid['image'] ?? 0 ) ) : absint( $gid );
							if ( $id > 0 ) {
								$gallery[] = $id;
							}
						}
						if ( empty( $gallery ) ) {
							$gallery = array( 0 );
						}
						$addr_items = ! empty( $addr['items'] ) && is_array( $addr['items'] ) ? $addr['items'] : array( $empty_item );
						?>
						<div class="repeater-item" data-repeater-item data-address-item>
							<div class="row">
								<label><?php esc_html_e( 'Адрес (подпись вкладки)', 'tolstenko-theme' ); ?></label>
								<input type="text" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][address]" value="<?php echo esc_attr( $addr['address'] ?? '' ); ?>" style="width:100%">
							</div>
							<div class="addr-items">
								<strong><?php esc_html_e( 'Контактные данные этого адреса', 'tolstenko-theme' ); ?></strong>
								<p class="muted" style="margin:4px 0 8px"><?php esc_html_e( 'Телефон, почта, режим работы и т.п. — показываются слева при выборе вкладки.', 'tolstenko-theme' ); ?></p>
								<div data-addr-items-list>
									<?php foreach ( $addr_items as $ii => $item ) :
										$icon_meta = tolstenko_contacts_page_icon_preview( $item['icon'] ?? '' );
										$links     = ! empty( $item['links'] ) && is_array( $item['links'] ) ? $item['links'] : array( array( 'text' => '', 'link' => '' ) );
										?>
										<div class="addr-item" data-addr-contact-item>
											<div class="row">
												<label><?php esc_html_e( 'Заголовок пункта', 'tolstenko-theme' ); ?></label>
												<input type="text" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][items][<?php echo (int) $ii; ?>][title]" value="<?php echo esc_attr( $item['title'] ?? '' ); ?>" style="width:100%">
											</div>
											<div class="row">
												<label><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></label>
												<div class="cols">
													<span class="icon-preview"><?php if ( $icon_meta['url'] !== '' ) : ?><img src="<?php echo esc_url( $icon_meta['url'] ); ?>" alt=""><?php endif; ?></span>
													<input type="hidden" class="js-cp-icon-value" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][items][<?php echo (int) $ii; ?>][icon]" value="<?php echo esc_attr( $item['icon'] ?? '' ); ?>">
													<button type="button" class="button js-cp-pick-icon"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
													<button type="button" class="button js-cp-clear-icon"><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
												</div>
											</div>
											<div class="nested" data-nested-links>
												<strong><?php esc_html_e( 'Ссылки / строки', 'tolstenko-theme' ); ?></strong>
												<?php foreach ( $links as $li => $link ) : ?>
													<div class="cols" data-link-row style="margin-top:6px">
														<input type="text" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][items][<?php echo (int) $ii; ?>][links][<?php echo (int) $li; ?>][text]" value="<?php echo esc_attr( $link['text'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Текст', 'tolstenko-theme' ); ?>">
														<input type="url" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][items][<?php echo (int) $ii; ?>][links][<?php echo (int) $li; ?>][link]" value="<?php echo esc_attr( $link['link'] ?? '' ); ?>" placeholder="https://… / tel:… / mailto:…">
														<button type="button" class="button js-cp-remove-link"><?php esc_html_e( '×', 'tolstenko-theme' ); ?></button>
													</div>
												<?php endforeach; ?>
												<p><button type="button" class="button js-cp-add-link"><?php esc_html_e( 'Добавить строку', 'tolstenko-theme' ); ?></button></p>
											</div>
											<p><button type="button" class="button-link-delete js-cp-remove-addr-item"><?php esc_html_e( 'Удалить пункт', 'tolstenko-theme' ); ?></button></p>
										</div>
									<?php endforeach; ?>
								</div>
								<p><button type="button" class="button js-cp-add-addr-item"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
							</div>
							<div class="addr-gallery">
								<strong><?php esc_html_e( 'Галерея (список фото)', 'tolstenko-theme' ); ?></strong>
								<p class="muted" style="margin:4px 0 8px"><?php esc_html_e( 'Каждый пункт — одно изображение слайдера. Нажмите «Добавить пункт», чтобы добавить ещё фото.', 'tolstenko-theme' ); ?></p>
								<div data-addr-gallery-list>
									<?php foreach ( $gallery as $gi => $gid ) :
										$gurl = $gid ? (string) wp_get_attachment_image_url( (int) $gid, 'thumbnail' ) : '';
										?>
										<div class="gallery-item" data-gallery-item>
											<div class="row">
												<label><?php esc_html_e( 'Изображение', 'tolstenko-theme' ); ?></label>
												<div class="cols">
													<span class="gallery-image-preview"><?php if ( $gurl !== '' ) : ?><img src="<?php echo esc_url( $gurl ); ?>" alt=""><?php endif; ?></span>
													<input type="hidden" class="js-cp-gallery-image" name="tolstenko_block_defaults[contacts_page][addresses][<?php echo (int) $i; ?>][gallery][<?php echo (int) $gi; ?>][image]" value="<?php echo esc_attr( (string) (int) $gid ); ?>">
													<button type="button" class="button js-cp-pick-gallery-image"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
													<button type="button" class="button js-cp-clear-gallery-image"><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
												</div>
											</div>
											<p><button type="button" class="button-link-delete js-cp-remove-gallery-item"><?php esc_html_e( 'Удалить пункт', 'tolstenko-theme' ); ?></button></p>
										</div>
									<?php endforeach; ?>
								</div>
								<p><button type="button" class="button js-cp-add-gallery-item"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
							</div>
							<p><button type="button" class="button-link-delete" data-remove-item><?php esc_html_e( 'Удалить адрес', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-add="addresses"><?php esc_html_e( 'Добавить адрес', 'tolstenko-theme' ); ?></button></p>
				<p class="muted"><?php esc_html_e( 'Кнопки «Позвонить» / «Написать в Telegram» берутся из «Контактные данные» (телефон и Telegram).', 'tolstenko-theme' ); ?></p>
			</div>

			<div class="tolstenko-df-panel" data-panel="details" data-group="contacts-page">
				<div class="row">
					<label for="tolstenko-cp-details-title"><?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cp-details-title" name="tolstenko_block_defaults[contacts_details][title]" value="<?php echo esc_attr( $cd['title'] ?? '' ); ?>" style="width:100%">
				</div>
				<h3><?php esc_html_e( 'Блоки реквизитов', 'tolstenko-theme' ); ?></h3>
				<div data-repeater-list="details-items">
					<?php foreach ( $details_items as $i => $item ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="row">
								<label><?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?></label>
								<textarea name="tolstenko_block_defaults[contacts_details][items][<?php echo (int) $i; ?>][text]" rows="5" style="width:100%"><?php echo esc_textarea( $item['text'] ?? '' ); ?></textarea>
							</div>
							<p><button type="button" class="button-link-delete" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-add="details-items"><?php esc_html_e( 'Добавить блок', 'tolstenko-theme' ); ?></button></p>

				<hr>
				<div class="row">
					<label for="tolstenko-cp-form-title"><?php esc_html_e( 'Заголовок формы', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cp-form-title" name="tolstenko_block_defaults[contacts_details][form_title]" value="<?php echo esc_attr( $cd['form_title'] ?? '' ); ?>" style="width:100%">
				</div>
				<div class="row">
					<label for="tolstenko-cp-form-text"><?php esc_html_e( 'Подзаголовок формы', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cp-form-text" name="tolstenko_block_defaults[contacts_details][form_text]" value="<?php echo esc_attr( $cd['form_text'] ?? '' ); ?>" style="width:100%">
				</div>
			</div>

			<div class="tolstenko-df-panel" data-panel="maps" data-group="contacts-page">
				<div class="row">
					<label for="tolstenko-cp-maps-title"><?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?></label>
					<input type="text" id="tolstenko-cp-maps-title" name="tolstenko_block_defaults[contacts_maps][title]" value="<?php echo esc_attr( $cm['title'] ?? '' ); ?>" style="width:100%">
				</div>
				<h3><?php esc_html_e( 'Карты по адресам', 'tolstenko-theme' ); ?></h3>
				<div data-repeater-list="maps-items">
					<?php foreach ( $maps_items as $i => $item ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="row">
								<label><?php esc_html_e( 'Адрес (вкладка)', 'tolstenko-theme' ); ?></label>
								<input type="text" name="tolstenko_block_defaults[contacts_maps][items][<?php echo (int) $i; ?>][address]" value="<?php echo esc_attr( $item['address'] ?? '' ); ?>" style="width:100%">
							</div>
							<div class="row">
								<label><?php esc_html_e( 'Код карты (iframe Яндекс/Google)', 'tolstenko-theme' ); ?></label>
								<textarea name="tolstenko_block_defaults[contacts_maps][items][<?php echo (int) $i; ?>][map]" rows="4" style="width:100%"><?php echo esc_textarea( $item['map'] ?? '' ); ?></textarea>
							</div>
							<p><button type="button" class="button-link-delete" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-add="maps-items"><?php esc_html_e( 'Добавить карту', 'tolstenko-theme' ); ?></button></p>
			</div>

					</div>
				</div>
			</div>

			<?php submit_button( __( 'Сохранить', 'tolstenko-theme' ) ); ?>
		</form>
	</div>
	<script>
	(function () {
		var root = document.querySelector('.tolstenko-cp');
		if (!root) return;

		root.querySelectorAll('.tolstenko-df-tab').forEach(function (tab) {
			tab.addEventListener('click', function () {
				var panel = tab.getAttribute('data-panel');
				root.querySelectorAll('.tolstenko-df-tab').forEach(function (t) { t.classList.toggle('active', t === tab); });
				root.querySelectorAll('.tolstenko-df-panel').forEach(function (p) {
					p.classList.toggle('active', p.getAttribute('data-panel') === panel);
				});
			});
		});

		function addrContactItemHtml(aidx, iidx) {
			return ''
				+ '<div class="addr-item" data-addr-contact-item>'
				+ '<div class="row"><label>Заголовок пункта</label><input type="text" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][items][' + iidx + '][title]" value="" style="width:100%"></div>'
				+ '<div class="row"><label>Иконка</label><div class="cols"><span class="icon-preview"></span>'
				+ '<input type="hidden" class="js-cp-icon-value" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][items][' + iidx + '][icon]" value="">'
				+ '<button type="button" class="button js-cp-pick-icon">Выбрать</button>'
				+ '<button type="button" class="button js-cp-clear-icon">Очистить</button></div></div>'
				+ '<div class="nested" data-nested-links><strong>Ссылки / строки</strong>'
				+ '<div class="cols" data-link-row style="margin-top:6px">'
				+ '<input type="text" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][items][' + iidx + '][links][0][text]" placeholder="Текст">'
				+ '<input type="url" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][items][' + iidx + '][links][0][link]" placeholder="https://…">'
				+ '<button type="button" class="button js-cp-remove-link">×</button></div>'
				+ '<p><button type="button" class="button js-cp-add-link">Добавить строку</button></p></div>'
				+ '<p><button type="button" class="button-link-delete js-cp-remove-addr-item">Удалить пункт</button></p></div>';
		}

		function getAddressIndex(el) {
			var addr = el && el.closest('[data-address-item]');
			if (!addr) return 0;
			var input = addr.querySelector('input[name*="[addresses]"]');
			if (!input) return 0;
			var m = input.name.match(/\[addresses\]\[(\d+)\]/);
			return m ? parseInt(m[1], 10) : 0;
		}

		function galleryItemHtml(aidx, gidx) {
			return ''
				+ '<div class="gallery-item" data-gallery-item>'
				+ '<div class="row"><label>Изображение</label><div class="cols">'
				+ '<span class="gallery-image-preview"></span>'
				+ '<input type="hidden" class="js-cp-gallery-image" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][gallery][' + gidx + '][image]" value="">'
				+ '<button type="button" class="button js-cp-pick-gallery-image">Выбрать</button>'
				+ '<button type="button" class="button js-cp-clear-gallery-image">Очистить</button>'
				+ '</div></div>'
				+ '<p><button type="button" class="button-link-delete js-cp-remove-gallery-item">Удалить пункт</button></p></div>';
		}

		root.addEventListener('click', function (e) {
			var t = e.target;
			if (!(t instanceof HTMLElement)) return;

			if (t.matches('[data-add="addresses"]')) {
				var alist = root.querySelector('[data-repeater-list="addresses"]');
				var aidx = alist.querySelectorAll(':scope > [data-repeater-item]').length;
				alist.insertAdjacentHTML('beforeend',
					'<div class="repeater-item" data-repeater-item data-address-item>'
					+ '<div class="row"><label>Адрес (подпись вкладки)</label><input type="text" name="tolstenko_block_defaults[contacts_page][addresses][' + aidx + '][address]" value="" style="width:100%"></div>'
					+ '<div class="addr-items"><strong>Контактные данные этого адреса</strong>'
					+ '<p class="muted" style="margin:4px 0 8px">Телефон, почта, режим работы и т.п. — показываются слева при выборе вкладки.</p>'
					+ '<div data-addr-items-list>' + addrContactItemHtml(aidx, 0) + '</div>'
					+ '<p><button type="button" class="button js-cp-add-addr-item">Добавить пункт</button></p></div>'
					+ '<div class="addr-gallery"><strong>Галерея (список фото)</strong>'
					+ '<p class="muted" style="margin:4px 0 8px">Каждый пункт — одно изображение слайдера. Нажмите «Добавить пункт», чтобы добавить ещё фото.</p>'
					+ '<div data-addr-gallery-list>' + galleryItemHtml(aidx, 0) + '</div>'
					+ '<p><button type="button" class="button js-cp-add-gallery-item">Добавить пункт</button></p></div>'
					+ '<p><button type="button" class="button-link-delete" data-remove-item>Удалить адрес</button></p></div>'
				);
				return;
			}

			if (t.matches('.js-cp-add-gallery-item')) {
				var gAddr = t.closest('[data-address-item]');
				var gList = gAddr && gAddr.querySelector('[data-addr-gallery-list]');
				if (!gList) return;
				var gAIndex = getAddressIndex(gAddr);
				var gIndex = gList.querySelectorAll(':scope > [data-gallery-item]').length;
				gList.insertAdjacentHTML('beforeend', galleryItemHtml(gAIndex, gIndex));
				return;
			}

			if (t.matches('.js-cp-remove-gallery-item')) {
				var gItem = t.closest('[data-gallery-item]');
				var gParent = gItem && gItem.parentElement;
				if (!gItem || !gParent) return;
				if (gParent.querySelectorAll(':scope > [data-gallery-item]').length <= 1) {
					var gInputClr = gItem.querySelector('.js-cp-gallery-image');
					var gPrevClr = gItem.querySelector('.gallery-image-preview');
					if (gInputClr) gInputClr.value = '';
					if (gPrevClr) gPrevClr.innerHTML = '';
					return;
				}
				gItem.remove();
				return;
			}

			if (t.matches('.js-cp-add-addr-item')) {
				var addrRow = t.closest('[data-address-item]');
				var itemsList = addrRow && addrRow.querySelector('[data-addr-items-list]');
				if (!itemsList) return;
				var aIndex = getAddressIndex(addrRow);
				var iIndex = itemsList.querySelectorAll(':scope > [data-addr-contact-item]').length;
				itemsList.insertAdjacentHTML('beforeend', addrContactItemHtml(aIndex, iIndex));
				return;
			}

			if (t.matches('.js-cp-remove-addr-item')) {
				var citem = t.closest('[data-addr-contact-item]');
				var clist = citem && citem.parentElement;
				if (!citem || !clist) return;
				if (clist.querySelectorAll(':scope > [data-addr-contact-item]').length <= 1) {
					citem.querySelectorAll('input').forEach(function (el) { el.value = ''; });
					var iprev = citem.querySelector('.icon-preview');
					if (iprev) iprev.innerHTML = '';
					return;
				}
				citem.remove();
				return;
			}

			if (t.matches('[data-add="details-items"]')) {
				var dlist = root.querySelector('[data-repeater-list="details-items"]');
				var didx = dlist.querySelectorAll(':scope > [data-repeater-item]').length;
				dlist.insertAdjacentHTML('beforeend',
					'<div class="repeater-item" data-repeater-item>'
					+ '<div class="row"><label>Текст</label><textarea name="tolstenko_block_defaults[contacts_details][items][' + didx + '][text]" rows="5" style="width:100%"></textarea></div>'
					+ '<p><button type="button" class="button-link-delete" data-remove-item>Удалить</button></p></div>'
				);
				return;
			}

			if (t.matches('[data-add="maps-items"]')) {
				var mlist = root.querySelector('[data-repeater-list="maps-items"]');
				var midx = mlist.querySelectorAll(':scope > [data-repeater-item]').length;
				mlist.insertAdjacentHTML('beforeend',
					'<div class="repeater-item" data-repeater-item>'
					+ '<div class="row"><label>Адрес (вкладка)</label><input type="text" name="tolstenko_block_defaults[contacts_maps][items][' + midx + '][address]" value="" style="width:100%"></div>'
					+ '<div class="row"><label>Код карты (iframe)</label><textarea name="tolstenko_block_defaults[contacts_maps][items][' + midx + '][map]" rows="4" style="width:100%"></textarea></div>'
					+ '<p><button type="button" class="button-link-delete" data-remove-item>Удалить</button></p></div>'
				);
				return;
			}

			if (t.matches('[data-remove-item]')) {
				var item = t.closest('[data-repeater-item]');
				var list = item && item.parentElement;
				if (!item || !list) return;
				if (list.querySelectorAll(':scope > [data-repeater-item]').length <= 1) {
					item.querySelectorAll('input, textarea').forEach(function (el) { el.value = ''; });
					item.querySelectorAll('.gallery-image-preview, .icon-preview').forEach(function (el) { el.innerHTML = ''; });
					return;
				}
				item.remove();
				return;
			}

			if (t.matches('.js-cp-add-link')) {
				var nest = t.closest('[data-nested-links]');
				var contactItem = t.closest('[data-addr-contact-item]');
				if (!nest || !contactItem) return;
				var aIndex2 = getAddressIndex(contactItem);
				var itemIndex = 0;
				var nameInput = contactItem.querySelector('input[name*="[items]"]');
				if (nameInput) {
					var m2 = nameInput.name.match(/\[items\]\[(\d+)\]/);
					if (m2) itemIndex = parseInt(m2[1], 10);
				}
				var linkCount = nest.querySelectorAll('[data-link-row]').length;
				var wrap = document.createElement('div');
				wrap.className = 'cols';
				wrap.setAttribute('data-link-row', '');
				wrap.style.marginTop = '6px';
				wrap.innerHTML = '<input type="text" name="tolstenko_block_defaults[contacts_page][addresses][' + aIndex2 + '][items][' + itemIndex + '][links][' + linkCount + '][text]" placeholder="Текст">'
					+ '<input type="url" name="tolstenko_block_defaults[contacts_page][addresses][' + aIndex2 + '][items][' + itemIndex + '][links][' + linkCount + '][link]" placeholder="https://…">'
					+ '<button type="button" class="button js-cp-remove-link">×</button>';
				nest.insertBefore(wrap, t.parentElement);
				return;
			}

			if (t.matches('.js-cp-remove-link')) {
				var row = t.closest('[data-link-row]');
				var nest2 = t.closest('[data-nested-links]');
				if (!row || !nest2) return;
				if (nest2.querySelectorAll('[data-link-row]').length <= 1) {
					row.querySelectorAll('input').forEach(function (el) { el.value = ''; });
					return;
				}
				row.remove();
				return;
			}

			if (t.matches('.js-cp-pick-icon') || t.matches('.js-cp-clear-icon')) {
				var box = t.closest('.row');
				var input = box && box.querySelector('.js-cp-icon-value');
				var preview = box && box.querySelector('.icon-preview');
				if (!input) return;
				if (t.matches('.js-cp-clear-icon')) {
					input.value = '';
					if (preview) preview.innerHTML = '';
					return;
				}
				if (typeof wp === 'undefined' || !wp.media) return;
				var frame = wp.media({ title: 'Иконка', button: { text: 'Выбрать' }, multiple: false });
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					input.value = String(att.id);
					if (preview) {
						var src = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) || att.url;
						preview.innerHTML = src ? '<img src="' + src + '" alt="">' : '';
					}
				});
				frame.open();
				return;
			}

			if (t.matches('.js-cp-pick-gallery-image') || t.matches('.js-cp-clear-gallery-image')) {
				var gBox = t.closest('.row');
				var gImgInput = gBox && gBox.querySelector('.js-cp-gallery-image');
				var gImgPrev = gBox && gBox.querySelector('.gallery-image-preview');
				if (!gImgInput) return;
				if (t.matches('.js-cp-clear-gallery-image')) {
					gImgInput.value = '';
					if (gImgPrev) gImgPrev.innerHTML = '';
					return;
				}
				if (typeof wp === 'undefined' || !wp.media) return;
				var gImgFrame = wp.media({
					title: 'Изображение галереи',
					button: { text: 'Выбрать' },
					multiple: false,
					library: { type: 'image' }
				});
				gImgFrame.on('select', function () {
					var att = gImgFrame.state().get('selection').first().toJSON();
					gImgInput.value = String(att.id);
					if (gImgPrev) {
						var src = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) || att.url;
						gImgPrev.innerHTML = src ? '<img src="' + src + '" alt="">' : '';
					}
				});
				gImgFrame.open();
			}
		});
	})();
	</script>
	<?php
}
