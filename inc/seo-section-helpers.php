<?php
/**
 * Хелперы блока «SEO продвижение»: раскладки гибкого содержимого и нормализация строк.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Раскладки блока: layout → партиал в template-parts/blocks/seo-section/.
 * Ключи совпадают с acf_fc_layout, чтобы контент из ACF читался без миграции.
 *
 * @return array<string,string>
 */
function tolstenko_get_seo_section_layouts() {
	return array(
		'image_text'  => 'image-text',
		'text_image'  => 'image-text',
		'two_columns' => 'two-columns',
		'gallery'     => 'gallery',
		'text'        => 'text',
		'redactor'    => 'redactor',
	);
}

/**
 * Подписи раскладок для админки и редактора.
 *
 * @return array<string,string>
 */
function tolstenko_get_seo_section_layout_labels() {
	return array(
		'image_text'  => __( 'Фото + текст', 'tolstenko-theme' ),
		'text_image'  => __( 'Текст + фото', 'tolstenko-theme' ),
		'two_columns' => __( 'Две колонки', 'tolstenko-theme' ),
		'gallery'     => __( 'Галерея', 'tolstenko-theme' ),
		'text'        => __( 'Текст', 'tolstenko-theme' ),
		'redactor'    => __( 'Редактор (HTML)', 'tolstenko-theme' ),
	);
}

/**
 * Список ID вложений из массива/строки.
 *
 * @param mixed $raw ID через запятую или массив ID/объектов вложений.
 * @return int[]
 */
