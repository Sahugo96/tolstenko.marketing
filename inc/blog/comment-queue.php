<?php
/**
 * Очередь модерации комментариев с CF7 → одобрение в meta blog_comments.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const TOLSTENKO_BLOG_COMMENT_CF7_ID   = 128;
const TOLSTENKO_BLOG_COMMENT_CF7_HASH = '5bfc3f8';
const TOLSTENKO_BLOG_COMMENT_QUEUE_PT = 'blog_cmt_mod';

/**
 * @param WPCF7_ContactForm|object $contact_form CF7 form.
 * @return bool
 */
function tolstenko_is_blog_comment_cf7( $contact_form ) {
	if ( ! is_object( $contact_form ) ) {
		return false;
	}

	$id = method_exists( $contact_form, 'id' ) ? (int) $contact_form->id() : 0;
	if ( $id === TOLSTENKO_BLOG_COMMENT_CF7_ID ) {
		return true;
	}

	$hash = method_exists( $contact_form, 'hash' ) ? (string) $contact_form->hash() : '';
	if ( $hash !== '' && $hash === TOLSTENKO_BLOG_COMMENT_CF7_HASH ) {
		return true;
	}

	$title = method_exists( $contact_form, 'title' ) ? (string) $contact_form->title() : '';
	return $title === 'Комментарий';
}

/**
 * @param int $post_id Post ID.
 * @return bool
 */
function tolstenko_comment_queue_is_valid_target( $post_id ) {
	$post_id = (int) $post_id;
	if ( ! $post_id ) {
		return false;
	}
	$pt = get_post_type( $post_id );
	// Кейсы без комментариев.
	return in_array( (string) $pt, array( 'blog', 'actions' ), true );
}

/**
 * ID вложения-заглушки аватарки (assets/img/default-ava.jpg). Один раз кладётся в медиатеку.
 *
 * @return int Attachment ID or 0.
 */
