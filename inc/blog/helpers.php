<?php
/**
 * Хелперы single-блога (TOC, картинки, автор, счётчик комментариев).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Атрибуты изображения из ID или ACF-массива.
 *
 * @param mixed $image Attachment ID or ACF image array.
 * @param string $size Image size.
 * @return array{url:string,srcset:string,alt:string,sizes:string,id:int}|null
 */
function tolstenko_get_image_attrs( $image, $size = 'full' ) {
	$id = 0;

	if ( is_numeric( $image ) ) {
		$id = (int) $image;
	} elseif ( is_array( $image ) && ! empty( $image['ID'] ) ) {
		$id = (int) $image['ID'];
	}

	if ( ! $id ) {
		return null;
	}

	$url = wp_get_attachment_image_url( $id, $size );
	if ( ! $url ) {
		return null;
	}

	$alt = (string) get_post_meta( $id, '_wp_attachment_image_alt', true );
	if ( $alt === '' && is_array( $image ) && ! empty( $image['alt'] ) ) {
		$alt = (string) $image['alt'];
	}

	return array(
		'id'     => $id,
		'url'    => $url,
		'srcset' => (string) ( wp_get_attachment_image_srcset( $id, $size ) ?: '' ),
		'sizes'  => (string) ( wp_get_attachment_image_sizes( $id, $size ) ?: '' ),
		'alt'    => $alt,
	);
}

/**
 * Количество curated-комментариев (родители + ответы).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_curated_blog_comments_count( $post_id ) {
	$comments = get_post_meta( $post_id, 'blog_comments', true );
	if ( ! is_array( $comments ) || ! $comments ) {
		return 0;
	}

	$count = 0;
	foreach ( $comments as $comment ) {
		$count++;
		$replies = $comment['replies'] ?? array();
		if ( is_array( $replies ) ) {
			$count += count( $replies );
		}
	}

	return $count;
}

/**
 * Счётчик для stats: кураторские комментарии.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_blog_comments_count( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return 0;
	}

	return tolstenko_get_curated_blog_comments_count( $post_id );
}

/**
 * Добавить кураторский комментарий в meta blog_comments (append, без затирания).
 *
 * @param int   $post_id Target blog/actions post ID.
 * @param array $item    Keys: photo, name, date, time, text; replies optional.
 * @return bool
 */
function tolstenko_append_blog_comment( $post_id, array $item ) {
	$post_id = (int) $post_id;
	if ( ! $post_id || ! get_post( $post_id ) ) {
		return false;
	}

	$row = array(
		'photo'   => (int) ( $item['photo'] ?? 0 ),
		'name'    => sanitize_text_field( (string) ( $item['name'] ?? '' ) ),
		'date'    => sanitize_text_field( (string) ( $item['date'] ?? '' ) ),
		'time'    => sanitize_text_field( (string) ( $item['time'] ?? '' ) ),
		'text'    => sanitize_textarea_field( (string) ( $item['text'] ?? '' ) ),
		'replies' => array(),
	);

	if ( $row['name'] === '' && $row['text'] === '' && ! $row['photo'] ) {
		return false;
	}

	if ( function_exists( 'tolstenko_blog_sanitize_comments' ) ) {
		$sanitized = tolstenko_blog_sanitize_comments( array( $row ) );
		if ( ! $sanitized ) {
			return false;
		}
		$row = $sanitized[0];
	}

	$comments = get_post_meta( $post_id, 'blog_comments', true );
	if ( ! is_array( $comments ) ) {
		$comments = array();
	}
	$comments[] = $row;
	update_post_meta( $post_id, 'blog_comments', $comments );

	return true;
}

/**
 * Автор для .single-blog__director и сайдбара.
 * Приоритет: выбранный в записи → главный автор → шаблон вакансии → legacy-поля.
 *
 * @param int $post_id Post ID.
 * @return array{photo:mixed,name:string,title:string,position:string,description:string,show_quest:bool}
 */
