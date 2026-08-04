<?php
/**
 * Блоки тела статьи/акции (flexible content):
 * — в редакторе только у CPT blog и actions;
 * — «Настройки сайта → Блоки для статей» = дефолтное наполнение (не выбор видимости).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'tolstenko_register_blog_content_defaults_admin_page', 22 );
add_action( 'admin_enqueue_scripts', 'tolstenko_blog_content_defaults_admin_assets' );

/**
 * CPT, где доступны блоки гибкого содержимого тела.
 *
 * @return string[]
 */
function tolstenko_get_content_body_post_types() {
	return array( 'blog', 'actions' );
}

/**
 * @param string|null $post_type Post type or null = current.
 * @return bool
 */
function tolstenko_is_content_body_post_type( $post_type = null ) {
	if ( $post_type === null ) {
		$post_type = get_post_type();
	}
	return in_array( (string) $post_type, tolstenko_get_content_body_post_types(), true );
}

/**
 * @return bool
 */
function tolstenko_is_content_body_singular() {
	return is_singular( tolstenko_get_content_body_post_types() );
}

/**
 * BEM-префикс оболочки single (статья / акция).
 *
 * @return string
 */
function tolstenko_get_single_content_bem() {
	return is_singular( 'actions' ) ? 'single-actions' : 'single-blog';
}

/**
 * В теле акции блоки отдают классы single-blog__* — подменяем на single-actions__*.
 *
 * @param string $html HTML.
 * @return string
 */
function tolstenko_adapt_single_content_classes( $html ) {
	$html = (string) $html;
	if ( $html === '' || ! is_singular( 'actions' ) ) {
		return $html;
	}
	return str_replace( 'single-blog__', 'single-actions__', $html );
}

/**
 * Каталог блоков гибкого содержимого (подписи для админки).
 *
 * @return array<string, string>
 */
function tolstenko_get_blog_theme_blocks_catalog() {
	$catalog = array(
		'tolstenko/blog-large-img'   => __( 'Крупное фото', 'tolstenko-theme' ),
		'tolstenko/blog-imgs'        => __( 'Два фото', 'tolstenko-theme' ),
		'tolstenko/blog-video'       => __( 'Видео', 'tolstenko-theme' ),
		'tolstenko/blog-blockquote'  => __( 'Цитата', 'tolstenko-theme' ),
		'tolstenko/blog-number-list' => __( 'Нумерованный список', 'tolstenko-theme' ),
		'tolstenko/blog-warning'     => __( 'Предупреждения', 'tolstenko-theme' ),
		'tolstenko/blog-seo'         => __( 'SEO / CTA', 'tolstenko-theme' ),
		'tolstenko/blog-table'       => __( 'Таблица', 'tolstenko-theme' ),
		'tolstenko/consultation-whatsapp' => __( 'Консультация WhatsApp', 'tolstenko-theme' ),
		'tolstenko/consultation-tg'       => __( 'Консультация Telegram', 'tolstenko-theme' ),
	);

	return apply_filters( 'tolstenko_blog_theme_blocks_catalog', $catalog );
}

/**
 * Имена блоков, которые можно вставлять только в blog/actions.
 * (consultation-* остаются и на услугах — их сюда не включаем.)
 *
 * @return string[]
 */
function tolstenko_get_blog_content_only_block_names() {
	$names = function_exists( 'tolstenko_get_blog_content_block_names' )
		? tolstenko_get_blog_content_block_names()
		: array();
	// Алиасы старого namespace.
	$aliases = array();
	foreach ( $names as $name ) {
		$aliases[] = str_replace( 'tolstenko/', 'koritan/', $name );
	}
	return array_values( array_unique( array_merge( $names, $aliases ) ) );
}

/**
 * Базовые Gutenberg-блоки для текста.
 *
 * @return string[]
 */
function tolstenko_get_blog_core_writing_blocks() {
	$blocks = array(
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/image',
		'core/gallery',
		'core/quote',
		'core/table',
		'core/embed',
		'core/html',
		'core/separator',
		'core/spacer',
		'core/shortcode',
		'core/freeform',
		'core/missing',
		'core/block',
		'core/group',
		'core/columns',
		'core/column',
		'core/buttons',
		'core/button',
		'core/video',
		'core/audio',
		'core/file',
		'core/code',
		'core/preformatted',
		'core/pullquote',
	);
	return apply_filters( 'tolstenko_blog_core_writing_blocks', $blocks );
}