function tolstenko_get_default_comment_avatar_id() {
	$option_key = 'tolstenko_default_comment_avatar_id';
	$cached     = (int) get_option( $option_key, 0 );
	if ( $cached && wp_attachment_is_image( $cached ) ) {
		return $cached;
	}

	$path = trailingslashit( get_template_directory() ) . 'assets/img/default-ava.jpg';
	if ( ! is_readable( $path ) ) {
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( 'default-ava.jpg' );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		return 0;
	}

	$file_array = array(
		'name'     => 'default-ava.jpg',
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, 0, __( 'Аватар комментария по умолчанию', 'tolstenko-theme' ) );
	if ( is_wp_error( $attachment_id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return 0;
	}

	update_option( $option_key, (int) $attachment_id, false );
	return (int) $attachment_id;
}

add_action( 'init', 'tolstenko_register_blog_comment_queue_cpt' );

/**
 * CPT очереди (только админка).
 */
function tolstenko_register_blog_comment_queue_cpt() {
	register_post_type(
		TOLSTENKO_BLOG_COMMENT_QUEUE_PT,
		array(
			'labels'              => array(
				'name'               => __( 'Комментарии с сайта', 'tolstenko-theme' ),
				'singular_name'      => __( 'Комментарий с сайта', 'tolstenko-theme' ),
				'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
				'add_new_item'       => __( 'Добавить в очередь', 'tolstenko-theme' ),
				'edit_item'          => __( 'Модерация комментария', 'tolstenko-theme' ),
				'new_item'           => __( 'Новый комментарий', 'tolstenko-theme' ),
				'search_items'       => __( 'Искать комментарии', 'tolstenko-theme' ),
				'not_found'          => __( 'Очередь пуста', 'tolstenko-theme' ),
				'not_found_in_trash' => __( 'В корзине пусто', 'tolstenko-theme' ),
				'all_items'          => __( 'Все из очереди', 'tolstenko-theme' ),
				'menu_name'          => __( 'Комментарии с сайта', 'tolstenko-theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'menu_position'       => 29,
			'menu_icon'           => 'dashicons-format-chat',
			'supports'            => array( 'title' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'show_in_rest'        => false,
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
		)
	);
}

add_filter( 'wpcf7_form_hidden_fields', 'tolstenko_comment_queue_cf7_hidden_fields' );

/**
 * Скрытое поле целевой статьи только у формы «Комментарий».
 *
 * @param array $hidden Hidden fields.
 * @return array
 */
function tolstenko_comment_queue_cf7_hidden_fields( $hidden ) {
	if ( ! is_array( $hidden ) ) {
		$hidden = array();
	}

	$form = function_exists( 'wpcf7_get_current_contact_form' ) ? wpcf7_get_current_contact_form() : null;
	if ( ! $form || ! tolstenko_is_blog_comment_cf7( $form ) ) {
		return $hidden;
	}

	$post_id = (int) get_queried_object_id();
	if ( ! $post_id ) {
		$post_id = (int) get_the_ID();
	}
	if ( ! tolstenko_comment_queue_is_valid_target( $post_id ) ) {
		return $hidden;
	}

	$hidden['tolstenko_target_post_id'] = (string) $post_id;
	return $hidden;
}

add_action( 'wpcf7_mail_sent', 'tolstenko_comment_queue_capture_cf7' );

/**
 * Сохранить отправку CF7 в очередь модерации.
 *
 * @param WPCF7_ContactForm $contact_form Form.
 */
function tolstenko_comment_queue_capture_cf7( $contact_form ) {
	if ( ! tolstenko_is_blog_comment_cf7( $contact_form ) ) {
		return;
	}
	if ( ! class_exists( 'WPCF7_Submission' ) ) {
		return;
	}

	$submission = WPCF7_Submission::get_instance();
	if ( ! $submission ) {
		return;
	}

	$data = $submission->get_posted_data();
	if ( ! is_array( $data ) ) {
		return;
	}

	$target_id = isset( $data['tolstenko_target_post_id'] ) ? (int) $data['tolstenko_target_post_id'] : 0;
	if ( ! tolstenko_comment_queue_is_valid_target( $target_id ) ) {
		return;
	}

	$name  = sanitize_text_field( (string) ( $data['your-name'] ?? '' ) );
	$phone = sanitize_text_field( (string) ( $data['your-phone'] ?? '' ) );
	$text  = sanitize_textarea_field( (string) ( $data['textarea-464'] ?? '' ) );

	if ( $name === '' && $text === '' ) {
		return;
	}

	$now   = current_time( 'timestamp' );
	$title = $name !== '' ? $name : __( 'Без имени', 'tolstenko-theme' );
	$title = wp_strip_all_tags( $title );
	if ( function_exists( 'mb_substr' ) ) {
		$title = mb_substr( $title, 0, 80 );
	} else {
		$title = substr( $title, 0, 80 );
	}

	$queue_id = wp_insert_post(
		array(
			'post_type'   => TOLSTENKO_BLOG_COMMENT_QUEUE_PT,
			'post_status' => 'pending',
			'post_title'  => $title,
			'post_content' => '',
		),
		true
	);

	if ( is_wp_error( $queue_id ) || ! $queue_id ) {
		return;
	}

	update_post_meta( $queue_id, '_tolstenko_target_post_id', $target_id );
	update_post_meta( $queue_id, '_tolstenko_cmt_name', $name );
	update_post_meta( $queue_id, '_tolstenko_cmt_phone', $phone );
	update_post_meta( $queue_id, '_tolstenko_cmt_text', $text );
	update_post_meta( $queue_id, '_tolstenko_cmt_date', wp_date( 'd.m.Y', $now ) );
	update_post_meta( $queue_id, '_tolstenko_cmt_time', wp_date( 'H:i', $now ) );
	update_post_meta( $queue_id, '_tolstenko_cmt_photo', tolstenko_get_default_comment_avatar_id() );
	update_post_meta( $queue_id, '_tolstenko_published', 0 );
}

add_action( 'add_meta_boxes', 'tolstenko_comment_queue_metaboxes' );

/**
 * Метабокс модерации.
 */
function tolstenko_comment_queue_metaboxes() {
	add_meta_box(
		'tolstenko_comment_queue_fields',
		__( 'Данные комментария', 'tolstenko-theme' ),
		'tolstenko_comment_queue_render_metabox',
		TOLSTENKO_BLOG_COMMENT_QUEUE_PT,
		'normal',
		'high'
	);
	add_meta_box(
		'tolstenko_comment_queue_actions',
		__( 'Модерация', 'tolstenko-theme' ),
		'tolstenko_comment_queue_render_actions_metabox',
		TOLSTENKO_BLOG_COMMENT_QUEUE_PT,
		'side',
		'high'
	);
}

/**
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default.
 * @return mixed
 */
function tolstenko_comment_queue_meta( $post_id, $key, $default = '' ) {
	$v = get_post_meta( $post_id, $key, true );
	return ( $v === '' || $v === null ) ? $default : $v;
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_comment_queue_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_comment_queue_save', 'tolstenko_comment_queue_nonce' );

	$target = (int) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_target_post_id', 0 );
	$name   = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_name', '' );
	$phone  = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_phone', '' );
	$text   = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_text', '' );
	$date   = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_date', '' );
	$photo  = (int) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_photo', 0 );
	$url    = $photo ? (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
	?>
	<style>
		.tolstenko-cq-grid{display:grid;grid-template-columns:120px 1fr;gap:16px;max-width:720px}
		.tolstenko-cq-grid label{display:block;font-weight:600;margin:10px 0 4px}
		.tolstenko-cq-grid input[type=text],.tolstenko-cq-grid textarea{width:100%}
		.tolstenko-cq-preview{width:96px;height:96px;background:#f0f0f1;border:1px solid #c3c4c7;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:8px}
		.tolstenko-cq-preview img{max-width:100%;max-height:100%;display:block}
	</style>
	<div class="tolstenko-cq-grid">
		<div>
			<div class="tolstenko-cq-preview" data-cq-preview>
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="">
				<?php endif; ?>
			</div>
			<input type="hidden" name="tolstenko_cq_photo" id="tolstenko_cq_photo" value="<?php echo (int) $photo; ?>">
			<button type="button" class="button" id="tolstenko_cq_pick" data-cq-pick><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
			<button type="button" class="button" id="tolstenko_cq_clear" data-cq-clear><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
		</div>
		<div>
			<label for="tolstenko_cq_name"><?php esc_html_e( 'Имя', 'tolstenko-theme' ); ?></label>
			<input type="text" id="tolstenko_cq_name" name="tolstenko_cq_name" value="<?php echo esc_attr( $name ); ?>">

			<p>
				<strong><?php esc_html_e( 'Телефон', 'tolstenko-theme' ); ?>:</strong>
				<?php echo $phone !== '' ? esc_html( $phone ) : '—'; ?>
			</p>

			<p>
				<strong><?php esc_html_e( 'Дата', 'tolstenko-theme' ); ?>:</strong>
				<?php echo $date !== '' ? esc_html( $date ) : '—'; ?>
			</p>

			<label for="tolstenko_cq_text"><?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?></label>
			<textarea id="tolstenko_cq_text" name="tolstenko_cq_text" rows="5"><?php echo esc_textarea( $text ); ?></textarea>

			<p>
				<strong><?php esc_html_e( 'Статья / акция', 'tolstenko-theme' ); ?>:</strong>
				<?php if ( $target && get_post( $target ) ) : ?>
					<a href="<?php echo esc_url( get_edit_post_link( $target ) ); ?>"><?php echo esc_html( get_the_title( $target ) ); ?></a>
					<code>#<?php echo (int) $target; ?></code>
					—
					<a href="<?php echo esc_url( get_permalink( $target ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'На сайте', 'tolstenko-theme' ); ?></a>
				<?php elseif ( $target ) : ?>
					<code>#<?php echo (int) $target; ?></code>
				<?php else : ?>
					—
				<?php endif; ?>
			</p>
		</div>
	</div>
	<script>
	(function(){
		const root = document.getElementById('tolstenko_comment_queue_fields') || document;
		root.addEventListener('click', function(e){
			const pick = e.target.closest('[data-cq-pick]');
			const clear = e.target.closest('[data-cq-clear]');
			const input = document.getElementById('tolstenko_cq_photo');
			const preview = document.querySelector('[data-cq-preview]');
			if (pick) {
				e.preventDefault();
				if (!window.wp || !wp.media) return;
				const frame = wp.media({ title: 'Фото', button: { text: 'Выбрать' }, multiple: false, library: { type: 'image' } });
				frame.on('select', function(){
					const att = frame.state().get('selection').first().toJSON();
					if (input) input.value = att.id || 0;
					if (preview) {
						const src = (att.sizes && att.sizes.thumbnail && att.sizes.thumbnail.url) ? att.sizes.thumbnail.url : att.url;
						preview.innerHTML = src ? '<img src="'+src+'" alt="">' : '';
					}
				});
				frame.open();
				return;
			}
			if (clear) {
				e.preventDefault();
				if (input) input.value = '0';
				if (preview) preview.innerHTML = '';
			}
		});
	})();
	</script>
	<?php
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_comment_queue_render_actions_metabox( $post ) {
	$published = (int) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_published', 0 );
	$approve_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=tolstenko_approve_blog_comment&post_id=' . (int) $post->ID ),
		'tolstenko_approve_blog_comment_' . (int) $post->ID
	);
	$reject_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=tolstenko_reject_blog_comment&post_id=' . (int) $post->ID ),
		'tolstenko_reject_blog_comment_' . (int) $post->ID
	);

	if ( $published ) {
		echo '<p><strong>' . esc_html__( 'Уже опубликовано в статье.', 'tolstenko-theme' ) . '</strong></p>';
		return;
	}

	echo '<p>' . esc_html__( 'Сначала нажмите «Обновить», если правили поля. Одобрение добавит комментарий в кураторский список целевой записи.', 'tolstenko-theme' ) . '</p>';
	echo '<p><a class="button button-primary" href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Одобрить', 'tolstenko-theme' ) . '</a></p>';
	echo '<p><a class="button" href="' . esc_url( $reject_url ) . '" onclick="return confirm(\'' . esc_js( __( 'Отклонить и в корзину?', 'tolstenko-theme' ) ) . '\');">' . esc_html__( 'Отклонить', 'tolstenko-theme' ) . '</a></p>';
}

add_action( 'save_post_' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT, 'tolstenko_comment_queue_save', 10, 2 );

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function tolstenko_comment_queue_save( $post_id, $post ) {
	if ( ! isset( $_POST['tolstenko_comment_queue_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_comment_queue_nonce'] ) ), 'tolstenko_comment_queue_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$name  = isset( $_POST['tolstenko_cq_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_cq_name'] ) ) : '';
	$text  = isset( $_POST['tolstenko_cq_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_cq_text'] ) ) : '';
	$photo = isset( $_POST['tolstenko_cq_photo'] ) ? (int) $_POST['tolstenko_cq_photo'] : 0;

	update_post_meta( $post_id, '_tolstenko_cmt_name', $name );
	update_post_meta( $post_id, '_tolstenko_cmt_text', $text );
	update_post_meta( $post_id, '_tolstenko_cmt_photo', $photo );

	if ( $name !== '' && $post->post_title !== $name ) {
		remove_action( 'save_post_' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT, 'tolstenko_comment_queue_save', 10 );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'post_title' => $name,
			)
		);
		add_action( 'save_post_' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT, 'tolstenko_comment_queue_save', 10, 2 );
	}
}

/**
 * @param int $queue_id Queue post ID.
 * @return true|WP_Error
 */
function tolstenko_comment_queue_approve( $queue_id ) {
	$queue_id = (int) $queue_id;
	$post     = get_post( $queue_id );
	if ( ! $post || $post->post_type !== TOLSTENKO_BLOG_COMMENT_QUEUE_PT ) {
		return new WP_Error( 'invalid', __( 'Запись очереди не найдена.', 'tolstenko-theme' ) );
	}

	if ( (int) get_post_meta( $queue_id, '_tolstenko_published', true ) === 1 ) {
		return new WP_Error( 'already', __( 'Комментарий уже опубликован.', 'tolstenko-theme' ) );
	}

	$target = (int) get_post_meta( $queue_id, '_tolstenko_target_post_id', true );
	if ( ! tolstenko_comment_queue_is_valid_target( $target ) ) {
		return new WP_Error( 'target', __( 'Некорректная целевая статья.', 'tolstenko-theme' ) );
	}

	$item = array(
		'photo' => (int) get_post_meta( $queue_id, '_tolstenko_cmt_photo', true ),
		'name'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_name', true ),
		'date'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_date', true ),
		'time'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_time', true ),
		'text'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_text', true ),
	);

	if ( ! function_exists( 'tolstenko_append_blog_comment' ) || ! tolstenko_append_blog_comment( $target, $item ) ) {
		return new WP_Error( 'append', __( 'Не удалось добавить комментарий в статью (пустое имя/текст?).', 'tolstenko-theme' ) );
	}

	update_post_meta( $queue_id, '_tolstenko_published', 1 );
	wp_update_post(
		array(
			'ID'          => $queue_id,
			'post_status' => 'private',
		)
	);

	return true;
}

add_action( 'admin_post_tolstenko_approve_blog_comment', 'tolstenko_comment_queue_handle_approve' );
add_action( 'admin_post_tolstenko_reject_blog_comment', 'tolstenko_comment_queue_handle_reject' );

/**
 * Approve from admin-post.
 */
function tolstenko_comment_queue_handle_approve() {
	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
	if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'tolstenko-theme' ) );
	}
	check_admin_referer( 'tolstenko_approve_blog_comment_' . $post_id );

	$result = tolstenko_comment_queue_approve( $post_id );
	$redirect = get_edit_post_link( $post_id, 'raw' );
	if ( ! $redirect ) {
		$redirect = admin_url( 'edit.php?post_type=' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT );
	}

	if ( is_wp_error( $result ) ) {
		$redirect = add_query_arg( 'tolstenko_cq_err', rawurlencode( $result->get_error_message() ), $redirect );
	} else {
		$redirect = add_query_arg( 'tolstenko_cq_ok', '1', $redirect );
	}

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Reject → trash.
 */