function tolstenko_get_single_blog_director( $post_id = 0 ) {
	$post_id    = $post_id ? (int) $post_id : (int) get_the_ID();
	$quest_raw  = get_post_meta( $post_id, 'single-blog_quest', true );
	$show_quest = ( $quest_raw === '' || $quest_raw === '1' || $quest_raw === 1 || $quest_raw === true );

	$author_index = get_post_meta( $post_id, 'blog_author', true );
	if ( $author_index !== null && $author_index !== '' && $author_index !== false ) {
		$author = tolstenko_get_blog_author_by_index( $author_index );
		if ( is_array( $author ) ) {
			$title = trim( (string) ( $author['job_title'] ?? '' ) );
			if ( $title === '' ) {
				$title = trim( (string) ( $author['position'] ?? '' ) );
			}

			return array(
				'photo'       => ! empty( $author['photo'] ) ? (int) $author['photo'] : null,
				'name'        => trim( (string) ( $author['name'] ?? '' ) ),
				'title'       => $title,
				'position'    => trim( (string) ( $author['position'] ?? '' ) ),
				'description' => trim( (string) ( $author['description'] ?? '' ) ),
				'show_quest'  => $show_quest,
			);
		}
	}

	// Главный автор из «Настройки сайта → Авторы статей».
	if ( function_exists( 'tolstenko_get_blog_main_author' ) ) {
		$main = tolstenko_get_blog_main_author();
		if ( is_array( $main ) ) {
			$title = trim( (string) ( $main['job_title'] ?? '' ) );
			if ( $title === '' ) {
				$title = trim( (string) ( $main['position'] ?? '' ) );
			}

			return array(
				'photo'       => ! empty( $main['photo'] ) ? (int) $main['photo'] : null,
				'name'        => trim( (string) ( $main['name'] ?? '' ) ),
				'title'       => $title,
				'position'    => trim( (string) ( $main['position'] ?? '' ) ),
				'description' => trim( (string) ( $main['description'] ?? '' ) ),
				'show_quest'  => $show_quest,
			);
		}
	}

	// Fallback: автор по умолчанию из шаблона вакансии.
	$defaults     = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'vacancy_content' )
		: array();
	$author_index = (string) ( $defaults['sidebar_author'] ?? '' );
	$author       = tolstenko_get_blog_author_by_index( $author_index );
	if ( is_array( $author ) ) {
		return array(
			'photo'       => ! empty( $author['photo'] ) ? (int) $author['photo'] : null,
			'name'        => trim( (string) ( $author['name'] ?? '' ) ),
			'title'       => trim( (string) ( $author['job_title'] ?? $author['position'] ?? '' ) ),
			'position'    => trim( (string) ( $author['position'] ?? '' ) ),
			'description' => trim( (string) ( $author['description'] ?? '' ) ),
			'show_quest'  => $show_quest,
		);
	}

	// Legacy: старые ручные поля шаблона вакансии.
	$photo_id = (int) ( $defaults['sidebar_photo'] ?? 0 );

	return array(
		'photo'       => $photo_id ?: null,
		'name'        => trim( (string) ( $defaults['sidebar_name'] ?? '' ) ),
		'title'       => '',
		'position'    => '',
		'description' => trim( (string) ( $defaults['sidebar_text'] ?? '' ) ),
		'show_quest'  => $show_quest,
	);
}

/**
 * Собирает иерархический TOC (h2 → пункты 1..n, h3 → вложенные) и проставляет id.
 *
 * @param string $html Content HTML.
 * @return array{html:string,items:array<int,array{id:string,text:string,level:int,children:array}>}
 */
function tolstenko_prepare_blog_toc( $html ) {
	$html        = (string) $html;
	$toc_items   = array();
	$used_toc_ids = array();

	if ( $html === '' ) {
		return array(
			'html'  => $html,
			'items' => $toc_items,
		);
	}

	$has_h2 = (bool) preg_match( '/<h2\b/i', $html );
	$has_h3 = (bool) preg_match( '/<h3\b/i', $html );

	if ( ! $has_h2 && ! $has_h3 ) {
		return array(
			'html'  => $html,
			'items' => $toc_items,
		);
	}

	// Оба уровня, если есть h2; иначе только h3 как верхний уровень.
	$heading_pattern = $has_h2
		? '/<h([23])([^>]*)>(.*?)<\/h\1>/isu'
		: '/<h(3)([^>]*)>(.*?)<\/h\1>/isu';

	$html = preg_replace_callback(
		$heading_pattern,
		function ( $matches ) use ( &$toc_items, &$used_toc_ids, $has_h2 ) {
			$level = (int) $matches[1];
			$attrs = (string) $matches[2];
			$body  = (string) $matches[3];
			$text  = trim( wp_strip_all_tags( $body ) );

			if ( $text === '' ) {
				return $matches[0];
			}

			$base_id = sanitize_title( $text );
			if ( $base_id === '' ) {
				$base_id = 'section';
			}

			$unique_id = $base_id;
			$suffix    = 2;
			while ( isset( $used_toc_ids[ $unique_id ] ) ) {
				$unique_id = $base_id . '-' . $suffix;
				$suffix++;
			}
			$used_toc_ids[ $unique_id ] = true;

			$entry = array(
				'id'       => $unique_id,
				'text'     => $text,
				'level'    => $level,
				'children' => array(),
			);

			if ( $has_h2 && 3 === $level ) {
				$last = count( $toc_items ) - 1;
				if ( $last >= 0 && (int) $toc_items[ $last ]['level'] === 2 ) {
					$toc_items[ $last ]['children'][] = $entry;
				} else {
					// h3 до первого h2 — как верхний пункт.
					$toc_items[] = $entry;
				}
			} else {
				$toc_items[] = $entry;
			}

			if ( preg_match( '/\sid=(["\']).*?\1/i', $attrs ) ) {
				return $matches[0];
			}

			return '<h' . $level . $attrs . ' id="' . esc_attr( $unique_id ) . '">' . $body . '</h' . $level . '>';
		},
		$html
	);

	return array(
		'html'  => is_string( $html ) ? $html : '',
		'items' => $toc_items,
	);
}
