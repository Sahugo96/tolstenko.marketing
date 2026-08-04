<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_register_header_footer_socials_meta_box' );
add_action( 'save_post_block', 'tolstenko_save_header_footer_socials_meta_box' );
add_action( 'admin_enqueue_scripts', 'tolstenko_header_footer_socials_admin_assets' );

function tolstenko_register_header_footer_socials_meta_box() {
	add_meta_box(
		'tolstenko_header_footer_socials',
		__( 'Соцсети (репитер)', 'tolstenko-theme' ),
		'tolstenko_render_header_footer_socials_meta_box',
		'block',
		'normal',
		'default'
	);
}

function tolstenko_header_footer_socials_admin_assets( $hook ) {
	if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || $screen->post_type !== 'block' ) {
		return;
	}
	wp_enqueue_media();
}

function tolstenko_render_header_footer_socials_meta_box( $post ) {
	wp_nonce_field( 'tolstenko_hf_socials_save', 'tolstenko_hf_socials_nonce' );

	$block_type = function_exists( 'get_field' ) ? (string) get_field( 'block_type', $post->ID ) : '';
	$is_header_footer = ( $block_type === 'header-footer' );

	$groups = array(
		'header_1' => __( 'Хедер: блок 1', 'tolstenko-theme' ),
		'header_2' => __( 'Хедер: блок 2', 'tolstenko-theme' ),
		'footer_1' => __( 'Футер: блок 1', 'tolstenko-theme' ),
		'footer_2' => __( 'Футер: блок 2', 'tolstenko-theme' ),
	);

	if ( ! $is_header_footer ) {
		echo '<p class="description" style="color:#b32d2e;">' . esc_html__( 'Заполняется только для шаблона «Шапка и подвал сайта» (header-footer).', 'tolstenko-theme' ) . '</p>';
	}
	?>
	<style>
		.tolstenko-hf-group{border:1px solid #dcdcde;background:#fff;padding:12px;margin:14px 0}
		.tolstenko-hf-title{font-weight:600;margin:0 0 10px}
		.tolstenko-hf-list{display:flex;flex-direction:column;gap:8px}
		.tolstenko-hf-row{border:1px solid #e2e4e7;background:#f9f9f9;padding:10px 12px;display:grid;grid-template-columns:92px minmax(0,1fr) minmax(0,1fr) auto;grid-template-areas:"enabled link text actions" "icon icon icon icon";gap:10px 12px;align-items:end}
		.tolstenko-hf-row.is-disabled{opacity:.55}
		.tolstenko-hf-field-enabled{grid-area:enabled;align-self:center}
		.tolstenko-hf-field-icon{grid-area:icon}
		.tolstenko-hf-field-link{grid-area:link}
		.tolstenko-hf-field-text{grid-area:text}
		.tolstenko-hf-field-actions{grid-area:actions}
		.tolstenko-hf-field-enabled>.tolstenko-hf-enabled{display:flex;align-items:center;gap:6px;margin:0;white-space:nowrap;font-size:12px;color:#50575e;font-weight:400;cursor:pointer}
		.tolstenko-hf-field-enabled>.tolstenko-hf-enabled input{margin:0}
		.tolstenko-hf-icon-controls{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
		.tolstenko-hf-icon-preview{width:28px;height:28px;flex:0 0 28px;border:1px solid #dcdcde;background:#fff;object-fit:contain}
		.tolstenko-hf-icon-value{flex:0 1 auto;max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#666}
		.tolstenko-hf-row .field{margin:0;min-width:0}
		.tolstenko-hf-row .field>label{display:block;font-size:12px;color:#50575e;margin:0 0 4px;font-weight:600}
		.tolstenko-hf-row .field input[type="url"],
		.tolstenko-hf-row .field input[type="text"]{width:100%;max-width:100%;box-sizing:border-box}
		.tolstenko-hf-row .field-actions .tolstenko-hf-row-btns{display:flex;flex-wrap:wrap;gap:6px}
		.tolstenko-hf-actions{display:flex;gap:8px;align-items:center}
		.tolstenko-hf-empty{color:#777;font-style:italic}
		@media (max-width: 960px){
			.tolstenko-hf-row{grid-template-columns:1fr auto;grid-template-areas:"enabled actions" "link link" "text text" "icon icon"}
		}
	</style>
	<script>
	(function(){
		function getWrap(){
			return document.getElementById('tolstenko_header_footer_socials');
		}
		function updateEmpty(list){
			let empty = list.parentElement.querySelector('.tolstenko-hf-empty');
			if (!empty) return;
			empty.style.display = list.children.length ? 'none' : '';
		}
		function updateAllEmpty(wrap){
			wrap.querySelectorAll('[data-social-list]').forEach(updateEmpty);
		}
		function init(){
			const wrap = getWrap();
			if (!wrap || wrap.dataset.tolstenkoSocialsInit === '1') return;
			wrap.dataset.tolstenkoSocialsInit = '1';
			updateAllEmpty(wrap);
			wrap.addEventListener('click', function(e){
				const addBtn = e.target.closest('[data-social-add]');
				if (addBtn) {
					const group = addBtn.closest('[data-social-group]');
					if (!group) return;
					const list = group.querySelector('[data-social-list]');
					const tpl = group.querySelector('template');
					if (!list || !tpl) return;
					const idx = Date.now().toString() + Math.floor(Math.random() * 1000).toString();
					const html = tpl.innerHTML.replace(/__INDEX__/g, idx);
					list.insertAdjacentHTML('beforeend', html);
					updateEmpty(list);
					return;
				}
				const enabledToggle = e.target.closest('[data-social-enabled]');
				if (enabledToggle) {
					const row = enabledToggle.closest('.tolstenko-hf-row');
					if (row) row.classList.toggle('is-disabled', !enabledToggle.checked);
					return;
				}
				const removeBtn = e.target.closest('[data-social-remove]');
				if (removeBtn) {
					const row = removeBtn.closest('.tolstenko-hf-row');
					const list = removeBtn.closest('[data-social-group]')?.querySelector('[data-social-list]');
					if (row) row.remove();
					if (list) updateEmpty(list);
					return;
				}
				const up = e.target.closest('[data-social-up]');
				if (up) {
					const row = up.closest('.tolstenko-hf-row');
					if (row && row.previousElementSibling) {
						row.parentNode.insertBefore(row, row.previousElementSibling);
					}
					return;
				}
				const down = e.target.closest('[data-social-down]');
				if (down) {
					const row = down.closest('.tolstenko-hf-row');
					if (row && row.nextElementSibling) {
						row.parentNode.insertBefore(row.nextElementSibling, row);
					}
					return;
				}
				const pick = e.target.closest('[data-social-pick-icon]');
				if (pick) {
					const row = pick.closest('.tolstenko-hf-row');
					if (!row || !window.wp || !wp.media) return;
					const input = row.querySelector('[data-social-icon-input]');
					const preview = row.querySelector('[data-social-icon-preview]');
					const valueText = row.querySelector('[data-social-icon-value]');
					const frame = wp.media({ title: 'Выбор иконки', multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						const att = frame.state().get('selection').first().toJSON();
						if (!att) return;
						if (input) input.value = String(att.id || '');
						if (preview && att.url) preview.src = att.url;
						if (valueText) valueText.textContent = input && input.value ? ('ID: ' + input.value) : 'Не выбрана';
					});
					frame.open();
					return;
				}
				const fromUrl = e.target.closest('[data-social-set-url]');
				if (fromUrl) {
					const row = fromUrl.closest('.tolstenko-hf-row');
					if (!row) return;
					const input = row.querySelector('[data-social-icon-input]');
					const preview = row.querySelector('[data-social-icon-preview]');
					const valueText = row.querySelector('[data-social-icon-value]');
					const currentVal = input && input.value ? input.value : '';
					const url = window.prompt('Вставьте URL изображения', currentVal);
					if (url === null) return;
					const clean = String(url).trim();
					if (input) input.value = clean;
					if (preview) preview.src = clean;
					if (valueText) valueText.textContent = clean ? 'URL задан' : 'Не выбрана';
					return;
				}
				const clear = e.target.closest('[data-social-clear-icon]');
				if (clear) {
					const row = clear.closest('.tolstenko-hf-row');
					if (!row) return;
					const input = row.querySelector('[data-social-icon-input]');
					const preview = row.querySelector('[data-social-icon-preview]');
					const valueText = row.querySelector('[data-social-icon-value]');
					if (input) input.value = '';
					if (preview) preview.src = '';
					if (valueText) valueText.textContent = 'Не выбрана';
				}
			});
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', init);
		} else {
			init();
		}
	})();
	</script>
	<?php

	foreach ( $groups as $key => $label ) {
		$meta_key = '_tolstenko_hf_socials_' . $key;
		$rows     = get_post_meta( $post->ID, $meta_key, true );
		if ( ! is_array( $rows ) ) {
			$rows = array();
		}
		if ( empty( $rows ) && ! metadata_exists( 'post', $post->ID, $meta_key ) && function_exists( 'tolstenko_get_site_header_footer_data' ) ) {
			$hf_data = tolstenko_get_site_header_footer_data();
			$fallback_key = 'socials_' . $key;
			if ( isset( $hf_data[ $fallback_key ] ) && is_array( $hf_data[ $fallback_key ] ) ) {
				$rows = $hf_data[ $fallback_key ];
			}
		}
		echo '<div class="tolstenko-hf-group" data-social-group>';
		echo '<p class="tolstenko-hf-title">' . esc_html( $label ) . '</p>';
		echo '<div class="tolstenko-hf-list" data-social-list>';
		foreach ( $rows as $i => $row ) {
			$icon    = isset( $row['icon'] ) ? (string) $row['icon'] : '';
			$link    = isset( $row['link'] ) ? (string) $row['link'] : '';
			$text    = isset( $row['text'] ) ? (string) $row['text'] : '';
			$enabled = ! isset( $row['enabled'] ) || ! empty( $row['enabled'] );
			tolstenko_render_hf_social_row( $key, (string) $i, $icon, $link, $text, $enabled );
		}
		echo '</div>';
		echo '<p class="tolstenko-hf-empty">' . esc_html__( 'Пока нет элементов.', 'tolstenko-theme' ) . '</p>';
		echo '<p class="tolstenko-hf-actions"><button type="button" class="button" data-social-add>' . esc_html__( 'Добавить элемент', 'tolstenko-theme' ) . '</button></p>';
		echo '<template>';
		tolstenko_render_hf_social_row( $key, '__INDEX__', '', '', '', true );
		echo '</template>';
		echo '</div>';
	}
}

function tolstenko_render_hf_social_row( $group, $index, $icon, $link, $text, $enabled = true ) {
	$name = 'tolstenko_hf_socials[' . $group . '][' . $index . ']';
	$preview = '';
	if ( $icon !== '' && ctype_digit( $icon ) ) {
		$img = wp_get_attachment_image_url( (int) $icon, 'thumbnail' );
		$preview = $img ? (string) $img : '';
	} elseif ( $icon !== '' ) {
		$preview = $icon;
	}
	$row_class = 'tolstenko-hf-row' . ( $enabled ? '' : ' is-disabled' );
	echo '<div class="' . esc_attr( $row_class ) . '">';
	echo '<div class="field tolstenko-hf-field-enabled"><label class="tolstenko-hf-enabled"><input type="hidden" name="' . esc_attr( $name . '[enabled]' ) . '" value="0"><input type="checkbox" data-social-enabled name="' . esc_attr( $name . '[enabled]' ) . '" value="1"' . checked( $enabled, true, false ) . '> ' . esc_html__( 'Показать', 'tolstenko-theme' ) . '</label></div>';
	echo '<div class="field tolstenko-hf-field-link"><label>' . esc_html__( 'Ссылка', 'tolstenko-theme' ) . '</label><input type="url" name="' . esc_attr( $name . '[link]' ) . '" value="' . esc_attr( $link ) . '" placeholder="https://..."></div>';
	echo '<div class="field tolstenko-hf-field-text"><label>' . esc_html__( 'Текст', 'tolstenko-theme' ) . '</label><input type="text" name="' . esc_attr( $name . '[text]' ) . '" value="' . esc_attr( $text ) . '" placeholder="' . esc_attr__( 'Например Telegram', 'tolstenko-theme' ) . '"></div>';
	echo '<div class="field tolstenko-hf-field-actions field-actions"><label>&nbsp;</label><div class="tolstenko-hf-row-btns"><button type="button" class="button button-small" data-social-up>↑</button><button type="button" class="button button-small" data-social-down>↓</button><button type="button" class="button button-small" data-social-remove>' . esc_html__( 'Удалить', 'tolstenko-theme' ) . '</button></div></div>';
	$icon_value_label = $icon !== '' ? ( ctype_digit( $icon ) ? 'ID: ' . $icon : __( 'URL задан', 'tolstenko-theme' ) ) : __( 'Не выбрана', 'tolstenko-theme' );
	echo '<div class="field tolstenko-hf-field-icon"><label>' . esc_html__( 'Иконка', 'tolstenko-theme' ) . '</label><div class="tolstenko-hf-icon-controls"><img class="tolstenko-hf-icon-preview" data-social-icon-preview src="' . esc_url( $preview ) . '" alt=""><input type="hidden" data-social-icon-input name="' . esc_attr( $name . '[icon]' ) . '" value="' . esc_attr( $icon ) . '"><span class="tolstenko-hf-icon-value" data-social-icon-value>' . esc_html( $icon_value_label ) . '</span><button type="button" class="button button-small" data-social-pick-icon>' . esc_html__( 'Выбрать', 'tolstenko-theme' ) . '</button><button type="button" class="button button-small" data-social-set-url>' . esc_html__( 'Вставить URL', 'tolstenko-theme' ) . '</button><button type="button" class="button button-small" data-social-clear-icon>' . esc_html__( 'Очистить', 'tolstenko-theme' ) . '</button></div></div>';
	echo '</div>';
}

function tolstenko_save_header_footer_socials_meta_box( $post_id ) {
	if ( ! isset( $_POST['tolstenko_hf_socials_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_hf_socials_nonce'] ) ), 'tolstenko_hf_socials_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['tolstenko_hf_socials'] ) && is_array( $_POST['tolstenko_hf_socials'] )
		? wp_unslash( $_POST['tolstenko_hf_socials'] )
		: array();

	$groups = array( 'header_1', 'header_2', 'footer_1', 'footer_2' );
	foreach ( $groups as $group ) {
		$rows = array();
		if ( isset( $raw[ $group ] ) && is_array( $raw[ $group ] ) ) {
			foreach ( $raw[ $group ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				$icon    = isset( $row['icon'] ) ? trim( (string) $row['icon'] ) : '';
				$link    = isset( $row['link'] ) ? esc_url_raw( trim( (string) $row['link'] ) ) : '';
				$text    = isset( $row['text'] ) ? sanitize_text_field( (string) $row['text'] ) : '';
				$enabled = ! empty( $row['enabled'] );
				if ( $icon === '' && $link === '' && $text === '' ) {
					continue;
				}
				$rows[] = array(
					'icon'    => $icon,
					'link'    => $link,
					'text'    => $text,
					'enabled' => $enabled ? 1 : 0,
				);
			}
		}
		update_post_meta( $post_id, '_tolstenko_hf_socials_' . $group, $rows );
	}
}