/**
 * Полный allowlist редактора для CPT blog / actions.
 *
 * @return string[]
 */
function tolstenko_get_blog_editor_allowed_blocks() {
	$theme = array_keys( tolstenko_get_blog_theme_blocks_catalog() );
	// Алиасы koritan/* для старого контента.
	$aliases = array();
	foreach ( $theme as $name ) {
		$aliases[] = str_replace( 'tolstenko/', 'koritan/', $name );
	}
	return array_values(
		array_unique(
			array_merge(
				tolstenko_get_blog_core_writing_blocks(),
				$theme,
				$aliases
			)
		)
	);
}

/**
 * Схема дефолтов блоков тела статьи.
 *
 * @return array<string, array>
 */
function tolstenko_blog_content_defaults_schema() {
	return array(
		'blog_large_img' => array(
			'image' => 0,
		),
		'blog_imgs' => array(
			'left'  => 0,
			'right' => 0,
		),
		'blog_video' => array(
			'preview' => 0,
			'url'     => '',
			'iframe'  => '',
		),
		'blog_blockquote' => array(
			'text'         => '',
			'link'         => '',
			'show_author'  => false,
			'image'        => 0,
			'author'       => '',
			'author_under' => '',
			'btn_text'     => '',
			'btn_url'      => '',
		),
		'blog_number_list' => array(
			'items' => array(),
		),
		'blog_warning' => array(
			'items' => array(),
		),
		'blog_seo' => array(
			'title'   => 'Нужна помощь с продвижением?',
			'btn'     => 'Получить консультацию',
			'btn_url' => '',
		),
		'blog_table' => array(
			'use_header' => true,
			'header'     => array(),
			'rows'       => array(),
		),
	);
}

/**
 * @param string $key Schema key.
 * @return array
 */
function tolstenko_get_blog_content_defaults( $key ) {
	$schema = tolstenko_blog_content_defaults_schema();
	$base   = isset( $schema[ $key ] ) ? $schema[ $key ] : array();
	if ( function_exists( 'tolstenko_get_block_defaults' ) ) {
		$saved = tolstenko_get_block_defaults( $key );
		if ( is_array( $saved ) && $saved ) {
			return array_replace_recursive( $base, $saved );
		}
	}
	return $base;
}

function tolstenko_register_blog_content_defaults_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Блоки для статей', 'tolstenko-theme' ),
		__( 'Блоки для статей', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-blog-blocks',
		'tolstenko_render_blog_content_defaults_admin_page'
	);
}

/**
 * @param string $hook Hook.
 */
function tolstenko_blog_content_defaults_admin_assets( $hook ) {
	unset( $hook );
	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'tolstenko-blog-blocks' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	wp_enqueue_media();
}

/**
 * Поле выбора картинки для дефолтов.
 *
 * @param string $name  Input name.
 * @param int    $id    Attachment ID.
 * @param string $label Label.
 */
function tolstenko_blog_content_defaults_image_field( $name, $id, $label ) {
	$id  = (int) $id;
	$url = $id ? (string) wp_get_attachment_image_url( $id, 'medium' ) : '';
	?>
	<div class="tolstenko-bcd-image" data-bcd-image>
		<label><strong><?php echo esc_html( $label ); ?></strong></label>
		<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $id ); ?>" data-bcd-id>
		<div class="tolstenko-bcd-image__preview" data-bcd-preview>
			<?php if ( $url ) : ?>
				<img src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:160px;height:auto;display:block;margin:6px 0;">
			<?php endif; ?>
		</div>
		<p>
			<button type="button" class="button" data-bcd-pick><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
			<button type="button" class="button-link-delete" data-bcd-clear <?php echo $id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
		</p>
	</div>
	<?php
}

