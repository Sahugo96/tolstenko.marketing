<?php
/**
 * Нативный метабокс CPT blog — без ACF Pro.
 * Ключи meta совместимы с прежними именами полей.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_blog_add_metabox' );
add_action( 'save_post_blog', 'tolstenko_blog_save_metabox', 10, 2 );
add_action( 'save_post_actions', 'tolstenko_blog_save_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts', 'tolstenko_blog_metabox_assets' );

/**
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default.
 * @return mixed
 */
function tolstenko_blog_meta( $post_id, $key, $default = '' ) {
	$val = get_post_meta( $post_id, $key, true );
	return ( $val === '' || $val === false || $val === null ) ? $default : $val;
}

function tolstenko_blog_add_metabox() {
	$types = function_exists( 'tolstenko_get_content_body_post_types' )
		? tolstenko_get_content_body_post_types()
		: array( 'blog', 'actions' );
	foreach ( $types as $pt ) {
		$title = ( $pt === 'actions' )
			? __( 'Поля акции', 'tolstenko-theme' )
			: __( 'Статья блога', 'tolstenko-theme' );
		add_meta_box(
			'tolstenko_blog_fields',
			$title,
			'tolstenko_blog_render_metabox',
			$pt,
			'normal',
			'high'
		);
	}
}

