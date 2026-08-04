<?php
/**
 * Хелперы блока отзывов: постер видео, парсинг embed, выборка по типам.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Прочитать поле отзыва из post meta (без ACF).
 * Для image-ключей возвращает array{url,alt,ID} или пустой array.
 * Для review_contact — array{url,title,target}.
 *
 * @param string $key     Meta key.
 * @param int    $post_id Post ID.
 * @return mixed
 */
function tolstenko_get_review_field( $key, $post_id ) {
	$post_id = (int) $post_id;
	$val     = get_post_meta( $post_id, $key, true );

	$image_keys = array( 'review_thenks', 'review_logo', 'review_photo', 'review_screen', 'review_thanks_image', 'review_preview_image' );
	if ( in_array( $key, $image_keys, true ) ) {
		if ( is_array( $val ) && ! empty( $val['url'] ) ) {
			return $val;
		}
		$id = 0;
		if ( is_numeric( $val ) ) {
			$id = (int) $val;
		} elseif ( is_array( $val ) && ! empty( $val['ID'] ) ) {
			$id = (int) $val['ID'];
		}
		if ( $id > 0 ) {
			$url = wp_get_attachment_image_url( $id, 'full' );
			if ( $url ) {
				return array(
					'ID'  => $id,
					'url' => $url,
					'alt' => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
				);
			}
		}
		return array();
	}

	if ( $key === 'review_contact' ) {
		return is_array( $val ) ? $val : array();
	}

	if ( $key === 'review_case' && is_array( $val ) && ! empty( $val['url'] ) ) {
		return (string) $val['url'];
	}

	return $val;
}

/**
 * @param mixed $image ACF image array|ID|null.
 * @return array{url:string,alt:string}
 */
function tolstenko_review_image_attrs( $image ) {
	if ( is_array( $image ) && ! empty( $image['url'] ) ) {
		return array(
			'url' => (string) $image['url'],
			'alt' => (string) ( $image['alt'] ?? '' ),
		);
	}
	if ( is_numeric( $image ) && (int) $image > 0 ) {
		$id  = (int) $image;
		$url = wp_get_attachment_image_url( $id, 'full' );
		if ( $url ) {
			return array(
				'url' => $url,
				'alt' => (string) get_post_meta( $id, '_wp_attachment_image_alt', true ),
			);
		}
	}
	return array( 'url' => '', 'alt' => '' );
}

function tolstenko_get_rutube_video_id( $url ) {
	$url = (string) $url;
	if ( preg_match( '#rutube\.ru/(?:play/embed|video)/([a-zA-Z0-9_-]+)#', $url, $matches ) ) {
		return $matches[1];
	}
	return '';
}

/**
 * Извлечь URL embed из iframe-кода или чистого URL.
 *
 * @param string $raw
 * @return string
 */
function tolstenko_parse_video_embed_src( $raw ) {
	$video = html_entity_decode( trim( (string) $raw ), ENT_QUOTES, 'UTF-8' );
	if ( $video === '' ) {
		return '';
	}
	if ( preg_match( '/src=(["\'])([^"\']+)\1/i', $video, $matches ) ) {
		return html_entity_decode( $matches[2], ENT_QUOTES, 'UTF-8' );
	}
	if ( preg_match( '#https?://[^\s<>"\']+#', $video, $matches ) ) {
		return $matches[0];
	}
	return '';
}

/**
 * Получить JSON-ответ удалённого API с проверкой всех этапов запроса.
 *
 * @param string $url  URL.
 * @param array  $args Аргументы wp_remote_get().
 * @return array|WP_Error Декодированный ответ или ошибка.
 */
function tolstenko_fetch_remote_json( $url, $args = array() ) {
	$response = wp_remote_get( $url, $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		return new WP_Error(
			'tolstenko_remote_http_error',
			sprintf( 'HTTP %d от %s', $code, $url ),
			array( 'status' => $code )
		);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_Error( 'tolstenko_remote_json_error', json_last_error_msg() );
	}
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'tolstenko_remote_json_shape', 'Ожидался JSON-объект.' );
	}

	return $data;
}