function tolstenko_render_blog_content_defaults_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для редактирования дефолтов блоков статьи.', 'tolstenko-theme' ), 403 );
	}

	$posted = ! empty( $_POST );
	if ( $posted
		&& ( ! isset( $_POST['tolstenko_blog_blocks_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_blocks_nonce'] ) ), 'tolstenko_blog_content_defaults_save' ) )
	) {
		tolstenko_admin_notice_nonce_failed();
	} elseif ( $posted ) {
		if ( tolstenko_save_blog_content_defaults_from_request() ) {
			tolstenko_admin_notice( __( 'Дефолты блоков тела статьи сохранены.', 'tolstenko-theme' ), 'success' );
		} else {
			tolstenko_admin_notice_save_failed();
		}
	}

	$schema = tolstenko_blog_content_defaults_schema();
	$all    = array();
	foreach ( array_keys( $schema ) as $key ) {
		$all[ $key ] = tolstenko_get_blog_content_defaults( $key );
	}

	$li   = $all['blog_large_img'];
	$imgs = $all['blog_imgs'];
	$vid  = $all['blog_video'];
	$bq   = $all['blog_blockquote'];
	$nl   = $all['blog_number_list'];
	$wn   = $all['blog_warning'];
	$seo  = $all['blog_seo'];
	$tbl  = $all['blog_table'];

	$nl_items = ! empty( $nl['items'] ) && is_array( $nl['items'] ) ? $nl['items'] : array( array( 'text' => '' ) );
	$wn_items = ! empty( $wn['items'] ) && is_array( $wn['items'] ) ? $wn['items'] : array( array( 'type' => 'warn', 'text' => '', 'icon' => 0 ) );
	$header   = is_array( $tbl['header'] ?? null ) ? $tbl['header'] : array();
	$rows     = is_array( $tbl['rows'] ?? null ) ? $tbl['rows'] : array();
	if ( ! $header ) {
		$header = array( '', '' );
	}
	if ( ! $rows ) {
		$rows = array( array( 'cells' => array( '', '' ) ) );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Блоки для статей', 'tolstenko-theme' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Дефолтное наполнение блоков тела статьи и акции. В редакторе эти блоки доступны только у записей «Статья» и «Акция». Пустые поля в блоке на записи подставляют значения отсюда. WhatsApp / Telegram — во вкладке «Дефолты блоков».', 'tolstenko-theme' ); ?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=tolstenko-site-settings' ) ); ?>"><?php esc_html_e( 'Открыть дефолты блоков', 'tolstenko-theme' ); ?></a>
		</p>

		<form method="post" action="" id="tolstenko-blog-content-defaults">
			<?php wp_nonce_field( 'tolstenko_blog_content_defaults_save', 'tolstenko_blog_blocks_nonce' ); ?>

			<style>
				.tolstenko-bcd-panel{background:#fff;border:1px solid #dcdcde;padding:14px 16px;margin:0 0 14px;max-width:860px}
				.tolstenko-bcd-panel h2{margin:0 0 10px;font-size:15px}
				.tolstenko-bcd-panel .row{margin:0 0 10px}
				.tolstenko-bcd-panel input[type=text],
				.tolstenko-bcd-panel input[type=url],
				.tolstenko-bcd-panel textarea{width:100%;max-width:720px}
				.tolstenko-bcd-item{border:1px solid #dcdcde;background:#f6f7f7;padding:10px;margin:0 0 8px;max-width:720px}
				.tolstenko-bcd-tabs{display:flex;flex-wrap:wrap;gap:6px;margin:12px 0}
				.tolstenko-bcd-tab{cursor:pointer}
				.tolstenko-bcd-tab.is-active{font-weight:600}
				.tolstenko-bcd-panel[hidden]{display:none!important}
			</style>

			<div class="tolstenko-bcd-tabs">
				<?php
				$tabs = array(
					'blog_seo'         => __( 'SEO / CTA', 'tolstenko-theme' ),
					'blog_blockquote'  => __( 'Цитата', 'tolstenko-theme' ),
					'blog_number_list' => __( 'Список', 'tolstenko-theme' ),
					'blog_warning'     => __( 'Предупреждения', 'tolstenko-theme' ),
					'blog_table'       => __( 'Таблица', 'tolstenko-theme' ),
					'blog_large_img'   => __( 'Крупное фото', 'tolstenko-theme' ),
					'blog_imgs'        => __( 'Два фото', 'tolstenko-theme' ),
					'blog_video'       => __( 'Видео', 'tolstenko-theme' ),
				);
				$first = true;
				foreach ( $tabs as $key => $label ) :
					?>
					<button type="button" class="button tolstenko-bcd-tab<?php echo $first ? ' is-active' : ''; ?>" data-bcd-tab="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
					<?php
					$first = false;
				endforeach;
				?>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_seo">
				<h2><?php esc_html_e( 'SEO / CTA', 'tolstenko-theme' ); ?></h2>
				<div class="row"><input type="text" name="tolstenko_block_defaults[blog_seo][title]" value="<?php echo esc_attr( (string) ( $seo['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Заголовок', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[blog_seo][btn]" value="<?php echo esc_attr( (string) ( $seo['btn'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Текст кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="url" name="tolstenko_block_defaults[blog_seo][btn_url]" value="<?php echo esc_attr( (string) ( $seo['btn_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Ссылка (пусто = #modal)', 'tolstenko-theme' ); ?>"></div>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_blockquote" hidden>
				<h2><?php esc_html_e( 'Цитата', 'tolstenko-theme' ); ?></h2>
				<div class="row"><textarea name="tolstenko_block_defaults[blog_blockquote][text]" rows="4" placeholder="<?php esc_attr_e( 'Текст цитаты', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $bq['text'] ?? '' ) ); ?></textarea></div>
				<div class="row"><input type="url" name="tolstenko_block_defaults[blog_blockquote][link]" value="<?php echo esc_attr( (string) ( $bq['link'] ?? '' ) ); ?>" placeholder="cite URL"></div>
				<div class="row">
					<label>
						<input type="hidden" name="tolstenko_block_defaults[blog_blockquote][show_author]" value="0">
						<input type="checkbox" name="tolstenko_block_defaults[blog_blockquote][show_author]" value="1" <?php checked( ! empty( $bq['show_author'] ) ); ?>>
						<?php esc_html_e( 'Показывать автора справа', 'tolstenko-theme' ); ?>
					</label>
				</div>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_blockquote][image]', (int) ( $bq['image'] ?? 0 ), __( 'Фото автора', 'tolstenko-theme' ) ); ?>
				<div class="row"><input type="text" name="tolstenko_block_defaults[blog_blockquote][author]" value="<?php echo esc_attr( (string) ( $bq['author'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Имя автора', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[blog_blockquote][author_under]" value="<?php echo esc_attr( (string) ( $bq['author_under'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Подпись', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[blog_blockquote][btn_text]" value="<?php echo esc_attr( (string) ( $bq['btn_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Текст кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="url" name="tolstenko_block_defaults[blog_blockquote][btn_url]" value="<?php echo esc_attr( (string) ( $bq['btn_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'URL кнопки (пусто = модалка #modal)', 'tolstenko-theme' ); ?>"></div>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_number_list" hidden>
				<h2><?php esc_html_e( 'Нумерованный список', 'tolstenko-theme' ); ?></h2>
				<div data-bcd-list="number">
					<?php foreach ( $nl_items as $i => $item ) : ?>
						<div class="tolstenko-bcd-item">
							<textarea name="tolstenko_block_defaults[blog_number_list][items][<?php echo esc_attr( (string) $i ); ?>][text]" rows="2" placeholder="<?php esc_attr_e( 'Пункт', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item ); ?></textarea>
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="number"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
				<template data-bcd-tpl="number">
					<div class="tolstenko-bcd-item">
						<textarea name="tolstenko_block_defaults[blog_number_list][items][__INDEX__][text]" rows="2" placeholder="<?php esc_attr_e( 'Пункт', 'tolstenko-theme' ); ?>"></textarea>
						<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
					</div>
				</template>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_warning" hidden>
				<h2><?php esc_html_e( 'Предупреждения', 'tolstenko-theme' ); ?></h2>
				<div data-bcd-list="warning">
					<?php foreach ( $wn_items as $i => $item ) : ?>
						<?php
						$type = is_array( $item ) ? (string) ( $item['type'] ?? 'warn' ) : 'warn';
						$text = is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item;
						$icon = is_array( $item ) ? (int) ( $item['icon'] ?? 0 ) : 0;
						?>
						<div class="tolstenko-bcd-item">
							<select name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( (string) $i ); ?>][type]">
								<option value="warn" <?php selected( $type, 'warn' ); ?>><?php esc_html_e( 'Внимание', 'tolstenko-theme' ); ?></option>
								<option value="pin" <?php selected( $type, 'pin' ); ?>><?php esc_html_e( 'Подметить', 'tolstenko-theme' ); ?></option>
								<option value="ide" <?php selected( $type, 'ide' ); ?>><?php esc_html_e( 'Идея', 'tolstenko-theme' ); ?></option>
								<option value="custom" <?php selected( $type, 'custom' ); ?>><?php esc_html_e( 'Кастомный', 'tolstenko-theme' ); ?></option>
							</select>
							<textarea name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( (string) $i ); ?>][text]" rows="2" placeholder="<?php esc_attr_e( 'Текст', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $text ); ?></textarea>
							<input type="hidden" name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( (string) $i ); ?>][icon]" value="<?php echo esc_attr( (string) $icon ); ?>">
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="warning"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
				<template data-bcd-tpl="warning">
					<div class="tolstenko-bcd-item">
						<select name="tolstenko_block_defaults[blog_warning][items][__INDEX__][type]">
							<option value="warn"><?php esc_html_e( 'Внимание', 'tolstenko-theme' ); ?></option>
							<option value="pin"><?php esc_html_e( 'Подметить', 'tolstenko-theme' ); ?></option>
							<option value="ide"><?php esc_html_e( 'Идея', 'tolstenko-theme' ); ?></option>
							<option value="custom"><?php esc_html_e( 'Кастомный', 'tolstenko-theme' ); ?></option>
						</select>
						<textarea name="tolstenko_block_defaults[blog_warning][items][__INDEX__][text]" rows="2"></textarea>
						<input type="hidden" name="tolstenko_block_defaults[blog_warning][items][__INDEX__][icon]" value="0">
						<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
					</div>
				</template>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_table" hidden>
				<h2><?php esc_html_e( 'Таблица', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label>
						<input type="hidden" name="tolstenko_block_defaults[blog_table][use_header]" value="0">
						<input type="checkbox" name="tolstenko_block_defaults[blog_table][use_header]" value="1" <?php checked( ! empty( $tbl['use_header'] ) ); ?>>
						<?php esc_html_e( 'Показывать шапку', 'tolstenko-theme' ); ?>
					</label>
				</div>
				<p class="description"><?php esc_html_e( 'Шапка: ячейки через | . Строки: одна строка = ряд, ячейки через | .', 'tolstenko-theme' ); ?></p>
				<div class="row">
					<input type="text" name="tolstenko_block_defaults[blog_table][header_raw]" value="<?php echo esc_attr( implode( ' | ', array_map( 'strval', $header ) ) ); ?>" placeholder="Колонка 1 | Колонка 2">
				</div>
				<div class="row">
					<textarea name="tolstenko_block_defaults[blog_table][rows_raw]" rows="6" placeholder="яч1 | яч2"><?php
					$lines = array();
					foreach ( $rows as $row ) {
						$cells = is_array( $row ) ? ( $row['cells'] ?? $row ) : array();
						if ( ! is_array( $cells ) ) {
							continue;
						}
						$lines[] = implode( ' | ', array_map( 'strval', $cells ) );
					}
					echo esc_textarea( implode( "\n", $lines ) );
					?></textarea>
				</div>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_large_img" hidden>
				<h2><?php esc_html_e( 'Крупное фото', 'tolstenko-theme' ); ?></h2>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_large_img][image]', (int) ( $li['image'] ?? 0 ), __( 'Изображение по умолчанию', 'tolstenko-theme' ) ); ?>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_imgs" hidden>
				<h2><?php esc_html_e( 'Два фото', 'tolstenko-theme' ); ?></h2>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_imgs][left]', (int) ( $imgs['left'] ?? 0 ), __( 'Левое', 'tolstenko-theme' ) ); ?>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_imgs][right]', (int) ( $imgs['right'] ?? 0 ), __( 'Правое', 'tolstenko-theme' ) ); ?>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_video" hidden>
				<h2><?php esc_html_e( 'Видео', 'tolstenko-theme' ); ?></h2>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_video][preview]', (int) ( $vid['preview'] ?? 0 ), __( 'Превью', 'tolstenko-theme' ) ); ?>
				<div class="row"><input type="url" name="tolstenko_block_defaults[blog_video][url]" value="<?php echo esc_attr( (string) ( $vid['url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'URL видео', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><textarea name="tolstenko_block_defaults[blog_video][iframe]" rows="3" placeholder="iframe HTML"><?php echo esc_textarea( (string) ( $vid['iframe'] ?? '' ) ); ?></textarea></div>
			</div>

			<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить', 'tolstenko-theme' ); ?></button></p>
		</form>
	</div>
	<script>
	(function(){
		var root = document.getElementById('tolstenko-blog-content-defaults');
		if (!root) return;
		root.querySelectorAll('[data-bcd-tab]').forEach(function(btn){
			btn.addEventListener('click', function(){
				var key = btn.getAttribute('data-bcd-tab');
				root.querySelectorAll('[data-bcd-tab]').forEach(function(b){ b.classList.toggle('is-active', b===btn); });
				root.querySelectorAll('[data-bcd-panel]').forEach(function(p){
					p.hidden = p.getAttribute('data-bcd-panel') !== key;
				});
			});
		});
		root.addEventListener('click', function(e){
			var add = e.target.closest('[data-bcd-add]');
			if (add) {
				var kind = add.getAttribute('data-bcd-add');
				var list = root.querySelector('[data-bcd-list="'+kind+'"]');
				var tpl = root.querySelector('[data-bcd-tpl="'+kind+'"]');
				if (list && tpl) list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, Date.now().toString()));
				return;
			}
			var rm = e.target.closest('[data-bcd-remove]');
			if (rm) {
				var item = rm.closest('.tolstenko-bcd-item');
				if (item) item.remove();
				return;
			}
			var pick = e.target.closest('[data-bcd-pick]');
			if (pick && typeof wp !== 'undefined' && wp.media) {
				var wrap = pick.closest('[data-bcd-image]');
				var frame = wp.media({ title: 'Выбрать изображение', button: { text: 'Выбрать' }, multiple: false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					wrap.querySelector('[data-bcd-id]').value = att.id || 0;
					wrap.querySelector('[data-bcd-preview]').innerHTML = att.url ? '<img src="'+att.url+'" alt="" style="max-width:160px;height:auto;display:block;margin:6px 0;">' : '';
					var clear = wrap.querySelector('[data-bcd-clear]');
					if (clear) clear.style.display = '';
				});
				frame.open();
				return;
			}
			var clearBtn = e.target.closest('[data-bcd-clear]');
			if (clearBtn) {
				var w = clearBtn.closest('[data-bcd-image]');
				w.querySelector('[data-bcd-id]').value = '0';
				w.querySelector('[data-bcd-preview]').innerHTML = '';
				clearBtn.style.display = 'none';
			}
		});
	})();
	</script>
	<?php
}

