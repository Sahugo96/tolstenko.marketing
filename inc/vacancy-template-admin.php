<?php
/**
 * Настройки сайта → Шаблон вакансии.
 * Дефолты секций hero / content / same (табы). Хранятся в tolstenko_block_defaults.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Схема дефолтов шаблона вакансии.
 *
 * @return array<string, array>
 */
function tolstenko_vacancy_template_schema() {
	return array(
		'hero_vacancy' => array(
			'title'          => '',
			'cost'           => 'от 80 000 ₽',
			'conditions'     => array( 'Удалённо', 'Полный день' ),
			'items'          => array(
				'Опыт от 1 года',
				'Готовность к удалённой работе',
			),
			'btn_text'       => 'Откликнуться',
			'btn_url'        => '',
			'btn_close_text' => 'Ответим в течение дня',
			'image'          => 0,
		),
		'vacancy_content' => array(
			'title'            => '',
			'content'          => '<p>Описание вакансии: обязанности, требования и условия работы.</p>',
			'apply_text'       => 'Отправить заявку',
			'apply_url'        => '',
			'sidebar_author'   => '',
			'sidebar_btn'      => 'Бесплатный аудит',
			'sidebar_btn_url'  => '',
		),
		'same_vacancy' => array(
			'title' => 'Другие вакансии',
			'items' => array(),
		),
	);
}

add_action( 'admin_menu', 'tolstenko_register_vacancy_template_admin_page', 20 );
add_action( 'admin_enqueue_scripts', 'tolstenko_vacancy_template_admin_assets' );

function tolstenko_register_vacancy_template_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Шаблон вакансии', 'tolstenko-theme' ),
		__( 'Шаблон вакансии', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-vacancy-template',
		'tolstenko_render_vacancy_template_admin_page'
	);
}

