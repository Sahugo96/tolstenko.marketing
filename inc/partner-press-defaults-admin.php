<?php
/**
 * Настройки сайта → Партнёры блоки / Пресс-портрет.
 * Хранятся в tolstenko_block_defaults (merge-save, без затирания остальных ключей).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ключи дефолтов партнёрских блоков.
 *
 * @return string[]
 */
function tolstenko_partner_defaults_keys() {
	return array( 'referal', 'we_can', 'recomendation', 'commission', 'benefits_cooperation' );
}

/**
 * Ключи дефолтов блоков пресс-портрета.
 *
 * @return string[]
 */
function tolstenko_press_defaults_keys() {
	return array( 'aducation', 'clients', 'themes', 'collaboration' );
}

/**
 * Все ключи partner + press.
 *
 * @return string[]
 */
function tolstenko_partner_press_defaults_keys() {
	return array_merge( tolstenko_partner_defaults_keys(), tolstenko_press_defaults_keys() );
}

add_action( 'admin_menu', 'tolstenko_register_partner_press_defaults_admin_pages', 20 );
add_action( 'admin_enqueue_scripts', 'tolstenko_partner_press_defaults_admin_assets' );

function tolstenko_register_partner_press_defaults_admin_pages() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Партнёры блоки', 'tolstenko-theme' ),
		__( 'Партнёры блоки', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-partner-blocks',
		'tolstenko_render_partner_defaults_admin_page'
	);
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Пресс-портрет', 'tolstenko-theme' ),
		__( 'Пресс-портрет', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-press-blocks',
		'tolstenko_render_press_defaults_admin_page'
	);
}

/**
 * @param string $hook Hook.
 */
function tolstenko_partner_press_defaults_admin_assets( $hook ) {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! in_array( $page, array( 'tolstenko-partner-blocks', 'tolstenko-press-blocks' ), true ) ) {
		return;
	}
	wp_enqueue_media();
}

/**
 * Смержить сохранённые дефолты поверх схемы для набора ключей.
 *
 * @param string[] $keys Ключи блоков.
 * @return array<string, array>
 */
function tolstenko_get_merged_defaults_for_keys( $keys ) {
	$schema = function_exists( 'tolstenko_block_defaults_schema' ) ? tolstenko_block_defaults_schema() : array();
	$saved  = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$all = array();
	foreach ( $keys as $key ) {
		$base = isset( $schema[ $key ] ) && is_array( $schema[ $key ] ) ? $schema[ $key ] : array();
		$cur  = isset( $saved[ $key ] ) && is_array( $saved[ $key ] ) ? $saved[ $key ] : array();
		$all[ $key ] = function_exists( 'tolstenko_merge_block_defaults_data' )
			? tolstenko_merge_block_defaults_data( $base, $cur )
			: array_replace_recursive( $base, $cur );
	}
	return $all;
}

/**
 * Общие стили вкладок дефолтов.
 */
