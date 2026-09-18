<?php
/**
 * Блоки тела статьи / акции / кейса (flexible content):
 * — в редакторе у CPT blog и case;
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
	return array( 'blog', 'case' );
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
	if ( is_singular( 'actions' ) ) {
		return 'single-actions';
	}
	// Кейс визуально как статья.
	return 'single-blog';
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
		'tolstenko/blog-video'       => __( 'Видео', 'tolstenko-theme' ),
		'tolstenko/blog-blockquote'  => __( 'Цитата', 'tolstenko-theme' ),
		'tolstenko/blog-number-list' => __( 'Нумерованный список', 'tolstenko-theme' ),
		'tolstenko/blog-warning'     => __( 'Предупреждения', 'tolstenko-theme' ),
		'tolstenko/blog-pros-cons'   => __( 'Плюсы и минусы', 'tolstenko-theme' ),
		'tolstenko/blog-definition'  => __( 'Определение', 'tolstenko-theme' ),
		'tolstenko/blog-seo'         => __( 'SEO / CTA', 'tolstenko-theme' ),
		'tolstenko/consultation-whatsapp' => __( 'Забронируйте место', 'tolstenko-theme' ),
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
 * Типы плашек «Предупреждения».
 *
 * @return string[]
 */
function tolstenko_blog_warning_allowed_types() {
	return array( 'warn', 'pin', 'ide', 'err', 'custom' );
}

/**
 * Подписи типов в админке.
 *
 * @return array<string, string>
 */
function tolstenko_blog_warning_admin_labels() {
	return array(
		'warn'   => __( 'Внимание', 'tolstenko-theme' ),
		'pin'    => __( 'Подметить', 'tolstenko-theme' ),
		'ide'    => __( 'Идея', 'tolstenko-theme' ),
		'err'    => __( 'Ошибка', 'tolstenko-theme' ),
		'custom' => __( 'Кастомный (иконка необязательна)', 'tolstenko-theme' ),
	);
}

/**
 * Заголовок карточки на фронте (как на макете).
 *
 * @param string $type Type key.
 * @return string
 */