function tolstenko_vacancy_template_admin_assets( $hook ) {
	if ( empty( $_GET['page'] ) || $_GET['page'] !== 'tolstenko-vacancy-template' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_editor();
}

function tolstenko_render_vacancy_template_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['tolstenko_vacancy_template_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_vacancy_template_nonce'] ) ), 'tolstenko_vacancy_template_save' )
	) {
		tolstenko_save_vacancy_template_from_request();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Шаблон вакансии сохранён.', 'tolstenko-theme' ) . '</p></div>';
	}

	$schema = tolstenko_vacancy_template_schema();
	$all    = array();
	foreach ( array_keys( $schema ) as $key ) {
		$all[ $key ] = function_exists( 'tolstenko_get_block_defaults' )
			? tolstenko_get_block_defaults( $key )
			: $schema[ $key ];
	}

	$hero = $all['hero_vacancy'];
	$content = $all['vacancy_content'];
	$same = $all['same_vacancy'];

	$hero_img_id = (int) ( $hero['image'] ?? 0 );
	$hero_img_url = $hero_img_id ? wp_get_attachment_image_url( $hero_img_id, 'medium' ) : '';
	$sidebar_author = (string) ( $content['sidebar_author'] ?? '' );

	$vacancy_posts = get_posts(
		array(
			'post_type'      => 'vacancy',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		)
	);
	$selected_ids = array_map( 'intval', (array) ( $same['items'] ?? array() ) );

	$hero_conditions = (array) ( $hero['conditions'] ?? array() );
	$hero_items      = (array) ( $hero['items'] ?? array() );
	if ( empty( $hero_conditions ) ) {
		$hero_conditions = array( '' );
	}
	if ( empty( $hero_items ) ) {
		$hero_items = array( '' );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Настройки сайта: шаблон вакансии', 'tolstenko-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Дефолты для блоков на странице вакансии. На конкретной записи их можно переопределить в редакторе блока.', 'tolstenko-theme' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'tolstenko_vacancy_template_save', 'tolstenko_vacancy_template_nonce' ); ?>
			<style>
				.tolstenko-df-tabs{display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid #dcdcde;margin-top:12px}
				.tolstenko-df-tab{border:1px solid #dcdcde;border-bottom:0;background:#f6f7f7;padding:10px 14px;cursor:pointer;margin:0 6px 0 0}
				.tolstenko-df-tab.active{background:#fff;font-weight:600}
				.tolstenko-df-panel{display:none;border:1px solid #dcdcde;border-top:0;padding:14px;background:#fff}
				.tolstenko-df-panel.active{display:block}
				.tolstenko-df .row{margin:10px 0}
				.tolstenko-df textarea{width:100%}
				.tolstenko-df .repeater-item{padding:10px;border:1px solid #ddd;background:#fafafa;margin-bottom:8px}
				.tolstenko-df .repeater-item .cols{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
				.tolstenko-df .repeater-item .cols input[type="text"]{flex:1 1 220px}
				.tolstenko-df .muted{font-size:12px;color:#666}
				.tolstenko-df .icon-preview img{max-width:120px;max-height:120px;display:block;object-fit:cover;border-radius:8px}
				.tolstenko-df .vacancy-check{display:block;margin:4px 0}
			</style>
			<div class="tolstenko-df">
				<div class="tolstenko-df-tabs">
					<button type="button" class="tolstenko-df-tab active" data-panel="hero"><?php esc_html_e( 'Баннер вакансии', 'tolstenko-theme' ); ?></button>
					<button type="button" class="tolstenko-df-tab" data-panel="content"><?php esc_html_e( 'Контент', 'tolstenko-theme' ); ?></button>
					<button type="button" class="tolstenko-df-tab" data-panel="same"><?php esc_html_e( 'Похожие вакансии', 'tolstenko-theme' ); ?></button>
				</div>

				<div class="tolstenko-df-panel active" data-panel="hero">
					<div class="row"><input type="text" name="tolstenko_vacancy_template[hero_vacancy][title]" value="<?php echo esc_attr( $hero['title'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Заголовок (пусто = название записи)', 'tolstenko-theme' ); ?>"></div>
					<div class="row"><input type="text" name="tolstenko_vacancy_template[hero_vacancy][cost]" value="<?php echo esc_attr( $hero['cost'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Зарплата / стоимость', 'tolstenko-theme' ); ?>"></div>
					<div class="row">
						<div class="muted"><?php esc_html_e( 'Условия (чипы)', 'tolstenko-theme' ); ?></div>
						<div data-repeater-list="hero-conditions">
							<?php foreach ( $hero_conditions as $idx => $txt ) : ?>
								<div class="repeater-item" data-repeater-item>
									<div class="cols">
										<input type="text" name="tolstenko_vacancy_template[hero_vacancy][conditions][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : $txt ); ?>">
										<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="button" data-add-item data-list="hero-conditions" data-name="tolstenko_vacancy_template[hero_vacancy][conditions]"><?php esc_html_e( 'Добавить условие', 'tolstenko-theme' ); ?></button>
					</div>
					<div class="row">
						<div class="muted"><?php esc_html_e( 'Пункты списка', 'tolstenko-theme' ); ?></div>
						<div data-repeater-list="hero-items">
							<?php foreach ( $hero_items as $idx => $txt ) : ?>
								<div class="repeater-item" data-repeater-item>
									<div class="cols">
										<input type="text" name="tolstenko_vacancy_template[hero_vacancy][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : $txt ); ?>">
										<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
						<button type="button" class="button" data-add-item data-list="hero-items" data-name="tolstenko_vacancy_template[hero_vacancy][items]"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button>
					</div>
					<div class="row"><input type="text" name="tolstenko_vacancy_template[hero_vacancy][btn_text]" value="<?php echo esc_attr( $hero['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Текст кнопки', 'tolstenko-theme' ); ?>"></div>
					<div class="row"><input type="url" name="tolstenko_vacancy_template[hero_vacancy][btn_url]" value="<?php echo esc_attr( $hero['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Ссылка кнопки (пусто = модалка)', 'tolstenko-theme' ); ?>"></div>
					<div class="row"><input type="text" name="tolstenko_vacancy_template[hero_vacancy][btn_close_text]" value="<?php echo esc_attr( $hero['btn_close_text'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Текст рядом с кнопкой', 'tolstenko-theme' ); ?>"></div>
					<div class="row">
						<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_vacancy_template[hero_vacancy][image]" value="<?php echo (int) $hero_img_id; ?>">
						<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать изображение', 'tolstenko-theme' ); ?></button>
						<button type="button" class="button tolstenko-defaults-clear-icon"><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
						<div class="icon-preview" style="margin-top:8px;"><?php if ( $hero_img_url ) : ?><img src="<?php echo esc_url( $hero_img_url ); ?>" alt=""><?php endif; ?></div>
					</div>
				</div>

				<div class="tolstenko-df-panel" data-panel="content">
					<div class="row"><input type="text" name="tolstenko_vacancy_template[vacancy_content][title]" value="<?php echo esc_attr( $content['title'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Заголовок блока (пусто = название записи)', 'tolstenko-theme' ); ?>"></div>
					<div class="row">
						<div class="muted" style="margin-bottom:6px;"><?php esc_html_e( 'Текст вакансии', 'tolstenko-theme' ); ?></div>
						<?php
						wp_editor(
							(string) ( $content['content'] ?? '' ),
							'tolstenko_vacancy_content_editor',
							array(
								'textarea_name' => 'tolstenko_vacancy_template[vacancy_content][content]',
								'media_buttons' => true,
								'textarea_rows' => 14,
								'teeny'         => false,
								'quicktags'     => true,
								'editor_height' => 280,
							)
						);
						?>
					</div>
					<div class="row"><input type="text" name="tolstenko_vacancy_template[vacancy_content][apply_text]" value="<?php echo esc_attr( $content['apply_text'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Текст кнопки заявки', 'tolstenko-theme' ); ?>"></div>
					<div class="row"><input type="url" name="tolstenko_vacancy_template[vacancy_content][apply_url]" value="<?php echo esc_attr( $content['apply_url'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Ссылка кнопки заявки (пусто = модалка)', 'tolstenko-theme' ); ?>"></div>
					<hr>
					<div class="muted"><?php esc_html_e( 'Сайдбар', 'tolstenko-theme' ); ?></div>
					<div class="row">
						<label for="tolstenko_vacancy_template_sidebar_author"><strong><?php esc_html_e( 'Автор по умолчанию', 'tolstenko-theme' ); ?></strong></label><br>
						<?php
						tolstenko_render_blog_author_select(
							'tolstenko_vacancy_template[vacancy_content][sidebar_author]',
							$sidebar_author,
							__( 'Не выбран', 'tolstenko-theme' ),
							'tolstenko_vacancy_template_sidebar_author'
						);
						?>
						<p class="muted" style="margin-top:6px">
							<?php esc_html_e( 'Список авторов:', 'tolstenko-theme' ); ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=tolstenko-blog-authors' ) ); ?>">
								<?php esc_html_e( 'Настройки сайта → Авторы статей', 'tolstenko-theme' ); ?>
							</a>.
							<?php esc_html_e( 'Пусто = из «Шаблон вакансии» или из блока «Контент вакансии».', 'tolstenko-theme' ); ?>
						</p>
					</div>
					<div class="row"><input type="text" name="tolstenko_vacancy_template[vacancy_content][sidebar_btn]" value="<?php echo esc_attr( $content['sidebar_btn'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Текст кнопки сайдбара', 'tolstenko-theme' ); ?>"></div>
					<div class="row"><input type="url" name="tolstenko_vacancy_template[vacancy_content][sidebar_btn_url]" value="<?php echo esc_attr( $content['sidebar_btn_url'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Ссылка кнопки сайдбара (пусто = модалка)', 'tolstenko-theme' ); ?>"></div>
					<p class="muted"><?php esc_html_e( 'Соцсети в сайдбаре берутся из блока «Шапка и подвал».', 'tolstenko-theme' ); ?></p>
				</div>

				<div class="tolstenko-df-panel" data-panel="same">
					<div class="row"><input type="text" name="tolstenko_vacancy_template[same_vacancy][title]" value="<?php echo esc_attr( $same['title'] ?? '' ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Заголовок блока', 'tolstenko-theme' ); ?>"></div>
					<div class="row">
						<div class="muted"><?php esc_html_e( 'Вакансии в слайдере (пусто = последние опубликованные)', 'tolstenko-theme' ); ?></div>
						<?php if ( empty( $vacancy_posts ) ) : ?>
							<p class="muted"><?php esc_html_e( 'Пока нет опубликованных вакансий.', 'tolstenko-theme' ); ?></p>
						<?php else : ?>
							<?php foreach ( $vacancy_posts as $vp ) : ?>
								<label class="vacancy-check">
									<input type="checkbox" name="tolstenko_vacancy_template[same_vacancy][items][]" value="<?php echo (int) $vp->ID; ?>" <?php checked( in_array( (int) $vp->ID, $selected_ids, true ) ); ?>>
									<?php echo esc_html( get_the_title( $vp ) ); ?>
								</label>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить', 'tolstenko-theme' ); ?></button></p>
		</form>
	</div>
	<script>
	(function(){
		var tabs = document.querySelectorAll('.tolstenko-df-tab');
		var panels = document.querySelectorAll('.tolstenko-df-panel');
		var form = document.querySelector('.wrap form');

		function refreshContentEditor() {
			if (typeof tinymce === 'undefined') return;
			var ed = tinymce.get('tolstenko_vacancy_content_editor');
			if (!ed) return;
			ed.hidden = false;
			try {
				ed.theme && ed.theme.resizeTo && ed.theme.resizeTo('100%', 280);
			} catch (e) {}
			var iframe = ed.getContentAreaContainer() && ed.getContentAreaContainer().querySelector('iframe');
			if (iframe) {
				iframe.style.height = '280px';
			}
		}

		tabs.forEach(function(tab){
			tab.addEventListener('click', function(){
				var id = tab.getAttribute('data-panel');
				tabs.forEach(function(t){ t.classList.remove('active'); });
				panels.forEach(function(p){ p.classList.remove('active'); });
				tab.classList.add('active');
				var panel = document.querySelector('.tolstenko-df-panel[data-panel="'+id+'"]');
				if (panel) panel.classList.add('active');
				if (id === 'content') {
					setTimeout(refreshContentEditor, 30);
				}
			});
		});

		if (form) {
			form.addEventListener('submit', function(){
				if (typeof tinymce !== 'undefined') {
					tinymce.triggerSave();
				}
			});
		}

		document.addEventListener('click', function(e){
			var rm = e.target.closest('[data-remove-item]');
			if (rm) {
				var item = rm.closest('[data-repeater-item]');
				if (item) item.remove();
				return;
			}
			var add = e.target.closest('[data-add-item]');
			if (add) {
				var listName = add.getAttribute('data-list');
				var fieldName = add.getAttribute('data-name');
				var list = document.querySelector('[data-repeater-list="'+listName+'"]');
				if (!list || !fieldName) return;
				var idx = list.querySelectorAll('[data-repeater-item]').length;
				var wrap = document.createElement('div');
				wrap.className = 'repeater-item';
				wrap.setAttribute('data-repeater-item', '');
				wrap.innerHTML = '<div class="cols"><input type="text" name="'+fieldName+'['+idx+']"><button type="button" class="button" data-remove-item><?php echo esc_js( __( 'Удалить', 'tolstenko-theme' ) ); ?></button></div>';
				list.appendChild(wrap);
				return;
			}
			var clear = e.target.closest('.tolstenko-defaults-clear-icon');
			if (clear) {
				var row = clear.closest('.row');
				if (!row) return;
				var input = row.querySelector('.tolstenko-defaults-icon-id');
				var preview = row.querySelector('.icon-preview');
				if (input) input.value = '0';
				if (preview) preview.innerHTML = '';
				return;
			}
			var pick = e.target.closest('.tolstenko-defaults-pick-icon');
			if (!pick || typeof wp === 'undefined' || !wp.media) return;
			var row = pick.closest('.row');
			if (!row) return;
			var input = row.querySelector('.tolstenko-defaults-icon-id');
			var preview = row.querySelector('.icon-preview');
			var frame = wp.media({ title: 'Выбрать изображение', button: { text: 'Использовать' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				if (input) input.value = att.id || 0;
				if (preview) {
					var url = (att.sizes && att.sizes.medium && att.sizes.medium.url) ? att.sizes.medium.url : att.url;
					preview.innerHTML = url ? '<img src="'+url+'" alt="">' : '';
				}
			});
			frame.open();
		});
	})();
	</script>
	<?php
}

function tolstenko_save_vacancy_template_from_request() {
	$raw = isset( $_POST['tolstenko_vacancy_template'] ) ? wp_unslash( $_POST['tolstenko_vacancy_template'] ) : array();
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}

	$hero_raw = isset( $raw['hero_vacancy'] ) && is_array( $raw['hero_vacancy'] ) ? $raw['hero_vacancy'] : array();
	$content_raw = isset( $raw['vacancy_content'] ) && is_array( $raw['vacancy_content'] ) ? $raw['vacancy_content'] : array();
	$same_raw = isset( $raw['same_vacancy'] ) && is_array( $raw['same_vacancy'] ) ? $raw['same_vacancy'] : array();

	$conditions = array();
	if ( ! empty( $hero_raw['conditions'] ) && is_array( $hero_raw['conditions'] ) ) {
		foreach ( $hero_raw['conditions'] as $v ) {
			$v = sanitize_text_field( (string) $v );
			if ( $v !== '' ) {
				$conditions[] = $v;
			}
		}
	}
	$items = array();
	if ( ! empty( $hero_raw['items'] ) && is_array( $hero_raw['items'] ) ) {
		foreach ( $hero_raw['items'] as $v ) {
			$v = sanitize_text_field( (string) $v );
			if ( $v !== '' ) {
				$items[] = $v;
			}
		}
	}

	$same_items = array();
	if ( ! empty( $same_raw['items'] ) && is_array( $same_raw['items'] ) ) {
		foreach ( $same_raw['items'] as $id ) {
			$id = (int) $id;
			if ( $id > 0 ) {
				$same_items[] = $id;
			}
		}
	}

	$patch = array(
		'hero_vacancy' => array(
			'title'          => sanitize_text_field( $hero_raw['title'] ?? '' ),
			'cost'           => sanitize_text_field( $hero_raw['cost'] ?? '' ),
			'conditions'     => $conditions,
			'items'          => $items,
			'btn_text'       => sanitize_text_field( $hero_raw['btn_text'] ?? '' ),
			'btn_url'        => esc_url_raw( $hero_raw['btn_url'] ?? '' ),
			'btn_close_text' => sanitize_text_field( $hero_raw['btn_close_text'] ?? '' ),
			'image'          => (int) ( $hero_raw['image'] ?? 0 ),
		),
		'vacancy_content' => array(
			'title'           => sanitize_text_field( $content_raw['title'] ?? '' ),
			'content'         => wp_kses_post( $content_raw['content'] ?? '' ),
			'apply_text'      => sanitize_text_field( $content_raw['apply_text'] ?? '' ),
			'apply_url'       => esc_url_raw( $content_raw['apply_url'] ?? '' ),
			'sidebar_author'  => sanitize_text_field( $content_raw['sidebar_author'] ?? '' ),
			'sidebar_btn'     => sanitize_text_field( $content_raw['sidebar_btn'] ?? '' ),
			'sidebar_btn_url' => esc_url_raw( $content_raw['sidebar_btn_url'] ?? '' ),
		),
		'same_vacancy' => array(
			'title' => sanitize_text_field( $same_raw['title'] ?? '' ),
			'items' => $same_items,
		),
	);

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$saved = array_merge( $saved, $patch );
	update_option( 'tolstenko_block_defaults', $saved, false );
}

/**
 * Атрибуты блока из post_content записи.
 *
 * @param int    $post_id    ID записи.
 * @param string $block_name Имя блока, например tolstenko/hero-vacancy.
 * @return array
 */
function tolstenko_get_block_attrs_from_post( $post_id, $block_name ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return array();
	}
	$blocks = parse_blocks( (string) $post->post_content );
	foreach ( $blocks as $block ) {
		if ( ( $block['blockName'] ?? '' ) === $block_name && ! empty( $block['attrs'] ) && is_array( $block['attrs'] ) ) {
			return $block['attrs'];
		}
	}
	return array();
}

/**
 * Данные карточки вакансии: attrs героя записи → дефолты шаблона.
 *
 * @param int $post_id ID вакансии.
 * @return array{cost:string,conditions:array<int,string>,btn_text:string,btn_url:string}
 */
function tolstenko_get_vacancy_card_data( $post_id ) {
	$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'hero_vacancy' ) : array();
	$attrs    = tolstenko_get_block_attrs_from_post( (int) $post_id, 'tolstenko/hero-vacancy' );

	$cost = trim( (string) ( $attrs['block_hero_vacancy_cost'] ?? '' ) );
	if ( $cost === '' ) {
		$cost = (string) ( $defaults['cost'] ?? '' );
	}

	$conditions = array();
	if ( ! empty( $attrs['block_hero_vacancy_conditions'] ) && is_array( $attrs['block_hero_vacancy_conditions'] ) ) {
		foreach ( $attrs['block_hero_vacancy_conditions'] as $c ) {
			$text = is_array( $c ) ? trim( (string) ( $c['text'] ?? '' ) ) : trim( (string) $c );
			if ( $text !== '' ) {
				$conditions[] = $text;
			}
		}
	}
	if ( empty( $conditions ) && ! empty( $defaults['conditions'] ) && is_array( $defaults['conditions'] ) ) {
		foreach ( $defaults['conditions'] as $c ) {
			$text = is_array( $c ) ? trim( (string) ( $c['text'] ?? '' ) ) : trim( (string) $c );
			if ( $text !== '' ) {
				$conditions[] = $text;
			}
		}
	}

	$btn_text = trim( (string) ( $attrs['block_hero_vacancy_btn_text'] ?? '' ) );
	if ( $btn_text === '' ) {
		$btn_text = (string) ( $defaults['btn_text'] ?? '' );
	}
	$btn_url = trim( (string) ( $attrs['block_hero_vacancy_btn_url'] ?? '' ) );
	if ( $btn_url === '' ) {
		$btn_url = (string) ( $defaults['btn_url'] ?? '' );
	}

	return array(
		'cost'       => $cost,
		'conditions' => $conditions,
		'btn_text'   => $btn_text,
		'btn_url'    => $btn_url,
	);
}