function tolstenko_seo_section_attachment_ids( $raw ) {
	if ( is_string( $raw ) ) {
		$raw = preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$ids = array();
	foreach ( $raw as $item ) {
		if ( is_array( $item ) ) {
			$item = $item['ID'] ?? ( $item['id'] ?? 0 );
		}
		$id = (int) $item;
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Нормализовать строку гибкого содержимого блока.
 * Пустые строки (без текста, изображений и HTML) отбрасываются вызывающим кодом.
 *
 * @param mixed $row Строка из атрибутов блока или дефолтов.
 * @return array{layout:string,partial:string,reverse:bool,title:string,title_center:bool,image:int,text:string,btn_text:string,btn_url:string,btn_wide:bool,columns:string[],items:array<int,array{title:string,text:string}>,gallery:int[],redactor:string}|null
 */
function tolstenko_normalize_seo_section_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}

	$layouts = tolstenko_get_seo_section_layouts();
	$layout  = (string) ( $row['layout'] ?? ( $row['acf_fc_layout'] ?? '' ) );
	if ( ! isset( $layouts[ $layout ] ) ) {
		return null;
	}

	$columns = array();
	foreach ( (array) ( $row['columns'] ?? array() ) as $column ) {
		$columns[] = (string) ( is_array( $column ) ? ( $column['text'] ?? '' ) : $column );
	}

	$items = array();
	foreach ( (array) ( $row['items'] ?? array() ) as $item ) {
		if ( ! is_array( $item ) ) {
			$item = array( 'text' => (string) $item );
		}
		$item_title = trim( (string) ( $item['title'] ?? '' ) );
		$item_text  = (string) ( $item['text'] ?? '' );
		if ( $item_title === '' && trim( wp_strip_all_tags( $item_text ) ) === '' ) {
			continue;
		}
		$items[] = array(
			'title' => $item_title,
			'text'  => $item_text,
		);
	}

	return array(
		'layout'       => $layout,
		'partial'      => $layouts[ $layout ],
		'reverse'      => $layout === 'text_image',
		'title'        => trim( (string) ( $row['title'] ?? '' ) ),
		'title_center' => ! empty( $row['title_center'] ),
		'image'        => (int) ( $row['image'] ?? 0 ),
		'text'         => (string) ( $row['text'] ?? '' ),
		'btn_text'     => trim( (string) ( $row['btn_text'] ?? '' ) ),
		'btn_url'      => trim( (string) ( $row['btn_url'] ?? '' ) ),
		'btn_wide'     => ! empty( $row['btn_wide'] ),
		'columns'      => $columns,
		'items'        => $items,
		'gallery'      => tolstenko_seo_section_attachment_ids( $row['gallery'] ?? array() ),
		'redactor'     => (string) ( $row['redactor'] ?? '' ),
	);
}

/**
 * Пустая ли строка гибкого содержимого (нечего выводить).
 *
 * @param array $row Нормализованная строка.
 * @return bool
 */
function tolstenko_is_seo_section_row_empty( array $row ) {
	if ( $row['title'] !== '' || $row['image'] > 0 || ! empty( $row['gallery'] ) || ! empty( $row['items'] ) ) {
		return false;
	}
	$text = $row['text'] . ' ' . $row['redactor'] . ' ' . implode( ' ', $row['columns'] );
	return trim( wp_strip_all_tags( $text ) ) === '';
}

/**
 * Нормализовать гибкое содержимое блока целиком.
 *
 * @param mixed $rows Строки из атрибутов блока или дефолтов.
 * @return array<int,array>
 */
function tolstenko_normalize_seo_section_blocks( $rows ) {
	if ( ! is_array( $rows ) ) {
		return array();
	}
	$out = array();
	foreach ( $rows as $row ) {
		$normalized = tolstenko_normalize_seo_section_row( $row );
		if ( null === $normalized || tolstenko_is_seo_section_row_empty( $normalized ) ) {
			continue;
		}
		$out[] = $normalized;
	}
	return $out;
}

/**
 * Строка блока в партиале: нормализованные данные из $args.
 *
 * @param array $args Аргументы get_template_part().
 * @return array|null
 */
function tolstenko_get_seo_section_partial_row( $args ) {
	if ( ! is_array( $args ) || ! isset( $args['block'] ) || ! is_array( $args['block'] ) ) {
		return null;
	}
	return $args['block'];
}

/**
 * Разметка заголовка строки блока.
 *
 * @param array $row Нормализованная строка.
 * @return void
 */
function tolstenko_seo_section_block_title( array $row ) {
	if ( $row['title'] === '' ) {
		return;
	}
	$classes = 'seo-section__block-title h3';
	if ( ! empty( $row['title_center'] ) ) {
		$classes .= ' seo-section__block-title--center';
	}
	printf(
		'<h3 class="%1$s">%2$s</h3>',
		esc_attr( $classes ),
		tolstenko_kses_html( $row['title'] ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses.
	);
}

/**
 * Санитизация гибкого содержимого для дефолтов и атрибутов блока.
 *
 * @param mixed $raw Строки из POST или атрибутов Gutenberg.
 * @return array<int,array<string,mixed>>
 */
function tolstenko_sanitize_seo_section_blocks_raw( $raw ) {
	if ( ! is_array( $raw ) ) {
		return array();
	}

	$allowed_layouts = array_keys( tolstenko_get_seo_section_layouts() );
	$out             = array();

	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}

		$layout = sanitize_key( (string) ( $row['layout'] ?? ( $row['acf_fc_layout'] ?? '' ) ) );
		if ( ! in_array( $layout, $allowed_layouts, true ) ) {
			continue;
		}

		$columns = array();
		foreach ( (array) ( $row['columns'] ?? array() ) as $column ) {
			$columns[] = tolstenko_kses_redactor( is_array( $column ) ? ( $column['text'] ?? '' ) : $column );
		}
		while ( count( $columns ) < 2 ) {
			$columns[] = '';
		}

		$items = array();
		foreach ( (array) ( $row['items'] ?? array() ) as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$item_title = sanitize_text_field( $item['title'] ?? '' );
			$item_text  = tolstenko_kses_redactor( $item['text'] ?? '' );
			if ( $item_title === '' && trim( wp_strip_all_tags( $item_text ) ) === '' ) {
				continue;
			}
			$items[] = array(
				'title' => $item_title,
				'text'  => $item_text,
			);
		}

		$sanitized = array(
			'layout'       => $layout,
			'title'        => sanitize_text_field( $row['title'] ?? '' ),
			'title_center' => ! empty( $row['title_center'] ),
			'image'        => (int) ( $row['image'] ?? 0 ),
			'text'         => tolstenko_kses_redactor( $row['text'] ?? '' ),
			'btn_text'     => sanitize_text_field( $row['btn_text'] ?? '' ),
			'btn_url'      => esc_url_raw( $row['btn_url'] ?? '' ),
			'btn_wide'     => ! empty( $row['btn_wide'] ),
			'columns'      => $columns,
			'items'        => $items,
			'gallery'      => tolstenko_seo_section_attachment_ids( $row['gallery'] ?? array() ),
			'redactor'     => tolstenko_kses_redactor( $row['redactor'] ?? '' ),
		);

		$normalized = tolstenko_normalize_seo_section_row( $sanitized );
		if ( null === $normalized || tolstenko_is_seo_section_row_empty( $normalized ) ) {
			continue;
		}

		$out[] = $sanitized;
	}

	return $out;
}
