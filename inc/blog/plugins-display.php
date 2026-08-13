<?php
/**
 * WP ULike / Post Views Counter: вывод в stats, подсчёт для CPT blog.
 * Админка: кнопка seed лайков/просмотров (рандом 10–30, разные числа) → в плагины.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp', 'tolstenko_disable_blog_auto_views_likes', 20 );
add_action( 'init', 'tolstenko_pvc_enable_blog_post_type', 20 );
add_action( 'init', 'tolstenko_pvc_ensure_table', 21 );
add_filter( 'pvc_display_views_count', 'tolstenko_hide_blog_post_views_auto_display' );
add_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );
add_action( 'wp_ajax_tolstenko_seed_article_stats', 'tolstenko_ajax_seed_article_stats' );
add_action( 'add_meta_boxes', 'tolstenko_blog_stats_add_metabox' );

/**
 * Пара лайков/просмотров: оба в [10, 30], значения разные.
 *
 * @return array{likes: int, views: int}
 */
function tolstenko_generate_random_stats_pair() {
	$likes = wp_rand( 10, 30 );
	$views = wp_rand( 10, 30 );
	$guard = 0;
	while ( $views === $likes && $guard < 20 ) {
		$views = wp_rand( 10, 30 );
		$guard++;
	}
	if ( $views === $likes ) {
		$views = $likes >= 30 ? 10 : $likes + 1;
	}
	return array(
		'likes' => (int) $likes,
		'views' => (int) $views,
	);
}

/**
 * Сброс object cache Post Views Counter для конкретного поста.
 *
 * @param int $post_id Post ID.
 */
function tolstenko_pvc_invalidate_post_views_cache( $post_id ) {
	global $wpdb;

	$post_id = (int) $post_id;
	if ( ! $post_id || ! tolstenko_pvc_table_exists() ) {
		tolstenko_flush_pvc_caches();
		return;
	}

	$query = $wpdb->prepare(
		'SELECT SUM(count) AS views FROM ' . $wpdb->prefix . 'post_views WHERE id IN (%d) AND type = %d',
		$post_id,
		4
	);
	wp_cache_delete( md5( $query ), 'pvc-get_post_views' );
	tolstenko_flush_pvc_caches();
}

/**
 * @return bool
 */
function tolstenko_pvc_table_exists() {
	global $wpdb;

	static $exists = null;
	if ( $exists !== null ) {
		return $exists;
	}

	$table  = $wpdb->prefix . 'post_views';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$exists = ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table );

	return $exists;
}

/**
 * Создаёт таблицу PVC, если плагин активен, а таблицы нет.
 */
function tolstenko_pvc_ensure_table() {
	if ( tolstenko_pvc_table_exists() || ! function_exists( 'Post_Views_Counter' ) ) {
		return;
	}

	$pvc = Post_Views_Counter();
	if ( $pvc && method_exists( $pvc, 'activate' ) ) {
		$pvc->activate( false );
	}
}

/**
 * Сброс object cache Post Views Counter.
 */
function tolstenko_flush_pvc_caches() {
	global $wp_object_cache;

	if ( ! is_object( $wp_object_cache ) || ! property_exists( $wp_object_cache, 'cache' ) ) {
		return;
	}

	$groups = array( 'pvc', 'pvc-get_post_views', 'pvc-get_views' );
	foreach ( $groups as $group ) {
		if ( empty( $wp_object_cache->cache[ $group ] ) || ! is_array( $wp_object_cache->cache[ $group ] ) ) {
			continue;
		}
		foreach ( array_keys( $wp_object_cache->cache[ $group ] ) as $key ) {
			wp_cache_delete( $key, $group );
		}
	}
}

