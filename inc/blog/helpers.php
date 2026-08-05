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
 * Автор для .single-blog__director и сайдбара.
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
 * Собирает TOC и проставляет id заголовкам в HTML (h2, иначе h3).
 *
 * @param string $html Content HTML.
 * @return array{html:string,items:array<int,array{id:string,text:string,level:int}>}
 */
function tolstenko_prepare_blog_toc( $html ) {
	$html = (string) $html;
	$toc_items = array();
	$used_toc_ids = array();
	$toc_heading_level = 0;

	if ( $html === '' ) {
		return array(
			'html'  => $html,
			'items' => $toc_items,
		);
	}

	if ( preg_match( '/<h2\b/i', $html ) ) {
		$toc_heading_level = 2;
	} elseif ( preg_match( '/<h3\b/i', $html ) ) {
		$toc_heading_level = 3;
	}

	if ( ! $toc_heading_level ) {
		return array(
			'html'  => $html,
			'items' => $toc_items,
		);
	}

	$heading_pattern = '/<h(' . $toc_heading_level . ')([^>]*)>(.*?)<\/h\1>/isu';

	$html = preg_replace_callback(
		$heading_pattern,
		function ( $matches ) use ( &$toc_items, &$used_toc_ids ) {
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

			$toc_items[] = array(
				'id'    => $unique_id,
				'text'  => $text,
				'level' => $level,
			);

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