function tolstenko_comment_queue_handle_reject() {
	$post_id = isset( $_GET['post_id'] ) ? (int) $_GET['post_id'] : 0;
	if ( ! $post_id || ! current_user_can( 'delete_post', $post_id ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'tolstenko-theme' ) );
	}
	check_admin_referer( 'tolstenko_reject_blog_comment_' . $post_id );

	wp_trash_post( $post_id );

	wp_safe_redirect( admin_url( 'edit.php?post_type=' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT . '&tolstenko_cq_rejected=1' ) );
	exit;
}

add_action( 'admin_notices', 'tolstenko_comment_queue_admin_notices' );

/**
 * Notices after approve/reject.
 */
function tolstenko_comment_queue_admin_notices() {
	if ( ! empty( $_GET['tolstenko_cq_ok'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Комментарий опубликован в статье.', 'tolstenko-theme' ) . '</p></div>';
	}
	if ( ! empty( $_GET['tolstenko_cq_rejected'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Комментарий отклонён.', 'tolstenko-theme' ) . '</p></div>';
	}
	if ( ! empty( $_GET['tolstenko_cq_err'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$msg = sanitize_text_field( wp_unslash( (string) $_GET['tolstenko_cq_err'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $msg !== '' ) {
			echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
		}
	}
}

add_filter( 'manage_' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT . '_posts_columns', 'tolstenko_comment_queue_columns' );
add_action( 'manage_' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT . '_posts_custom_column', 'tolstenko_comment_queue_column_content', 10, 2 );
add_filter( 'post_row_actions', 'tolstenko_comment_queue_row_actions', 10, 2 );
add_action( 'admin_enqueue_scripts', 'tolstenko_comment_queue_admin_assets' );

/**
 * @param array $columns Columns.
 * @return array
 */
function tolstenko_comment_queue_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( $key === 'title' ) {
			$new['tolstenko_cq_target'] = __( 'Статья', 'tolstenko-theme' );
			$new['tolstenko_cq_phone']  = __( 'Телефон', 'tolstenko-theme' );
			$new['tolstenko_cq_status'] = __( 'Публикация', 'tolstenko-theme' );
		}
	}
	return $new;
}

/**
 * @param string $column  Column key.
 * @param int    $post_id Post ID.
 */
function tolstenko_comment_queue_column_content( $column, $post_id ) {
	if ( $column === 'tolstenko_cq_target' ) {
		$target = (int) get_post_meta( $post_id, '_tolstenko_target_post_id', true );
		if ( $target && get_post( $target ) ) {
			$edit = get_edit_post_link( $target );
			echo $edit
				? '<a href="' . esc_url( $edit ) . '">' . esc_html( get_the_title( $target ) ) . '</a>'
				: esc_html( get_the_title( $target ) );
			echo '<br><code>#' . (int) $target . '</code>';
		} else {
			echo '—';
		}
		return;
	}
	if ( $column === 'tolstenko_cq_phone' ) {
		$phone = (string) get_post_meta( $post_id, '_tolstenko_cmt_phone', true );
		echo $phone !== '' ? esc_html( $phone ) : '—';
		return;
	}
	if ( $column === 'tolstenko_cq_status' ) {
		if ( (int) get_post_meta( $post_id, '_tolstenko_published', true ) === 1 ) {
			esc_html_e( 'В статье', 'tolstenko-theme' );
		} else {
			esc_html_e( 'Ожидает', 'tolstenko-theme' );
		}
	}
}

/**
 * @param array   $actions Actions.
 * @param WP_Post $post    Post.
 * @return array
 */
function tolstenko_comment_queue_row_actions( $actions, $post ) {
	if ( ! ( $post instanceof WP_Post ) || $post->post_type !== TOLSTENKO_BLOG_COMMENT_QUEUE_PT ) {
		return $actions;
	}

	unset( $actions['view'], $actions['inline hide-if-no-js'] );

	if ( (int) get_post_meta( $post->ID, '_tolstenko_published', true ) !== 1 && current_user_can( 'edit_post', $post->ID ) ) {
		$approve_url = wp_nonce_url(
			admin_url( 'admin-post.php?action=tolstenko_approve_blog_comment&post_id=' . (int) $post->ID ),
			'tolstenko_approve_blog_comment_' . (int) $post->ID
		);
		$actions['tolstenko_approve'] = '<a href="' . esc_url( $approve_url ) . '">' . esc_html__( 'Одобрить', 'tolstenko-theme' ) . '</a>';
	}

	return $actions;
}

/**
 * Media for photo picker on queue edit screen.
 *
 * @param string $hook Hook.
 */
function tolstenko_comment_queue_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== TOLSTENKO_BLOG_COMMENT_QUEUE_PT ) {
		return;
	}
	wp_enqueue_media();
}

add_action( 'admin_menu', 'tolstenko_comment_queue_menu_badge', 999 );

/**
 * Pending count in admin menu label.
 */
function tolstenko_comment_queue_menu_badge() {
	global $menu;
	if ( ! is_array( $menu ) ) {
		return;
	}

	$pending = (int) wp_count_posts( TOLSTENKO_BLOG_COMMENT_QUEUE_PT )->pending;
	if ( $pending < 1 ) {
		return;
	}

	foreach ( $menu as $i => $item ) {
		if ( ! isset( $item[2] ) || $item[2] !== 'edit.php?post_type=' . TOLSTENKO_BLOG_COMMENT_QUEUE_PT ) {
			continue;
		}
		$menu[ $i ][0] .= ' <span class="awaiting-mod">' . (int) $pending . '</span>';
		break;
	}
}

add_action( 'admin_menu', 'tolstenko_hide_native_comments_menu' );
add_action( 'admin_bar_menu', 'tolstenko_hide_native_comments_admin_bar', 999 );

/**
 * Скрыть нативное меню «Комментарии» (edit-comments.php) — используется очередь CF7.
 */
function tolstenko_hide_native_comments_menu() {
	remove_menu_page( 'edit-comments.php' );
}

/**
 * Убрать иконку комментариев из админ-бара.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar.
 */
function tolstenko_hide_native_comments_admin_bar( $wp_admin_bar ) {
	if ( $wp_admin_bar instanceof WP_Admin_Bar ) {
		$wp_admin_bar->remove_node( 'comments' );
	}
}