function tolstenko_blog_warning_card_title( $type ) {
	$type    = sanitize_key( (string) $type );
	$titles  = array(
		'warn' => __( 'Важно', 'tolstenko-theme' ),
		'pin'  => __( 'На заметку', 'tolstenko-theme' ),
		'ide'  => __( 'Совет', 'tolstenko-theme' ),
		'err'  => __( 'Частая ошибка', 'tolstenko-theme' ),
	);
	return isset( $titles[ $type ] ) ? $titles[ $type ] : '';
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
		'blog_pros_cons' => array(
			'pros_title' => 'Плюсы',
			'cons_title' => 'Минусы',
			'pros'       => array(),
			'cons'       => array(),
		),
		'blog_definition' => array(
			'mode'   => 'define',
			'kicker' => 'Определение',
			'term'   => 'Продвижение сайта статьями',
			'suffix' => 'способ SEO-продвижения через публикацию полезного контента.',
			'title'  => 'Семантическое ядро',
			'text'   => 'Набор поисковых запросов, по которым пользователи ищут ваш продукт.',
		),
		'blog_seo' => array(
			'title'   => 'Нужна помощь с продвижением?',
			'btn'     => 'Получить консультацию',
			'btn_url' => '',
		),
		'article_consultation_whatsapp' => array(
			'title'       => 'Напишите нам в WhatsApp',
			'text'        => 'Ответим на вопросы и поможем с расчётом стоимости.',
			'btn_text'    => 'Написать в WhatsApp',
			'btn_url'     => '',
			'color'       => '#25D366',
			'color_hover' => '#1EBE57',
		),
		'article_consultation_tg' => array(
			'title'       => 'Консультация в Telegram',
			'text'        => 'Быстрые ответы и удобное общение в мессенджере.',
			'btn_text'    => 'Написать в Telegram',
			'btn_url'     => '',
			'text_btn'    => 'Обычно отвечаем в течение 15 минут',
			'color'       => '#2AABEE',
			'color_hover' => '#229ED9',
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

/**
 * Дефолты плашки в статье — отдельный ключ, не секция страницы.
 * Если article-ключ ещё не сохраняли, читаем старый общий ключ (миграция).
 *
 * @param string $article_key article_consultation_whatsapp|article_consultation_tg.
 * @param string $page_key    consultation_whatsapp|consultation_tg.
 * @return array
 */
function tolstenko_get_article_consultation_defaults( $article_key, $page_key ) {
	$schema = tolstenko_blog_content_defaults_schema();
	$base   = isset( $schema[ $article_key ] ) && is_array( $schema[ $article_key ] ) ? $schema[ $article_key ] : array();
	$saved  = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	if ( ! empty( $saved[ $article_key ] ) && is_array( $saved[ $article_key ] ) ) {
		return array_replace_recursive( $base, $saved[ $article_key ] );
	}
	if ( ! empty( $saved[ $page_key ] ) && is_array( $saved[ $page_key ] ) ) {
		$page = $saved[ $page_key ];
		unset( $page['image'] );
		return array_replace_recursive( $base, $page );
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
	wp_enqueue_editor();
}

/**
 * Визуальный редактор пункта списка (дефолты «Блоки для статей»).
 * TinyMCE намеренно не авто-инициализируется: панель вкладки скрыта (hidden),
 * иначе остаётся голый textarea. Инициализация — в JS при открытии вкладки.
 *
 * @param string $content Content.
 * @param string $index   Repeater index.
 */
function tolstenko_blog_number_list_item_editor( $content, $index ) {
	$index     = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $index );
	$editor_id = 'tolstenko_bcd_nl_' . $index;
	wp_editor(
		(string) $content,
		$editor_id,
		array(
			'textarea_name' => 'tolstenko_block_defaults[blog_number_list][items][' . $index . '][text]',
			'textarea_rows' => 8,
			'media_buttons' => true,
			'tinymce'       => false,
			'quicktags'     => false,
			'editor_class'  => 'tolstenko-bcd-wysiwyg',
			'editor_height' => 180,
		)
	);
}

/**
 * Визуальный редактор пункта предупреждения (дефолты «Блоки для статей»).
 * См. комментарий к tolstenko_blog_number_list_item_editor().
 *
 * @param string $content Content.
 * @param string $index   Repeater index.
 */
function tolstenko_blog_warning_item_editor( $content, $index ) {
	$index     = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $index );
	$editor_id = 'tolstenko_bcd_wn_' . $index;
	wp_editor(
		(string) $content,
		$editor_id,
		array(
			'textarea_name' => 'tolstenko_block_defaults[blog_warning][items][' . $index . '][text]',
			'textarea_rows' => 8,
			'media_buttons' => true,
			'tinymce'       => false,
			'quicktags'     => false,
			'editor_class'  => 'tolstenko-bcd-wysiwyg',
			'editor_height' => 180,
		)
	);
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
		return;
	}

	if (
		isset( $_POST['tolstenko_blog_blocks_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_blog_blocks_nonce'] ) ), 'tolstenko_blog_content_defaults_save' )
	) {
		tolstenko_save_blog_content_defaults_from_request();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Дефолты блоков тела статьи сохранены.', 'tolstenko-theme' ) . '</p></div>';
	}

	$schema = tolstenko_blog_content_defaults_schema();
	$all    = array();
	foreach ( array_keys( $schema ) as $key ) {
		$all[ $key ] = tolstenko_get_blog_content_defaults( $key );
	}

	$li   = $all['blog_large_img'];
	$vid  = $all['blog_video'];
	$bq   = $all['blog_blockquote'];
	$nl   = $all['blog_number_list'];
	$wn   = $all['blog_warning'];
	$pc   = $all['blog_pros_cons'];
	$dfn  = $all['blog_definition'];
	$seo  = $all['blog_seo'];
	$cw   = function_exists( 'tolstenko_get_article_consultation_defaults' )
		? tolstenko_get_article_consultation_defaults( 'article_consultation_whatsapp', 'consultation_whatsapp' )
		: $all['article_consultation_whatsapp'];
	$ctg  = function_exists( 'tolstenko_get_article_consultation_defaults' )
		? tolstenko_get_article_consultation_defaults( 'article_consultation_tg', 'consultation_tg' )
		: $all['article_consultation_tg'];

	$nl_items = ! empty( $nl['items'] ) && is_array( $nl['items'] ) ? $nl['items'] : array( array( 'text' => '' ) );
	$wn_items = ! empty( $wn['items'] ) && is_array( $wn['items'] ) ? $wn['items'] : array( array( 'type' => 'warn', 'text' => '', 'icon' => 0 ) );
	$pc_pros  = ! empty( $pc['pros'] ) && is_array( $pc['pros'] ) ? $pc['pros'] : array( '' );
	$pc_cons  = ! empty( $pc['cons'] ) && is_array( $pc['cons'] ) ? $pc['cons'] : array( '' );
	if ( ! $pc_pros ) {
		$pc_pros = array( '' );
	}
	if ( ! $pc_cons ) {
		$pc_cons = array( '' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Блоки для статей', 'tolstenko-theme' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Дефолтное наполнение блоков тела статьи, акции и кейса. Пустые поля в блоке на записи подставляют значения отсюда.', 'tolstenko-theme' ); ?>
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
				.tolstenko-bcd-editor-wrap{margin:8px 0;max-width:720px}
				.tolstenko-bcd-editor-wrap .wp-editor-wrap{width:100%;max-width:720px}
				.tolstenko-bcd-editor-wrap .wp-editor-area{width:100%}
				.tolstenko-bcd-tabs{display:flex;flex-wrap:wrap;gap:6px;margin:12px 0}
				.tolstenko-bcd-tab{cursor:pointer}
				.tolstenko-bcd-tab.is-active{font-weight:600}
				.tolstenko-bcd-panel[hidden]{display:none!important}
			</style>

			<div class="tolstenko-bcd-tabs">
				<?php
				$tabs = array(
					'blog_seo'                      => __( 'SEO / CTA', 'tolstenko-theme' ),
					'article_consultation_whatsapp' => __( 'Забронируйте место', 'tolstenko-theme' ),
					'article_consultation_tg'       => __( 'Консультация Telegram', 'tolstenko-theme' ),
					'blog_blockquote'        => __( 'Цитата', 'tolstenko-theme' ),
					'blog_number_list'       => __( 'Список', 'tolstenko-theme' ),
					'blog_warning'           => __( 'Предупреждения', 'tolstenko-theme' ),
					'blog_pros_cons'         => __( 'Плюсы и минусы', 'tolstenko-theme' ),
					'blog_definition'        => __( 'Определение', 'tolstenko-theme' ),
					'blog_large_img'         => __( 'Крупное фото', 'tolstenko-theme' ),
					'blog_video'             => __( 'Видео', 'tolstenko-theme' ),
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

			<div class="tolstenko-bcd-panel" data-bcd-panel="article_consultation_whatsapp" hidden>
				<h2><?php esc_html_e( 'Забронируйте место', 'tolstenko-theme' ); ?></h2>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_whatsapp][title]" value="<?php echo esc_attr( (string) ( $cw['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Заголовок', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><textarea name="tolstenko_block_defaults[article_consultation_whatsapp][text]" rows="3" placeholder="<?php esc_attr_e( 'Текст', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $cw['text'] ?? '' ) ); ?></textarea></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_whatsapp][btn_text]" value="<?php echo esc_attr( (string) ( $cw['btn_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Текст кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="url" name="tolstenko_block_defaults[article_consultation_whatsapp][btn_url]" value="<?php echo esc_attr( (string) ( $cw['btn_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Ссылка кнопки (https://wa.me/...)', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_whatsapp][color]" value="<?php echo esc_attr( (string) ( $cw['color'] ?? '#25D366' ) ); ?>" placeholder="<?php esc_attr_e( 'Цвет кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_whatsapp][color_hover]" value="<?php echo esc_attr( (string) ( $cw['color_hover'] ?? '#1EBE57' ) ); ?>" placeholder="<?php esc_attr_e( 'Цвет hover', 'tolstenko-theme' ); ?>"></div>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="article_consultation_tg" hidden>
				<h2><?php esc_html_e( 'Консультация Telegram', 'tolstenko-theme' ); ?></h2>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_tg][title]" value="<?php echo esc_attr( (string) ( $ctg['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Заголовок', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><textarea name="tolstenko_block_defaults[article_consultation_tg][text]" rows="3" placeholder="<?php esc_attr_e( 'Текст', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $ctg['text'] ?? '' ) ); ?></textarea></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_tg][btn_text]" value="<?php echo esc_attr( (string) ( $ctg['btn_text'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Текст кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="url" name="tolstenko_block_defaults[article_consultation_tg][btn_url]" value="<?php echo esc_attr( (string) ( $ctg['btn_url'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Ссылка Telegram', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_tg][text_btn]" value="<?php echo esc_attr( (string) ( $ctg['text_btn'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Подпись / описание', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_tg][color]" value="<?php echo esc_attr( (string) ( $ctg['color'] ?? '#2AABEE' ) ); ?>" placeholder="<?php esc_attr_e( 'Цвет кнопки', 'tolstenko-theme' ); ?>"></div>
				<div class="row"><input type="text" name="tolstenko_block_defaults[article_consultation_tg][color_hover]" value="<?php echo esc_attr( (string) ( $ctg['color_hover'] ?? '#229ED9' ) ); ?>" placeholder="<?php esc_attr_e( 'Цвет hover', 'tolstenko-theme' ); ?>"></div>
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
						<?php
						$nl_text = is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item;
						$nl_idx  = (string) $i;
						?>
						<div class="tolstenko-bcd-item">
							<div class="tolstenko-bcd-editor-wrap">
								<?php tolstenko_blog_number_list_item_editor( $nl_text, $nl_idx ); ?>
							</div>
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="number"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_warning" hidden>
				<h2><?php esc_html_e( 'Предупреждения', 'tolstenko-theme' ); ?></h2>
				<div data-bcd-list="warning">
					<?php foreach ( $wn_items as $i => $item ) : ?>
						<?php
						$type    = is_array( $item ) ? (string) ( $item['type'] ?? 'warn' ) : 'warn';
						$title   = is_array( $item ) ? (string) ( $item['title'] ?? '' ) : '';
						$text    = is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item;
						$icon    = is_array( $item ) ? (int) ( $item['icon'] ?? 0 ) : 0;
						$wn_idx  = (string) $i;
						$wn_ph   = function_exists( 'tolstenko_blog_warning_card_title' )
							? tolstenko_blog_warning_card_title( $type )
							: '';
						?>
						<div class="tolstenko-bcd-item">
							<select name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( $wn_idx ); ?>][type]">
								<?php
								$wn_labels = function_exists( 'tolstenko_blog_warning_admin_labels' )
									? tolstenko_blog_warning_admin_labels()
									: array();
								foreach ( $wn_labels as $wn_type => $wn_label ) :
									?>
									<option value="<?php echo esc_attr( $wn_type ); ?>" <?php selected( $type, $wn_type ); ?>><?php echo esc_html( $wn_label ); ?></option>
								<?php endforeach; ?>
							</select>
							<p>
								<label>
									<?php esc_html_e( 'Заглавие', 'tolstenko-theme' ); ?><br>
									<input type="text" name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( $wn_idx ); ?>][title]" value="<?php echo esc_attr( $title ); ?>" placeholder="<?php echo esc_attr( $wn_ph ); ?>">
								</label>
							</p>
							<div class="tolstenko-bcd-editor-wrap">
								<?php tolstenko_blog_warning_item_editor( $text, $wn_idx ); ?>
							</div>
							<input type="hidden" name="tolstenko_block_defaults[blog_warning][items][<?php echo esc_attr( $wn_idx ); ?>][icon]" value="<?php echo esc_attr( (string) $icon ); ?>">
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="warning"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></p>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_pros_cons" hidden>
				<h2><?php esc_html_e( 'Плюсы и минусы', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label>
						<?php esc_html_e( 'Заглавие плюсов', 'tolstenko-theme' ); ?><br>
						<input type="text" name="tolstenko_block_defaults[blog_pros_cons][pros_title]" value="<?php echo esc_attr( (string) ( $pc['pros_title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Плюсы', 'tolstenko-theme' ); ?>">
					</label>
				</div>
				<div data-bcd-list="pros">
					<?php foreach ( $pc_pros as $i => $line ) : ?>
						<?php
						$line_text = is_array( $line ) ? (string) ( $line['text'] ?? '' ) : (string) $line;
						?>
						<div class="tolstenko-bcd-item">
							<input type="text" name="tolstenko_block_defaults[blog_pros_cons][pros][<?php echo esc_attr( (string) $i ); ?>][text]" value="<?php echo esc_attr( $line_text ); ?>" placeholder="<?php esc_attr_e( 'Пункт плюса', 'tolstenko-theme' ); ?>">
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="pros"><?php esc_html_e( 'Добавить плюс', 'tolstenko-theme' ); ?></button></p>

				<div class="row" style="margin-top:18px">
					<label>
						<?php esc_html_e( 'Заглавие минусов', 'tolstenko-theme' ); ?><br>
						<input type="text" name="tolstenko_block_defaults[blog_pros_cons][cons_title]" value="<?php echo esc_attr( (string) ( $pc['cons_title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Минусы', 'tolstenko-theme' ); ?>">
					</label>
				</div>
				<div data-bcd-list="cons">
					<?php foreach ( $pc_cons as $i => $line ) : ?>
						<?php
						$line_text = is_array( $line ) ? (string) ( $line['text'] ?? '' ) : (string) $line;
						?>
						<div class="tolstenko-bcd-item">
							<input type="text" name="tolstenko_block_defaults[blog_pros_cons][cons][<?php echo esc_attr( (string) $i ); ?>][text]" value="<?php echo esc_attr( $line_text ); ?>" placeholder="<?php esc_attr_e( 'Пункт минуса', 'tolstenko-theme' ); ?>">
							<p><button type="button" class="button-link-delete" data-bcd-remove><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
						</div>
					<?php endforeach; ?>
				</div>
				<p><button type="button" class="button" data-bcd-add="cons"><?php esc_html_e( 'Добавить минус', 'tolstenko-theme' ); ?></button></p>
			</div>

			<?php
			$dfn_mode = sanitize_key( (string) ( $dfn['mode'] ?? 'define' ) );
			if ( ! in_array( $dfn_mode, array( 'define', 'card' ), true ) ) {
				$dfn_mode = 'define';
			}
			?>
			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_definition" hidden>
				<h2><?php esc_html_e( 'Определение', 'tolstenko-theme' ); ?></h2>
				<div class="row">
					<label>
						<?php esc_html_e( 'Вид', 'tolstenko-theme' ); ?><br>
						<select name="tolstenko_block_defaults[blog_definition][mode]" data-bcd-define-mode>
							<option value="define" <?php selected( $dfn_mode, 'define' ); ?>><?php esc_html_e( 'Определение (с меткой и термином)', 'tolstenko-theme' ); ?></option>
							<option value="card" <?php selected( $dfn_mode, 'card' ); ?>><?php esc_html_e( 'Термин (заголовок и текст)', 'tolstenko-theme' ); ?></option>
						</select>
					</label>
				</div>
				<div data-bcd-define-view="define" <?php echo $dfn_mode === 'define' ? '' : 'hidden'; ?>>
					<div class="row">
						<label>
							<?php esc_html_e( 'Метка', 'tolstenko-theme' ); ?><br>
							<input type="text" name="tolstenko_block_defaults[blog_definition][kicker]" value="<?php echo esc_attr( (string) ( $dfn['kicker'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Определение', 'tolstenko-theme' ); ?>">
						</label>
					</div>
					<div class="row">
						<label>
							<?php esc_html_e( 'Термин', 'tolstenko-theme' ); ?><br>
							<input type="text" name="tolstenko_block_defaults[blog_definition][term]" value="<?php echo esc_attr( (string) ( $dfn['term'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Продвижение сайта статьями', 'tolstenko-theme' ); ?>">
						</label>
					</div>
					<div class="row">
						<label>
							<?php esc_html_e( 'Текст после тире', 'tolstenko-theme' ); ?><br>
							<textarea name="tolstenko_block_defaults[blog_definition][suffix]" rows="3" placeholder="<?php esc_attr_e( 'способ SEO-продвижения через публикацию полезного контента.', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $dfn['suffix'] ?? '' ) ); ?></textarea>
						</label>
						<p class="description"><?php esc_html_e( 'Тире перед этим текстом подставляется само, вводить его не нужно.', 'tolstenko-theme' ); ?></p>
					</div>
				</div>
				<div data-bcd-define-view="card" <?php echo $dfn_mode === 'card' ? '' : 'hidden'; ?>>
					<div class="row">
						<label>
							<?php esc_html_e( 'Заголовок', 'tolstenko-theme' ); ?><br>
							<input type="text" name="tolstenko_block_defaults[blog_definition][title]" value="<?php echo esc_attr( (string) ( $dfn['title'] ?? '' ) ); ?>" placeholder="<?php esc_attr_e( 'Семантическое ядро', 'tolstenko-theme' ); ?>">
						</label>
					</div>
					<div class="row">
						<label>
							<?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?><br>
							<textarea name="tolstenko_block_defaults[blog_definition][text]" rows="3" placeholder="<?php esc_attr_e( 'Набор поисковых запросов…', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( (string) ( $dfn['text'] ?? '' ) ); ?></textarea>
						</label>
					</div>
				</div>
			</div>

			<div class="tolstenko-bcd-panel" data-bcd-panel="blog_large_img" hidden>
				<h2><?php esc_html_e( 'Крупное фото', 'tolstenko-theme' ); ?></h2>
				<?php tolstenko_blog_content_defaults_image_field( 'tolstenko_block_defaults[blog_large_img][image]', (int) ( $li['image'] ?? 0 ), __( 'Изображение по умолчанию', 'tolstenko-theme' ) ); ?>
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

		var bcdEditorSettings = {
			tinymce: {
				wpautop: true,
				plugins: 'charmap colorpicker hr lists paste tabfocus textcolor wordpress wpautoresize wpeditimage wpemoji wpgallery wplink wptextpattern',
				toolbar1: 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
				toolbar2: 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo',
				height: 180
			},
			quicktags: true,
			mediaButtons: true
		};

		function triggerBcdSave() {
			if (typeof window.tinymce !== 'undefined' && tinymce.triggerSave) {
				tinymce.triggerSave();
			}
		}

		function initBcdEditor(editorId) {
			if (!editorId || typeof wp === 'undefined' || !wp.editor || typeof wp.editor.initialize !== 'function') return;
			try { wp.editor.remove(editorId); } catch (err) {}
			wp.editor.initialize(editorId, bcdEditorSettings);
		}

		function removeBcdEditor(scope) {
			if (!scope) return;
			triggerBcdSave();
			scope.querySelectorAll('textarea.wp-editor-area, textarea.tolstenko-bcd-wysiwyg').forEach(function(area) {
				if (area && area.id && window.wp && wp.editor && typeof wp.editor.remove === 'function') {
					try { wp.editor.remove(area.id); } catch (err) {}
				}
			});
		}

		function initPanelEditors(panel) {
			if (!panel) return;
			panel.querySelectorAll('textarea.wp-editor-area, textarea.tolstenko-bcd-wysiwyg').forEach(function(area) {
				if (!area.id) return;
				initBcdEditor(area.id);
			});
		}

		function activateBcdTab(key) {
			triggerBcdSave();
			root.querySelectorAll('[data-bcd-tab]').forEach(function(b) {
				b.classList.toggle('is-active', b.getAttribute('data-bcd-tab') === key);
			});
			root.querySelectorAll('[data-bcd-panel]').forEach(function(p) {
				var show = p.getAttribute('data-bcd-panel') === key;
				p.hidden = !show;
				if (show) {
					window.setTimeout(function() {
						initPanelEditors(p);
					}, 40);
				}
			});
		}

		root.addEventListener('submit', function(){
			triggerBcdSave();
		});

		root.querySelectorAll('[data-bcd-tab]').forEach(function(btn){
			btn.addEventListener('click', function(){
				activateBcdTab(btn.getAttribute('data-bcd-tab'));
			});
		});

		root.querySelectorAll('[data-bcd-define-mode]').forEach(function(sel){
			sel.addEventListener('change', function(){
				var mode = sel.value;
				root.querySelectorAll('[data-bcd-define-view]').forEach(function(box){
					box.hidden = box.getAttribute('data-bcd-define-view') !== mode;
				});
			});
		});

		// Если активна вкладка со списком/предупреждениями — поднять редакторы.
		var activeTab = root.querySelector('[data-bcd-tab].is-active');
		if (activeTab) {
			activateBcdTab(activeTab.getAttribute('data-bcd-tab'));
		}

		root.addEventListener('click', function(e){
			var add = e.target.closest('[data-bcd-add]');
			if (add) {
				var kind = add.getAttribute('data-bcd-add');
				var list = root.querySelector('[data-bcd-list="'+kind+'"]');
				if (kind === 'number' && list) {
					var idx = String(Date.now());
					var editorId = 'tolstenko_bcd_nl_' + idx;
					var html = ''
						+ '<div class="tolstenko-bcd-item">'
						+ '<div class="tolstenko-bcd-editor-wrap">'
						+ '<textarea id="'+editorId+'" name="tolstenko_block_defaults[blog_number_list][items]['+idx+'][text]" rows="8" class="wp-editor-area tolstenko-bcd-wysiwyg"></textarea>'
						+ '</div>'
						+ '<p><button type="button" class="button-link-delete" data-bcd-remove><?php echo esc_js( __( 'Удалить', 'tolstenko-theme' ) ); ?></button></p>'
						+ '</div>';
					list.insertAdjacentHTML('beforeend', html);
					initBcdEditor(editorId);
					return;
				}
				if (kind === 'warning' && list) {
					var wIdx = String(Date.now());
					var wEditorId = 'tolstenko_bcd_wn_' + wIdx;
					var wHtml = ''
						+ '<div class="tolstenko-bcd-item">'
						+ '<select name="tolstenko_block_defaults[blog_warning][items]['+wIdx+'][type]">'
						+ '<option value="warn"><?php echo esc_js( __( 'Внимание', 'tolstenko-theme' ) ); ?></option>'
						+ '<option value="pin"><?php echo esc_js( __( 'Подметить', 'tolstenko-theme' ) ); ?></option>'
						+ '<option value="ide"><?php echo esc_js( __( 'Идея', 'tolstenko-theme' ) ); ?></option>'
						+ '<option value="err"><?php echo esc_js( __( 'Ошибка', 'tolstenko-theme' ) ); ?></option>'
						+ '<option value="custom"><?php echo esc_js( __( 'Кастомный (иконка необязательна)', 'tolstenko-theme' ) ); ?></option>'
						+ '</select>'
						+ '<p><label><?php echo esc_js( __( 'Заглавие', 'tolstenko-theme' ) ); ?><br>'
						+ '<input type="text" name="tolstenko_block_defaults[blog_warning][items]['+wIdx+'][title]" value="" placeholder="<?php echo esc_js( __( 'Важно', 'tolstenko-theme' ) ); ?>"></label></p>'
						+ '<div class="tolstenko-bcd-editor-wrap">'
						+ '<textarea id="'+wEditorId+'" name="tolstenko_block_defaults[blog_warning][items]['+wIdx+'][text]" rows="8" class="wp-editor-area tolstenko-bcd-wysiwyg"></textarea>'
						+ '</div>'
						+ '<input type="hidden" name="tolstenko_block_defaults[blog_warning][items]['+wIdx+'][icon]" value="0">'
						+ '<p><button type="button" class="button-link-delete" data-bcd-remove><?php echo esc_js( __( 'Удалить', 'tolstenko-theme' ) ); ?></button></p>'
						+ '</div>';
					list.insertAdjacentHTML('beforeend', wHtml);
					initBcdEditor(wEditorId);
					return;
				}
				if ((kind === 'pros' || kind === 'cons') && list) {
					var pcIdx = String(Date.now());
					var pcName = kind === 'pros' ? 'pros' : 'cons';
					var pcPh = kind === 'pros'
						? '<?php echo esc_js( __( 'Пункт плюса', 'tolstenko-theme' ) ); ?>'
						: '<?php echo esc_js( __( 'Пункт минуса', 'tolstenko-theme' ) ); ?>';
					var pcHtml = ''
						+ '<div class="tolstenko-bcd-item">'
						+ '<input type="text" name="tolstenko_block_defaults[blog_pros_cons]['+pcName+']['+pcIdx+'][text]" value="" placeholder="'+pcPh+'">'
						+ '<p><button type="button" class="button-link-delete" data-bcd-remove><?php echo esc_js( __( 'Удалить', 'tolstenko-theme' ) ); ?></button></p>'
						+ '</div>';
					list.insertAdjacentHTML('beforeend', pcHtml);
					return;
				}
				var tpl = root.querySelector('[data-bcd-tpl="'+kind+'"]');
				if (list && tpl) list.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__INDEX__/g, Date.now().toString()));
				return;
			}
			var rm = e.target.closest('[data-bcd-remove]');
			if (rm) {
				var item = rm.closest('.tolstenko-bcd-item');
				if (item) {
					removeBcdEditor(item);
					item.remove();
				}
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
		$text = (string) ( $row['text'] ?? '' );
		if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
			continue;
		}
		$type = sanitize_key( (string) ( $row['type'] ?? 'warn' ) );
		$allowed_wn = function_exists( 'tolstenko_blog_warning_allowed_types' )
			? tolstenko_blog_warning_allowed_types()
			: array( 'warn', 'pin', 'ide', 'err', 'custom' );
		if ( ! in_array( $type, $allowed_wn, true ) ) {
			$type = 'warn';
		}
		$wn_items[] = array(
			'type'  => $type,
			'title' => isset( $row['title'] ) ? sanitize_text_field( (string) $row['title'] ) : '',
			'text'  => function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $text ) : wp_kses_post( $text ),
			'icon'  => isset( $row['icon'] ) ? (int) $row['icon'] : 0,
		);
	}
	$saved['blog_warning'] = array( 'items' => $wn_items );

	$pc_sanitize_lines = static function ( $raw ) {
		$out = array();
		if ( ! is_array( $raw ) ) {
			return $out;
		}
		foreach ( $raw as $row ) {
			$text = is_array( $row ) ? trim( (string) ( $row['text'] ?? '' ) ) : trim( (string) $row );
			if ( $text === '' ) {
				continue;
			}
			$out[] = array(
				'text' => function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( $text ) : sanitize_text_field( $text ),
			);
		}
		return $out;
	};
	$pc_raw = isset( $raw_all['blog_pros_cons'] ) && is_array( $raw_all['blog_pros_cons'] ) ? $raw_all['blog_pros_cons'] : array();
	$saved['blog_pros_cons'] = array(
		'pros_title' => isset( $pc_raw['pros_title'] ) ? sanitize_text_field( (string) $pc_raw['pros_title'] ) : '',
		'cons_title' => isset( $pc_raw['cons_title'] ) ? sanitize_text_field( (string) $pc_raw['cons_title'] ) : '',
		'pros'       => $pc_sanitize_lines( $pc_raw['pros'] ?? array() ),
		'cons'       => $pc_sanitize_lines( $pc_raw['cons'] ?? array() ),
	);

	$dfn_raw  = isset( $raw_all['blog_definition'] ) && is_array( $raw_all['blog_definition'] ) ? $raw_all['blog_definition'] : array();
	$dfn_mode = sanitize_key( (string) ( $dfn_raw['mode'] ?? 'define' ) );
	if ( ! in_array( $dfn_mode, array( 'define', 'card' ), true ) ) {
		$dfn_mode = 'define';
	}
	$saved['blog_definition'] = array(
		'mode'   => $dfn_mode,
		'kicker' => isset( $dfn_raw['kicker'] ) ? sanitize_text_field( (string) $dfn_raw['kicker'] ) : '',
		'term'   => isset( $dfn_raw['term'] ) ? sanitize_text_field( (string) $dfn_raw['term'] ) : '',
		'suffix' => isset( $dfn_raw['suffix'] ) ? sanitize_textarea_field( (string) $dfn_raw['suffix'] ) : '',
		'title'  => isset( $dfn_raw['title'] ) ? sanitize_text_field( (string) $dfn_raw['title'] ) : '',
		'text'   => isset( $dfn_raw['text'] ) ? sanitize_textarea_field( (string) $dfn_raw['text'] ) : '',
	);

	$seo = isset( $raw_all['blog_seo'] ) && is_array( $raw_all['blog_seo'] ) ? $raw_all['blog_seo'] : array();
	$saved['blog_seo'] = array(
		'title'   => isset( $seo['title'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $seo['title'] ) : sanitize_text_field( (string) $seo['title'] ) ) : '',
		'btn'     => isset( $seo['btn'] ) ? sanitize_text_field( (string) $seo['btn'] ) : '',
		'btn_url' => isset( $seo['btn_url'] ) ? esc_url_raw( (string) $seo['btn_url'] ) : '',
	);

	$cw = isset( $raw_all['article_consultation_whatsapp'] ) && is_array( $raw_all['article_consultation_whatsapp'] )
		? $raw_all['article_consultation_whatsapp']
		: array();
	$saved['article_consultation_whatsapp'] = array(
		'title'       => isset( $cw['title'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $cw['title'] ) : sanitize_text_field( (string) $cw['title'] ) ) : '',
		'text'        => isset( $cw['text'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $cw['text'] ) : wp_kses_post( (string) $cw['text'] ) ) : '',
		'btn_text'    => isset( $cw['btn_text'] ) ? sanitize_text_field( (string) $cw['btn_text'] ) : '',
		'btn_url'     => isset( $cw['btn_url'] ) ? esc_url_raw( (string) $cw['btn_url'] ) : '',
		'color'       => sanitize_hex_color( (string) ( $cw['color'] ?? '' ) ) ?: sanitize_text_field( (string) ( $cw['color'] ?? '' ) ),
		'color_hover' => sanitize_hex_color( (string) ( $cw['color_hover'] ?? '' ) ) ?: sanitize_text_field( (string) ( $cw['color_hover'] ?? '' ) ),
	);

	$ctg = isset( $raw_all['article_consultation_tg'] ) && is_array( $raw_all['article_consultation_tg'] )
		? $raw_all['article_consultation_tg']
		: array();
	$saved['article_consultation_tg'] = array(
		'title'       => isset( $ctg['title'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $ctg['title'] ) : sanitize_text_field( (string) $ctg['title'] ) ) : '',
		'text'        => isset( $ctg['text'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $ctg['text'] ) : wp_kses_post( (string) $ctg['text'] ) ) : '',
		'btn_text'    => isset( $ctg['btn_text'] ) ? sanitize_text_field( (string) $ctg['btn_text'] ) : '',
		'btn_url'     => isset( $ctg['btn_url'] ) ? esc_url_raw( (string) $ctg['btn_url'] ) : '',
		'text_btn'    => isset( $ctg['text_btn'] ) ? ( function_exists( 'tolstenko_kses_html' ) ? tolstenko_kses_html( (string) $ctg['text_btn'] ) : sanitize_text_field( (string) $ctg['text_btn'] ) ) : '',
		'color'       => sanitize_hex_color( (string) ( $ctg['color'] ?? '' ) ) ?: sanitize_text_field( (string) ( $ctg['color'] ?? '' ) ),
		'color_hover' => sanitize_hex_color( (string) ( $ctg['color_hover'] ?? '' ) ) ?: sanitize_text_field( (string) ( $ctg['color_hover'] ?? '' ) ),
	);

	update_option( 'tolstenko_block_defaults', $saved, false );
}
