<?php
/**
 * Слайдеры на single blog: услуги (без фильтра) + похожие статьи.
 * Пустые поля → дефолты «Настройки сайта».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_blog_sliders_add_metabox' );
add_action( 'save_post_blog', 'tolstenko_blog_sliders_save_metabox', 10, 2 );
add_action( 'save_post_actions', 'tolstenko_blog_sliders_save_metabox', 10, 2 );

/**
 * @param mixed $raw Raw IDs.
 * @return int[]
 */
function tolstenko_blog_sliders_sanitize_ids( $raw ) {
	if ( function_exists( 'tolstenko_sanitize_service_section_ids' ) ) {
		return tolstenko_sanitize_service_section_ids( $raw );
	}
	$ids = array();
	if ( is_string( $raw ) ) {
		$raw = preg_split( '/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY );
	}
	if ( ! is_array( $raw ) ) {
		return array();
	}
	foreach ( $raw as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return array_values( array_unique( $ids ) );
}

/**
 * Атрибуты блока «Слайдер услуг» для статьи.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function tolstenko_get_blog_services_block_attrs( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$hidden = get_post_meta( $post_id, 'blog_services_hidden', true );
	if ( $hidden === '1' || $hidden === 1 || $hidden === true ) {
		return array( '_tolstenko_hidden' => 1 );
	}

	$title = trim( (string) get_post_meta( $post_id, 'blog_services_title', true ) );
	$text  = trim( (string) get_post_meta( $post_id, 'blog_services_text', true ) );
	$ppp   = get_post_meta( $post_id, 'blog_services_posts_per_page', true );
	$ids   = tolstenko_blog_sliders_sanitize_ids( get_post_meta( $post_id, 'blog_services_ids', true ) );

	$attrs = array();
	if ( $title !== '' ) {
		$attrs['block_service_section_title'] = $title;
	}
	if ( $text !== '' ) {
		$attrs['block_service_section_text'] = $text;
	}
	if ( $ppp !== '' && $ppp !== null ) {
		$attrs['block_service_section_posts_per_page'] = (int) $ppp;
	}
	if ( $ids ) {
		$attrs['block_service_section_ids'] = $ids;
	}

	return $attrs;
}

/**
 * Атрибуты блока «Похожие статьи» для статьи.
 *
 * @param int $post_id Post ID.
 * @return array
 */
function tolstenko_get_blog_related_block_attrs( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( ! $post_id ) {
		return array();
	}

	$hidden = get_post_meta( $post_id, 'blog_related_hidden', true );
	if ( $hidden === '1' || $hidden === 1 || $hidden === true ) {
		return array( '_tolstenko_hidden' => 1 );
	}

	$title = trim( (string) get_post_meta( $post_id, 'blog_related_title', true ) );
	$text  = trim( (string) get_post_meta( $post_id, 'blog_related_text', true ) );
	$ppp   = get_post_meta( $post_id, 'blog_related_posts_per_page', true );
	$ids   = tolstenko_blog_sliders_sanitize_ids( get_post_meta( $post_id, 'blog_related_ids', true ) );

	$attrs = array(
		'block_blog_section_exclude' => array( $post_id ),
	);
	if ( $title !== '' ) {
		$attrs['block_blog_section_title'] = $title;
	}
	if ( $text !== '' ) {
		$attrs['block_blog_section_text'] = $text;
	}
	if ( $ppp !== '' && $ppp !== null ) {
		$attrs['block_blog_section_posts_per_page'] = (int) $ppp;
	}
	if ( $ids ) {
		$attrs['block_blog_section_ids'] = $ids;
	}

	return $attrs;
}

function tolstenko_blog_sliders_add_metabox() {
	$types = function_exists( 'tolstenko_get_content_body_post_types' )
		? tolstenko_get_content_body_post_types()
		: array( 'blog', 'actions' );
	foreach ( $types as $pt ) {
		$title = ( $pt === 'actions' )
			? __( 'Слайдеры под акцией', 'tolstenko-theme' )
			: __( 'Слайдеры под статьёй', 'tolstenko-theme' );
		add_meta_box(
			'tolstenko_blog_sliders',
			$title,
			'tolstenko_blog_sliders_render_metabox',
			$pt,
			'normal',
			'default'
		);
	}
}

/**
 * @param WP_Post $post Post.
 */
function tolstenko_blog_sliders_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_blog_sliders_save', 'tolstenko_blog_sliders_nonce' );

	$svc_hidden = (string) get_post_meta( $post->ID, 'blog_services_hidden', true );
	$svc_title  = (string) get_post_meta( $post->ID, 'blog_services_title', true );
	$svc_text   = (string) get_post_meta( $post->ID, 'blog_services_text', true );
	$svc_ppp    = get_post_meta( $post->ID, 'blog_services_posts_per_page', true );
	$svc_ids    = tolstenko_blog_sliders_sanitize_ids( get_post_meta( $post->ID, 'blog_services_ids', true ) );

	$rel_hidden = (string) get_post_meta( $post->ID, 'blog_related_hidden', true );
	$rel_title  = (string) get_post_meta( $post->ID, 'blog_related_title', true );
	$rel_text   = (string) get_post_meta( $post->ID, 'blog_related_text', true );
	$rel_ppp    = get_post_meta( $post->ID, 'blog_related_posts_per_page', true );
	$rel_ids    = tolstenko_blog_sliders_sanitize_ids( get_post_meta( $post->ID, 'blog_related_ids', true ) );

	$settings_url = admin_url( 'admin.php?page=tolstenko-site-settings' );
	$rel_exclude  = ( $post->post_type === 'blog' ) ? array( (int) $post->ID ) : array();

	if ( function_exists( 'tolstenko_post_select_print_assets' ) ) {
		tolstenko_post_select_print_assets();
	}
	?>
	<style>
		.tolstenko-blog-sliders .tolstenko-bs-block{border:1px solid #dcdcde;background:#fff;padding:14px 16px;margin:0 0 16px;max-width:none;width:100%;box-sizing:border-box}
		.tolstenko-blog-sliders .tolstenko-bs-block h3{margin:0 0 8px;font-size:14px}
		.tolstenko-blog-sliders .tolstenko-bs-field{margin:0 0 12px}
		.tolstenko-blog-sliders .tolstenko-bs-field input[type=text],
		.tolstenko-blog-sliders .tolstenko-bs-field input[type=number],
		.tolstenko-blog-sliders .tolstenko-bs-field textarea{width:100%;max-width:none;box-sizing:border-box}
		.tolstenko-blog-sliders .description{margin:0 0 12px}
	</style>

	<div class="tolstenko-blog-sliders">
		<p class="description">
			<?php esc_html_e( 'Блоки выводятся перед FAQ. Пустые поля — из «Настройки сайта» (Слайдер услуг / Похожие статьи).', 'tolstenko-theme' ); ?>
			<a href="<?php echo esc_url( $settings_url ); ?>"><?php esc_html_e( 'Открыть настройки', 'tolstenko-theme' ); ?></a>
		</p>

		<div class="tolstenko-bs-block">
			<h3><?php esc_html_e( 'Слайдер услуг (без фильтра)', 'tolstenko-theme' ); ?></h3>
			<p class="tolstenko-bs-field">
				<label>
					<input type="hidden" name="tolstenko_blog_services_hidden" value="0">
					<input type="checkbox" name="tolstenko_blog_services_hidden" value="1" <?php checked( $svc_hidden, '1' ); ?>>
					<?php esc_html_e( 'Скрыть слайдер услуг на этой статье', 'tolstenko-theme' ); ?>
				</label>
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_services_title"><strong><?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_blog_services_title" name="tolstenko_blog_services_title" value="<?php echo esc_attr( $svc_title ); ?>" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>">
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_services_text"><strong><?php esc_html_e( 'Текст под заголовком', 'tolstenko-theme' ); ?></strong></label><br>
				<textarea id="tolstenko_blog_services_text" name="tolstenko_blog_services_text" rows="2" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $svc_text ); ?></textarea>
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_services_ppp"><strong><?php esc_html_e( 'Количество, если услуги не выбраны (−1 = все)', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="number" id="tolstenko_blog_services_ppp" name="tolstenko_blog_services_posts_per_page" value="<?php echo esc_attr( $svc_ppp === '' || $svc_ppp === null ? '' : (string) (int) $svc_ppp ); ?>" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>">
			</p>
			<div class="tolstenko-bs-field">
				<strong><?php esc_html_e( 'Услуги', 'tolstenko-theme' ); ?></strong>
				<p class="description" style="margin:4px 0 8px;"><?php esc_html_e( 'Пусто = дефолты настроек, иначе самые новые. Кликните в поле или начните ввод — уже выбранные не показываются.', 'tolstenko-theme' ); ?></p>
				<?php
				if ( function_exists( 'tolstenko_render_post_select' ) ) {
					tolstenko_render_post_select(
						'tolstenko_blog_services_ids',
						$svc_ids,
						'service',
						'',
						array(
							'placeholder' => __( 'Поиск услуг...', 'tolstenko-theme' ),
						)
					);
				}
				?>
			</div>
		</div>

		<div class="tolstenko-bs-block">
			<h3><?php esc_html_e( 'Похожие статьи', 'tolstenko-theme' ); ?></h3>
			<p class="tolstenko-bs-field">
				<label>
					<input type="hidden" name="tolstenko_blog_related_hidden" value="0">
					<input type="checkbox" name="tolstenko_blog_related_hidden" value="1" <?php checked( $rel_hidden, '1' ); ?>>
					<?php esc_html_e( 'Скрыть блок «Похожие статьи» на этой статье', 'tolstenko-theme' ); ?>
				</label>
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_related_title"><strong><?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_blog_related_title" name="tolstenko_blog_related_title" value="<?php echo esc_attr( $rel_title ); ?>" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>">
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_related_text"><strong><?php esc_html_e( 'Текст под заголовком', 'tolstenko-theme' ); ?></strong></label><br>
				<textarea id="tolstenko_blog_related_text" name="tolstenko_blog_related_text" rows="2" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $rel_text ); ?></textarea>
			</p>
			<p class="tolstenko-bs-field">
				<label for="tolstenko_blog_related_ppp"><strong><?php esc_html_e( 'Количество, если статьи не выбраны (−1 = все)', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="number" id="tolstenko_blog_related_ppp" name="tolstenko_blog_related_posts_per_page" value="<?php echo esc_attr( $rel_ppp === '' || $rel_ppp === null ? '' : (string) (int) $rel_ppp ); ?>" placeholder="<?php esc_attr_e( 'Пусто = из общих настроек', 'tolstenko-theme' ); ?>">
			</p>
			<div class="tolstenko-bs-field">
				<strong><?php esc_html_e( 'Статьи', 'tolstenko-theme' ); ?></strong>
				<p class="description" style="margin:4px 0 8px;"><?php esc_html_e( 'Пусто = дефолты настроек, иначе самые новые (текущая статья исключается). Кликните в поле или начните ввод — уже выбранные не показываются.', 'tolstenko-theme' ); ?></p>
				<?php
				if ( function_exists( 'tolstenko_render_post_select' ) ) {
					tolstenko_render_post_select(
						'tolstenko_blog_related_ids',
						$rel_ids,
						'blog',
						'',
						array(
							'exclude_ids' => $rel_exclude,
							'placeholder' => __( 'Поиск статей...', 'tolstenko-theme' ),
						)
					);
				}
				?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post.
 */
function tolstenko_blog_sliders_save_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['tolstenko_blog_sliders_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_sliders_nonce'] ) ), 'tolstenko_blog_sliders_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, 'blog_services_hidden', ! empty( $_POST['tolstenko_blog_services_hidden'] ) ? '1' : '0' );
	update_post_meta(
		$post_id,
		'blog_services_title',
		isset( $_POST['tolstenko_blog_services_title'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_services_title'] ) ) : ''
	);
	update_post_meta(
		$post_id,
		'blog_services_text',
		isset( $_POST['tolstenko_blog_services_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_blog_services_text'] ) ) : ''
	);
	if ( isset( $_POST['tolstenko_blog_services_posts_per_page'] ) && $_POST['tolstenko_blog_services_posts_per_page'] !== '' ) {
		update_post_meta( $post_id, 'blog_services_posts_per_page', (int) $_POST['tolstenko_blog_services_posts_per_page'] );
	} else {
		delete_post_meta( $post_id, 'blog_services_posts_per_page' );
	}
	$svc_ids = isset( $_POST['tolstenko_blog_services_ids'] ) ? tolstenko_blog_sliders_sanitize_ids( wp_unslash( $_POST['tolstenko_blog_services_ids'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	update_post_meta( $post_id, 'blog_services_ids', $svc_ids );

	update_post_meta( $post_id, 'blog_related_hidden', ! empty( $_POST['tolstenko_blog_related_hidden'] ) ? '1' : '0' );
	update_post_meta(
		$post_id,
		'blog_related_title',
		isset( $_POST['tolstenko_blog_related_title'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_related_title'] ) ) : ''
	);
	update_post_meta(
		$post_id,
		'blog_related_text',
		isset( $_POST['tolstenko_blog_related_text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['tolstenko_blog_related_text'] ) ) : ''
	);
	if ( isset( $_POST['tolstenko_blog_related_posts_per_page'] ) && $_POST['tolstenko_blog_related_posts_per_page'] !== '' ) {
		update_post_meta( $post_id, 'blog_related_posts_per_page', (int) $_POST['tolstenko_blog_related_posts_per_page'] );
	} else {
		delete_post_meta( $post_id, 'blog_related_posts_per_page' );
	}
	$rel_ids = isset( $_POST['tolstenko_blog_related_ids'] ) ? tolstenko_blog_sliders_sanitize_ids( wp_unslash( $_POST['tolstenko_blog_related_ids'] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$rel_ids = array_values( array_diff( $rel_ids, array( (int) $post_id ) ) );
	update_post_meta( $post_id, 'blog_related_ids', $rel_ids );
}
