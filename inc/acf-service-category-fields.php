<?php
/**
 * Поля таксономии «Категории услуг»: скрытый H1, главный баннер, SEO продвижение.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'service_category_edit_form_fields', 'tolstenko_service_category_custom_blocks_fields', 20 );
add_action( 'edited_service_category', 'tolstenko_save_service_category_custom_blocks_fields', 20 );
add_action( 'admin_enqueue_scripts', 'tolstenko_service_category_custom_blocks_assets' );

function tolstenko_service_category_custom_blocks_assets( $hook ) {
	if ( $hook !== 'term.php' ) {
		return;
	}
	if ( empty( $_GET['taxonomy'] ) || sanitize_key( $_GET['taxonomy'] ) !== 'service_category' ) {
		return;
	}
	wp_enqueue_media();
}

function tolstenko_service_category_custom_blocks_fields( $term ) {
	if ( ! $term || ! isset( $term->term_id ) ) {
		return;
	}
	$h1 = (string) get_term_meta( $term->term_id, '_tolstenko_sc_h1', true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="tolstenko_sc_h1"><?php esc_html_e( 'H1', 'tolstenko-theme' ); ?></label></th>
		<td>
			<?php wp_nonce_field( 'tolstenko_sc_blocks_save', 'tolstenko_sc_blocks_nonce' ); ?>
			<input type="text" name="tolstenko_sc_h1" id="tolstenko_sc_h1" value="<?php echo esc_attr( $h1 ); ?>" placeholder="<?php echo esc_attr( $term->name ); ?>">
			<p class="description"><?php esc_html_e( 'Выводится в начале main.main и скрыт классом hide. Если пусто — название категории.', 'tolstenko-theme' ); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label><?php esc_html_e( 'Блоки страницы', 'tolstenko-theme' ); ?></label></th>
		<td>
			<div class="tolstenko-sc-wrap">
				<div class="tolstenko-sc-tabs">
					<button type="button" class="tolstenko-sc-tab is-active" data-target="main-hero"><?php esc_html_e( 'Главный баннер', 'tolstenko-theme' ); ?></button>
					<button type="button" class="tolstenko-sc-tab" data-target="seo-section"><?php esc_html_e( 'SEO продвижение', 'tolstenko-theme' ); ?></button>
				</div>
				<?php
				if ( function_exists( 'tolstenko_sc_admin_print_hero_features_panels' ) ) {
					tolstenko_sc_admin_print_hero_features_panels( $term );
				}
				if ( function_exists( 'tolstenko_sc_admin_print_seo_section_panel' ) ) {
					tolstenko_sc_admin_print_seo_section_panel( $term );
				}
				?>
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

	$h1 = isset( $_POST['tolstenko_sc_h1'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_sc_h1'] ) ) : '';
	if ( $h1 === '' ) {
		delete_term_meta( $term_id, '_tolstenko_sc_h1' );
	} else {
		update_term_meta( $term_id, '_tolstenko_sc_h1', $h1 );
	}

	if ( function_exists( 'tolstenko_sc_admin_save_hero_features_meta' ) ) {
		tolstenko_sc_admin_save_hero_features_meta( $term_id );
	}
	if ( function_exists( 'tolstenko_sc_admin_save_seo_section_meta' ) ) {
		tolstenko_sc_admin_save_seo_section_meta( $term_id );
	}
}
