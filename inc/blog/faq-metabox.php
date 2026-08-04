<?php
/**
 * FAQ для CPT blog: метабокс на статье + attrs для template-parts/blocks/faq.php.
 * Пустые поля → дефолты «Настройки сайта → FAQ».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_blog_faq_add_metabox' );
add_action( 'save_post_blog', 'tolstenko_blog_faq_save_metabox', 10, 2 );
add_action( 'save_post_actions', 'tolstenko_blog_faq_save_metabox', 10, 2 );

function tolstenko_blog_faq_add_metabox() {
	$types = function_exists( 'tolstenko_get_content_body_post_types' )
		? tolstenko_get_content_body_post_types()
		: array( 'blog', 'actions' );
	foreach ( $types as $pt ) {
		$title = ( $pt === 'actions' )
			? __( 'FAQ акции', 'tolstenko-theme' )
			: __( 'FAQ статьи', 'tolstenko-theme' );
		add_meta_box(
			'tolstenko_blog_faq',
			$title,
			'tolstenko_blog_faq_render_metabox',
			$pt,
			'normal',
			'default'
		);
	}
}

/**
 * Атрибуты блока FAQ для текущей статьи.
 * Пустой массив = полностью дефолты сайта.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function tolstenko_get_blog_faq_block_attrs( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$hidden = get_post_meta( $post_id, 'blog_faq_hidden', true );
	if ( $hidden === '1' || $hidden === 1 || $hidden === true ) {
		return array( '_tolstenko_faq_hidden' => 1 );
	}

	$title = trim( (string) get_post_meta( $post_id, 'blog_faq_title', true ) );
	$text  = trim( (string) get_post_meta( $post_id, 'blog_faq_text', true ) );
	$items = get_post_meta( $post_id, 'blog_faq_items', true );
	if ( ! is_array( $items ) ) {
		$items = array();
	}

	$clean_items = array();
	foreach ( $items as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$q = trim( (string) ( $it['title'] ?? '' ) );
		$a = (string) ( $it['redactor'] ?? '' );
		if ( $q === '' && trim( wp_strip_all_tags( $a ) ) === '' ) {
			continue;
		}
		$clean_items[] = array(
			'title'    => $q,
			'redactor' => $a,
		);
	}

	$attrs = array();
	if ( $title !== '' ) {
		$attrs['block_faq_title'] = $title;
	}
	if ( $text !== '' ) {
		$attrs['block_faq_text'] = $text;
	}
	if ( $clean_items ) {
		$attrs['block_faq_items'] = $clean_items;
	}

	return $attrs;
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_blog_faq_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_blog_faq_save', 'tolstenko_blog_faq_nonce' );

	$hidden = (string) get_post_meta( $post->ID, 'blog_faq_hidden', true );
	$title  = (string) get_post_meta( $post->ID, 'blog_faq_title', true );
	$text   = (string) get_post_meta( $post->ID, 'blog_faq_text', true );
	$items  = get_post_meta( $post->ID, 'blog_faq_items', true );
	if ( ! is_array( $items ) || ! $items ) {
		$items = array(
			array(
				'title'    => '',
				'redactor' => '',
			),
		);
	}
	?>
	<style>
		.tolstenko-blog-faq .tolstenko-bf-field{margin:0 0 12px}
		.tolstenko-blog-faq .tolstenko-bf-field input[type=text],
		.tolstenko-blog-faq .tolstenko-bf-field textarea{width:100%;max-width:760px}
		.tolstenko-blog-faq .tolstenko-bf-item{border:1px solid #dcdcde;background:#fff;padding:12px;margin:0 0 10px;max-width:760px}
		.tolstenko-blog-faq .tolstenko-bf-item label{display:block;font-weight:600;margin:0 0 4px}
		.tolstenko-blog-faq .description{margin:0 0 12px}
	</style>

	<div class="tolstenko-blog-faq" id="tolstenko-blog-faq">
		<p class="description">
			<?php esc_html_e( 'Если поля пустые — на сайте подставятся общие данные из «Настройки сайта → FAQ». Форма/фото справа всегда из общих настроек.', 'tolstenko-theme' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tolstenko-site-settings' ) ); ?>">
				<?php esc_html_e( 'Открыть настройки FAQ', 'tolstenko-theme' ); ?>
			</a>
		</p>

		<p class="tolstenko-bf-field">
			<label>
				<input type="hidden" name="tolstenko_blog_faq_hidden" value="0">
				<input type="checkbox" name="tolstenko_blog_faq_hidden" value="1" <?php checked( $hidden, '1' ); ?>>
				<?php esc_html_e( 'Скрыть блок FAQ на этой статье', 'tolstenko-theme' ); ?>
			</label>
		</p>

		<p class="tolstenko-bf-field">
			<label for="tolstenko_blog_faq_title"><strong><?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?></strong></label><br>
			<input type="text" id="tolstenko_blog_faq_title" name="tolstenko_blog_faq_title" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>">
		</p>

		<p class="tolstenko-bf-field">
			<label for="tolstenko_blog_faq_text"><strong><?php esc_html_e( 'Текст под заголовком', 'tolstenko-theme' ); ?></strong></label><br>
			<textarea id="tolstenko_blog_faq_text" name="tolstenko_blog_faq_text" rows="2" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
		</p>

		<p><strong><?php esc_html_e( 'Вопросы и ответы', 'tolstenko-theme' ); ?></strong></p>
		<div data-bf-list>
			<?php foreach ( $items as $i => $item ) : ?>
				<?php tolstenko_blog_faq_render_item_row( (string) $i, is_array( $item ) ? $item : array() ); ?>
			<?php endforeach; ?>
		</div>
		<p>
			<button type="button" class="button" data-bf-add><?php esc_html_e( 'Добавить вопрос', 'tolstenko-theme' ); ?></button>
		</p>
		<template data-bf-tpl>
			<?php tolstenko_blog_faq_render_item_row( '__INDEX__', array() ); ?>
		</template>
	</div>
	<script>
	(function(){
		const root = document.getElementById('tolstenko-blog-faq');
		if (!root) return;
		const list = root.querySelector('[data-bf-list]');
		const tpl = root.querySelector('[data-bf-tpl]');
		root.addEventListener('click', function(e){
			const add = e.target.closest('[data-bf-add]');
			if (add && tpl && list) {
				list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, Date.now().toString()));
				return;
			}
			const remove = e.target.closest('[data-bf-remove]');
			if (remove) {
				const row = remove.closest('.tolstenko-bf-item');
				if (row) row.remove();
			}
		});
	})();
	</script>
	<?php
}

/**
 * @param string $index Row index.
 * @param array  $item  FAQ item.
 */
