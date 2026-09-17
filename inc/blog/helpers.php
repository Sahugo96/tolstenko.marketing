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
		if ( ! is_array( $comment ) ) {
			continue;
		}
		$count++;
		$replies = $comment['replies'] ?? array();
		if ( ! is_array( $replies ) ) {
			continue;
		}
		foreach ( $replies as $reply ) {
			if ( ! is_array( $reply ) ) {
				continue;
			}
			$count++;
			$nested = $reply['replies'] ?? array();
			if ( is_array( $nested ) ) {
				$count += count( $nested );
			}
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
 * Email комментатора (на сайте не показывается).
 *
 * @param mixed $email Raw email.
 * @return string
 */
function tolstenko_blog_comment_sanitize_email( $email ) {
	$email = sanitize_email( (string) $email );
	return is_email( $email ) ? $email : '';
}

/**
 * Стабильный id комментария (не индекс массива).
 *
 * @return string
 */
function tolstenko_blog_comment_generate_id() {
	try {
		return 'cmt_' . bin2hex( random_bytes( 8 ) );
	} catch ( Exception $e ) {
		return 'cmt_' . strtolower( wp_generate_password( 16, false, false ) );
	}
}

/**
 * @param mixed $id Raw id.
 * @return string
 */
function tolstenko_blog_comment_normalize_id( $id ) {
	$id = strtolower( trim( (string) $id ) );
	$id = preg_replace( '/[^a-z0-9_]/', '', $id );
	if ( ! is_string( $id ) || $id === '' || strpos( $id, 'cmt_' ) !== 0 || strlen( $id ) < 8 ) {
		return '';
	}
	return $id;
}

/**
 * @param mixed $raw  Raw id.
 * @param array $used Used ids (key = id).
 * @return string
 */
function tolstenko_blog_comment_unique_id( $raw, array &$used ) {
	$id = tolstenko_blog_comment_normalize_id( $raw );
	if ( $id === '' || isset( $used[ $id ] ) ) {
		$id = tolstenko_blog_comment_generate_id();
		while ( isset( $used[ $id ] ) ) {
			$id = tolstenko_blog_comment_generate_id();
		}
	}
	$used[ $id ] = true;
	return $id;
}

/**
 * @param array $comments Comments list.
 * @return array{comments:array,changed:bool}
 */
function tolstenko_blog_ensure_comment_ids( array $comments ) {
	$used    = array();
	$changed = false;
	$out     = array();

	foreach ( $comments as $comment ) {
		if ( ! is_array( $comment ) ) {
			continue;
		}
		$old_id        = (string) ( $comment['id'] ?? '' );
		$comment['id'] = tolstenko_blog_comment_unique_id( $old_id, $used );
		if ( $comment['id'] !== $old_id ) {
			$changed = true;
		}

		$replies_out = array();
		$replies     = isset( $comment['replies'] ) && is_array( $comment['replies'] ) ? $comment['replies'] : array();
		foreach ( $replies as $reply ) {
			if ( ! is_array( $reply ) ) {
				continue;
			}
			$old_rid     = (string) ( $reply['id'] ?? '' );
			$reply['id'] = tolstenko_blog_comment_unique_id( $old_rid, $used );
			if ( $reply['id'] !== $old_rid ) {
				$changed = true;
			}

			$nested_out = array();
			$nested     = isset( $reply['replies'] ) && is_array( $reply['replies'] ) ? $reply['replies'] : array();
			foreach ( $nested as $nested_item ) {
				if ( ! is_array( $nested_item ) ) {
					continue;
				}
				$old_nid           = (string) ( $nested_item['id'] ?? '' );
				$nested_item['id'] = tolstenko_blog_comment_unique_id( $old_nid, $used );
				if ( $nested_item['id'] !== $old_nid ) {
					$changed = true;
				}
				unset( $nested_item['replies'], $nested_item['reply_to'] );
				$nested_out[] = $nested_item;
			}
			$reply['replies'] = $nested_out;
			$replies_out[]    = $reply;
		}

		$migrated = tolstenko_blog_nest_flat_reply_to( $comment['id'], $replies_out );
		if ( $migrated['changed'] ) {
			$changed     = true;
			$replies_out = $migrated['replies'];
		}

		$comment['replies'] = $replies_out;
		$out[]              = $comment;
	}

	return array(
		'comments' => $out,
		'changed'  => $changed,
	);
}

/**
 * Плоские reply_to (старый формат) → replies[] у ответа первого уровня.
 *
 * @param string $root_id Root comment id.
 * @param array  $replies First-level replies.
 * @return array{replies:array,changed:bool}
 */
function tolstenko_blog_nest_flat_reply_to( $root_id, array $replies ) {
	$root_id = tolstenko_blog_comment_normalize_id( $root_id );
	$anchors = array();
	$pending = array();
	$changed = false;

	foreach ( $replies as $reply ) {
		if ( ! is_array( $reply ) ) {
			continue;
		}
		$reply_to = tolstenko_blog_comment_normalize_id( $reply['reply_to'] ?? '' );
		unset( $reply['reply_to'] );
		if ( $reply_to !== '' && $reply_to !== $root_id ) {
			$pending[] = array(
				'reply' => $reply,
				'to'    => $reply_to,
			);
			$changed   = true;
		} else {
			if ( $reply_to !== '' ) {
				$changed = true;
			}
			$anchors[] = $reply;
		}
	}

	$by_id = array();
	foreach ( $anchors as $index => $anchor ) {
		$aid = tolstenko_blog_comment_normalize_id( $anchor['id'] ?? '' );
		if ( $aid !== '' ) {
			$by_id[ $aid ] = $index;
		}
	}

	foreach ( $pending as $item ) {
		$target = $item['to'];
		if ( isset( $by_id[ $target ] ) ) {
			$idx = $by_id[ $target ];
			if ( ! isset( $anchors[ $idx ]['replies'] ) || ! is_array( $anchors[ $idx ]['replies'] ) ) {
				$anchors[ $idx ]['replies'] = array();
			}
			unset( $item['reply']['replies'] );
			$anchors[ $idx ]['replies'][] = $item['reply'];
		} else {
			$anchors[] = $item['reply'];
		}
	}

	return array(
		'replies' => $anchors,
		'changed' => $changed,
	);
}

/**
 * @param int $post_id Post ID.
 * @return array
 */
function tolstenko_blog_ensure_post_comment_ids( $post_id ) {
	$post_id  = (int) $post_id;
	$comments = $post_id ? get_post_meta( $post_id, 'blog_comments', true ) : array();
	if ( ! is_array( $comments ) ) {
		$comments = array();
	}

	$ensured = tolstenko_blog_ensure_comment_ids( $comments );
	if ( $post_id && $ensured['changed'] ) {
		update_post_meta( $post_id, 'blog_comments', $ensured['comments'] );
	}

	return $ensured['comments'];
}

/**
 * Найти корневой комментарий или ответ в его ветке.
 *
 * @param array  $comments Comments.
 * @param string $id       Comment id.
 * @return array{kind:string,depth:int,root_index:int,reply_index:?int,nested_index:?int,comment:array,root:array}|null
 */
function tolstenko_blog_find_comment( array $comments, $id ) {
	$id = tolstenko_blog_comment_normalize_id( $id );
	if ( $id === '' ) {
		return null;
	}
	foreach ( $comments as $index => $comment ) {
		if ( ! is_array( $comment ) ) {
			continue;
		}
		if ( tolstenko_blog_comment_normalize_id( $comment['id'] ?? '' ) === $id ) {
			return array(
				'kind'         => 'root',
				'depth'        => 0,
				'root_index'   => (int) $index,
				'reply_index'  => null,
				'nested_index' => null,
				'comment'      => $comment,
				'root'         => $comment,
			);
		}
		$replies = isset( $comment['replies'] ) && is_array( $comment['replies'] ) ? $comment['replies'] : array();
		foreach ( $replies as $ri => $reply ) {
			if ( ! is_array( $reply ) ) {
				continue;
			}
			if ( tolstenko_blog_comment_normalize_id( $reply['id'] ?? '' ) === $id ) {
				return array(
					'kind'         => 'reply',
					'depth'        => 1,
					'root_index'   => (int) $index,
					'reply_index'  => (int) $ri,
					'nested_index' => null,
					'comment'      => $reply,
					'root'         => $comment,
				);
			}
			$nested = isset( $reply['replies'] ) && is_array( $reply['replies'] ) ? $reply['replies'] : array();
			foreach ( $nested as $ni => $nested_item ) {
				if ( ! is_array( $nested_item ) ) {
					continue;
				}
				if ( tolstenko_blog_comment_normalize_id( $nested_item['id'] ?? '' ) === $id ) {
					return array(
						'kind'         => 'nested',
						'depth'        => 2,
						'root_index'   => (int) $index,
						'reply_index'  => (int) $ri,
						'nested_index' => (int) $ni,
						'comment'      => $nested_item,
						'root'         => $comment,
					);
				}
			}
		}
	}
	return null;
}

/**
 * @param array  $comments Comments.
 * @param string $id       Comment id.
 * @return array{index:int,comment:array}|null
 */
function tolstenko_blog_find_root_comment( array $comments, $id ) {
	$found = tolstenko_blog_find_comment( $comments, $id );
	if ( ! $found || $found['kind'] !== 'root' ) {
		return null;
	}
	return array(
		'index'   => $found['root_index'],
		'comment' => $found['comment'],
	);
}

/**
 * Комментарий по id: корень или ответ в ветке.
 *
 * @param int    $post_id Post ID.
 * @param string $id      Comment id.
 * @return array|null
 */
function tolstenko_blog_get_comment( $post_id, $id ) {
	$post_id  = (int) $post_id;
	$comments = $post_id ? get_post_meta( $post_id, 'blog_comments', true ) : array();
	if ( ! is_array( $comments ) ) {
		$comments = array();
	}
	$found = tolstenko_blog_find_comment( $comments, $id );
	return $found ? $found['comment'] : null;
}

/**
 * @param int    $post_id Post ID.
 * @param string $id      Comment id.
 * @return array|null
 */
function tolstenko_blog_get_root_comment( $post_id, $id ) {
	return tolstenko_blog_get_comment( $post_id, $id );
}

/**
 * Добавить кураторский комментарий в meta blog_comments (append, без затирания).
 *
 * @param int    $post_id   Target blog/actions post ID.
 * @param array  $item      Keys: photo, name, date, time, text, id; replies optional.
 * @param string $parent_id Id корня или ответа в ветке (empty = новый корень).
 * @return bool
 */
function tolstenko_append_blog_comment( $post_id, array $item, $parent_id = '' ) {
	$post_id = (int) $post_id;
	if ( ! $post_id || ! get_post( $post_id ) ) {
		return false;
	}

	$row = array(
		'id'      => (string) ( $item['id'] ?? '' ),
		'photo'   => (int) ( $item['photo'] ?? 0 ),
		'name'    => sanitize_text_field( (string) ( $item['name'] ?? '' ) ),
		'email'   => tolstenko_blog_comment_sanitize_email( $item['email'] ?? '' ),
		'date'    => sanitize_text_field( (string) ( $item['date'] ?? '' ) ),
		'time'    => sanitize_text_field( (string) ( $item['time'] ?? '' ) ),
		'text'    => sanitize_textarea_field( (string) ( $item['text'] ?? '' ) ),
		'replies' => array(),
	);

	if ( $row['name'] === '' && $row['text'] === '' && ! $row['photo'] ) {
		return false;
	}

	$comments = get_post_meta( $post_id, 'blog_comments', true );
	if ( ! is_array( $comments ) ) {
		$comments = array();
	}
	$ensured  = tolstenko_blog_ensure_comment_ids( $comments );
	$comments = $ensured['comments'];

	$used = array();
	foreach ( $comments as $existing ) {
		if ( ! is_array( $existing ) ) {
			continue;
		}
		$eid = tolstenko_blog_comment_normalize_id( $existing['id'] ?? '' );
		if ( $eid !== '' ) {
			$used[ $eid ] = true;
		}
		$existing_replies = isset( $existing['replies'] ) && is_array( $existing['replies'] ) ? $existing['replies'] : array();
		foreach ( $existing_replies as $existing_reply ) {
			if ( ! is_array( $existing_reply ) ) {
				continue;
			}
			$rid = tolstenko_blog_comment_normalize_id( $existing_reply['id'] ?? '' );
			if ( $rid !== '' ) {
				$used[ $rid ] = true;
			}
			$existing_nested = isset( $existing_reply['replies'] ) && is_array( $existing_reply['replies'] ) ? $existing_reply['replies'] : array();
			foreach ( $existing_nested as $existing_nested_item ) {
				if ( ! is_array( $existing_nested_item ) ) {
					continue;
				}
				$nid = tolstenko_blog_comment_normalize_id( $existing_nested_item['id'] ?? '' );
				if ( $nid !== '' ) {
					$used[ $nid ] = true;
				}
			}
		}
	}
	$row['id'] = tolstenko_blog_comment_unique_id( $row['id'], $used );

	if ( function_exists( 'tolstenko_blog_sanitize_comments' ) ) {
		$sanitized = tolstenko_blog_sanitize_comments( array( $row ) );
		if ( ! $sanitized ) {
			return false;
		}
		$row = $sanitized[0];
	}

	$parent_id = tolstenko_blog_comment_normalize_id( $parent_id );
	if ( $parent_id !== '' ) {
		$found = tolstenko_blog_find_comment( $comments, $parent_id );
		if ( ! $found || (int) ( $found['depth'] ?? 0 ) >= 2 ) {
			return false;
		}
		if ( $found['kind'] === 'reply' ) {
			unset( $row['replies'] );
			$ri = (int) $found['reply_index'];
			if ( ! isset( $comments[ $found['root_index'] ]['replies'][ $ri ] ) || ! is_array( $comments[ $found['root_index'] ]['replies'][ $ri ] ) ) {
				return false;
			}
			if ( ! isset( $comments[ $found['root_index'] ]['replies'][ $ri ]['replies'] ) || ! is_array( $comments[ $found['root_index'] ]['replies'][ $ri ]['replies'] ) ) {
				$comments[ $found['root_index'] ]['replies'][ $ri ]['replies'] = array();
			}
			$comments[ $found['root_index'] ]['replies'][ $ri ]['replies'][] = $row;
		} else {
			if ( ! isset( $row['replies'] ) || ! is_array( $row['replies'] ) ) {
				$row['replies'] = array();
			}
			$comments[ $found['root_index'] ]['replies'][] = $row;
		}
	} else {
		$comments[] = $row;
	}

	update_post_meta( $post_id, 'blog_comments', $comments );

	return (string) ( $row['id'] ?? '' ) !== '' ? (string) $row['id'] : true;
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
 * Транслит кириллицы в латиницу для якорей TOC.
 *
 * @param string $text Heading text.
 * @return string
 */
function tolstenko_transliterate_slug( $text ) {
	$map = array(
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z',
		'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
		'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shh',
		'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z',
		'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r',
		'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shh',
		'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
		'І' => 'I', 'Ї' => 'Yi', 'Є' => 'Ye', 'Ґ' => 'G',
		'і' => 'i', 'ї' => 'yi', 'є' => 'ye', 'ґ' => 'g',
	);

	$slug = sanitize_title( strtr( (string) $text, $map ) );

	return $slug !== '' ? $slug : 'section';
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

			$base_id   = tolstenko_transliterate_slug( $text );
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

			$attrs = preg_replace( '/\s+id=(["\']).*?\1/i', '', $attrs );

			return '<h' . $level . $attrs . ' id="' . esc_attr( $unique_id ) . '">' . $body . '</h' . $level . '>';
		},
		$html
	);

	return array(
		'html'  => is_string( $html ) ? $html : '',
		'items' => $toc_items,
	);
}
