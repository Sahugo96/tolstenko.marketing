<?php
/**
 * Доп. блоки страницы подкатегории (term meta → те же шаблоны, что у услуги в Gutenberg).
 * База контента — «Настройки сайта → Дефолты блоков»; термин подкатегории может переопределить отдельные поля.
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Порядок блоков как в CPT service (functions.php → template).
 *
 * @return string[]
 */
function tolstenko_sc_get_service_category_block_slugs() {
	return array(
		'main-hero',
		'seo-section',
	);
}

/**
 * Атрибуты блока в формате Gutenberg из tolstenko_get_block_defaults() (без term meta).
 *
 * @param string $which Block key.
 * @return array<string, mixed>
 */
function tolstenko_sc_get_default_block_attributes_for_category( $which ) {
	if ( ! function_exists( 'tolstenko_get_block_defaults' ) ) {
		return array();
	}
	switch ( $which ) {
		case 'main_hero':
			$d     = tolstenko_get_block_defaults( 'main_hero' );
			$items = array();
			if ( ! empty( $d['items'] ) && is_array( $d['items'] ) ) {
				foreach ( $d['items'] as $txt ) {
					$item_text = is_string( $txt ) ? trim( $txt ) : ( is_array( $txt ) ? trim( (string) ( $txt['text'] ?? '' ) ) : '' );
					if ( $item_text !== '' ) {
						$items[] = $item_text;
					}
				}
			}
			return array(
				'block_main_hero_title'           => isset( $d['title'] ) ? (string) $d['title'] : '',
				'block_main_hero_title_tag'       => 'h1',
				'block_main_hero_text'            => isset( $d['text'] ) ? (string) $d['text'] : '',
				'block_main_hero_items'           => $items,
				'block_main_hero_btn_text'        => isset( $d['btn_text'] ) ? (string) $d['btn_text'] : '',
				'block_main_hero_show_promo'      => ! empty( $d['show_promo'] ) ? '1' : '0',
				'block_main_hero_promo_text'      => isset( $d['promo_text'] ) ? (string) $d['promo_text'] : '',
				'block_main_hero_present_image'   => isset( $d['present_image'] ) ? (int) $d['present_image'] : 0,
				'block_main_hero_person_name'     => isset( $d['person_name'] ) ? (string) $d['person_name'] : '',
				'block_main_hero_person_position' => isset( $d['person_position'] ) ? (string) $d['person_position'] : '',
				'block_main_hero_image'           => isset( $d['image'] ) ? (int) $d['image'] : 0,
			);
		case 'seo_section':
			$d = tolstenko_get_block_defaults( 'seo_section' );
			return array(
				'block_seo_section_title'     => isset( $d['title'] ) ? (string) $d['title'] : '',
				'block_seo_section_title_tag' => 'h2',
				'block_seo_section_subtitle'  => isset( $d['subtitle'] ) ? (string) $d['subtitle'] : '',
				'block_seo_section_more_text' => isset( $d['more_text'] ) ? (string) $d['more_text'] : '',
				'block_seo_section_blocks'    => isset( $d['blocks'] ) && is_array( $d['blocks'] ) ? $d['blocks'] : array(),
			);
		default:
			return array();
	}
}

/**
 * Дефолты сайта + непустые переопределения из терма (пустые строки из сохранённой формы не затирают дефолт).
 *
 * @param string  $which Block key.
 * @param WP_Term $term  Term.
 * @param string  $meta_key Term meta key.
 * @return array<string, mixed>
 */
/**
 * Старый term meta `_tolstenko_sc_banner` → атрибуты main-hero.
 *
 * @param mixed $saved Raw meta.
 * @return array<string, mixed>
 */
