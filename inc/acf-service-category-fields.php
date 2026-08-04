<?php
/**
 * ACF: поля для таксономии «Категории услуг» (service_category).
 * Страница подкатегории: ACF-заголовки + вкладки «Блоки страницы» (баннер, отзывы, текстовый блок).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'tolstenko_register_acf_service_category_fields' );
add_action( 'service_category_edit_form_fields', 'tolstenko_service_category_custom_blocks_fields', 20 );
add_action( 'edited_service_category', 'tolstenko_save_service_category_custom_blocks_fields', 20 );
add_action( 'admin_enqueue_scripts', 'tolstenko_service_category_custom_blocks_assets' );

function tolstenko_register_acf_service_category_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'                   => 'group_tolstenko_service_category',
			'title'                 => __( 'Страница подкатегории', 'tolstenko-theme' ),
			'fields'                => array(
				array(
					'key'         => 'field_sc_article_title',
					'label'       => __( 'Заголовок текстового блока', 'tolstenko-theme' ),
					'name'        => 'service_category_article_title',
					'type'        => 'text',
					'placeholder' => __( 'Если пусто — используется название категории.', 'tolstenko-theme' ),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'service_category',
					),
				),
			),
		)
	);

}

function tolstenko_service_category_custom_blocks_assets( $hook ) {
	if ( $hook !== 'term.php' ) {
		return;
	}
	if ( empty( $_GET['taxonomy'] ) || sanitize_key( $_GET['taxonomy'] ) !== 'service_category' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_editor();
}

function tolstenko_service_category_custom_blocks_fields( $term ) {
	if ( ! $term || ! isset( $term->term_id ) ) {
		return;
	}
	$text_reviews   = get_term_meta( $term->term_id, '_tolstenko_sc_text_reviews', true );
	if ( ! is_array( $text_reviews ) ) {
		$text_reviews = array();
	}
	$article_sections = get_term_meta( $term->term_id, '_tolstenko_sc_article_sections', true );
	if ( ! is_array( $article_sections ) ) {
		$article_sections = array();
	}

	?>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Блоки страницы', 'tolstenko-theme' ); ?></label></th>
		<td>
			<?php wp_nonce_field( 'tolstenko_sc_blocks_save', 'tolstenko_sc_blocks_nonce' ); ?>
			<div class="tolstenko-sc-wrap">
				<div class="tolstenko-sc-tabs">
					<button type="button" class="tolstenko-sc-tab is-active" data-target="main-hero"><?php esc_html_e( 'Главный баннер', 'tolstenko-theme' ); ?></button>
					<button type="button" class="tolstenko-sc-tab" data-target="text-reviews"><?php esc_html_e( 'Отзывы', 'tolstenko-theme' ); ?></button>
					<button type="button" class="tolstenko-sc-tab" data-target="article-text"><?php esc_html_e( 'Текстовый блок', 'tolstenko-theme' ); ?></button>
				</div>

				<?php
				if ( function_exists( 'tolstenko_sc_admin_print_hero_features_panels' ) ) {
					tolstenko_sc_admin_print_hero_features_panels( $term );
				}
				?>

				<div class="tolstenko-sc-panel" data-panel="text-reviews">
					<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Уникальные текстовые отзывы для этой категории. Если список пустой, блок «Отзывы» возьмёт отзывы из общего CPT.', 'tolstenko-theme' ); ?></p>
					<div id="tolstenko-sc-reviews-list">
						<?php foreach ( $text_reviews as $index => $row ) : ?>
							<?php
							$text       = isset( $row['text'] ) ? (string) $row['text'] : '';
							$link       = isset( $row['link'] ) ? (string) $row['link'] : '';
							$source     = isset( $row['source_name'] ) ? (string) $row['source_name'] : '';
							$icon_id    = isset( $row['source_icon_id'] ) ? (int) $row['source_icon_id'] : 0;
							$icon_url   = $icon_id ? wp_get_attachment_image_url( $icon_id, 'thumbnail' ) : '';
							?>
							<div class="tolstenko-sc-review-row" style="margin-bottom:8px;padding:8px;border:1px solid #ddd;background:#fff;">
								<textarea name="tolstenko_sc_text_reviews[<?php echo (int) $index; ?>][text]" rows="4" placeholder="<?php esc_attr_e( 'Текст отзыва', 'tolstenko-theme' ); ?>" style="width:100%;margin-bottom:8px;"><?php echo esc_textarea( $text ); ?></textarea>
								<input type="url" name="tolstenko_sc_text_reviews[<?php echo (int) $index; ?>][link]" value="<?php echo esc_attr( $link ); ?>" placeholder="<?php esc_attr_e( 'Ссылка на источник (необязательно)', 'tolstenko-theme' ); ?>" style="width:100%;margin-bottom:8px;">
								<input type="text" name="tolstenko_sc_text_reviews[<?php echo (int) $index; ?>][source_name]" value="<?php echo esc_attr( $source ); ?>" placeholder="<?php esc_attr_e( 'Название источника', 'tolstenko-theme' ); ?>" style="width:100%;margin-bottom:8px;">
								<input type="hidden" name="tolstenko_sc_text_reviews[<?php echo (int) $index; ?>][source_icon_id]" value="<?php echo (int) $icon_id; ?>" class="tolstenko-sc-review-icon-id">
								<button type="button" class="button tolstenko-sc-pick-review-icon"><?php esc_html_e( 'Выбрать иконку источника', 'tolstenko-theme' ); ?></button>
								<button type="button" class="button tolstenko-sc-remove-row"><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
								<div class="tolstenko-sc-review-preview" style="margin-top:8px;"><?php if ( $icon_url ) : ?><img src="<?php echo esc_url( $icon_url ); ?>" alt="" style="max-width:90px;height:auto;"><?php endif; ?></div>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button" id="tolstenko-sc-add-review"><?php esc_html_e( 'Добавить текстовый отзыв', 'tolstenko-theme' ); ?></button>
				</div>

				<div class="tolstenko-sc-panel" data-panel="article-text">
					<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Конструктор как в блоке «Статья» у услуги: картинка слева/справа, текст в 2 колонки или на всю ширину. Если секций нет — используется описание категории.', 'tolstenko-theme' ); ?></p>
					<input type="hidden" name="tolstenko_sc_article_sections_json" id="tolstenko-sc-article-sections-json" value="<?php echo esc_attr( wp_json_encode( array_values( $article_sections ) ) ); ?>">
					<div id="tolstenko-sc-article-list">
						<?php
						foreach ( $article_sections as $index => $row ) {
							if ( function_exists( 'tolstenko_sc_render_article_section_admin_row' ) ) {
								tolstenko_sc_render_article_section_admin_row( (int) $index, is_array( $row ) ? $row : array() );
							}
						}
						?>
					</div>
					<button type="button" class="button" id="tolstenko-sc-add-article-section"><?php esc_html_e( 'Добавить секцию', 'tolstenko-theme' ); ?></button>
					<div id="tolstenko-sc-article-editor-prototype" class="tolstenko-sc-article-editor-prototype" aria-hidden="true">
						<?php
						if ( function_exists( 'tolstenko_sc_render_article_section_text_field' ) ) {
							tolstenko_sc_render_article_section_text_field( 'template', '' );
						}
						?>
					</div>
					<template id="tolstenko-sc-article-row-tpl">
						<?php
						if ( function_exists( 'tolstenko_sc_render_article_section_admin_row' ) ) {
							tolstenko_sc_render_article_section_admin_row( '__INDEX__', array( 'type' => 'media_left', 'text' => '', 'image_id' => 0 ) );
						}
						?>
					</template>
				</div>
			</div>
		</td>
	</tr>

	<style>
	.tolstenko-sc-wrap{border:1px solid #dcdcde;background:#fff}
	.tolstenko-sc-tabs{display:flex;flex-wrap:wrap;gap:0;border-bottom:1px solid #dcdcde;background:#f6f7f7}
	.tolstenko-sc-tab{border:0;border-right:1px solid #dcdcde;background:transparent;padding:10px 14px;cursor:pointer}
	.tolstenko-sc-tab.is-active{background:#fff;font-weight:600}
	.tolstenko-sc-panel{display:none;padding:12px}
	.tolstenko-sc-panel.is-active{display:block}
	.tolstenko-sc-article-editor-wrap .wp-editor-wrap{max-width:100%}
	.tolstenko-sc-article-editor-wrap .wp-editor-container{border:1px solid #dcdcde}
	.tolstenko-sc-article-editor-prototype{position:absolute!important;left:-9999px;width:600px;height:1px;overflow:hidden;opacity:0;pointer-events:none}
	</style>

	<script>
	(function(){
		var tabButtons = document.querySelectorAll('.tolstenko-sc-tab');
		var panels = document.querySelectorAll('.tolstenko-sc-panel');
		tabButtons.forEach(function(btn){
			btn.addEventListener('click', function(){
				var target = btn.getAttribute('data-target');
				tabButtons.forEach(function(b){ b.classList.remove('is-active'); });
				panels.forEach(function(p){ p.classList.remove('is-active'); });
				btn.classList.add('is-active');
				var panel = document.querySelector('.tolstenko-sc-panel[data-panel="' + target + '"]');
				if (panel) panel.classList.add('is-active');
			});
		});

		function reindexRows(container, rowSelector, namePrefix){
			var rows = container.querySelectorAll(rowSelector);
			var prefixRe = new RegExp('^' + namePrefix.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[\\d+\\]');
			rows.forEach(function(row, idx){
				var fields = row.querySelectorAll('[name]');
				fields.forEach(function(f){
					if (!prefixRe.test(f.name)) return;
					f.name = f.name.replace(prefixRe, namePrefix + '[' + idx + ']');
				});
			});
		}

		function tolstenkoScGetArticleTextarea(row){
			if (!row) return null;
			var wrap = row.querySelector('.tolstenko-sc-article-editor-wrap');
			if (wrap) {
				var ta = wrap.querySelector('textarea');
				if (ta) return ta;
			}
			return row.querySelector('textarea.tolstenko-sc-article-text, textarea.wp-editor-area');
		}

		function tolstenkoScSyncAllArticleEditors(){
			if (typeof tinymce !== 'undefined' && tinymce.triggerSave) {
				tinymce.triggerSave();
			}
		}

		function tolstenkoScGetArticleRowText(row){
			var textEl = tolstenkoScGetArticleTextarea(row);
			if (!textEl) return '';
			var id = textEl.id;
			if (id && typeof tinymce !== 'undefined') {
				var ed = tinymce.get(id);
				if (ed) {
					var fromEditor = ed.getContent();
					if (fromEditor) return fromEditor;
					if (typeof ed.save === 'function') ed.save();
				}
			}
			return textEl.value || '';
		}

		function tolstenkoScArticleEditorRefId(){
			var list = document.getElementById('tolstenko-sc-article-list');
			if (list) {
				var rows = list.querySelectorAll('textarea[id^="tolstenko_sc_article_text_"]');
				for (var i = 0; i < rows.length; i++) {
					var id = rows[i].id;
					if (!id || id === 'tolstenko_sc_article_text_template' || id.indexOf('__INDEX__') !== -1) continue;
					if (window.tinyMCEPreInit && window.tinyMCEPreInit.mceInit && window.tinyMCEPreInit.mceInit[id]) return id;
				}
			}
			if (window.tinyMCEPreInit && window.tinyMCEPreInit.mceInit && window.tinyMCEPreInit.mceInit.tolstenko_sc_article_text_template) {
				return 'tolstenko_sc_article_text_template';
			}
			return null;
		}

		function tolstenkoScCloneArticleEditorSettings(){
			var settings = { tinymce: true, quicktags: true, mediaButtons: true };
			var refId = tolstenkoScArticleEditorRefId();
			if (!refId || !window.tinyMCEPreInit) return settings;
			if (window.tinyMCEPreInit.mceInit && window.tinyMCEPreInit.mceInit[refId]) {
				var tinymceCfg = window.tinyMCEPreInit.mceInit[refId];
				settings.tinymce = (typeof jQuery !== 'undefined' && jQuery.extend) ? jQuery.extend({}, tinymceCfg) : Object.assign({}, tinymceCfg);
				delete settings.tinymce.elements;
				delete settings.tinymce.selector;
				delete settings.tinymce.setup;
			}
			if (window.tinyMCEPreInit.qtInit && window.tinyMCEPreInit.qtInit[refId]) {
				var qtCfg = window.tinyMCEPreInit.qtInit[refId];
				settings.quicktags = (typeof jQuery !== 'undefined' && jQuery.extend) ? jQuery.extend({}, qtCfg) : Object.assign({}, qtCfg);
				delete settings.quicktags.id;
			}
			return settings;
		}

		function tolstenkoScInitArticleEditor(editorId){
			if (!editorId || typeof wp === 'undefined' || !wp.editor || typeof wp.editor.initialize !== 'function') return;
			var el = document.getElementById(editorId);
			if (!el || el.closest('.wp-editor-wrap')) return;
			var settings = tolstenkoScCloneArticleEditorSettings();
			wp.editor.initialize(editorId, settings);
		}

		function tolstenkoScRemoveArticleEditor(editorId){
			if (!editorId || typeof wp === 'undefined' || !wp.editor || typeof wp.editor.remove !== 'function') return;
			wp.editor.remove(editorId);
		}

		function collectArticleSectionsPayload(){
			var list = document.getElementById('tolstenko-sc-article-list');
			if (!list) return [];
			var payload = [];
			list.querySelectorAll('.tolstenko-sc-article-row').forEach(function(row){
				var typeEl = row.querySelector('.tolstenko-sc-article-type');
				var imgEl = row.querySelector('.tolstenko-sc-article-image-id');
				payload.push({
					type: typeEl ? typeEl.value : 'media_left',
					text: tolstenkoScGetArticleRowText(row),
					image_id: imgEl ? (parseInt(imgEl.value, 10) || 0) : 0
				});
			});
			return payload;
		}

		function syncArticleSectionsJson(){
			tolstenkoScSyncAllArticleEditors();
			var hidden = document.getElementById('tolstenko-sc-article-sections-json');
			if (!hidden) return;
			hidden.value = JSON.stringify(collectArticleSectionsPayload());
		}

		var categoryForm = document.getElementById('edittag') || document.querySelector('form[action*="edit-tags"]');
		if (categoryForm) {
			categoryForm.addEventListener('submit', function(){
				syncArticleSectionsJson();
			}, true);
			categoryForm.addEventListener('click', function(e){
				var t = e.target;
				if (!t) return;
				if (t.id === 'submit' || (t.type === 'submit' && t.name)) {
					syncArticleSectionsJson();
				}
			}, true);
		}

		document.addEventListener('click', function(e){
			if (e.target.classList.contains('tolstenko-sc-remove-row')) {
				e.preventDefault();
				var row = e.target.closest('.tolstenko-sc-row, .tolstenko-sc-review-row, .tolstenko-sc-solution-row, .tolstenko-sc-article-row');
				if (!row) return;
				var parent = row.parentNode;
				if (row.classList.contains('tolstenko-sc-article-row')) {
					var articleTextEl = tolstenkoScGetArticleTextarea(row);
					if (articleTextEl && articleTextEl.id) tolstenkoScRemoveArticleEditor(articleTextEl.id);
				}
				row.remove();
				if (parent && parent.id === 'tolstenko-sc-reviews-list') reindexRows(parent, '.tolstenko-sc-review-row', 'tolstenko_sc_text_reviews');
			}
		});

		function toggleArticleRowImage(row){
			if (!row) return;
			var sel = row.querySelector('.tolstenko-sc-article-type');
			var wrap = row.querySelector('.tolstenko-sc-article-image-wrap');
			if (!sel || !wrap) return;
			var t = sel.value || '';
			var show = (t === 'media_left' || t === 'media_right');
			wrap.style.display = show ? '' : 'none';
			row.setAttribute('data-needs-image', show ? '1' : '0');
		}

		function bindArticleSectionRow(row){
			if (!row || row.dataset.articleBound === '1') return;
			row.dataset.articleBound = '1';
			var sel = row.querySelector('.tolstenko-sc-article-type');
			if (sel) {
				sel.addEventListener('change', function(){ toggleArticleRowImage(row); });
				toggleArticleRowImage(row);
			}
			var input = row.querySelector('.tolstenko-sc-article-image-id');
			var preview = row.querySelector('.tolstenko-sc-article-preview');
			var clearBtn = row.querySelector('.tolstenko-sc-article-clear-image');
			function updateArticleClearBtn(){
				if (!clearBtn) return;
				clearBtn.style.display = (input && parseInt(input.value, 10) > 0) ? '' : 'none';
			}
			if (clearBtn && !clearBtn.dataset.bound) {
				clearBtn.dataset.bound = '1';
				clearBtn.addEventListener('click', function(e){
					e.preventDefault();
					if (input) input.value = '0';
					if (preview) preview.innerHTML = '';
					updateArticleClearBtn();
				});
			}
			var pick = row.querySelector('.tolstenko-sc-article-pick-image');
			if (pick && !pick.dataset.bound) {
				pick.dataset.bound = '1';
				pick.addEventListener('click', function(e){
					e.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var frame = wp.media({ title: 'Выберите картинку', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var selAtt = frame.state().get('selection').first();
						if (!selAtt) return;
						var json = selAtt.toJSON();
						if (input) input.value = json.id || 0;
						var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
						if (preview) preview.innerHTML = img ? '<img src="' + img + '" alt="" style="max-width:120px;height:auto;">' : '';
						updateArticleClearBtn();
					});
					frame.open();
				});
			}
			updateArticleClearBtn();
		}

		function bindArticleSections(scope){
			scope.querySelectorAll('.tolstenko-sc-article-row').forEach(bindArticleSectionRow);
		}
		bindArticleSections(document);

		var articleAdd = document.getElementById('tolstenko-sc-add-article-section');
		var articleList = document.getElementById('tolstenko-sc-article-list');
		var articleTpl = document.getElementById('tolstenko-sc-article-row-tpl');
		if (articleAdd && articleList && articleTpl) {
			articleAdd.addEventListener('click', function(e){
				e.preventDefault();
				var count = articleList.querySelectorAll('.tolstenko-sc-article-row').length;
				var html = articleTpl.innerHTML.replace(/__INDEX__/g, String(count));
				var wrap = document.createElement('div');
				wrap.innerHTML = html.trim();
				var row = wrap.firstElementChild;
				if (!row) return;
				articleList.appendChild(row);
				bindArticleSectionRow(row);
				var newTextEl = tolstenkoScGetArticleTextarea(row);
				if (newTextEl && newTextEl.id) {
					window.setTimeout(function(){
						tolstenkoScInitArticleEditor(newTextEl.id);
					}, 0);
				}
			});
		}

		function bindReviewIconPicker(scope){
			scope.querySelectorAll('.tolstenko-sc-pick-review-icon').forEach(function(btn){
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function(e){
					e.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var row = btn.closest('.tolstenko-sc-review-row');
					if (!row) return;
					var input = row.querySelector('.tolstenko-sc-review-icon-id');
					var preview = row.querySelector('.tolstenko-sc-review-preview');
					var frame = wp.media({ title: 'Выберите иконку', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var sel = frame.state().get('selection').first();
						if (!sel) return;
						var json = sel.toJSON();
						input.value = json.id || 0;
						var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
						preview.innerHTML = img ? '<img src="' + img + '" alt="" style="max-width:90px;height:auto;">' : '';
					});
					frame.open();
				});
			});
		}

		function bindScImagePickers(scope, btnSelector, inputSelector, previewSelector){
			(scope || document).querySelectorAll(btnSelector).forEach(function(btn){
				if (btn.dataset.bound) return;
				btn.dataset.bound = '1';
				btn.addEventListener('click', function(e){
					e.preventDefault();
					if (typeof wp === 'undefined' || !wp.media) return;
					var row = btn.closest('.tolstenko-sc-hero-row, div');
					var input = row ? row.querySelector(inputSelector) : null;
					var preview = row ? row.querySelector(previewSelector) : null;
					if (!input) return;
					var frame = wp.media({ title: 'Выберите изображение', button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var sel = frame.state().get('selection').first();
						if (!sel) return;
						var json = sel.toJSON();
						input.value = json.id || 0;
						var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
						if (preview) preview.innerHTML = img ? '<img src="' + img + '" alt="" style="max-width:48px;">' : '';
					});
					frame.open();
				});
			});
		}
		bindScImagePickers(document, '.tolstenko-sc-hero-pick-icon', '.tolstenko-sc-hero-icon-id', '.tolstenko-sc-hero-icon-preview');

		function bindScSingleImage(btnId, inputId, previewId, title) {
			var btn = document.getElementById(btnId);
			if (!btn) return;
			btn.addEventListener('click', function(e){
				e.preventDefault();
				if (typeof wp === 'undefined' || !wp.media) return;
				var input = document.getElementById(inputId);
				var preview = document.getElementById(previewId);
				var frame = wp.media({ title: title, button: { text: 'Использовать' }, multiple: false, library: { type: 'image' } });
				frame.on('select', function(){
					var sel = frame.state().get('selection').first();
					if (!sel) return;
					var json = sel.toJSON();
					if (input) input.value = json.id || 0;
					var img = (json.sizes && json.sizes.thumbnail && json.sizes.thumbnail.url) || json.url || '';
					if (preview) preview.innerHTML = img ? '<img src="' + img + '" alt="" style="max-width:120px;height:auto;">' : '';
				});
				frame.open();
			});
		}
		bindScSingleImage('tolstenko-sc-hero-pick-main', 'tolstenko-sc-hero-main-image', 'tolstenko-sc-hero-main-preview', 'Основное изображение');
		bindScSingleImage('tolstenko-sc-mh-pick-present', 'tolstenko-sc-mh-present-image', 'tolstenko-sc-mh-present-preview', 'Картинка подарка');

		var reviewBtn = document.getElementById('tolstenko-sc-add-review');
		var reviewList = document.getElementById('tolstenko-sc-reviews-list');
		if (reviewBtn && reviewList) {
			reviewBtn.addEventListener('click', function(e){
				e.preventDefault();
				var count = reviewList.querySelectorAll('.tolstenko-sc-review-row').length;
				var wrap = document.createElement('div');
				wrap.className = 'tolstenko-sc-review-row';
				wrap.style.cssText = 'margin-bottom:8px;padding:8px;border:1px solid #ddd;background:#fff;';
				wrap.innerHTML = '<textarea name="tolstenko_sc_text_reviews[' + count + '][text]" rows="4" placeholder="Текст отзыва" style="width:100%;margin-bottom:8px;"></textarea><input type="url" name="tolstenko_sc_text_reviews[' + count + '][link]" placeholder="Ссылка на источник (необязательно)" style="width:100%;margin-bottom:8px;"><input type="text" name="tolstenko_sc_text_reviews[' + count + '][source_name]" placeholder="Название источника" style="width:100%;margin-bottom:8px;"><input type="hidden" name="tolstenko_sc_text_reviews[' + count + '][source_icon_id]" value="0" class="tolstenko-sc-review-icon-id"><button type="button" class="button tolstenko-sc-pick-review-icon">Выбрать иконку источника</button> <button type="button" class="button tolstenko-sc-remove-row">Удалить</button><div class="tolstenko-sc-review-preview" style="margin-top:8px;"></div>';
				reviewList.appendChild(wrap);
				bindReviewIconPicker(wrap);
			});
		}
		bindReviewIconPicker(document);
	})();
	</script>
	<?php
}

function tolstenko_save_service_category_custom_blocks_fields( $term_id ) {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	if ( ! isset( $_POST['tolstenko_sc_blocks_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_sc_blocks_nonce'] ) ), 'tolstenko_sc_blocks_save' ) ) {
		return;
	}

	if ( function_exists( 'tolstenko_sc_admin_save_hero_features_meta' ) ) {
		tolstenko_sc_admin_save_hero_features_meta( $term_id );
	}

	$text_reviews_raw = isset( $_POST['tolstenko_sc_text_reviews'] ) ? wp_unslash( $_POST['tolstenko_sc_text_reviews'] ) : array();
	$text_reviews_out = array();
	if ( is_array( $text_reviews_raw ) ) {
		foreach ( $text_reviews_raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$text = isset( $row['text'] ) ? wp_kses_post( $row['text'] ) : '';
			$link = isset( $row['link'] ) ? esc_url_raw( trim( (string) $row['link'] ) ) : '';
			$source_name = isset( $row['source_name'] ) ? sanitize_text_field( $row['source_name'] ) : '';
			$source_icon_id = isset( $row['source_icon_id'] ) ? (int) $row['source_icon_id'] : 0;
			if ( trim( wp_strip_all_tags( $text ) ) === '' && $link === '' && $source_name === '' && $source_icon_id === 0 ) {
				continue;
			}
			$text_reviews_out[] = array(
				'text'           => $text,
				'link'           => $link,
				'source_name'    => $source_name,
				'source_icon_id' => $source_icon_id,
			);
		}
	}
	update_term_meta( $term_id, '_tolstenko_sc_text_reviews', $text_reviews_out );

	$article_out = array();
	if ( isset( $_POST['tolstenko_sc_article_sections_json'] ) ) {
		$decoded = json_decode( wp_unslash( (string) $_POST['tolstenko_sc_article_sections_json'] ), true );
		if ( is_array( $decoded ) && function_exists( 'tolstenko_sc_sanitize_article_sections' ) ) {
			$article_out = tolstenko_sc_sanitize_article_sections( $decoded );
		}
	} elseif ( isset( $_POST['tolstenko_sc_article_sections'] ) && is_array( $_POST['tolstenko_sc_article_sections'] ) ) {
		$article_out = function_exists( 'tolstenko_sc_sanitize_article_sections' )
			? tolstenko_sc_sanitize_article_sections( wp_unslash( $_POST['tolstenko_sc_article_sections'] ) )
			: array();
	}
	update_term_meta( $term_id, '_tolstenko_sc_article_sections', $article_out );
	delete_term_meta( $term_id, '_tolstenko_sc_banner_reverse' );
}