/**
 * Прямое чтение total-просмотров из таблицы PVC (минуя кэш).
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_post_views_count_direct( $post_id ) {
	global $wpdb;

	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return 0;
	}

	$table = $wpdb->prefix . 'post_views';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(count) FROM {$table} WHERE id = %d AND type = 4 AND period = 'total'",
			$post_id
		)
	);

	return max( 0, (int) $count );
}

/**
 * Записать total-просмотры в Post Views Counter.
 *
 * @param int $post_id Post ID.
 * @param int $views   Views.
 * @return int Saved views count.
 */
function tolstenko_pvc_set_post_views( $post_id, $views ) {
	global $wpdb;

	$post_id = (int) $post_id;
	$views   = max( 0, (int) $views );
	if ( ! $post_id ) {
		return 0;
	}

	tolstenko_pvc_ensure_table();
	if ( ! tolstenko_pvc_table_exists() ) {
		return 0;
	}

	$table = $wpdb->prefix . 'post_views';

	if ( function_exists( 'pvc_update_post_views' ) ) {
		pvc_update_post_views( $post_id, $views );
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$table} (id, type, period, count) VALUES (%d, 4, 'total', %d) ON DUPLICATE KEY UPDATE count = %d",
				$post_id,
				$views,
				$views
			)
		);
	}

	tolstenko_pvc_invalidate_post_views_cache( $post_id );

	return tolstenko_get_post_views_count_direct( $post_id );
}

/**
 * Записать лайки в WP ULike.
 *
 * @param int $post_id Post ID.
 * @param int $likes   Likes.
 * @return int
 */
function tolstenko_ulike_set_post_likes( $post_id, $likes ) {
	$post_id = (int) $post_id;
	$likes   = max( 0, (int) $likes );
	if ( ! $post_id ) {
		return 0;
	}

	if ( function_exists( 'wp_ulike_update_meta_counter_value' ) ) {
		wp_ulike_update_meta_counter_value( $post_id, $likes, 'post', 'like', true );
		wp_ulike_update_meta_counter_value( $post_id, $likes, 'post', 'like', false );
	}

	update_post_meta( $post_id, '_liked', $likes );

	return tolstenko_get_post_likes_count( $post_id );
}

/**
 * Записать лайки/просмотры в WP ULike и Post Views Counter.
 *
 * @param int $post_id Post ID.
 * @param int $likes   Likes.
 * @param int $views   Views.
 * @return array{likes: int, views: int}|WP_Error
 */
function tolstenko_set_article_stats( $post_id, $likes, $views ) {
	$post_id = (int) $post_id;
	if ( ! $post_id || get_post_type( $post_id ) !== 'blog' ) {
		return new WP_Error( 'invalid_post', __( 'Некорректная статья.', 'tolstenko-theme' ) );
	}

	$likes = tolstenko_ulike_set_post_likes( $post_id, $likes );
	$views = tolstenko_pvc_set_post_views( $post_id, $views );

	// Старые ключи темы больше не используем.
	delete_post_meta( $post_id, 'tolstenko_stats_likes' );
	delete_post_meta( $post_id, 'tolstenko_stats_views' );

	return array(
		'likes' => (int) $likes,
		'views' => (int) $views,
	);
}

/**
 * Число лайков из WP ULike.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_post_likes_count( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return 0;
	}

	if ( function_exists( 'wp_ulike_get_post_likes' ) ) {
		$raw = wp_ulike_get_post_likes( $post_id );
		if ( is_numeric( $raw ) ) {
			return (int) $raw;
		}
		if ( is_string( $raw ) ) {
			return (int) preg_replace( '/\D+/', '', $raw );
		}
	}

	$legacy = get_post_meta( $post_id, '_liked', true );
	if ( $legacy !== '' && $legacy !== false ) {
		return (int) $legacy;
	}

	return 0;
}

/**
 * Число просмотров из Post Views Counter.
 *
 * @param int $post_id Post ID.
 * @return int
 */