function tolstenko_sc_map_legacy_banner_to_main_hero( $saved ) {
	if ( ! is_array( $saved ) ) {
		return array();
	}
	if ( isset( $saved['block_main_hero_title'] ) || isset( $saved['block_main_hero_text'] ) || isset( $saved['block_main_hero_items'] ) ) {
		return $saved;
	}
	$items = array();
	if ( ! empty( $saved['block_hero_items'] ) && is_array( $saved['block_hero_items'] ) ) {
		foreach ( $saved['block_hero_items'] as $row ) {
			if ( is_string( $row ) ) {
				$t = trim( $row );
			} elseif ( is_array( $row ) ) {
				$t = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
			} else {
				$t = '';
			}
			if ( $t !== '' ) {
				$items[] = $t;
			}
		}
	}
	$notice = isset( $saved['block_hero_notice'] ) ? trim( (string) $saved['block_hero_notice'] ) : '';
	return array(
		'block_main_hero_title'           => isset( $saved['block_hero_title'] ) ? (string) $saved['block_hero_title'] : '',
		'block_main_hero_title_tag'       => isset( $saved['block_hero_title_tag'] ) ? (string) $saved['block_hero_title_tag'] : 'h1',
		'block_main_hero_text'            => isset( $saved['block_hero_subtitle'] ) ? (string) $saved['block_hero_subtitle'] : '',
		'block_main_hero_items'           => $items,
		'block_main_hero_btn_text'        => isset( $saved['block_hero_button'] ) ? (string) $saved['block_hero_button'] : '',
		'block_main_hero_show_promo'      => $notice !== '' ? '1' : '0',
		'block_main_hero_promo_text'      => $notice,
		'block_main_hero_present_image'   => 0,
		'block_main_hero_person_name'     => '',
		'block_main_hero_person_position' => '',
		'block_main_hero_image'           => isset( $saved['block_hero_main_image'] ) ? (int) $saved['block_hero_main_image'] : 0,
	);
}

function tolstenko_sc_resolve_category_block_attributes( $which, $term, $meta_key ) {
	$base  = tolstenko_sc_get_default_block_attributes_for_category( $which );
	$saved = get_term_meta( $term->term_id, $meta_key, true );
	if ( $which === 'main_hero' && ( ! is_array( $saved ) || empty( $saved ) ) ) {
		$legacy = get_term_meta( $term->term_id, '_tolstenko_sc_banner', true );
		if ( is_array( $legacy ) && ! empty( $legacy ) ) {
			$saved = tolstenko_sc_map_legacy_banner_to_main_hero( $legacy );
		}
	} elseif ( $which === 'main_hero' && is_array( $saved ) ) {
		$saved = tolstenko_sc_map_legacy_banner_to_main_hero( $saved );
	}
	if ( $which === 'main_hero' && $term instanceof WP_Term ) {
		$base['block_main_hero_title'] = $term->name;
	}
	$has = false;
	if ( $which === 'main_hero' ) {
		$has = tolstenko_sc_main_hero_has_custom_content( $saved );
	} elseif ( $which === 'seo_section' ) {
		$has = tolstenko_sc_seo_section_has_custom_content( $saved );
	}
	if ( ! $has || ! is_array( $saved ) ) {
		return $base;
	}
	$out = $base;
	if ( $which === 'seo_section' ) {
		foreach ( array( 'block_seo_section_title', 'block_seo_section_subtitle', 'block_seo_section_more_text' ) as $k ) {
			if ( isset( $saved[ $k ] ) && trim( (string) $saved[ $k ] ) !== '' ) {
				$out[ $k ] = $saved[ $k ];
			}
		}
		if ( ! empty( $saved['block_seo_section_title_tag'] ) ) {
			$out['block_seo_section_title_tag'] = (string) $saved['block_seo_section_title_tag'];
		}
		if ( ! empty( $saved['block_seo_section_blocks'] ) && is_array( $saved['block_seo_section_blocks'] ) ) {
			$out['block_seo_section_blocks'] = $saved['block_seo_section_blocks'];
		}
		return $out;
	}
	if ( $which === 'main_hero' ) {
		foreach ( array( 'block_main_hero_title', 'block_main_hero_text', 'block_main_hero_btn_text', 'block_main_hero_promo_text', 'block_main_hero_person_name', 'block_main_hero_person_position' ) as $k ) {
			if ( isset( $saved[ $k ] ) && trim( (string) $saved[ $k ] ) !== '' ) {
				$out[ $k ] = $saved[ $k ];
			}
		}
		if ( ! empty( $saved['block_main_hero_title_tag'] ) ) {
			$out['block_main_hero_title_tag'] = (string) $saved['block_main_hero_title_tag'];
		}
		if ( isset( $saved['block_main_hero_show_promo'] ) && (string) $saved['block_main_hero_show_promo'] !== '' ) {
			$out['block_main_hero_show_promo'] = (string) $saved['block_main_hero_show_promo'];
		}
		foreach ( array( 'block_main_hero_image', 'block_main_hero_present_image' ) as $img_k ) {
			if ( ! empty( $saved[ $img_k ] ) ) {
				$out[ $img_k ] = (int) $saved[ $img_k ];
			}
		}
		if ( ! empty( $saved['block_main_hero_items'] ) && is_array( $saved['block_main_hero_items'] ) ) {
			$rows = array();
			foreach ( $saved['block_main_hero_items'] as $row ) {
				if ( is_string( $row ) ) {
					$t = trim( $row );
				} elseif ( is_array( $row ) ) {
					$t = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
				} else {
					$t = '';
				}
				if ( $t !== '' ) {
					$rows[] = $t;
				}
			}
			if ( ! empty( $rows ) ) {
				$out['block_main_hero_items'] = $rows;
			}
		}
		return $out;
	}
	return $base;
}

