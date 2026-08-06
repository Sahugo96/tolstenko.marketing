<?php
/**
 * Нативный метабокс CPT «Кейс» — без ACF, по образцу «Отзыв».
 * Публичных страниц у кейса нет: ссылка «Разобрать кейс» задаётся вручную.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_case_add_metabox' );
add_action( 'save_post_case', 'tolstenko_case_save_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts', 'tolstenko_case_metabox_assets' );
add_filter( 'post_row_actions', 'tolstenko_case_row_actions', 10, 2 );
add_filter( 'get_sample_permalink_html', 'tolstenko_case_hide_sample_permalink', 10, 2 );
add_filter( 'use_block_editor_for_post_type', 'tolstenko_case_disable_block_editor', 10, 2 );

/**
 * Классический экран редактирования, как у отзывов.
 *
 * @param bool   $use       Whether to use block editor.
 * @param string $post_type Post type.
 * @return bool
 */
function tolstenko_case_disable_block_editor( $use, $post_type ) {
	if ( $post_type === 'case' ) {
		return false;
	}
	return $use;
}

/**
 * Убрать «Просмотреть» — публичных страниц нет.
 *
 * @param array   $actions Row actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function tolstenko_case_row_actions( $actions, $post ) {
	if ( $post instanceof WP_Post && $post->post_type === 'case' ) {
		unset( $actions['view'] );
	}
	return $actions;
}

/**
 * @param string $html    Permalink HTML.
 * @param int    $post_id Post ID.
 * @return string
 */
function tolstenko_case_hide_sample_permalink( $html, $post_id ) {
	if ( get_post_type( $post_id ) === 'case' ) {
		return '';
	}
	return $html;
}

/**
 * @param string $hook Admin hook.
 */
function tolstenko_case_metabox_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'case' ) {
		return;
	}
	wp_enqueue_media();
}

function tolstenko_case_add_metabox() {
	add_meta_box(
		'tolstenko_case_fields',
		__( 'Данные кейса (карточка)', 'tolstenko-theme' ),
		'tolstenko_case_render_metabox',
		'case',
		'normal',
		'high'
	);
}

/**
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default.
 * @return mixed
 */
function tolstenko_case_meta( $post_id, $key, $default = '' ) {
	$val = get_post_meta( $post_id, $key, true );
	return ( $val === '' || $val === false || $val === null ) ? $default : $val;
}

/**
 * Список опубликованных услуг для select.
 *
 * @return array<int,string> id => title
 */
