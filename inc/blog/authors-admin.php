<?php
/**
 * Настройки сайта → Авторы статей (без ACF Pro).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOLSTENKO_BLOG_AUTHORS_OPTION', 'tolstenko_blog_authors' );

add_action( 'admin_menu', 'tolstenko_register_blog_authors_admin_page', 21 );
add_action( 'admin_enqueue_scripts', 'tolstenko_blog_authors_admin_assets' );

/**
 * @return array<int, array{photo:int,name:string,job_title:string,position:string,description:string}>
 */
function tolstenko_get_blog_authors_list() {
	$list = get_option( TOLSTENKO_BLOG_AUTHORS_OPTION, array() );
	if ( ! is_array( $list ) ) {
		return array();
	}

	$out = array();
	foreach ( $list as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$out[] = array(
			'photo'       => (int) ( $row['photo'] ?? 0 ),
			'name'        => trim( (string) ( $row['name'] ?? '' ) ),
			'job_title'   => trim( (string) ( $row['job_title'] ?? '' ) ),
			'position'    => trim( (string) ( $row['position'] ?? '' ) ),
			'description' => trim( (string) ( $row['description'] ?? '' ) ),
		);
	}

	return $out;
}

function tolstenko_register_blog_authors_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Авторы статей', 'tolstenko-theme' ),
		__( 'Авторы статей', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-blog-authors',
		'tolstenko_render_blog_authors_admin_page'
	);
}

function tolstenko_blog_authors_admin_assets( $hook ) {
	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'tolstenko-blog-authors' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	wp_enqueue_media();
}

function tolstenko_render_blog_authors_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для редактирования авторов статей.', 'tolstenko-theme' ), 403 );
	}

	$posted = ! empty( $_POST );
	if (
		$posted
		&& ( ! isset( $_POST['tolstenko_blog_authors_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_authors_nonce'] ) ), 'tolstenko_blog_authors_save' ) )
	) {
		tolstenko_admin_notice_nonce_failed();
	} elseif ( $posted ) {
		$raw  = isset( $_POST['tolstenko_blog_authors'] ) && is_array( $_POST['tolstenko_blog_authors'] )
			? wp_unslash( $_POST['tolstenko_blog_authors'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();
		$save = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name = sanitize_text_field( (string) ( $row['name'] ?? '' ) );
			$job  = sanitize_text_field( (string) ( $row['job_title'] ?? '' ) );
			$pos  = sanitize_text_field( (string) ( $row['position'] ?? '' ) );
			$desc = sanitize_textarea_field( (string) ( $row['description'] ?? '' ) );
			$photo = (int) ( $row['photo'] ?? 0 );
			if ( $name === '' && $job === '' && $pos === '' && $desc === '' && ! $photo ) {
				continue;
			}
			$save[] = array(
				'photo'       => $photo,
				'name'        => $name,
				'job_title'   => $job,
				'position'    => $pos,
				'description' => $desc,
			);
		}
		if ( tolstenko_update_option_checked( TOLSTENKO_BLOG_AUTHORS_OPTION, $save, false ) ) {
			tolstenko_admin_notice( __( 'Авторы сохранены.', 'tolstenko-theme' ), 'success' );
		} else {
			tolstenko_admin_notice_save_failed();
		}
	}

	$authors = tolstenko_get_blog_authors_list();
	if ( empty( $authors ) ) {
		$authors = array(
			array(
				'photo'       => 0,
				'name'        => '',
				'job_title'   => '',
				'position'    => '',
				'description' => '',
			),
		);
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Авторы статей', 'tolstenko-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Список авторов для блока в шапке статьи (CPT «Статьи»).', 'tolstenko-theme' ); ?></p>

		<form method="post">
			<?php wp_nonce_field( 'tolstenko_blog_authors_save', 'tolstenko_blog_authors_nonce' ); ?>

			<div id="tolstenko-blog-authors" class="tolstenko-ba">
				<style>
					.tolstenko-ba .tolstenko-ba-row{border:1px solid #dcdcde;background:#fff;padding:12px;margin:0 0 12px;max-width:720px}
					.tolstenko-ba .tolstenko-ba-grid{display:grid;grid-template-columns:120px 1fr;gap:12px;align-items:start}
					.tolstenko-ba .tolstenko-ba-fields label{display:block;font-weight:600;margin:0 0 4px}
					.tolstenko-ba .tolstenko-ba-fields input,
					.tolstenko-ba .tolstenko-ba-fields textarea{width:100%;margin:0 0 8px}
					.tolstenko-ba .tolstenko-ba-preview img{max-width:100px;height:auto;display:block;margin-bottom:8px}
					.tolstenko-ba .tolstenko-ba-actions{margin-top:8px}
				</style>

				<div data-ba-list>
					<?php foreach ( $authors as $i => $author ) : ?>
						<?php tolstenko_render_blog_author_admin_row( (string) $i, $author ); ?>
					<?php endforeach; ?>
				</div>

				<p>
					<button type="button" class="button" data-ba-add><?php esc_html_e( 'Добавить автора', 'tolstenko-theme' ); ?></button>
				</p>

				<template data-ba-tpl>
					<?php
					tolstenko_render_blog_author_admin_row(
						'__INDEX__',
						array(
							'photo'       => 0,
							'name'        => '',
							'job_title'   => '',
							'position'    => '',
							'description' => '',
						)
					);
					?>
				</template>
			</div>

			<?php submit_button( __( 'Сохранить авторов', 'tolstenko-theme' ) ); ?>
		</form>
	</div>
	<script>
	(function(){
		const root = document.getElementById('tolstenko-blog-authors');
		if (!root) return;
		const list = root.querySelector('[data-ba-list]');
		const tpl = root.querySelector('[data-ba-tpl]');
		root.addEventListener('click', function(e){
			const add = e.target.closest('[data-ba-add]');
			if (add && tpl && list) {
				const idx = Date.now().toString();
				list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, idx));
				return;
			}
			const remove = e.target.closest('[data-ba-remove]');
			if (remove) {
				const row = remove.closest('.tolstenko-ba-row');
				if (row) row.remove();
				return;
			}
			const pick = e.target.closest('[data-ba-pick]');
			if (pick && window.wp && wp.media) {
				const row = pick.closest('.tolstenko-ba-row');
				const input = row && row.querySelector('[data-ba-photo]');
				const preview = row && row.querySelector('[data-ba-preview]');
				const frame = wp.media({ title: 'Фото автора', multiple: false, library: { type: 'image' } });
				frame.on('select', function(){
					const att = frame.state().get('selection').first().toJSON();
					if (!att || !input) return;
					input.value = String(att.id || 0);
					if (preview) {
						preview.innerHTML = att.url ? '<img src="'+att.url+'" alt="">' : '';
					}
				});
				frame.open();
				return;
			}
			const clear = e.target.closest('[data-ba-clear]');
			if (clear) {
				const row = clear.closest('.tolstenko-ba-row');
				const input = row && row.querySelector('[data-ba-photo]');
				const preview = row && row.querySelector('[data-ba-preview]');
				if (input) input.value = '0';
				if (preview) preview.innerHTML = '';
			}
		});
	})();
	</script>
	<?php
}