function tolstenko_get_video_embed_poster( $embed_url ) {
	$embed_url = (string) $embed_url;
	if ( $embed_url === '' ) {
		return '';
	}

	$request_args = array(
		'timeout' => 8,
		'headers' => array(
			'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'Accept'     => 'application/json',
		),
	);

	$rutube_id = tolstenko_get_rutube_video_id( $embed_url );
	if ( $rutube_id !== '' ) {
		$cache_key = 'tolstenko_rutube_poster_' . $rutube_id;
		$cached    = get_transient( $cache_key );
		if ( is_string( $cached ) && $cached !== '' ) {
			return $cached;
		}
		if ( get_transient( $cache_key . '_failed' ) ) {
			return '';
		}

		$data = tolstenko_fetch_remote_json( 'https://rutube.ru/api/video/' . $rutube_id . '/', $request_args );
		if ( is_wp_error( $data ) ) {
			tolstenko_log_error( 'tolstenko_get_video_embed_poster', 'Rutube API недоступен', $data );
		} else {
			$poster = (string) ( $data['thumbnail_url'] ?? '' );
			if ( $poster !== '' ) {
				set_transient( $cache_key, $poster, WEEK_IN_SECONDS );
				return $poster;
			}
			tolstenko_log_error( 'tolstenko_get_video_embed_poster', 'В ответе Rutube API нет thumbnail_url', $rutube_id );
		}

		$oembed_url = add_query_arg(
			'url',
			'https://rutube.ru/video/' . $rutube_id . '/',
			'https://rutube.ru/api/oembed/'
		);
		$oembed_data = tolstenko_fetch_remote_json( $oembed_url, $request_args );
		if ( is_wp_error( $oembed_data ) ) {
			tolstenko_log_error( 'tolstenko_get_video_embed_poster', 'Rutube oEmbed недоступен', $oembed_data );
		} else {
			$poster = (string) ( $oembed_data['thumbnail_url'] ?? '' );
			if ( $poster !== '' ) {
				set_transient( $cache_key, $poster, WEEK_IN_SECONDS );
				return $poster;
			}
			tolstenko_log_error( 'tolstenko_get_video_embed_poster', 'В ответе Rutube oEmbed нет thumbnail_url', $rutube_id );
		}

		// Негативное кеширование: не дёргать недоступный API на каждый просмотр страницы.
		set_transient( $cache_key . '_failed', 1, 10 * MINUTE_IN_SECONDS );
	}

	if ( preg_match( '#(?:youtube\.com/embed/|youtu\.be/)([a-zA-Z0-9_-]+)#', $embed_url, $matches ) ) {
		return 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
	}

	return '';
}

function tolstenko_ajax_video_poster() {
	$src = isset( $_GET['src'] ) ? esc_url_raw( wp_unslash( $_GET['src'] ) ) : '';
	if ( $src === '' || ! wp_http_validate_url( $src ) ) {
		wp_send_json_error(
			array( 'message' => __( 'Некорректный адрес видео.', 'tolstenko-theme' ) ),
			400
		);
	}

	$poster = tolstenko_get_video_embed_poster( $src );
	if ( $poster === '' ) {
		wp_send_json_error(
			array( 'message' => __( 'Не удалось получить постер видео.', 'tolstenko-theme' ) ),
			502
		);
	}

	wp_send_json_success( array( 'poster' => $poster ) );
}
add_action( 'wp_ajax_tolstenko_video_poster', 'tolstenko_ajax_video_poster' );
add_action( 'wp_ajax_nopriv_tolstenko_video_poster', 'tolstenko_ajax_video_poster' );

/**
 * Отзывы по ID (порядок как в массиве). Пустой список — все опубликованные.
 *
 * @param int[] $post_ids
 * @return array{thanks:WP_Post[],video:WP_Post[],text:WP_Post[],messengers:WP_Post[]}
 */
function tolstenko_get_reviews_grouped( $post_ids = array() ) {
	$grouped = array(
		'thanks'     => array(),
		'video'      => array(),
		'text'       => array(),
		'messengers' => array(),
	);

	$post_ids = array_values( array_unique( array_filter( array_map( 'intval', (array) $post_ids ) ) ) );

	if ( ! empty( $post_ids ) ) {
		$posts = get_posts(
			array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__in'       => $post_ids,
				'orderby'        => 'post__in',
			)
		);
	} else {
		$posts = get_posts(
			array(
				'post_type'      => 'review',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
	}

	foreach ( $posts as $post ) {
		$type = (string) tolstenko_get_review_field( 'review_type', $post->ID );
		$map  = array(
			'thenks'        => 'thanks',
			'Благодарности' => 'thanks',
			'Видео'         => 'video',
			'Текстовые'     => 'text',
			'Месседжеры'    => 'messengers',
			'messenger'     => 'messengers',
		);
		if ( isset( $map[ $type ] ) ) {
			$type = $map[ $type ];
		}
		if ( isset( $grouped[ $type ] ) ) {
			$grouped[ $type ][] = $post;
		}
	}

	return $grouped;
}
