<?php
/**
 * Настройки сайта → Авторы статей (без ACF Pro).
 * Главный автор — fallback, если в записи автор не выбран.
 * Список «Другие авторы» — для выбора в статье / вакансии.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOLSTENKO_BLOG_AUTHORS_OPTION', 'tolstenko_blog_authors' );
define( 'TOLSTENKO_BLOG_MAIN_AUTHOR_OPTION', 'tolstenko_blog_main_author' );

add_action( 'admin_menu', 'tolstenko_register_blog_authors_admin_page', 21 );
add_action( 'admin_enqueue_scripts', 'tolstenko_blog_authors_admin_assets' );

/**
 * Нормализация строки автора.
 *
 * @param mixed $row Raw row.
 * @return array{photo:int,name:string,job_title:string,position:string,description:string}|null
 */
function tolstenko_normalize_blog_author_row( $row ) {
	if ( ! is_array( $row ) ) {
		return null;
	}
	$author = array(
		'photo'       => (int) ( $row['photo'] ?? 0 ),
		'name'        => trim( (string) ( $row['name'] ?? '' ) ),
		'job_title'   => trim( (string) ( $row['job_title'] ?? '' ) ),
		'position'    => trim( (string) ( $row['position'] ?? '' ) ),
		'description' => trim( (string) ( $row['description'] ?? '' ) ),
	);
	if (
		$author['name'] === ''
		&& $author['job_title'] === ''
		&& $author['position'] === ''
		&& $author['description'] === ''
		&& ! $author['photo']
	) {
		return null;
	}
	return $author;
}

/**
 * Главный автор (показывается, если в записи автор не выбран).
 *
 * @return array{photo:int,name:string,job_title:string,position:string,description:string}|null
 */
function tolstenko_get_blog_main_author() {
	return tolstenko_normalize_blog_author_row( get_option( TOLSTENKO_BLOG_MAIN_AUTHOR_OPTION, array() ) );
}

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
		$author = tolstenko_normalize_blog_author_row( $row );
		if ( $author ) {
			$out[] = $author;
		}
	}

	return $out;
}

/**
 * @param array<string, mixed> $author Author row.
 * @param int                  $index  Zero-based index.
 * @return string
 */
function tolstenko_get_blog_author_option_label( array $author, $index ) {
	$name = trim( (string) ( $author['name'] ?? '' ) );
	$job  = trim( (string) ( $author['job_title'] ?? '' ) );
	if ( $name === '' && $job === '' ) {
		return sprintf(
			/* translators: %d author number */
			__( 'Автор #%d', 'tolstenko-theme' ),
			$index + 1
		);
	}
	if ( $name !== '' && $job !== '' ) {
		return $name . ' — ' . $job;
	}
	return $name !== '' ? $name : $job;
}

/**
 * @param mixed $index Author index from meta/option.
 * @return array{photo:int,name:string,job_title:string,position:string,description:string}|null
 */
function tolstenko_get_blog_author_by_index( $index ) {
	if ( $index === null || $index === '' || $index === false ) {
		return null;
	}
	$authors = tolstenko_get_blog_authors_list();
	if ( ! isset( $authors[ (int) $index ] ) || ! is_array( $authors[ (int) $index ] ) ) {
		return null;
	}
	return $authors[ (int) $index ];
}

/**
 * @param string $field_name   Select name attribute.
 * @param string $selected     Selected index.
 * @param string $empty_label  Label for empty option.
 * @param string $select_id    Optional id attribute.
 */