function tolstenko_print_defaults_admin_styles() {
	?>
	<style>
	.tolstenko-df-tabs{display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid #dcdcde;margin-top:8px}
	.tolstenko-df-tabs-group{margin-top:20px;padding:14px;background:#fff;border:1px solid #dcdcde;border-radius:4px}
	.tolstenko-df-tabs-group.is-active{border-color:#2271b1;box-shadow:0 0 0 1px #2271b1}
	.tolstenko-df-tabs-group-title{margin:0;font-size:14px;font-weight:600;color:#1d2327}
	.tolstenko-df-tab{border:1px solid #dcdcde;border-bottom:0;background:#f0f0f1;padding:8px 12px;cursor:pointer;margin:0 4px 0 0;border-radius:4px 4px 0 0}
	.tolstenko-df-tab.active{background:#fff;font-weight:600;color:#1d2327;position:relative;z-index:1}
	.tolstenko-df-group-panels{border:1px solid #dcdcde;border-top:0;background:#fff;min-height:0}
	.tolstenko-df-group-panels:empty{display:none}
	.tolstenko-df-panel{display:none;padding:14px}
	.tolstenko-df-panel.active{display:block}
	.tolstenko-df .row{margin:10px 0}
	.tolstenko-df textarea{width:100%}
	.tolstenko-df .repeater-item{padding:10px;border:1px solid #ddd;background:#fafafa;margin-bottom:8px}
	.tolstenko-df .repeater-item .cols{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
	.tolstenko-df .repeater-item .cols input[type="text"]{flex:1 1 220px}
	.tolstenko-df .muted{font-size:12px;color:#666}
	.tolstenko-df .actions{margin-top:12px;display:flex;gap:8px}
	.tolstenko-df .icon-preview img{max-width:44px;max-height:44px;display:block}
	.tolstenko-df .cert-preview img{max-width:80px;max-height:110px;display:block;object-fit:cover;border-radius:4px}
	.tolstenko-df .tolstenko-defaults-image-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
	.tolstenko-df .tolstenko-faq-editor-wrap .wp-editor-wrap{width:100%}
	.tolstenko-df .tolstenko-faq-editor-wrap .wp-editor-area{width:100%}
	.tolstenko-df-service-ids{max-height:280px;overflow:auto;border:1px solid #dcdcde;background:#fff;padding:8px;margin-top:6px}
	.tolstenko-df-service-ids__item{display:flex;gap:8px;align-items:flex-start;padding:4px 2px;margin:0}
	.tolstenko-df-service-ids__item span{line-height:1.35}
	</style>
	<?php
}

/**
 * JS табов / репитеров / media picker для partner|press страниц.
 *
 * @param array<string, string> $panel_group_map panel => group.
 */
function tolstenko_print_partner_press_defaults_admin_script( $panel_group_map ) {
	$map_json = wp_json_encode( $panel_group_map );
	?>
	<script>
	(function(){
		var root = document.querySelector('.tolstenko-df');
		if (!root) return;

		var panelGroupMap = <?php echo $map_json ? $map_json : '{}'; ?>;

		root.querySelectorAll('.tolstenko-df-panel').forEach(function(panel){
			var key = panel.getAttribute('data-panel');
			var group = panel.getAttribute('data-group') || panelGroupMap[key];
			if (!group) return;
			var slot = root.querySelector('.tolstenko-df-group-panels[data-group-panels="' + group + '"]');
			if (slot) slot.appendChild(panel);
		});
		var source = root.querySelector('.tolstenko-df-panels-source');
		if (source) source.remove();

		function activateTab(tab){
			if (!tab) return;
			var target = tab.getAttribute('data-panel');
			var group = tab.getAttribute('data-group') || panelGroupMap[target] || '';
			root.querySelectorAll('.tolstenko-df-tab').forEach(function(t){ t.classList.remove('active'); });
			root.querySelectorAll('.tolstenko-df-panel').forEach(function(p){ p.classList.remove('active'); });
			root.querySelectorAll('.tolstenko-df-tabs-group').forEach(function(g){ g.classList.remove('is-active'); });
			tab.classList.add('active');
			var panel = root.querySelector('.tolstenko-df-panel[data-panel="' + target + '"]');
			if (panel) panel.classList.add('active');
			var groupEl = root.querySelector('.tolstenko-df-tabs-group[data-group="' + group + '"]');
			if (groupEl) {
				groupEl.classList.add('is-active');
				groupEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
			}
		}

		root.querySelectorAll('.tolstenko-df-tab').forEach(function(tab){
			tab.addEventListener('click', function(){ activateTab(tab); });
		});

		var initial = root.querySelector('.tolstenko-df-tab.active') || root.querySelector('.tolstenko-df-tab');
		activateTab(initial);

		document.addEventListener('click', function(e){
			var removeBtn = e.target && e.target.closest ? e.target.closest('[data-remove-item]') : null;
			if (removeBtn) {
				e.preventDefault();
				var item = removeBtn.closest('[data-repeater-item]');
				if (item) item.remove();
				return;
			}
			var addBtn = e.target && e.target.closest ? e.target.closest('[data-add-item]') : null;
			if (!addBtn) return;
			e.preventDefault();
			var key = addBtn.getAttribute('data-add-item');
			var panel = addBtn.closest('.tolstenko-df-panel') || document;
			var list;
			if (key === 'benefits-cooperation-list') {
				var colWrap = addBtn.closest('.benefits-col-lists') || addBtn.closest('[data-repeater-item]');
				list = colWrap ? colWrap.querySelector('[data-repeater-list="benefits-cooperation-list"]') : null;
			} else {
				list = panel.querySelector('[data-repeater-list="' + key + '"]') || document.querySelector('[data-repeater-list="' + key + '"]');
			}
			if (!list) return;
			var directItems = [];
			Array.prototype.forEach.call(list.children || [], function(node){
				if (node.matches && node.matches('[data-repeater-item]')) directItems.push(node);
			});
			var idx = directItems.length;
			var html = '';
				if (key === 'referal-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[referal][items][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'referal-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[referal][list][' + idx + ']" placeholder="Текст условия"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'we-can-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[we_can][items][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'we-can-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[we_can][list][' + idx + ']" placeholder="Текст условия"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'recomendation-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[recomendation][items][' + idx + '][title]" placeholder="Заголовок карточки" style="width:100%"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[recomendation][items][' + idx + '][ico]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Иконка</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div><div class="row"><textarea name="tolstenko_block_defaults[recomendation][items][' + idx + '][text]" rows="2" placeholder="Текст карточки"></textarea></div></div>';
				} else if (key === 'recomendation-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[recomendation][list][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'commission-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[commission][items][' + idx + '][title]" placeholder="Заголовок" style="width:100%"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[commission][items][' + idx + '][ico]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Иконка</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div><div class="cols"><input type="text" name="tolstenko_block_defaults[commission][items][' + idx + '][summa]" placeholder="Сумма"><input type="text" name="tolstenko_block_defaults[commission][items][' + idx + '][time]" placeholder="Сроки / Разовая"></div><div class="cols"><input type="text" name="tolstenko_block_defaults[commission][items][' + idx + '][commission]" placeholder="Вознаграждение"><input type="text" name="tolstenko_block_defaults[commission][items][' + idx + '][remark]" placeholder="Примечание"></div></div>';
				} else if (key === 'benefits-cooperation-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><strong>Колонка ' + (idx + 1) + '</strong><button type="button" class="button" data-remove-item>Удалить колонку</button></div><div class="benefits-col-lists" style="margin:8px 0;"><div class="muted">Пункты</div><div data-repeater-list="benefits-cooperation-list"><div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[benefits_cooperation][items][' + idx + '][list][0][title]" placeholder="Заголовок пункта" style="width:100%"><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row"><textarea name="tolstenko_block_defaults[benefits_cooperation][items][' + idx + '][list][0][text]" rows="2" placeholder="Текст пункта"></textarea></div></div></div><div class="actions"><button type="button" class="button" data-add-item="benefits-cooperation-list">Добавить пункт</button></div></div><div class="cols"><input type="text" name="tolstenko_block_defaults[benefits_cooperation][items][' + idx + '][btn_text]" placeholder="Текст кнопки"><input type="url" name="tolstenko_block_defaults[benefits_cooperation][items][' + idx + '][btn_url]" placeholder="Ссылка (пусто = модалка)"></div></div>';
				} else if (key === 'benefits-cooperation-list') {
					var colItem = addBtn.closest('.benefits-col-lists') && addBtn.closest('.benefits-col-lists').closest('[data-repeater-item]');
					var colIdx = 0;
					if (colItem && colItem.parentElement) {
						var siblings = [];
						Array.prototype.forEach.call(colItem.parentElement.children || [], function(node){
							if (node.matches && node.matches('[data-repeater-item]')) siblings.push(node);
						});
						colIdx = Math.max(0, siblings.indexOf(colItem));
					}
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[benefits_cooperation][items][' + colIdx + '][list][' + idx + '][title]" placeholder="Заголовок пункта" style="width:100%"><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row"><textarea name="tolstenko_block_defaults[benefits_cooperation][items][' + colIdx + '][list][' + idx + '][text]" rows="2" placeholder="Текст пункта"></textarea></div></div>';
				} else if (key === 'aducation-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[aducation][items][' + idx + '][year]" placeholder="Год"><input type="text" name="tolstenko_block_defaults[aducation][items][' + idx + '][type]" placeholder="Тип"><button type="button" class="button" data-remove-item>Удалить</button></div><div class="row"><input type="text" name="tolstenko_block_defaults[aducation][items][' + idx + '][title]" placeholder="Заголовок" style="width:100%"></div><div class="row"><input type="text" name="tolstenko_block_defaults[aducation][items][' + idx + '][speciality]" placeholder="Специальность" style="width:100%"></div></div>';
				} else if (key === 'aducation-images') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[aducation][images][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Выбрать фото</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'clients-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[clients][items][' + idx + '][name]" placeholder="Название / alt"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[clients][items][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Логотип</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'clients-smi') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[clients][smi][' + idx + '][name]" placeholder="Название / alt"><input type="url" name="tolstenko_block_defaults[clients][smi][' + idx + '][link]" placeholder="Ссылка"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[clients][smi][' + idx + '][image]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Логотип</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div></div>';
				} else if (key === 'themes-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[themes][items][' + idx + ']" placeholder="Тема" style="width:100%"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'collaboration-items') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[collaboration][items][' + idx + ']" placeholder="Пункт" style="width:100%"><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				}
			if (html) list.insertAdjacentHTML('beforeend', html);
		});

		function bindIconPicker(scope){
			scope.querySelectorAll('.tolstenko-defaults-pick-icon').forEach(function(btn){
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function(ev){
					ev.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var row = btn.closest('[data-repeater-item], .tolstenko-defaults-image-row');
					if (!row) return;
					var input = row.querySelector('.tolstenko-defaults-icon-id');
					var preview = row.querySelector('.icon-preview');
					var frame = wp.media({ title: 'Выберите иконку', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var sel = frame.state().get('selection').first();
						if (!sel) return;
						var json = sel.toJSON();
						input.value = json.id || 0;
						var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
						preview.innerHTML = img ? '<img src="' + img + '" alt="">' : '';
					});
					frame.open();
				});
			});
		}
		bindIconPicker(document);
		document.addEventListener('click', function(e){
			var btn = e.target;
			if (btn && btn.matches && btn.matches('[data-add-item]')) {
				setTimeout(function(){ bindIconPicker(document); }, 0);
			}
		});
	})();
	</script>
	<?php
}

/**
 * Sanitize partner+press raw POST fragment into patch array.
 *
 * @param array $raw Raw tolstenko_block_defaults POST.
 * @return array
 */
function tolstenko_sanitize_partner_press_defaults_from_raw( $raw ) {
	if ( ! is_array( $raw ) ) {
		$raw = array();
	}
	$patch = array();

	$patch['we_can'] = array(
		'title'      => tolstenko_kses_html( $raw['we_can']['title'] ?? '' ),
		'list_title' => tolstenko_kses_html( $raw['we_can']['list_title'] ?? '' ),
		'form_title' => tolstenko_kses_html( $raw['we_can']['form_title'] ?? '' ),
		'form_text'  => tolstenko_kses_html( $raw['we_can']['form_text'] ?? '' ),
		'items'      => array(),
		'list'       => array(),
	);
	if ( isset( $raw['we_can']['items'] ) && is_array( $raw['we_can']['items'] ) ) {
		foreach ( $raw['we_can']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['we_can']['items'][] = sanitize_text_field( $it );
			}
		}
	}
	if ( isset( $raw['we_can']['list'] ) && is_array( $raw['we_can']['list'] ) ) {
		foreach ( $raw['we_can']['list'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['we_can']['list'][] = sanitize_text_field( $it );
			}
		}
	}

	$patch['recomendation'] = array(
		'title'      => tolstenko_kses_html( $raw['recomendation']['title'] ?? '' ),
		'text'       => tolstenko_kses_html( $raw['recomendation']['text'] ?? '' ),
		'list_title' => tolstenko_kses_html( $raw['recomendation']['list_title'] ?? '' ),
		'btn_text'   => sanitize_text_field( $raw['recomendation']['btn_text'] ?? '' ),
		'btn_url'    => esc_url_raw( $raw['recomendation']['btn_url'] ?? '' ),
		'items'      => array(),
		'list'       => array(),
	);
	if ( isset( $raw['recomendation']['items'] ) && is_array( $raw['recomendation']['items'] ) ) {
		foreach ( $raw['recomendation']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$title = tolstenko_kses_html( $it['title'] ?? '' );
			$text  = tolstenko_kses_html( $it['text'] ?? '' );
			$ico   = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
			if ( $title === '' && $text === '' && ! $ico ) {
				continue;
			}
			$patch['recomendation']['items'][] = array(
				'ico'   => $ico,
				'title' => $title,
				'text'  => $text,
			);
		}
	}
	if ( isset( $raw['recomendation']['list'] ) && is_array( $raw['recomendation']['list'] ) ) {
		foreach ( $raw['recomendation']['list'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['recomendation']['list'][] = sanitize_text_field( $it );
			}
		}
	}

	$patch['referal'] = array(
		'title'      => tolstenko_kses_html( $raw['referal']['title'] ?? '' ),
		'list_title' => tolstenko_kses_html( $raw['referal']['list_title'] ?? '' ),
		'btn_text'   => sanitize_text_field( $raw['referal']['btn_text'] ?? '' ),
		'btn_url'    => esc_url_raw( $raw['referal']['btn_url'] ?? '' ),
		'items'      => array(),
		'list'       => array(),
	);
	if ( isset( $raw['referal']['items'] ) && is_array( $raw['referal']['items'] ) ) {
		foreach ( $raw['referal']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['referal']['items'][] = sanitize_text_field( $it );
			}
		}
	}
	if ( isset( $raw['referal']['list'] ) && is_array( $raw['referal']['list'] ) ) {
		foreach ( $raw['referal']['list'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['referal']['list'][] = sanitize_text_field( $it );
			}
		}
	}

	$patch['commission'] = array(
		'title' => tolstenko_kses_html( $raw['commission']['title'] ?? '' ),
		'text'  => tolstenko_kses_html( $raw['commission']['text'] ?? '' ),
		'items' => array(),
	);
	if ( isset( $raw['commission']['items'] ) && is_array( $raw['commission']['items'] ) ) {
		foreach ( $raw['commission']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$row = array(
				'ico'        => isset( $it['ico'] ) ? (int) $it['ico'] : 0,
				'title'      => tolstenko_kses_html( $it['title'] ?? '' ),
				'summa'      => sanitize_text_field( $it['summa'] ?? '' ),
				'time'       => sanitize_text_field( $it['time'] ?? '' ),
				'commission' => sanitize_text_field( $it['commission'] ?? '' ),
				'remark'     => tolstenko_kses_html( $it['remark'] ?? '' ),
			);
			if ( $row['title'] === '' && $row['summa'] === '' && $row['commission'] === '' && ! $row['ico'] ) {
				continue;
			}
			$patch['commission']['items'][] = $row;
		}
	}

	$patch['benefits_cooperation'] = array(
		'title' => tolstenko_kses_html( $raw['benefits_cooperation']['title'] ?? '' ),
		'items' => array(),
	);
	if ( isset( $raw['benefits_cooperation']['items'] ) && is_array( $raw['benefits_cooperation']['items'] ) ) {
		foreach ( $raw['benefits_cooperation']['items'] as $col ) {
			if ( ! is_array( $col ) ) {
				continue;
			}
			$list = array();
			foreach ( (array) ( $col['list'] ?? array() ) as $elem ) {
				if ( ! is_array( $elem ) ) {
					continue;
				}
				$et = tolstenko_kses_html( $elem['title'] ?? '' );
				$ex = tolstenko_kses_html( $elem['text'] ?? '' );
				if ( $et === '' && $ex === '' ) {
					continue;
				}
				$list[] = array(
					'title' => $et,
					'text'  => $ex,
				);
			}
			$btn_text = sanitize_text_field( $col['btn_text'] ?? '' );
			$btn_url  = esc_url_raw( $col['btn_url'] ?? '' );
			if ( empty( $list ) && $btn_text === '' ) {
				continue;
			}
			$patch['benefits_cooperation']['items'][] = array(
				'list'     => $list,
				'btn_text' => $btn_text,
				'btn_url'  => $btn_url,
			);
		}
	}

	$patch['aducation'] = array(
		'title'  => tolstenko_kses_html( $raw['aducation']['title'] ?? '' ),
		'items'  => array(),
		'images' => array(),
	);
	if ( isset( $raw['aducation']['items'] ) && is_array( $raw['aducation']['items'] ) ) {
		foreach ( $raw['aducation']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$row = array(
				'year'       => sanitize_text_field( $it['year'] ?? '' ),
				'type'       => tolstenko_kses_html( $it['type'] ?? '' ),
				'title'      => tolstenko_kses_html( $it['title'] ?? '' ),
				'speciality' => tolstenko_kses_html( $it['speciality'] ?? '' ),
			);
			if ( $row['year'] === '' && $row['title'] === '' && $row['type'] === '' ) {
				continue;
			}
			$patch['aducation']['items'][] = $row;
		}
	}
	if ( isset( $raw['aducation']['images'] ) && is_array( $raw['aducation']['images'] ) ) {
		foreach ( $raw['aducation']['images'] as $it ) {
			$img_id = is_array( $it ) ? (int) ( $it['image'] ?? 0 ) : (int) $it;
			if ( $img_id > 0 ) {
				$patch['aducation']['images'][] = array( 'image' => $img_id );
			}
		}
	}

	$patch['clients'] = array(
		'title'    => tolstenko_kses_html( $raw['clients']['title'] ?? '' ),
		'text'     => tolstenko_kses_html( $raw['clients']['text'] ?? '' ),
		'subtitle' => tolstenko_kses_html( $raw['clients']['subtitle'] ?? '' ),
		'items'    => array(),
		'smi'      => array(),
	);
	if ( isset( $raw['clients']['items'] ) && is_array( $raw['clients']['items'] ) ) {
		foreach ( $raw['clients']['items'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$img_id = isset( $it['image'] ) ? (int) $it['image'] : 0;
			$name   = sanitize_text_field( $it['name'] ?? '' );
			if ( ! $img_id && $name === '' ) {
				continue;
			}
			$patch['clients']['items'][] = array(
				'image' => $img_id,
				'name'  => $name,
			);
		}
	}
	if ( isset( $raw['clients']['smi'] ) && is_array( $raw['clients']['smi'] ) ) {
		foreach ( $raw['clients']['smi'] as $it ) {
			if ( ! is_array( $it ) ) {
				continue;
			}
			$img_id = isset( $it['image'] ) ? (int) $it['image'] : 0;
			$name   = sanitize_text_field( $it['name'] ?? '' );
			$link   = esc_url_raw( $it['link'] ?? '' );
			if ( ! $img_id && $name === '' ) {
				continue;
			}
			$patch['clients']['smi'][] = array(
				'image' => $img_id,
				'name'  => $name,
				'link'  => $link,
			);
		}
	}

	$patch['themes'] = array(
		'title'     => tolstenko_kses_html( $raw['themes']['title'] ?? '' ),
		'more_text' => tolstenko_kses_html( $raw['themes']['more_text'] ?? '' ),
		'btn_text'  => sanitize_text_field( $raw['themes']['btn_text'] ?? '' ),
		'btn_url'   => esc_url_raw( $raw['themes']['btn_url'] ?? '' ),
		'image'     => isset( $raw['themes']['image'] ) ? (int) $raw['themes']['image'] : 0,
		'items'     => array(),
	);
	if ( isset( $raw['themes']['items'] ) && is_array( $raw['themes']['items'] ) ) {
		foreach ( $raw['themes']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['themes']['items'][] = sanitize_text_field( $it );
			}
		}
	}

	$patch['collaboration'] = array(
		'title'    => tolstenko_kses_html( $raw['collaboration']['title'] ?? '' ),
		'btn_text' => sanitize_text_field( $raw['collaboration']['btn_text'] ?? '' ),
		'btn_url'  => esc_url_raw( $raw['collaboration']['btn_url'] ?? '' ),
		'image'    => isset( $raw['collaboration']['image'] ) ? (int) $raw['collaboration']['image'] : 0,
		'items'    => array(),
	);
	if ( isset( $raw['collaboration']['items'] ) && is_array( $raw['collaboration']['items'] ) ) {
		foreach ( $raw['collaboration']['items'] as $it ) {
			$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
			if ( $it !== '' ) {
				$patch['collaboration']['items'][] = sanitize_text_field( $it );
			}
		}
	}
	return $patch;
}

/**
 * @param string[] $keys Keys to keep from patch.
 * @return bool true, если данные записаны в БД.
 */
function tolstenko_save_partner_press_defaults_keys_from_request( $keys ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		tolstenko_log_error( 'tolstenko_save_partner_press_defaults_keys_from_request', 'Попытка сохранения без прав manage_options' );
		return false;
	}
	$raw   = isset( $_POST['tolstenko_block_defaults'] ) ? wp_unslash( $_POST['tolstenko_block_defaults'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$patch = tolstenko_sanitize_partner_press_defaults_from_raw( is_array( $raw ) ? $raw : array() );
	$out   = array();
	foreach ( $keys as $key ) {
		if ( isset( $patch[ $key ] ) ) {
			$out[ $key ] = $patch[ $key ];
		}
	}

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$saved = array_merge( $saved, $out );

	return tolstenko_update_option_checked( 'tolstenko_block_defaults', $saved, false );
}

function tolstenko_render_partner_defaults_panels( $all ) {
	$wc  = $all['we_can'] ?? array();
	$rec = $all['recomendation'] ?? array();
	$ref = $all['referal'] ?? array();
	$cm  = $all['commission'] ?? array();
	$bc  = $all['benefits_cooperation'] ?? array();
	?>
		<div class="tolstenko-df-panel" data-panel="referal" data-group="partner">
			<div class="row"><input type="text" name="tolstenko_block_defaults[referal][title]" value="<?php echo esc_attr( $ref['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты слева', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="referal-items">
					<?php foreach ( (array) ( $ref['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[referal][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="referal-items"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[referal][list_title]" value="<?php echo esc_attr( $ref['list_title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок условий"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Условия выплат', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="referal-list">
					<?php foreach ( (array) ( $ref['list'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[referal][list][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст условия">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="referal-list"><?php esc_html_e( 'Добавить условие', 'tolstenko-theme' ); ?></button></div>
			</div>
			<hr>
			<div class="row"><input type="text" name="tolstenko_block_defaults[referal][btn_text]" value="<?php echo esc_attr( $ref['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[referal][btn_url]" value="<?php echo esc_attr( $ref['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="we_can" data-group="partner">
			<div class="row"><input type="text" name="tolstenko_block_defaults[we_can][title]" value="<?php echo esc_attr( $wc['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты («мы можем»)', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="we-can-items">
					<?php foreach ( (array) ( $wc['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[we_can][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="we-can-items"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[we_can][list_title]" value="<?php echo esc_attr( $wc['list_title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок условий"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Условия выплат', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="we-can-list">
					<?php foreach ( (array) ( $wc['list'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[we_can][list][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст условия">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="we-can-list"><?php esc_html_e( 'Добавить условие', 'tolstenko-theme' ); ?></button></div>
			</div>
			<hr>
			<div class="muted"><?php esc_html_e( 'Форма', 'tolstenko-theme' ); ?></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[we_can][form_title]" value="<?php echo esc_attr( $wc['form_title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок формы"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[we_can][form_text]" rows="2" placeholder="Текст формы"><?php echo esc_textarea( $wc['form_text'] ?? '' ); ?></textarea></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="recomendation" data-group="partner">
			<div class="row"><input type="text" name="tolstenko_block_defaults[recomendation][title]" value="<?php echo esc_attr( $rec['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[recomendation][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $rec['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки вариантов', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="recomendation-items">
					<?php foreach ( (array) ( $rec['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$ico_id  = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
						$ico_url = $ico_id ? wp_get_attachment_image_url( $ico_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[recomendation][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок карточки" style="width:100%">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[recomendation][items][<?php echo (int) $idx; ?>][ico]" value="<?php echo (int) $ico_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $ico_url ) : ?><img src="<?php echo esc_url( $ico_url ); ?>" alt=""><?php endif; ?></div>
							<div class="row"><textarea name="tolstenko_block_defaults[recomendation][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Текст карточки"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="recomendation-items"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[recomendation][list_title]" value="<?php echo esc_attr( $rec['list_title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок справа"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Список справа', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="recomendation-list">
					<?php foreach ( (array) ( $rec['list'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[recomendation][list][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="recomendation-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<hr>
			<div class="row"><input type="text" name="tolstenko_block_defaults[recomendation][btn_text]" value="<?php echo esc_attr( $rec['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[recomendation][btn_url]" value="<?php echo esc_attr( $rec['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка)"></div>
		</div>

		<div class="tolstenko-df-panel" data-panel="commission" data-group="partner">
			<div class="row"><input type="text" name="tolstenko_block_defaults[commission][title]" value="<?php echo esc_attr( $cm['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[commission][text]" rows="3" placeholder="Текст под заголовком"><?php echo esc_textarea( $cm['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Карточки вознаграждения', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="commission-items">
					<?php foreach ( (array) ( $cm['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$ico_id  = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
						$ico_url = $ico_id ? wp_get_attachment_image_url( $ico_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок" style="width:100%">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][ico]" value="<?php echo (int) $ico_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $ico_url ) : ?><img src="<?php echo esc_url( $ico_url ); ?>" alt=""><?php endif; ?></div>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][summa]" value="<?php echo esc_attr( $it['summa'] ?? '' ); ?>" placeholder="Сумма">
								<input type="text" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][time]" value="<?php echo esc_attr( $it['time'] ?? '' ); ?>" placeholder="Сроки / Разовая">
							</div>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][commission]" value="<?php echo esc_attr( $it['commission'] ?? '' ); ?>" placeholder="Вознаграждение">
								<input type="text" name="tolstenko_block_defaults[commission][items][<?php echo (int) $idx; ?>][remark]" value="<?php echo esc_attr( $it['remark'] ?? '' ); ?>" placeholder="Примечание">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="commission-items"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="benefits_cooperation" data-group="partner">
			<div class="row"><input type="text" name="tolstenko_block_defaults[benefits_cooperation][title]" value="<?php echo esc_attr( $bc['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Колонки преимуществ', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="benefits-cooperation-items">
					<?php foreach ( (array) ( $bc['items'] ?? array() ) as $idx => $col ) : ?>
						<?php $col = is_array( $col ) ? $col : array(); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<strong><?php echo esc_html( sprintf( __( 'Колонка %d', 'tolstenko-theme' ), (int) $idx + 1 ) ); ?></strong>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить колонку', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="benefits-col-lists" style="margin:8px 0;">
								<div class="muted"><?php esc_html_e( 'Пункты', 'tolstenko-theme' ); ?></div>
								<div data-repeater-list="benefits-cooperation-list">
									<?php foreach ( (array) ( $col['list'] ?? array() ) as $j => $elem ) : ?>
										<?php $elem = is_array( $elem ) ? $elem : array(); ?>
										<div class="repeater-item" data-repeater-item>
											<div class="cols">
												<input type="text" name="tolstenko_block_defaults[benefits_cooperation][items][<?php echo (int) $idx; ?>][list][<?php echo (int) $j; ?>][title]" value="<?php echo esc_attr( $elem['title'] ?? '' ); ?>" placeholder="Заголовок пункта" style="width:100%">
												<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
											</div>
											<div class="row"><textarea name="tolstenko_block_defaults[benefits_cooperation][items][<?php echo (int) $idx; ?>][list][<?php echo (int) $j; ?>][text]" rows="2" placeholder="Текст пункта"><?php echo esc_textarea( $elem['text'] ?? '' ); ?></textarea></div>
										</div>
									<?php endforeach; ?>
								</div>
								<div class="actions"><button type="button" class="button" data-add-item="benefits-cooperation-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
							</div>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[benefits_cooperation][items][<?php echo (int) $idx; ?>][btn_text]" value="<?php echo esc_attr( $col['btn_text'] ?? '' ); ?>" placeholder="Текст кнопки">
								<input type="url" name="tolstenko_block_defaults[benefits_cooperation][items][<?php echo (int) $idx; ?>][btn_url]" value="<?php echo esc_attr( $col['btn_url'] ?? '' ); ?>" placeholder="Ссылка (пусто = модалка)">
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="benefits-cooperation-items"><?php esc_html_e( 'Добавить колонку', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>
	<?php
}

function tolstenko_render_press_defaults_panels( $all ) {
	$ad = $all['aducation'] ?? array();
	$cl = $all['clients'] ?? array();
	$th = $all['themes'] ?? array();
	$co = $all['collaboration'] ?? array();
	$th_img_id  = isset( $th['image'] ) ? (int) $th['image'] : 0;
	$th_img_url = $th_img_id ? wp_get_attachment_image_url( $th_img_id, 'medium' ) : '';
	$co_img_id  = isset( $co['image'] ) ? (int) $co['image'] : 0;
	$co_img_url = $co_img_id ? wp_get_attachment_image_url( $co_img_id, 'medium' ) : '';
	?>
		<div class="tolstenko-df-panel" data-panel="aducation" data-group="press">
			<div class="row"><input type="text" name="tolstenko_block_defaults[aducation][title]" value="<?php echo esc_attr( $ad['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Этапы', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="aducation-items">
					<?php foreach ( (array) ( $ad['items'] ?? array() ) as $idx => $it ) : ?>
						<?php $it = is_array( $it ) ? $it : array(); ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[aducation][items][<?php echo (int) $idx; ?>][year]" value="<?php echo esc_attr( $it['year'] ?? '' ); ?>" placeholder="Год">
								<input type="text" name="tolstenko_block_defaults[aducation][items][<?php echo (int) $idx; ?>][type]" value="<?php echo esc_attr( $it['type'] ?? '' ); ?>" placeholder="Тип">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="row"><input type="text" name="tolstenko_block_defaults[aducation][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
							<div class="row"><input type="text" name="tolstenko_block_defaults[aducation][items][<?php echo (int) $idx; ?>][speciality]" value="<?php echo esc_attr( $it['speciality'] ?? '' ); ?>" style="width:100%" placeholder="Специальность"></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="aducation-items"><?php esc_html_e( 'Добавить этап', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Фото справа', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="aducation-images">
					<?php foreach ( (array) ( $ad['images'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array( 'image' => (int) $it );
						$img_id  = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[aducation][images][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Выбрать фото', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="aducation-images"><?php esc_html_e( 'Добавить фото', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="clients" data-group="press">
			<div class="row"><input type="text" name="tolstenko_block_defaults[clients][title]" value="<?php echo esc_attr( $cl['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row"><textarea name="tolstenko_block_defaults[clients][text]" rows="2" placeholder="Текст"><?php echo esc_textarea( $cl['text'] ?? '' ); ?></textarea></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Логотипы клиентов', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="clients-items">
					<?php foreach ( (array) ( $cl['items'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$img_id  = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[clients][items][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $it['name'] ?? '' ); ?>" placeholder="Название / alt">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[clients][items][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Логотип', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="clients-items"><?php esc_html_e( 'Добавить логотип', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[clients][subtitle]" value="<?php echo esc_attr( $cl['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок (СМИ)"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'СМИ', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="clients-smi">
					<?php foreach ( (array) ( $cl['smi'] ?? array() ) as $idx => $it ) : ?>
						<?php
						$it = is_array( $it ) ? $it : array();
						$img_id  = isset( $it['image'] ) ? (int) $it['image'] : 0;
						$img_url = $img_id ? wp_get_attachment_image_url( $img_id, 'thumbnail' ) : '';
						?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[clients][smi][<?php echo (int) $idx; ?>][name]" value="<?php echo esc_attr( $it['name'] ?? '' ); ?>" placeholder="Название / alt">
								<input type="url" name="tolstenko_block_defaults[clients][smi][<?php echo (int) $idx; ?>][link]" value="<?php echo esc_attr( $it['link'] ?? '' ); ?>" placeholder="Ссылка">
								<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[clients][smi][<?php echo (int) $idx; ?>][image]" value="<?php echo (int) $img_id; ?>">
								<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Логотип', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
							<div class="icon-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt=""><?php endif; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="clients-smi"><?php esc_html_e( 'Добавить СМИ', 'tolstenko-theme' ); ?></button></div>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="themes" data-group="press">
			<div class="row"><input type="text" name="tolstenko_block_defaults[themes][title]" value="<?php echo esc_attr( $th['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Темы', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="themes-items">
					<?php foreach ( (array) ( $th['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[themes][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Тема" style="width:100%">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="themes-items"><?php esc_html_e( 'Добавить тему', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[themes][more_text]" value="<?php echo esc_attr( $th['more_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст внизу списка"></div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[themes][btn_text]" value="<?php echo esc_attr( $th['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[themes][btn_url]" value="<?php echo esc_attr( $th['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка)"></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[themes][image]" value="<?php echo (int) $th_img_id; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Изображение', 'tolstenko-theme' ); ?></button>
				<span class="icon-preview"><?php echo $th_img_url ? '<img src="' . esc_url( $th_img_url ) . '" style="max-height:80px">' : ''; ?></span>
			</div>
		</div>

		<div class="tolstenko-df-panel" data-panel="collaboration" data-group="press">
			<div class="row"><input type="text" name="tolstenko_block_defaults[collaboration][title]" value="<?php echo esc_attr( $co['title'] ?? '' ); ?>" style="width:100%" placeholder="Заголовок"></div>
			<div class="row">
				<div class="muted"><?php esc_html_e( 'Пункты', 'tolstenko-theme' ); ?></div>
				<div data-repeater-list="collaboration-items">
					<?php foreach ( (array) ( $co['items'] ?? array() ) as $idx => $txt ) : ?>
						<div class="repeater-item" data-repeater-item>
							<div class="cols">
								<input type="text" name="tolstenko_block_defaults[collaboration][items][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Пункт" style="width:100%">
								<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				<div class="actions"><button type="button" class="button" data-add-item="collaboration-items"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
			</div>
			<div class="row"><input type="text" name="tolstenko_block_defaults[collaboration][btn_text]" value="<?php echo esc_attr( $co['btn_text'] ?? '' ); ?>" style="width:100%" placeholder="Текст кнопки"></div>
			<div class="row"><input type="url" name="tolstenko_block_defaults[collaboration][btn_url]" value="<?php echo esc_attr( $co['btn_url'] ?? '' ); ?>" style="width:100%" placeholder="Ссылка кнопки (пусто = модалка)"></div>
			<div class="row tolstenko-defaults-image-row">
				<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[collaboration][image]" value="<?php echo (int) $co_img_id; ?>">
				<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Изображение', 'tolstenko-theme' ); ?></button>
				<span class="icon-preview"><?php echo $co_img_url ? '<img src="' . esc_url( $co_img_url ) . '" style="max-height:80px">' : ''; ?></span>
			</div>
		</div>
	<?php
}

function tolstenko_render_partner_defaults_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для редактирования партнёрских блоков.', 'tolstenko-theme' ), 403 );
	}
	$posted = ! empty( $_POST );
	if ( $posted
		&& ( ! isset( $_POST['tolstenko_partner_blocks_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_partner_blocks_nonce'] ) ), 'tolstenko_partner_blocks_save' ) )
	) {
		tolstenko_admin_notice_nonce_failed();
	} elseif ( $posted ) {
		if ( tolstenko_save_partner_press_defaults_keys_from_request( tolstenko_partner_defaults_keys() ) ) {
			tolstenko_admin_notice( __( 'Дефолты партнёрских блоков сохранены.', 'tolstenko-theme' ), 'success' );
		} else {
			tolstenko_admin_notice_save_failed();
		}
	}

	$all = tolstenko_get_merged_defaults_for_keys( tolstenko_partner_defaults_keys() );
	$map = array(
		'referal'              => 'partner',
		'we_can'               => 'partner',
		'recomendation'        => 'partner',
		'commission'           => 'partner',
		'benefits_cooperation' => 'partner',
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Партнёры блоки', 'tolstenko-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Дефолты блоков партнёрской воронки. Сохраняются в общие настройки сайта и не затирают остальные дефолты.', 'tolstenko-theme' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'tolstenko_partner_blocks_save', 'tolstenko_partner_blocks_nonce' ); ?>
			<?php tolstenko_print_defaults_admin_styles(); ?>
			<div class="tolstenko-df">
				<div class="tolstenko-df-tabs-group is-active" data-group="partner">
					<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Партнёры блоки', 'tolstenko-theme' ); ?></div>
					<div class="tolstenko-df-tabs">
						<button type="button" class="tolstenko-df-tab active" data-panel="referal" data-group="partner"><?php esc_html_e( 'Рефералка', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="we_can" data-group="partner"><?php esc_html_e( 'Мы можем', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="recomendation" data-group="partner"><?php esc_html_e( 'Рекомендации', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="commission" data-group="partner"><?php esc_html_e( 'Вознаграждение', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="benefits_cooperation" data-group="partner"><?php esc_html_e( 'Преимущества', 'tolstenko-theme' ); ?></button>
					</div>
					<div class="tolstenko-df-group-panels" data-group-panels="partner"></div>
				</div>
				<div class="tolstenko-df-panels-source">
					<?php tolstenko_render_partner_defaults_panels( $all ); ?>
				</div>
			</div>
			<?php submit_button( __( 'Сохранить', 'tolstenko-theme' ) ); ?>
			<?php tolstenko_print_partner_press_defaults_admin_script( $map ); ?>
		</form>
	</div>
	<?php
}

function tolstenko_render_press_defaults_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав для редактирования блоков пресс-портрета.', 'tolstenko-theme' ), 403 );
	}
	$posted = ! empty( $_POST );
	if ( $posted
		&& ( ! isset( $_POST['tolstenko_press_blocks_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_press_blocks_nonce'] ) ), 'tolstenko_press_blocks_save' ) )
	) {
		tolstenko_admin_notice_nonce_failed();
	} elseif ( $posted ) {
		if ( tolstenko_save_partner_press_defaults_keys_from_request( tolstenko_press_defaults_keys() ) ) {
			tolstenko_admin_notice( __( 'Дефолты блоков пресс-портрета сохранены.', 'tolstenko-theme' ), 'success' );
		} else {
			tolstenko_admin_notice_save_failed();
		}
	}

	$all = tolstenko_get_merged_defaults_for_keys( tolstenko_press_defaults_keys() );
	$map = array(
		'aducation'     => 'press',
		'clients'       => 'press',
		'themes'        => 'press',
		'collaboration' => 'press',
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Пресс-портрет', 'tolstenko-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Дефолты блоков пресс-портрета. Сохраняются в общие настройки сайта и не затирают остальные дефолты.', 'tolstenko-theme' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'tolstenko_press_blocks_save', 'tolstenko_press_blocks_nonce' ); ?>
			<?php tolstenko_print_defaults_admin_styles(); ?>
			<div class="tolstenko-df">
				<div class="tolstenko-df-tabs-group is-active" data-group="press">
					<div class="tolstenko-df-tabs-group-title"><?php esc_html_e( 'Пресс-портрет', 'tolstenko-theme' ); ?></div>
					<div class="tolstenko-df-tabs">
						<button type="button" class="tolstenko-df-tab active" data-panel="aducation" data-group="press"><?php esc_html_e( 'Образование', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="clients" data-group="press"><?php esc_html_e( 'Клиенты', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="themes" data-group="press"><?php esc_html_e( 'Темы обучений и выступлений', 'tolstenko-theme' ); ?></button>
						<button type="button" class="tolstenko-df-tab" data-panel="collaboration" data-group="press"><?php esc_html_e( 'Форматы сотрудничества', 'tolstenko-theme' ); ?></button>
					</div>
					<div class="tolstenko-df-group-panels" data-group-panels="press"></div>
				</div>
				<div class="tolstenko-df-panels-source">
					<?php tolstenko_render_press_defaults_panels( $all ); ?>
				</div>
			</div>
			<?php submit_button( __( 'Сохранить', 'tolstenko-theme' ) ); ?>
			<?php tolstenko_print_partner_press_defaults_admin_script( $map ); ?>
		</form>
	</div>
	<?php
}