function tolstenko_get_post_views_count( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return 0;
	}

	// Прямое чтение из БД — надёжнее кэша pvc_get_post_views.
	if ( tolstenko_pvc_table_exists() ) {
		$direct = tolstenko_get_post_views_count_direct( $post_id );
		if ( $direct > 0 ) {
			return $direct;
		}
	}

	if ( function_exists( 'pvc_get_post_views' ) ) {
		return (int) pvc_get_post_views( $post_id );
	}

	return tolstenko_get_post_views_count_direct( $post_id );
}

/**
 * AJAX (админка): случайные лайки/просмотры → плагины.
 */
function tolstenko_ajax_seed_article_stats() {
	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( ! $post_id || ! wp_verify_nonce( $nonce, 'tolstenko_seed_stats_' . $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Ошибка безопасности.', 'tolstenko-theme' ) ), 403 );
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Недостаточно прав.', 'tolstenko-theme' ) ), 403 );
	}

	$pair  = tolstenko_generate_random_stats_pair();
	$saved = tolstenko_set_article_stats( $post_id, $pair['likes'], $pair['views'] );
	if ( is_wp_error( $saved ) ) {
		wp_send_json_error( array( 'message' => $saved->get_error_message() ), 400 );
	}

	wp_send_json_success( $saved );
}

/**
 * Метабокс в сайдбаре редактора статьи.
 */
function tolstenko_blog_stats_add_metabox() {
	add_meta_box(
		'tolstenko_blog_stats',
		__( 'Лайки и просмотры', 'tolstenko-theme' ),
		'tolstenko_blog_stats_render_metabox',
		'blog',
		'side',
		'high'
	);
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_blog_stats_render_metabox( $post ) {
	$post_id = (int) $post->ID;
	$likes   = tolstenko_get_post_likes_count( $post_id );
	$views   = tolstenko_get_post_views_count( $post_id );
	$nonce   = wp_create_nonce( 'tolstenko_seed_stats_' . $post_id );
	?>
	<p class="description" style="margin-top:0;">
		<?php esc_html_e( 'Значения берутся из WP ULike и Post Views Counter. Кнопка задаёт оба числа случайно от 10 до 30 (разные).', 'tolstenko-theme' ); ?>
	</p>
	<p>
		<strong><?php esc_html_e( 'Лайки:', 'tolstenko-theme' ); ?></strong>
		<span id="tolstenko-stats-likes"><?php echo esc_html( (string) $likes ); ?></span><br>
		<strong><?php esc_html_e( 'Просмотры:', 'tolstenko-theme' ); ?></strong>
		<span id="tolstenko-stats-views"><?php echo esc_html( (string) $views ); ?></span>
	</p>
	<p style="margin-bottom:0;">
		<button
			type="button"
			class="button button-secondary"
			id="tolstenko-seed-article-stats"
			data-post-id="<?php echo esc_attr( (string) $post_id ); ?>"
			data-nonce="<?php echo esc_attr( $nonce ); ?>"
			<?php disabled( $post_id <= 0 || $post->post_status === 'auto-draft' ); ?>
		><?php esc_html_e( 'Случайные 10–30', 'tolstenko-theme' ); ?></button>
	</p>
	<?php if ( $post_id <= 0 || $post->post_status === 'auto-draft' ) : ?>
		<p class="description"><?php esc_html_e( 'Сначала сохраните статью.', 'tolstenko-theme' ); ?></p>
	<?php endif; ?>
	<script>
	(function(){
		var btn = document.getElementById('tolstenko-seed-article-stats');
		if (!btn || btn.disabled) return;
		btn.addEventListener('click', function(){
			if (btn.disabled) return;
			var postId = btn.getAttribute('data-post-id') || '';
			var nonce = btn.getAttribute('data-nonce') || '';
			if (!postId || !nonce) return;
			btn.disabled = true;
			var prev = btn.textContent;
			btn.textContent = '…';
			var body = new FormData();
			body.append('action', 'tolstenko_seed_article_stats');
			body.append('post_id', postId);
			body.append('nonce', nonce);
			fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: body })
				.then(function(r){ return r.json(); })
				.then(function(json){
					if (!json || !json.success || !json.data) {
						throw new Error((json && json.data && json.data.message) || 'error');
					}
					var likesEl = document.getElementById('tolstenko-stats-likes');
					var viewsEl = document.getElementById('tolstenko-stats-views');
					if (likesEl) likesEl.textContent = String(json.data.likes);
					if (viewsEl) viewsEl.textContent = String(json.data.views);
					btn.textContent = prev;
				})
				.catch(function(){ btn.textContent = prev; })
				.finally(function(){ btn.disabled = false; });
		});
	})();
	</script>
	<?php
}