/**
 * @param string $index Row index.
 * @param array  $author Author data.
 */
function tolstenko_render_blog_author_admin_row( $index, array $author ) {
	$photo = (int) ( $author['photo'] ?? 0 );
	$url   = $photo ? (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
	$name  = 'tolstenko_blog_authors[' . $index . ']';
	?>
	<div class="tolstenko-ba-row">
		<div class="tolstenko-ba-grid">
			<div>
				<div class="tolstenko-ba-preview" data-ba-preview>
					<?php if ( $url ) : ?>
						<img src="<?php echo esc_url( $url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" data-ba-photo name="<?php echo esc_attr( $name . '[photo]' ); ?>" value="<?php echo (int) $photo; ?>">
				<button type="button" class="button button-small" data-ba-pick><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
				<button type="button" class="button button-small" data-ba-clear><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-ba-fields">
				<label><?php esc_html_e( 'Имя', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $name . '[name]' ); ?>" value="<?php echo esc_attr( (string) ( $author['name'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Должность', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $name . '[job_title]' ); ?>" value="<?php echo esc_attr( (string) ( $author['job_title'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Позиция', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $name . '[position]' ); ?>" value="<?php echo esc_attr( (string) ( $author['position'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Описание (сайдбар)', 'tolstenko-theme' ); ?></label>
				<textarea name="<?php echo esc_attr( $name . '[description]' ); ?>" rows="4" placeholder="<?php esc_attr_e( 'Большой текст под именем в правом блоке статьи', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $author['description'] ?? '' ) ); ?></textarea>
				<div class="tolstenko-ba-actions">
					<button type="button" class="button-link-delete" data-ba-remove><?php esc_html_e( 'Удалить автора', 'tolstenko-theme' ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