function tolstenko_case_get_service_choices() {
	$posts = get_posts(
		array(
			'post_type'              => 'service',
			'post_status'            => 'publish',
			'posts_per_page'         => 200,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	$out = array();
	foreach ( $posts as $p ) {
		if ( $p instanceof WP_Post ) {
			$out[ (int) $p->ID ] = get_the_title( $p );
		}
	}
	return $out;
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_case_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_case_save', 'tolstenko_case_nonce' );

	$card_title = (string) tolstenko_case_meta( $post->ID, 'case_title', '' );
	$card_text  = (string) tolstenko_case_meta( $post->ID, 'case_text', '' );
	$case_link  = (string) tolstenko_case_meta( $post->ID, 'case_link', '' );
	$service_id = (int) tolstenko_case_meta( $post->ID, 'case_service', 0 );
	$items_raw  = tolstenko_case_meta( $post->ID, 'case_items', array() );
	$items      = is_array( $items_raw ) ? $items_raw : array();
	if ( empty( $items ) ) {
		$items = array( array( 'value' => '', 'text' => '' ) );
	}

	$services = tolstenko_case_get_service_choices();
	?>
	<style>
		.tolstenko-case-box .tolstenko-case-field{margin:0 0 14px}
		.tolstenko-case-box .tolstenko-case-field input[type=text],
		.tolstenko-case-box .tolstenko-case-field input[type=url],
		.tolstenko-case-box .tolstenko-case-field select,
		.tolstenko-case-box .tolstenko-case-field textarea{width:100%}
		.tolstenko-case-box .tolstenko-case-item{display:flex;gap:8px;align-items:flex-start;margin:0 0 8px;padding:10px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px}
		.tolstenko-case-box .tolstenko-case-item input{flex:1;width:auto}
		.tolstenko-case-box .description{color:#646970;margin:4px 0 0}
	</style>

	<div class="tolstenko-case-box" id="tolstenko-case-box">
		<p class="tolstenko-case-field">
			<label for="tolstenko_case_title"><strong><?php esc_html_e( 'Заголовок на карточке', 'tolstenko-theme' ); ?></strong></label><br>
			<input type="text" id="tolstenko_case_title" name="tolstenko_case_title" value="<?php echo esc_attr( $card_title ); ?>" placeholder="<?php esc_attr_e( 'Пусто = заголовок записи', 'tolstenko-theme' ); ?>">
		</p>

		<p class="tolstenko-case-field">
			<label for="tolstenko_case_text"><strong><?php esc_html_e( 'Текст на карточке', 'tolstenko-theme' ); ?></strong></label><br>
			<textarea id="tolstenko_case_text" name="tolstenko_case_text" rows="3"><?php echo esc_textarea( $card_text ); ?></textarea>
		</p>

		<p class="tolstenko-case-field">
			<label for="tolstenko_case_link"><strong><?php esc_html_e( 'Ссылка кнопки «Разобрать кейс»', 'tolstenko-theme' ); ?></strong></label><br>
			<input type="url" id="tolstenko_case_link" name="tolstenko_case_link" value="<?php echo esc_attr( $case_link ); ?>" placeholder="https://…">
		</p>

		<p class="tolstenko-case-field">
			<label for="tolstenko_case_service"><strong><?php esc_html_e( 'Услуга (ссылка «Подробнее об услуге»)', 'tolstenko-theme' ); ?></strong></label><br>
			<select id="tolstenko_case_service" name="tolstenko_case_service">
				<option value="0"><?php esc_html_e( '— Не выбрано —', 'tolstenko-theme' ); ?></option>
				<?php foreach ( $services as $sid => $stitle ) : ?>
					<option value="<?php echo (int) $sid; ?>" <?php selected( $service_id, (int) $sid ); ?>><?php echo esc_html( $stitle ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<div class="tolstenko-case-field">
			<strong><?php esc_html_e( 'Показатели', 'tolstenko-theme' ); ?></strong>
			<div class="tolstenko-case-items" data-case-items>
				<?php foreach ( $items as $idx => $row ) :
					$row = is_array( $row ) ? $row : array();
					$v   = (string) ( $row['value'] ?? '' );
					$t   = (string) ( $row['text'] ?? '' );
					?>
					<div class="tolstenko-case-item" data-case-item>
						<input type="text" name="tolstenko_case_items[<?php echo (int) $idx; ?>][value]" value="<?php echo esc_attr( $v ); ?>" placeholder="<?php esc_attr_e( 'Значение', 'tolstenko-theme' ); ?>">
						<input type="text" name="tolstenko_case_items[<?php echo (int) $idx; ?>][text]" value="<?php echo esc_attr( $t ); ?>" placeholder="<?php esc_attr_e( 'Подпись', 'tolstenko-theme' ); ?>">
						<button type="button" class="button" data-case-item-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
					</div>
				<?php endforeach; ?>
			</div>
			<p>
				<button type="button" class="button" data-case-item-add><?php esc_html_e( 'Добавить показатель', 'tolstenko-theme' ); ?></button>
			</p>
			<p class="description"><?php esc_html_e( 'Изображение карточки — миниатюра записи (блок справа).', 'tolstenko-theme' ); ?></p>
		</div>
	</div>

	<script>
	(function () {
		var box = document.getElementById('tolstenko-case-box');
		if (!box) return;
		var list = box.querySelector('[data-case-items]');
		var addBtn = box.querySelector('[data-case-item-add]');
		if (!list || !addBtn) return;

		function reindex() {
			list.querySelectorAll('[data-case-item]').forEach(function (row, i) {
				var inputs = row.querySelectorAll('input');
				if (inputs[0]) inputs[0].name = 'tolstenko_case_items[' + i + '][value]';
				if (inputs[1]) inputs[1].name = 'tolstenko_case_items[' + i + '][text]';
			});
		}

		addBtn.addEventListener('click', function (e) {
			e.preventDefault();
			var row = document.createElement('div');
			row.className = 'tolstenko-case-item';
			row.setAttribute('data-case-item', '');
			row.innerHTML =
				'<input type="text" name="tolstenko_case_items[0][value]" value="" placeholder="Значение">' +
				'<input type="text" name="tolstenko_case_items[0][text]" value="" placeholder="Подпись">' +
				'<button type="button" class="button" data-case-item-remove>Удалить</button>';
			list.appendChild(row);
			reindex();
		});

		list.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-case-item-remove]');
			if (!btn) return;
			e.preventDefault();
			var row = btn.closest('[data-case-item]');
			if (row) row.remove();
			if (!list.querySelector('[data-case-item]')) {
				addBtn.click();
			}
			reindex();
		});
	})();
	</script>
	<?php
}

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function tolstenko_case_save_metabox( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['tolstenko_case_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_case_nonce'] ) ), 'tolstenko_case_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$title = isset( $_POST['tolstenko_case_title'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_case_title'] ) ) : '';
	if ( $title !== '' ) {
		update_post_meta( $post_id, 'case_title', $title );
	} else {
		delete_post_meta( $post_id, 'case_title' );
	}

	$text = isset( $_POST['tolstenko_case_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_case_text'] ) ) : '';
	if ( $text !== '' ) {
		update_post_meta( $post_id, 'case_text', $text );
	} else {
		delete_post_meta( $post_id, 'case_text' );
	}

	$link = isset( $_POST['tolstenko_case_link'] ) ? esc_url_raw( wp_unslash( $_POST['tolstenko_case_link'] ) ) : '';
	if ( $link !== '' ) {
		update_post_meta( $post_id, 'case_link', $link );
	} else {
		delete_post_meta( $post_id, 'case_link' );
	}

	$service_id = isset( $_POST['tolstenko_case_service'] ) ? absint( $_POST['tolstenko_case_service'] ) : 0;
	if ( $service_id > 0 && get_post_type( $service_id ) === 'service' ) {
		update_post_meta( $post_id, 'case_service', $service_id );
	} else {
		delete_post_meta( $post_id, 'case_service' );
	}

	$items_out = array();
	if ( isset( $_POST['tolstenko_case_items'] ) && is_array( $_POST['tolstenko_case_items'] ) ) {
		foreach ( wp_unslash( $_POST['tolstenko_case_items'] ) as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$v = sanitize_text_field( (string) ( $row['value'] ?? '' ) );
			$t = sanitize_text_field( (string) ( $row['text'] ?? '' ) );
			if ( $v === '' && $t === '' ) {
				continue;
			}
			$items_out[] = array(
				'value' => $v,
				'text'  => $t,
			);
		}
	}
	if ( ! empty( $items_out ) ) {
		update_post_meta( $post_id, 'case_items', $items_out );
	} else {
		delete_post_meta( $post_id, 'case_items' );
	}
}