/**
 * Добавляет CPT blog в список типов Post Views Counter.
 *
 * @param mixed $opts General settings.
 * @return mixed
 */
function tolstenko_pvc_option_add_blog_type( $opts ) {
	if ( ! is_array( $opts ) ) {
		return $opts;
	}

	$types = isset( $opts['post_types_count'] ) && is_array( $opts['post_types_count'] )
		? $opts['post_types_count']
		: array();

	if ( ! in_array( 'blog', $types, true ) ) {
		$types[]                  = 'blog';
		$opts['post_types_count'] = $types;
	}

	return $opts;
}

/**
 * Патчит runtime-опции PVC + сохраняет blog в настройках плагина (один раз).
 */
function tolstenko_pvc_enable_blog_post_type() {
	if ( ! function_exists( 'Post_Views_Counter' ) ) {
		return;
	}

	$pvc = Post_Views_Counter();
	if ( ! $pvc || empty( $pvc->options['general'] ) || ! is_array( $pvc->options['general'] ) ) {
		return;
	}

	$types = isset( $pvc->options['general']['post_types_count'] ) && is_array( $pvc->options['general']['post_types_count'] )
		? $pvc->options['general']['post_types_count']
		: array();

	if ( ! in_array( 'blog', $types, true ) ) {
		$types[] = 'blog';
		$pvc->options['general']['post_types_count'] = $types;
	}

	if ( get_option( 'tolstenko_pvc_blog_enabled' ) === '1' ) {
		return;
	}

	remove_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );
	$opts = get_option( 'post_views_counter_settings_general', array() );
	add_filter( 'option_post_views_counter_settings_general', 'tolstenko_pvc_option_add_blog_type' );

	if ( ! is_array( $opts ) ) {
		$opts = array();
	}
	$saved_types = isset( $opts['post_types_count'] ) && is_array( $opts['post_types_count'] )
		? $opts['post_types_count']
		: array();
	if ( ! in_array( 'blog', $saved_types, true ) ) {
		$saved_types[]            = 'blog';
		$opts['post_types_count'] = $saved_types;
		update_option( 'post_views_counter_settings_general', $opts, false );
	}
	update_option( 'tolstenko_pvc_blog_enabled', '1', false );
}

/**
 * Убираем авто-кнопку лайков из the_content на записи блога.
 */
function tolstenko_disable_blog_auto_views_likes() {
	$is_body = function_exists( 'tolstenko_is_content_body_singular' )
		? tolstenko_is_content_body_singular()
		: is_singular( array( 'blog', 'actions' ) );
	if ( ! $is_body ) {
		return;
	}

	remove_filter( 'the_content', 'wp_ulike_put_posts', 15 );
	remove_filter( 'the_excerpt', 'wp_ulike_put_posts', 15 );
}

/**
 * Скрываем авто-счётчик просмотров на single blog (показываем в stats).
 *
 * @param mixed $display Display flag from Post Views Counter.
 * @return mixed
 */
function tolstenko_hide_blog_post_views_auto_display( $display ) {
	$is_body = function_exists( 'tolstenko_is_content_body_singular' )
		? tolstenko_is_content_body_singular()
		: is_singular( array( 'blog', 'actions' ) );
	if ( $is_body ) {
		return false;
	}

	return $display;
}