function tolstenko_blog_faq_render_item_row( $index, array $item ) {
	$base = 'tolstenko_blog_faq_items[' . $index . ']';
	?>
	<div class="tolstenko-bf-item">
		<label><?php esc_html_e( 'Вопрос', 'tolstenko-theme' ); ?></label>
		<input type="text" name="<?php echo esc_attr( $base . '[title]' ); ?>" value="<?php echo esc_attr( (string) ( $item['title'] ?? '' ) ); ?>">
		<label><?php esc_html_e( 'Ответ (можно HTML)', 'tolstenko-theme' ); ?></label>
		<textarea name="<?php echo esc_attr( $base . '[redactor]' ); ?>" rows="4"><?php echo esc_textarea( (string) ( $item['redactor'] ?? '' ) ); ?></textarea>
		<p><button type="button" class="button-link-delete" data-bf-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
	</div>
	<?php
}

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function tolstenko_blog_faq_save_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['tolstenko_blog_faq_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_faq_nonce'] ) ), 'tolstenko_blog_faq_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$hidden = ! empty( $_POST['tolstenko_blog_faq_hidden'] ) ? '1' : '0';
	update_post_meta( $post_id, 'blog_faq_hidden', $hidden );

	$title = isset( $_POST['tolstenko_blog_faq_title'] )
		? sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_faq_title'] ) )
		: '';
	update_post_meta( $post_id, 'blog_faq_title', $title );

	$text = isset( $_POST['tolstenko_blog_faq_text'] )
		? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_blog_faq_text'] ) )
		: '';
	update_post_meta( $post_id, 'blog_faq_text', $text );

	$raw = isset( $_POST['tolstenko_blog_faq_items'] ) && is_array( $_POST['tolstenko_blog_faq_items'] )
		? wp_unslash( $_POST['tolstenko_blog_faq_items'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();

	$items = array();
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$q = sanitize_text_field( (string) ( $row['title'] ?? '' ) );
		$a = isset( $row['redactor'] ) ? wp_kses_post( (string) $row['redactor'] ) : '';
		if ( $q === '' && trim( wp_strip_all_tags( $a ) ) === '' ) {
			continue;
		}
		$items[] = array(
			'title'    => $q,
			'redactor' => $a,
		);
	}
	update_post_meta( $post_id, 'blog_faq_items', $items );
}