function tolstenko_render_blog_author_select( $field_name, $selected, $empty_label, $select_id = '' ) {
	$authors = tolstenko_get_blog_authors_list();
	$selected = (string) $selected;
	?>
	<select<?php echo $select_id !== '' ? ' id="' . esc_attr( $select_id ) . '"' : ''; ?> name="<?php echo esc_attr( $field_name ); ?>" style="width:100%;max-width:none;box-sizing:border-box">
		<option value=""><?php echo esc_html( $empty_label ); ?></option>
		<?php foreach ( $authors as $index => $author ) : ?>
			<option value="<?php echo esc_attr( (string) $index ); ?>" <?php selected( $selected, (string) $index ); ?>>
				<?php echo esc_html( tolstenko_get_blog_author_option_label( $author, (int) $index ) ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}

/**
 * Персона в сайдбаре вакансии:
 * индекс автора блока → дефолт шаблона → главный автор → legacy-поля.
 *
 * @param string|null $author_index Индекс из block_vacancy_content_sidebar_author.
 * @return array{photo_id:int,name:string,text:string}
 */
function tolstenko_get_vacancy_sidebar_person( $author_index = null ) {
	$defaults = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'vacancy_content' )
		: array();

	if ( $author_index === null || $author_index === '' ) {
		$author_index = (string) ( $defaults['sidebar_author'] ?? '' );
	} else {
		$author_index = (string) $author_index;
	}

	$author = tolstenko_get_blog_author_by_index( $author_index );
	if ( ! is_array( $author ) ) {
		$author = tolstenko_get_blog_main_author();
	}
	if ( is_array( $author ) ) {
		return array(
			'photo_id' => (int) ( $author['photo'] ?? 0 ),
			'name'     => trim( (string) ( $author['name'] ?? '' ) ),
			'text'     => trim( (string) ( $author['description'] ?? '' ) ),
		);
	}

	return array(
		'photo_id' => (int) ( $defaults['sidebar_photo'] ?? 0 ),
		'name'     => trim( (string) ( $defaults['sidebar_name'] ?? '' ) ),
		'text'     => trim( (string) ( $defaults['sidebar_text'] ?? '' ) ),
	);
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
		return;
	}

	if (
		isset( $_POST['tolstenko_blog_authors_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_authors_nonce'] ) ), 'tolstenko_blog_authors_save' )
	) {
		$main_raw = isset( $_POST['tolstenko_blog_main_author'] ) && is_array( $_POST['tolstenko_blog_main_author'] )
			? wp_unslash( $_POST['tolstenko_blog_main_author'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();
		$main_save = array(
			'photo'       => (int) ( $main_raw['photo'] ?? 0 ),
			'name'        => sanitize_text_field( (string) ( $main_raw['name'] ?? '' ) ),
			'job_title'   => sanitize_text_field( (string) ( $main_raw['job_title'] ?? '' ) ),
			'position'    => sanitize_text_field( (string) ( $main_raw['position'] ?? '' ) ),
			'description' => sanitize_textarea_field( (string) ( $main_raw['description'] ?? '' ) ),
		);
		if (
			$main_save['name'] === ''
			&& $main_save['job_title'] === ''
			&& $main_save['position'] === ''
			&& $main_save['description'] === ''
			&& ! $main_save['photo']
		) {
			delete_option( TOLSTENKO_BLOG_MAIN_AUTHOR_OPTION );
		} else {
			update_option( TOLSTENKO_BLOG_MAIN_AUTHOR_OPTION, $main_save, false );
		}

		$raw  = isset( $_POST['tolstenko_blog_authors'] ) && is_array( $_POST['tolstenko_blog_authors'] )
			? wp_unslash( $_POST['tolstenko_blog_authors'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: array();
		$save = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$name  = sanitize_text_field( (string) ( $row['name'] ?? '' ) );
			$job   = sanitize_text_field( (string) ( $row['job_title'] ?? '' ) );
			$pos   = sanitize_text_field( (string) ( $row['position'] ?? '' ) );
			$desc  = sanitize_textarea_field( (string) ( $row['description'] ?? '' ) );
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
		update_option( TOLSTENKO_BLOG_AUTHORS_OPTION, $save, false );
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Авторы сохранены.', 'tolstenko-theme' ) . '</p></div>';
	}

	$main_author = tolstenko_get_blog_main_author();
	if ( ! is_array( $main_author ) ) {
		$main_author = array(
			'photo'       => 0,
			'name'        => '',
			'job_title'   => '',
			'position'    => '',
			'description' => '',
		);
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
		<p class="description">
			<?php esc_html_e( 'Главный автор показывается в статье и сайдбаре, если в записи автор не выбран. Остальные авторы — для выбора в карточке статьи / акции / вакансии.', 'tolstenko-theme' ); ?>
		</p>

		<form method="post">
			<?php wp_nonce_field( 'tolstenko_blog_authors_save', 'tolstenko_blog_authors_nonce' ); ?>

			<div id="tolstenko-blog-authors" class="tolstenko-ba">
				<style>
					.tolstenko-ba .tolstenko-ba-row{border:1px solid #dcdcde;background:#fff;padding:12px;margin:0 0 12px;max-width:720px}
					.tolstenko-ba .tolstenko-ba-row--main{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}
					.tolstenko-ba .tolstenko-ba-section-title{margin:24px 0 8px;font-size:15px}
					.tolstenko-ba .tolstenko-ba-section-title:first-child{margin-top:8px}
					.tolstenko-ba .tolstenko-ba-grid{display:grid;grid-template-columns:120px 1fr;gap:12px;align-items:start}
					.tolstenko-ba .tolstenko-ba-fields label{display:block;font-weight:600;margin:0 0 4px}
					.tolstenko-ba .tolstenko-ba-fields input,
					.tolstenko-ba .tolstenko-ba-fields textarea{width:100%;margin:0 0 8px}
					.tolstenko-ba .tolstenko-ba-preview img{max-width:100px;height:auto;display:block;margin-bottom:8px}
					.tolstenko-ba .tolstenko-ba-actions{margin-top:8px}
					.tolstenko-ba .tolstenko-ba-badge{display:inline-block;margin:0 0 10px;padding:2px 8px;border-radius:3px;background:#2271b1;color:#fff;font-size:11px;line-height:1.6;font-weight:600}
				</style>

				<h2 class="tolstenko-ba-section-title"><?php esc_html_e( 'Главный автор', 'tolstenko-theme' ); ?></h2>
				<p class="description" style="margin-top:0;max-width:720px;">
					<?php esc_html_e( 'Используется по умолчанию, когда в статье / акции автор не выбран.', 'tolstenko-theme' ); ?>
				</p>
				<?php
				tolstenko_render_blog_author_admin_row(
					'main',
					$main_author,
					array(
						'field_name' => 'tolstenko_blog_main_author',
						'is_main'    => true,
					)
				);
				?>

				<h2 class="tolstenko-ba-section-title"><?php esc_html_e( 'Другие авторы', 'tolstenko-theme' ); ?></h2>
				<p class="description" style="margin-top:0;max-width:720px;">
					<?php esc_html_e( 'Этих авторов можно выбрать в карточке записи.', 'tolstenko-theme' ); ?>
				</p>

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
 * @param string               $index   Row index.
 * @param array                $author  Author data.
 * @param array<string, mixed> $options Optional: field_name, is_main.
 */
function tolstenko_render_blog_author_admin_row( $index, array $author, $options = array() ) {
	$photo      = (int) ( $author['photo'] ?? 0 );
	$url        = $photo ? (string) wp_get_attachment_image_url( $photo, 'thumbnail' ) : '';
	$field_name = ! empty( $options['field_name'] )
		? (string) $options['field_name']
		: ( 'tolstenko_blog_authors[' . $index . ']' );
	$is_main = ! empty( $options['is_main'] );
	$row_class = 'tolstenko-ba-row' . ( $is_main ? ' tolstenko-ba-row--main' : '' );
	?>
	<div class="<?php echo esc_attr( $row_class ); ?>">
		<?php if ( $is_main ) : ?>
			<span class="tolstenko-ba-badge"><?php esc_html_e( 'Главный', 'tolstenko-theme' ); ?></span>
		<?php endif; ?>
		<div class="tolstenko-ba-grid">
			<div>
				<div class="tolstenko-ba-preview" data-ba-preview>
					<?php if ( $url ) : ?>
						<img src="<?php echo esc_url( $url ); ?>" alt="">
					<?php endif; ?>
				</div>
				<input type="hidden" data-ba-photo name="<?php echo esc_attr( $field_name . '[photo]' ); ?>" value="<?php echo (int) $photo; ?>">
				<button type="button" class="button button-small" data-ba-pick><?php esc_html_e( 'Фото', 'tolstenko-theme' ); ?></button>
				<button type="button" class="button button-small" data-ba-clear><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
			</div>
			<div class="tolstenko-ba-fields">
				<label><?php esc_html_e( 'Имя', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $field_name . '[name]' ); ?>" value="<?php echo esc_attr( (string) ( $author['name'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Должность', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $field_name . '[job_title]' ); ?>" value="<?php echo esc_attr( (string) ( $author['job_title'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Позиция', 'tolstenko-theme' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $field_name . '[position]' ); ?>" value="<?php echo esc_attr( (string) ( $author['position'] ?? '' ) ); ?>">
				<label><?php esc_html_e( 'Описание (сайдбар)', 'tolstenko-theme' ); ?></label>
				<textarea name="<?php echo esc_attr( $field_name . '[description]' ); ?>" rows="4" placeholder="<?php esc_attr_e( 'Большой текст под именем в правом блоке статьи', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $author['description'] ?? '' ) ); ?></textarea>
				<?php if ( ! $is_main ) : ?>
					<div class="tolstenko-ba-actions">
						<button type="button" class="button-link-delete" data-ba-remove><?php esc_html_e( 'Удалить автора', 'tolstenko-theme' ); ?></button>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