function tolstenko_blog_metabox_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$types  = function_exists( 'tolstenko_get_content_body_post_types' )
		? tolstenko_get_content_body_post_types()
		: array( 'blog', 'actions' );
	if ( ! $screen || ! in_array( $screen->post_type, $types, true ) ) {
		return;
	}
	wp_enqueue_media();
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_blog_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_blog_save', 'tolstenko_blog_nonce' );

	$text  = (string) tolstenko_blog_meta( $post->ID, 'single-blog_text', '' );
	$quest = (string) tolstenko_blog_meta( $post->ID, 'single-blog_quest', '1' );
	$quest = ( $quest === '' || $quest === '1' || $quest === 1 || $quest === true ) ? '1' : '0';
	$author = (string) tolstenko_blog_meta( $post->ID, 'blog_author', '' );
	$actions = tolstenko_blog_meta( $post->ID, 'blog_actions', array() );
	if ( ! is_array( $actions ) ) {
		$actions = array();
	}
	$actions = array_map( 'intval', $actions );

	$comments = tolstenko_blog_meta( $post->ID, 'blog_comments', array() );
	if ( ! is_array( $comments ) ) {
		$comments = array();
	}

	$exclude_ids = ( $post->post_type === 'actions' ) ? array( (int) $post->ID ) : array();
	$action_posts = get_posts(
		array(
			'post_type'      => 'actions',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post__not_in'   => $exclude_ids,
		)
	);
	$author_label = ( $post->post_type === 'actions' )
		? __( 'Автор', 'tolstenko-theme' )
		: __( 'Автор статьи', 'tolstenko-theme' );
	?>
	<style>
		.tolstenko-blog-box .tolstenko-blog-field{margin:0 0 14px}
		.tolstenko-blog-box .tolstenko-blog-field input[type=text],
		.tolstenko-blog-box .tolstenko-blog-field textarea,
		.tolstenko-blog-box .tolstenko-blog-field select{width:100%;max-width:720px}
		.tolstenko-blog-box .tolstenko-blog-section{margin:20px 0 0;padding:16px 0 0;border-top:1px solid #dcdcde}
		.tolstenko-blog-box .tolstenko-blog-section h3{margin:0 0 10px;font-size:14px}
		.tolstenko-blog-box .tolstenko-blog-actions-list{max-height:180px;overflow:auto;border:1px solid #dcdcde;padding:8px 10px;max-width:720px;background:#fff}
		.tolstenko-blog-box .tolstenko-blog-actions-list label{display:block;margin:0 0 6px}
		.tolstenko-blog-box .tolstenko-bc-item{border:1px solid #dcdcde;background:#fff;padding:12px;margin:0 0 10px;max-width:760px}
		.tolstenko-blog-box .tolstenko-bc-item.is-reply{margin-left:24px;background:#f6f7f7}
		.tolstenko-blog-box .tolstenko-bc-grid{display:grid;grid-template-columns:90px 1fr;gap:12px}
		.tolstenko-blog-box .tolstenko-bc-preview img{max-width:80px;height:auto;display:block;margin-bottom:6px}
		.tolstenko-blog-box .tolstenko-bc-fields label{display:block;font-weight:600;margin:0 0 4px}
		.tolstenko-blog-box .tolstenko-bc-fields input,
		.tolstenko-blog-box .tolstenko-bc-fields textarea{width:100%;margin:0 0 8px}
		.tolstenko-blog-box .tolstenko-bc-replies{margin-top:10px;padding-top:10px;border-top:1px dashed #c3c4c7}
	</style>

	<div class="tolstenko-blog-box" id="tolstenko-blog-box">
		<div class="tolstenko-blog-section" style="margin-top:0;padding-top:0;border:0">
			<h3><?php esc_html_e( 'Шапка', 'tolstenko-theme' ); ?></h3>

			<p class="tolstenko-blog-field">
				<label for="tolstenko_single_blog_text"><strong><?php esc_html_e( 'Текст под заголовком (лид)', 'tolstenko-theme' ); ?></strong></label><br>
				<textarea id="tolstenko_single_blog_text" name="tolstenko_single_blog_text" rows="3"><?php echo esc_textarea( $text ); ?></textarea>
			</p>

			<p class="tolstenko-blog-field">
				<label>
					<input type="hidden" name="tolstenko_single_blog_quest" value="0">
					<input type="checkbox" name="tolstenko_single_blog_quest" value="1" <?php checked( $quest, '1' ); ?>>
					<?php esc_html_e( 'Кнопка «Задать вопрос» у автора', 'tolstenko-theme' ); ?>
				</label>
			</p>

			<p class="tolstenko-blog-field">
				<label for="tolstenko_blog_author"><strong><?php echo esc_html( $author_label ); ?></strong></label><br>
				<?php
				tolstenko_render_blog_author_select(
					'tolstenko_blog_author',
					$author,
					__( 'По умолчанию (из шаблона вакансии / без автора)', 'tolstenko-theme' ),
					'tolstenko_blog_author'
				);
				?>
				<br><span class="description">
					<?php esc_html_e( 'Список авторов:', 'tolstenko-theme' ); ?>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=tolstenko-blog-authors' ) ); ?>">
						<?php esc_html_e( 'Настройки сайта → Авторы статей', 'tolstenko-theme' ); ?>
					</a>
				</span>
			</p>

			<div class="tolstenko-blog-field">
				<strong><?php esc_html_e( 'Акции в сайдбаре', 'tolstenko-theme' ); ?></strong>
				<div class="tolstenko-blog-actions-list">
					<?php if ( empty( $action_posts ) ) : ?>
						<em><?php esc_html_e( 'Нет опубликованных акций.', 'tolstenko-theme' ); ?></em>
					<?php else : ?>
						<?php foreach ( $action_posts as $action_post ) : ?>
							<label>
								<input type="checkbox" name="tolstenko_blog_actions[]" value="<?php echo (int) $action_post->ID; ?>" <?php checked( in_array( (int) $action_post->ID, $actions, true ) ); ?>>
								<?php echo esc_html( get_the_title( $action_post ) ); ?>
							</label>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="tolstenko-blog-section">
			<h3><?php esc_html_e( 'Кураторские комментарии', 'tolstenko-theme' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Показываются вместе с нативными комментариями WordPress.', 'tolstenko-theme' ); ?></p>

			<div data-bc-list>
				<?php foreach ( $comments as $i => $comment ) : ?>
					<?php tolstenko_blog_render_comment_admin_row( (string) $i, is_array( $comment ) ? $comment : array(), false ); ?>
				<?php endforeach; ?>
			</div>

			<p><button type="button" class="button" data-bc-add><?php esc_html_e( 'Добавить комментарий', 'tolstenko-theme' ); ?></button></p>

			<template data-bc-tpl>
				<?php tolstenko_blog_render_comment_admin_row( '__INDEX__', array(), false ); ?>
			</template>
			<template data-bc-reply-tpl>
				<?php tolstenko_blog_render_comment_admin_row( '__INDEX__', array(), true ); ?>
			</template>
		</div>
	</div>

	<script>
	(function(){
		const root = document.getElementById('tolstenko-blog-box');
		if (!root) return;
		const list = root.querySelector('[data-bc-list]');
		const tpl = root.querySelector('[data-bc-tpl]');
		const replyTpl = root.querySelector('[data-bc-reply-tpl]');

		function uid(){ return Date.now().toString() + Math.floor(Math.random()*1000).toString(); }

		function bindPhoto(scope){
			scope.addEventListener('click', function(e){
				const pick = e.target.closest('[data-bc-pick]');
				if (pick && window.wp && wp.media) {
					const row = pick.closest('.tolstenko-bc-item');
					const input = row && row.querySelector('[data-bc-photo]');
					const preview = row && row.querySelector('[data-bc-preview]');
					const frame = wp.media({ title: 'Фото', multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						const att = frame.state().get('selection').first().toJSON();
						if (!att || !input) return;
						input.value = String(att.id || 0);
						if (preview) preview.innerHTML = att.url ? '<img src="'+att.url+'" alt="">' : '';
					});
					frame.open();
					return;
				}
				const clear = e.target.closest('[data-bc-clear]');
				if (clear) {
					const row = clear.closest('.tolstenko-bc-item');
					const input = row && row.querySelector('[data-bc-photo]');
					const preview = row && row.querySelector('[data-bc-preview]');
					if (input) input.value = '0';
					if (preview) preview.innerHTML = '';
				}
			});
		}
		bindPhoto(root);

		root.addEventListener('click', function(e){
			const add = e.target.closest('[data-bc-add]');
			if (add && tpl && list) {
				list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, uid()));
				return;
			}
			const addReply = e.target.closest('[data-bc-add-reply]');
			if (addReply && replyTpl) {
				const item = addReply.closest('.tolstenko-bc-item');
				const replies = item && item.querySelector('[data-bc-replies]');
				const parentIndex = item && item.getAttribute('data-bc-index');
				if (!replies || !parentIndex) return;
				const html = replyTpl.innerHTML
					.replace(/__PARENT__/g, parentIndex)
					.replace(/__INDEX__/g, uid());
				replies.insertAdjacentHTML('beforeend', html);
				return;
			}
			const remove = e.target.closest('[data-bc-remove]');
			if (remove) {
				const row = remove.closest('.tolstenko-bc-item');
				if (row) row.remove();
			}
		});
	})();
	</script>
	<?php
}

/**
 * @param string $index Row index.
 * @param array  $item  Comment data.
 * @param bool   $is_reply Is nested reply row.
 */
function tolstenko_blog_render_comment_admin_row( $index, array $item, $is_reply = false ) {
	$photo = (int) ( $item['photo'] ?? 0 );
	$url   = $photo ? (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';

	if ( $is_reply ) {
		// Parent placeholder replaced in JS; for existing rows use real parent index via name path.
		$parent = isset( $item['_parent'] ) ? (string) $item['_parent'] : '__PARENT__';
		$base   = 'tolstenko_blog_comments[' . $parent . '][replies][' . $index . ']';
		$class  = 'tolstenko-bc-item is-reply';
	} else {
		$base  = 'tolstenko_blog_comments[' . $index . ']';
		$class = 'tolstenko-bc-item';
	}
	?>
	<div class="<?php echo esc_attr( $class ); ?>" data-bc-index="<?php echo esc_attr( $index ); ?>">
		<div class="tolstenko-bc-grid">
			<div>
				<div class="tolstenko-bc-preview" data-bc-preview>
					<?php if ( $url ) : ?>
						<img src="<?php echo esc_url( $url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" data-bc-photo name="<?php echo esc_attr( $base . '[photo]' ); ?>" value="<?php echo (int) $photo; ?>">
				<button type="button" class="button button-small" data-bc-pick><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
				<button type="button" class="button button-small" data-bc-clear><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-bc-fields">
				<label><?php esc_html_e( 'Имя', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $base . '[name]' ); ?>" value="<?php echo esc_attr( (string) ( $item['name'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Дата', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $base . '[date]' ); ?>" value="<?php echo esc_attr( (string) ( $item['date'] ?? '' ) ); ?>" placeholder="01.01.2026">
				<label><?php esc_html_e( 'Время', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $base . '[time]' ); ?>" value="<?php echo esc_attr( (string) ( $item['time'] ?? '' ) ); ?>" placeholder="12:00">
				<label><?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?></label>
				<textarea name="<?php echo esc_attr( $base . '[text]' ); ?>" rows="3"><?php echo esc_textarea( (string) ( $item['text'] ?? '' ) ); ?></textarea>
				<p>
					<button type="button" class="button-link-delete" data-bc-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
					<?php if ( ! $is_reply ) : ?>
						<button type="button" class="button button-small" data-bc-add-reply><?php esc_html_e( 'Добавить ответ', 'tolstenko-theme' ); ?></button>
					<?php endif; ?>
				</p>
			</div>
		</div>

		<?php if ( ! $is_reply ) : ?>
			<div class="tolstenko-bc-replies" data-bc-replies>
				<?php
				$replies = $item['replies'] ?? array();
				if ( is_array( $replies ) ) {
					foreach ( $replies as $ri => $reply ) {
						if ( ! is_array( $reply ) ) {
							continue;
						}
						$reply['_parent'] = $index;
						tolstenko_blog_render_comment_admin_row( (string) $ri, $reply, true );
					}
				}
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function tolstenko_blog_save_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['tolstenko_blog_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_nonce'] ) ), 'tolstenko_blog_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text = isset( $_POST['tolstenko_single_blog_text'] )
		? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_single_blog_text'] ) )
		: '';
	update_post_meta( $post_id, 'single-blog_text', $text );

	$quest = ! empty( $_POST['tolstenko_single_blog_quest'] ) ? '1' : '0';
	update_post_meta( $post_id, 'single-blog_quest', $quest );

	$author = isset( $_POST['tolstenko_blog_author'] )
		? sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_author'] ) )
		: '';
	update_post_meta( $post_id, 'blog_author', $author );

	$actions_raw = isset( $_POST['tolstenko_blog_actions'] ) && is_array( $_POST['tolstenko_blog_actions'] )
		? array_map( 'intval', wp_unslash( $_POST['tolstenko_blog_actions'] ) ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();
	$actions = array();
	foreach ( $actions_raw as $aid ) {
		if ( $aid > 0 && $aid !== (int) $post_id && get_post_type( $aid ) === 'actions' ) {
			$actions[] = $aid;
		}
	}
	update_post_meta( $post_id, 'blog_actions', $actions );

	$comments_raw = isset( $_POST['tolstenko_blog_comments'] ) && is_array( $_POST['tolstenko_blog_comments'] )
		? wp_unslash( $_POST['tolstenko_blog_comments'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();
	update_post_meta( $post_id, 'blog_comments', tolstenko_blog_sanitize_comments( $comments_raw ) );
}

/**
 * @param array $rows Raw comments.
 * @return array
 */
function tolstenko_blog_sanitize_comments( array $rows ) {
	$out = array();
	foreach ( $rows as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$item = array(
			'photo' => (int) ( $row['photo'] ?? 0 ),
			'name'  => sanitize_text_field( (string) ( $row['name'] ?? '' ) ),
			'date'  => sanitize_text_field( (string) ( $row['date'] ?? '' ) ),
			'time'  => sanitize_text_field( (string) ( $row['time'] ?? '' ) ),
			'text'  => sanitize_textarea_field( (string) ( $row['text'] ?? '' ) ),
		);
		$replies_out = array();
		$replies     = $row['replies'] ?? array();
		if ( is_array( $replies ) ) {
			foreach ( $replies as $reply ) {
				if ( ! is_array( $reply ) ) {
					continue;
				}
				$r = array(
					'photo' => (int) ( $reply['photo'] ?? 0 ),
					'name'  => sanitize_text_field( (string) ( $reply['name'] ?? '' ) ),
					'date'  => sanitize_text_field( (string) ( $reply['date'] ?? '' ) ),
					'time'  => sanitize_text_field( (string) ( $reply['time'] ?? '' ) ),
					'text'  => sanitize_textarea_field( (string) ( $reply['text'] ?? '' ) ),
				);
				if ( $r['name'] === '' && $r['text'] === '' && ! $r['photo'] ) {
					continue;
				}
				$replies_out[] = $r;
			}
		}
		$item['replies'] = $replies_out;
		if ( $item['name'] === '' && $item['text'] === '' && ! $item['photo'] && empty( $replies_out ) ) {
			continue;
		}
		$out[] = $item;
	}
	return $out;
}
