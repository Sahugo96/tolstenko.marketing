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

	$hidden['tolstenko_target_post_id']    = (string) $post_id;
	$hidden['tolstenko_parent_comment']    = '';
	return $hidden;
}

/**
 * Email из полей CF7 (текущее имя поля: email-826).
 *
 * @param array $data Posted data.
 * @return string
 */
function tolstenko_comment_queue_posted_email( array $data ) {
	$keys = array( 'email-826', 'your-email', 'email' );
	foreach ( $keys as $key ) {
		if ( ! isset( $data[ $key ] ) ) {
			continue;
		}
		$raw = $data[ $key ];
		if ( is_array( $raw ) ) {
			$raw = reset( $raw );
		}
		$email = sanitize_email( (string) $raw );
		if ( is_email( $email ) ) {
			return $email;
		}
	}
	return '';
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
	$email = tolstenko_comment_queue_posted_email( $data );
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

	$parent_id = $data['tolstenko_parent_comment'] ?? '';
	if ( is_array( $parent_id ) ) {
		$parent_id = (string) reset( $parent_id );
	} else {
		$parent_id = (string) $parent_id;
	}
	if ( function_exists( 'tolstenko_blog_comment_normalize_id' ) ) {
		$parent_id = tolstenko_blog_comment_normalize_id( $parent_id );
	} else {
		$parent_id = '';
	}

	$parent_name = '';
	if ( $parent_id !== '' && function_exists( 'tolstenko_blog_get_root_comment' ) ) {
		$parent_row = tolstenko_blog_get_root_comment( $target_id, $parent_id );
		if ( is_array( $parent_row ) ) {
			$parent_name = sanitize_text_field( (string) ( $parent_row['name'] ?? '' ) );
		}
	}

	update_post_meta( $queue_id, '_tolstenko_target_post_id', $target_id );
	update_post_meta( $queue_id, '_tolstenko_cmt_name', $name );
	update_post_meta( $queue_id, '_tolstenko_cmt_email', $email );
	update_post_meta( $queue_id, '_tolstenko_cmt_text', $text );
	update_post_meta( $queue_id, '_tolstenko_cmt_date', wp_date( 'd.m.Y', $now ) );
	update_post_meta( $queue_id, '_tolstenko_cmt_time', wp_date( 'H:i', $now ) );
	update_post_meta( $queue_id, '_tolstenko_cmt_photo', tolstenko_get_default_comment_avatar_id() );
	update_post_meta( $queue_id, '_tolstenko_cmt_parent', $parent_id );
	update_post_meta( $queue_id, '_tolstenko_cmt_parent_name', $parent_name );
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

	$target      = (int) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_target_post_id', 0 );
	$name        = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_name', '' );
	$email       = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_email', '' );
	if ( $email === '' ) {
		$email = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_phone', '' );
		$email = is_email( $email ) ? $email : '';
	}
	$text        = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_text', '' );
	$date        = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_date', '' );
	$photo       = (int) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_photo', 0 );
	$url         = $photo ? (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
	$parent_id   = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_parent', '' );
	if ( function_exists( 'tolstenko_blog_comment_normalize_id' ) ) {
		$parent_id = tolstenko_blog_comment_normalize_id( $parent_id );
	}
	$parent_name = (string) tolstenko_comment_queue_meta( $post->ID, '_tolstenko_cmt_parent_name', '' );
	$parent_live = ( $parent_id !== '' && $target && function_exists( 'tolstenko_blog_get_root_comment' ) )
		? tolstenko_blog_get_root_comment( $target, $parent_id )
		: null;
	$parent_label = '';
	if ( is_array( $parent_live ) ) {
		$parent_label = trim( (string) ( $parent_live['name'] ?? '' ) );
	}
	if ( $parent_label === '' ) {
		$parent_label = $parent_name;
	}
	$parent_missing = ( $parent_id !== '' && ! is_array( $parent_live ) );
	?>
	<style>
		.tolstenko-cq-grid{display:grid;grid-template-columns:120px 1fr;gap:16px;max-width:720px}
		.tolstenko-cq-grid label{display:block;font-weight:600;margin:10px 0 4px}
		.tolstenko-cq-grid input[type=text],.tolstenko-cq-grid textarea{width:100%}
		.tolstenko-cq-preview{width:96px;height:96px;background:#f0f0f1;border:1px solid #c3c4c7;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-bottom:8px}
		.tolstenko-cq-preview img{max-width:100%;max-height:100%;display:block}
		.tolstenko-cq-parent{margin-top:12px;padding:12px;background:#f6f7f7;border:1px solid #c3c4c7}
		.tolstenko-cq-parent.is-missing{border-color:#d63638;background:#fcf0f1}
		.tolstenko-cq-parent label{font-weight:400}
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

			<label for="tolstenko_cq_email"><?php esc_html_e( 'Email', 'tolstenko-theme' ); ?></label>
			<input type="email" id="tolstenko_cq_email" name="tolstenko_cq_email" value="<?php echo esc_attr( $email ); ?>">
			<p class="description"><?php esc_html_e( 'На сайт не выводится. Сюда уйдёт письмо об публикации и об ответах.', 'tolstenko-theme' ); ?></p>

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
			<?php if ( $parent_id !== '' ) : ?>
				<div class="tolstenko-cq-parent<?php echo $parent_missing ? ' is-missing' : ''; ?>">
					<input type="hidden" name="tolstenko_cq_parent" value="<?php echo esc_attr( $parent_id ); ?>">
					<p>
						<strong><?php esc_html_e( 'Ответ на', 'tolstenko-theme' ); ?>:</strong>
						<?php echo $parent_label !== '' ? esc_html( $parent_label ) : esc_html__( 'комментарий', 'tolstenko-theme' ); ?>
						<code><?php echo esc_html( $parent_id ); ?></code>
					</p>
					<?php if ( $parent_missing ) : ?>
						<p class="description" style="color:#b32d2e;">
							<?php esc_html_e( 'Родительский комментарий в статье не найден. Отметьте «Опубликовать как корневой», нажмите «Обновить», затем «Одобрить».', 'tolstenko-theme' ); ?>
						</p>
					<?php endif; ?>
					<p>
						<label>
							<input type="checkbox" name="tolstenko_cq_as_root" value="1">
							<?php esc_html_e( 'Опубликовать как корневой комментарий', 'tolstenko-theme' ); ?>
						</label>
					</p>
				</div>
			<?php endif; ?>
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
	$email = isset( $_POST['tolstenko_cq_email'] ) ? sanitize_email( wp_unslash( $_POST['tolstenko_cq_email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		$email = '';
	}
	$text  = isset( $_POST['tolstenko_cq_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_cq_text'] ) ) : '';
	$photo = isset( $_POST['tolstenko_cq_photo'] ) ? (int) $_POST['tolstenko_cq_photo'] : 0;

	update_post_meta( $post_id, '_tolstenko_cmt_name', $name );
	update_post_meta( $post_id, '_tolstenko_cmt_email', $email );
	update_post_meta( $post_id, '_tolstenko_cmt_text', $text );
	update_post_meta( $post_id, '_tolstenko_cmt_photo', $photo );

	$as_root = ! empty( $_POST['tolstenko_cq_as_root'] );
	if ( $as_root ) {
		update_post_meta( $post_id, '_tolstenko_cmt_parent', '' );
	} elseif ( isset( $_POST['tolstenko_cq_parent'] ) ) {
		$parent_save = (string) wp_unslash( $_POST['tolstenko_cq_parent'] );
		if ( function_exists( 'tolstenko_blog_comment_normalize_id' ) ) {
			$parent_save = tolstenko_blog_comment_normalize_id( $parent_save );
		} else {
			$parent_save = '';
		}
		update_post_meta( $post_id, '_tolstenko_cmt_parent', $parent_save );
	}

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
		'email' => (string) get_post_meta( $queue_id, '_tolstenko_cmt_email', true ),
		'date'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_date', true ),
		'time'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_time', true ),
		'text'  => (string) get_post_meta( $queue_id, '_tolstenko_cmt_text', true ),
	);

	$parent_id = (string) get_post_meta( $queue_id, '_tolstenko_cmt_parent', true );
	if ( function_exists( 'tolstenko_blog_comment_normalize_id' ) ) {
		$parent_id = tolstenko_blog_comment_normalize_id( $parent_id );
	} else {
		$parent_id = '';
	}

	if ( $parent_id !== '' ) {
		if ( function_exists( 'tolstenko_blog_ensure_post_comment_ids' ) ) {
			tolstenko_blog_ensure_post_comment_ids( $target );
		}
		$parent_row = function_exists( 'tolstenko_blog_get_root_comment' )
			? tolstenko_blog_get_root_comment( $target, $parent_id )
			: null;
		if ( ! is_array( $parent_row ) ) {
			return new WP_Error(
				'parent',
				__( 'Родительский комментарий не найден. Отметьте «Опубликовать как корневой», нажмите «Обновить», затем снова «Одобрить».', 'tolstenko-theme' )
			);
		}
		$comments_now = get_post_meta( $target, 'blog_comments', true );
		$found_parent = ( is_array( $comments_now ) && function_exists( 'tolstenko_blog_find_comment' ) )
			? tolstenko_blog_find_comment( $comments_now, $parent_id )
			: null;
		if ( $found_parent && (int) ( $found_parent['depth'] ?? 0 ) >= 2 ) {
			return new WP_Error(
				'parent_depth',
				__( 'На ответы второго уровня отвечать нельзя. Отметьте «Опубликовать как корневой» или выберите другой комментарий.', 'tolstenko-theme' )
			);
		}
	}

	$appended = function_exists( 'tolstenko_append_blog_comment' )
		? tolstenko_append_blog_comment( $target, $item, $parent_id )
		: false;
	if ( ! $appended ) {
		return new WP_Error( 'append', __( 'Не удалось добавить комментарий в статью (пустое имя/текст?).', 'tolstenko-theme' ) );
	}

	$new_id = is_string( $appended ) ? $appended : '';

	update_post_meta( $queue_id, '_tolstenko_published', 1 );
	wp_update_post(
		array(
			'ID'          => $queue_id,
			'post_status' => 'private',
		)
	);

	tolstenko_comment_queue_notify_on_approve( $queue_id, $target, $parent_id, $new_id );

	return true;
}

/**
 * Письма: автору — комментарий опубликован; автору родителя — появился ответ.
 *
 * @param int    $queue_id  Queue post ID.
 * @param int    $target_id Article ID.
 * @param string $parent_id Parent comment id.
 * @param string $new_id    New comment id.
 */
function tolstenko_comment_queue_notify_on_approve( $queue_id, $target_id, $parent_id, $new_id ) {
	$queue_id  = (int) $queue_id;
	$target_id = (int) $target_id;
	$permalink = $target_id ? (string) get_permalink( $target_id ) : '';
	if ( $permalink && $new_id !== '' ) {
		$permalink .= '#comment-' . rawurlencode( $new_id );
	}
	$post_title = $target_id ? wp_strip_all_tags( (string) get_the_title( $target_id ) ) : '';
	$site       = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
	$author     = (string) get_post_meta( $queue_id, '_tolstenko_cmt_name', true );
	$email      = sanitize_email( (string) get_post_meta( $queue_id, '_tolstenko_cmt_email', true ) );
	$text       = (string) get_post_meta( $queue_id, '_tolstenko_cmt_text', true );
	if ( function_exists( 'mb_substr' ) ) {
		$excerpt = trim( mb_substr( wp_strip_all_tags( $text ), 0, 220 ) );
	} else {
		$excerpt = trim( substr( wp_strip_all_tags( $text ), 0, 220 ) );
	}

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );

	if ( is_email( $email ) ) {
		$subject = sprintf(
			/* translators: 1: site name */
			__( '[%s] Ваш комментарий опубликован', 'tolstenko-theme' ),
			$site
		);
		$lines = array(
			$author !== '' ? sprintf( __( 'Здравствуйте, %s!', 'tolstenko-theme' ), $author ) : __( 'Здравствуйте!', 'tolstenko-theme' ),
			'',
			$post_title !== ''
				? sprintf( __( 'Ваш комментарий к материалу «%s» опубликован.', 'tolstenko-theme' ), $post_title )
				: __( 'Ваш комментарий опубликован.', 'tolstenko-theme' ),
		);
		if ( $excerpt !== '' ) {
			$lines[] = '';
			$lines[] = $excerpt;
		}
		if ( $permalink !== '' ) {
			$lines[] = '';
			$lines[] = __( 'Ссылка:', 'tolstenko-theme' );
			$lines[] = $permalink;
		}
		wp_mail( $email, $subject, implode( "\n", $lines ), $headers );
	}

	$parent_id = (string) $parent_id;
	if ( $parent_id === '' || ! function_exists( 'tolstenko_blog_get_comment' ) ) {
		return;
	}

	$parent = tolstenko_blog_get_comment( $target_id, $parent_id );
	if ( ! is_array( $parent ) ) {
		return;
	}
	$parent_email = function_exists( 'tolstenko_blog_comment_sanitize_email' )
		? tolstenko_blog_comment_sanitize_email( $parent['email'] ?? '' )
		: ( is_email( (string) ( $parent['email'] ?? '' ) ) ? (string) $parent['email'] : '' );
	if ( $parent_email === '' || ( is_email( $email ) && strcasecmp( $parent_email, $email ) === 0 ) ) {
		return;
	}

	$parent_name = trim( (string) ( $parent['name'] ?? '' ) );
	$subject     = sprintf(
		/* translators: 1: site name */
		__( '[%s] Новый ответ на ваш комментарий', 'tolstenko-theme' ),
		$site
	);
	$lines = array(
		$parent_name !== '' ? sprintf( __( 'Здравствуйте, %s!', 'tolstenko-theme' ), $parent_name ) : __( 'Здравствуйте!', 'tolstenko-theme' ),
		'',
		$post_title !== ''
			? sprintf( __( 'На ваш комментарий к материалу «%s» появился ответ.', 'tolstenko-theme' ), $post_title )
			: __( 'На ваш комментарий появился ответ.', 'tolstenko-theme' ),
	);
	if ( $author !== '' ) {
		$lines[] = sprintf( __( 'Автор ответа: %s', 'tolstenko-theme' ), $author );
	}
	if ( $excerpt !== '' ) {
		$lines[] = '';
		$lines[] = $excerpt;
	}
	if ( $permalink !== '' ) {
		$lines[] = '';
		$lines[] = __( 'Ссылка:', 'tolstenko-theme' );
		$lines[] = $permalink;
	}
	wp_mail( $parent_email, $subject, implode( "\n", $lines ), $headers );
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
			$new['tolstenko_cq_type']    = __( 'Тип', 'tolstenko-theme' );
			$new['tolstenko_cq_email']  = __( 'Email', 'tolstenko-theme' );
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
	if ( $column === 'tolstenko_cq_type' ) {
		$parent = (string) get_post_meta( $post_id, '_tolstenko_cmt_parent', true );
		if ( function_exists( 'tolstenko_blog_comment_normalize_id' ) ) {
			$parent = tolstenko_blog_comment_normalize_id( $parent );
		}
		if ( $parent !== '' ) {
			esc_html_e( 'Ответ', 'tolstenko-theme' );
			$name = (string) get_post_meta( $post_id, '_tolstenko_cmt_parent_name', true );
			if ( $name !== '' ) {
				echo '<br><span class="description">' . esc_html( $name ) . '</span>';
			}
		} else {
			esc_html_e( 'Комментарий', 'tolstenko-theme' );
		}
		return;
	}
	if ( $column === 'tolstenko_cq_email' ) {
		$email = (string) get_post_meta( $post_id, '_tolstenko_cmt_email', true );
		echo $email !== '' ? esc_html( $email ) : '—';
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