/**
 * Сохранить дефолты тела статьи в tolstenko_block_defaults.
 *
 * @return bool true, если данные записаны в БД.
 */
function tolstenko_save_blog_content_defaults_from_request() {
	$raw_all = isset( $_POST['tolstenko_block_defaults'] ) && is_array( $_POST['tolstenko_block_defaults'] )
		? wp_unslash( $_POST['tolstenko_block_defaults'] ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		: array();

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$li = isset( $raw_all['blog_large_img'] ) && is_array( $raw_all['blog_large_img'] ) ? $raw_all['blog_large_img'] : array();
	$saved['blog_large_img'] = array(
		'image' => isset( $li['image'] ) ? (int) $li['image'] : 0,
	);

	$imgs = isset( $raw_all['blog_imgs'] ) && is_array( $raw_all['blog_imgs'] ) ? $raw_all['blog_imgs'] : array();
	$saved['blog_imgs'] = array(
		'left'  => isset( $imgs['left'] ) ? (int) $imgs['left'] : 0,
		'right' => isset( $imgs['right'] ) ? (int) $imgs['right'] : 0,
	);

	$vid = isset( $raw_all['blog_video'] ) && is_array( $raw_all['blog_video'] ) ? $raw_all['blog_video'] : array();
	$saved['blog_video'] = array(
		'preview' => isset( $vid['preview'] ) ? (int) $vid['preview'] : 0,
		'url'     => isset( $vid['url'] ) ? esc_url_raw( (string) $vid['url'] ) : '',
		'iframe'  => isset( $vid['iframe'] ) ? wp_kses( (string) $vid['iframe'], function_exists( 'tolstenko_blog_video_iframe_allowed_html' ) ? tolstenko_blog_video_iframe_allowed_html() : array() ) : '',
	);

	$bq = isset( $raw_all['blog_blockquote'] ) && is_array( $raw_all['blog_blockquote'] ) ? $raw_all['blog_blockquote'] : array();
	$saved['blog_blockquote'] = array(
		'text'         => isset( $bq['text'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $bq['text'] ) : wp_kses_post( (string) $bq['text'] ) ) : '',
		'link'         => isset( $bq['link'] ) ? esc_url_raw( (string) $bq['link'] ) : '',
		'show_author'  => ! empty( $bq['show_author'] ),
		'image'        => isset( $bq['image'] ) ? (int) $bq['image'] : 0,
		'author'       => isset( $bq['author'] ) ? sanitize_text_field( (string) $bq['author'] ) : '',
		'author_under' => isset( $bq['author_under'] ) ? sanitize_text_field( (string) $bq['author_under'] ) : '',
		'btn_text'     => isset( $bq['btn_text'] ) ? sanitize_text_field( (string) $bq['btn_text'] ) : '',
		'btn_url'      => isset( $bq['btn_url'] ) ? esc_url_raw( (string) $bq['btn_url'] ) : '',
	);

	$nl_items = array();
	$nl_raw   = isset( $raw_all['blog_number_list']['items'] ) && is_array( $raw_all['blog_number_list']['items'] ) ? $raw_all['blog_number_list']['items'] : array();
	foreach ( $nl_raw as $row ) {
		$text = is_array( $row ) ? trim( (string) ( $row['text'] ?? '' ) ) : trim( (string) $row );
		if ( $text !== '' ) {
			$nl_items[] = array( 'text' => function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $text ) : wp_kses_post( $text ) );
		}
	}
	$saved['blog_number_list'] = array( 'items' => $nl_items );

	$wn_items = array();
	$wn_raw   = isset( $raw_all['blog_warning']['items'] ) && is_array( $raw_all['blog_warning']['items'] ) ? $raw_all['blog_warning']['items'] : array();
	foreach ( $wn_raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$text = trim( (string) ( $row['text'] ?? '' ) );
		if ( $text === '' ) {
			continue;
		}
		$type = sanitize_key( (string) ( $row['type'] ?? 'warn' ) );
		if ( ! in_array( $type, array( 'warn', 'pin', 'ide', 'custom' ), true ) ) {
			$type = 'warn';
		}
		$wn_items[] = array(
			'type' => $type,
			'text' => function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $text ) : wp_kses_post( $text ),
			'icon' => isset( $row['icon'] ) ? (int) $row['icon'] : 0,
		);
	}
	$saved['blog_warning'] = array( 'items' => $wn_items );

	$seo = isset( $raw_all['blog_seo'] ) && is_array( $raw_all['blog_seo'] ) ? $raw_all['blog_seo'] : array();
	$saved['blog_seo'] = array(
		'title'   => isset( $seo['title'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $seo['title'] ) : sanitize_text_field( (string) $seo['title'] ) ) : '',
		'btn'     => isset( $seo['btn'] ) ? sanitize_text_field( (string) $seo['btn'] ) : '',
		'btn_url' => isset( $seo['btn_url'] ) ? esc_url_raw( (string) $seo['btn_url'] ) : '',
	);

	$tbl        = isset( $raw_all['blog_table'] ) && is_array( $raw_all['blog_table'] ) ? $raw_all['blog_table'] : array();
	$header_raw = isset( $tbl['header_raw'] ) ? (string) $tbl['header_raw'] : '';
	$rows_raw   = isset( $tbl['rows_raw'] ) ? (string) $tbl['rows_raw'] : '';
	$header     = array_map( 'trim', $header_raw === '' ? array() : explode( '|', $header_raw ) );
	$header     = array_map( 'sanitize_text_field', $header );
	$body_rows  = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $rows_raw ) as $line ) {
		$line = trim( (string) $line );
		if ( $line === '' ) {
			continue;
		}
		$cells = array_map( 'sanitize_text_field', array_map( 'trim', explode( '|', $line ) ) );
		$body_rows[] = array( 'cells' => $cells );
	}
	$saved['blog_table'] = array(
		'use_header' => ! empty( $tbl['use_header'] ),
		'header'     => $header,
		'rows'       => $body_rows,
	);

	return tolstenko_update_option_checked( 'tolstenko_block_defaults', $saved, false );
}