/**
 * @param mixed $saved Raw meta array.
 */
function tolstenko_sc_main_hero_has_custom_content( $saved ) {
	if ( ! is_array( $saved ) ) {
		return false;
	}
	foreach ( array( 'block_main_hero_title', 'block_main_hero_text', 'block_main_hero_btn_text', 'block_main_hero_promo_text', 'block_main_hero_person_name', 'block_main_hero_person_position' ) as $k ) {
		if ( ! empty( $saved[ $k ] ) && trim( (string) $saved[ $k ] ) !== '' ) {
			return true;
		}
	}
	if ( ! empty( $saved['block_main_hero_image'] ) && (int) $saved['block_main_hero_image'] > 0 ) {
		return true;
	}
	if ( ! empty( $saved['block_main_hero_present_image'] ) && (int) $saved['block_main_hero_present_image'] > 0 ) {
		return true;
	}
	if ( isset( $saved['block_main_hero_show_promo'] ) && (string) $saved['block_main_hero_show_promo'] === '1' ) {
		return true;
	}
	if ( ! empty( $saved['block_main_hero_items'] ) && is_array( $saved['block_main_hero_items'] ) ) {
		foreach ( $saved['block_main_hero_items'] as $row ) {
			if ( is_string( $row ) && trim( $row ) !== '' ) {
				return true;
			}
			if ( is_array( $row ) && ! empty( $row['text'] ) && trim( (string) $row['text'] ) !== '' ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * @param mixed $saved Raw meta array.
 */
function tolstenko_sc_seo_section_has_custom_content( $saved ) {
	if ( ! is_array( $saved ) ) {
		return false;
	}
	foreach ( array( 'block_seo_section_title', 'block_seo_section_subtitle', 'block_seo_section_more_text' ) as $k ) {
		if ( ! empty( $saved[ $k ] ) && trim( (string) $saved[ $k ] ) !== '' ) {
			return true;
		}
	}
	if ( ! empty( $saved['block_seo_section_blocks'] ) && is_array( $saved['block_seo_section_blocks'] ) ) {
		foreach ( $saved['block_seo_section_blocks'] as $row ) {
			if ( is_array( $row ) && function_exists( 'tolstenko_normalize_seo_section_row' ) ) {
				$normalized = tolstenko_normalize_seo_section_row( $row );
				if ( $normalized && function_exists( 'tolstenko_is_seo_section_row_empty' ) && ! tolstenko_is_seo_section_row_empty( $normalized ) ) {
					return true;
				}
			}
		}
	}
	return false;
}

/**
 * Скрытый H1 категории услуг.
 *
 * @param WP_Term $term Term.
 * @return string
 */
function tolstenko_sc_get_hidden_h1( $term ) {
	if ( ! ( $term instanceof WP_Term ) ) {
		return '';
	}
	$h1 = trim( (string) get_term_meta( $term->term_id, '_tolstenko_sc_h1', true ) );
	if ( $h1 === '' ) {
		$h1 = trim( (string) $term->name );
	}
	return $h1;
}
