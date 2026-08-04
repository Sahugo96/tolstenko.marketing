<?php
/**
 * Админ-поля: главный баннер для подкатегории.
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param WP_Term $term Term.
 * @return void
 */
function tolstenko_sc_admin_print_hero_features_panels( $term ) {
	$sc_hero = function_exists( 'tolstenko_sc_resolve_category_block_attributes' )
		? tolstenko_sc_resolve_category_block_attributes( 'main_hero', $term, '_tolstenko_sc_main_hero' )
		: array();

	$hero_title      = isset( $sc_hero['block_main_hero_title'] ) ? (string) $sc_hero['block_main_hero_title'] : $term->name;
	$hero_text       = isset( $sc_hero['block_main_hero_text'] ) ? (string) $sc_hero['block_main_hero_text'] : '';
	$hero_btn        = isset( $sc_hero['block_main_hero_btn_text'] ) ? (string) $sc_hero['block_main_hero_btn_text'] : '';
	$hero_promo      = isset( $sc_hero['block_main_hero_promo_text'] ) ? (string) $sc_hero['block_main_hero_promo_text'] : '';
	$hero_show_promo = isset( $sc_hero['block_main_hero_show_promo'] ) ? (string) $sc_hero['block_main_hero_show_promo'] : '';
	$hero_img        = isset( $sc_hero['block_main_hero_image'] ) ? (int) $sc_hero['block_main_hero_image'] : 0;
	$hero_img_url    = $hero_img ? wp_get_attachment_image_url( $hero_img, 'thumbnail' ) : '';
	$present_id      = isset( $sc_hero['block_main_hero_present_image'] ) ? (int) $sc_hero['block_main_hero_present_image'] : 0;
	$present_url     = $present_id ? wp_get_attachment_image_url( $present_id, 'thumbnail' ) : '';
	$person_name     = isset( $sc_hero['block_main_hero_person_name'] ) ? (string) $sc_hero['block_main_hero_person_name'] : '';
	$person_pos      = isset( $sc_hero['block_main_hero_person_position'] ) ? (string) $sc_hero['block_main_hero_person_position'] : '';
	$hero_items      = isset( $sc_hero['block_main_hero_items'] ) && is_array( $sc_hero['block_main_hero_items'] ) ? $sc_hero['block_main_hero_items'] : array();
	while ( count( $hero_items ) < 3 ) {
		$hero_items[] = '';
	}
	?>
	<div class="tolstenko-sc-panel is-active" data-panel="main-hero">
		<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Как блок «Главный баннер» у услуги. Пустой заголовок на сайте — название подкатегории.', 'tolstenko-theme' ); ?></p>
		<label><strong><?php esc_html_e( 'Заголовок (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<textarea name="tolstenko_sc_mh_title" rows="2" style="width:100%;margin-bottom:8px;" placeholder="<?php echo esc_attr( $term->name ); ?>"><?php echo esc_textarea( $hero_title ); ?></textarea>
		<label><strong><?php esc_html_e( 'Текст под заголовком (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<textarea name="tolstenko_sc_mh_text" rows="3" style="width:100%;margin-bottom:8px;"><?php echo esc_textarea( $hero_text ); ?></textarea>
		<p class="description"><?php esc_html_e( 'Список (до 3 пунктов):', 'tolstenko-theme' ); ?></p>
		<div id="tolstenko-sc-hero-list">
			<?php foreach ( array_slice( $hero_items, 0, 3 ) as $hi => $hrow ) : ?>
				<?php
				$htext = is_string( $hrow ) ? $hrow : ( is_array( $hrow ) ? (string) ( $hrow['text'] ?? '' ) : '' );
				?>
				<div class="tolstenko-sc-hero-row" style="margin-bottom:8px;padding:8px;border:1px solid #ddd;background:#fff;">
					<textarea name="tolstenko_sc_mh_items[<?php echo (int) $hi; ?>]" rows="2" style="width:100%;" placeholder="<?php esc_attr_e( 'Текст пункта (HTML)', 'tolstenko-theme' ); ?>"><?php echo esc_textarea( $htext ); ?></textarea>
				</div>
			<?php endforeach; ?>
		</div>
		<label><strong><?php esc_html_e( 'Текст кнопки', 'tolstenko-theme' ); ?></strong></label>
		<input type="text" name="tolstenko_sc_mh_btn_text" value="<?php echo esc_attr( $hero_btn ); ?>" style="width:100%;margin-bottom:8px;">
		<label style="display:block;margin:8px 0;">
			<input type="checkbox" name="tolstenko_sc_mh_show_promo" value="1" <?php checked( $hero_show_promo === '1' || ( $hero_show_promo === '' && $hero_promo !== '' ) ); ?>>
			<?php esc_html_e( 'Показать промо у кнопки', 'tolstenko-theme' ); ?>
		</label>
		<label><strong><?php esc_html_e( 'Текст промо (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<textarea name="tolstenko_sc_mh_promo_text" rows="2" style="width:100%;margin-bottom:8px;"><?php echo esc_textarea( $hero_promo ); ?></textarea>
		<input type="hidden" name="tolstenko_sc_mh_present_image" value="<?php echo (int) $present_id; ?>" id="tolstenko-sc-mh-present-image">
		<p style="margin-top:10px;"><button type="button" class="button" id="tolstenko-sc-mh-pick-present"><?php esc_html_e( 'Картинка подарка', 'tolstenko-theme' ); ?></button></p>
		<div id="tolstenko-sc-mh-present-preview" style="margin:8px 0;"><?php if ( $present_url ) : ?><img src="<?php echo esc_url( $present_url ); ?>" alt="" style="max-width:80px;"><?php endif; ?></div>
		<label><strong><?php esc_html_e( 'Имя персоны', 'tolstenko-theme' ); ?></strong></label>
		<input type="text" name="tolstenko_sc_mh_person_name" value="<?php echo esc_attr( $person_name ); ?>" style="width:100%;margin-bottom:8px;">
		<label><strong><?php esc_html_e( 'Должность', 'tolstenko-theme' ); ?></strong></label>
		<input type="text" name="tolstenko_sc_mh_person_position" value="<?php echo esc_attr( $person_pos ); ?>" style="width:100%;margin-bottom:8px;">
		<input type="hidden" name="tolstenko_sc_mh_image" value="<?php echo (int) $hero_img; ?>" id="tolstenko-sc-hero-main-image">
		<p style="margin-top:10px;"><button type="button" class="button" id="tolstenko-sc-hero-pick-main"><?php esc_html_e( 'Основное изображение', 'tolstenko-theme' ); ?></button></p>
		<div id="tolstenko-sc-hero-main-preview" style="margin:8px 0;"><?php if ( $hero_img_url ) : ?><img src="<?php echo esc_url( $hero_img_url ); ?>" alt="" style="max-width:120px;height:auto;"><?php endif; ?></div>
	</div>
	<?php
}

/**
 * @param int $term_id Term ID.
 * @return void
 */
function tolstenko_sc_admin_save_hero_features_meta( $term_id ) {
	$hero_items_raw = isset( $_POST['tolstenko_sc_mh_items'] ) ? wp_unslash( $_POST['tolstenko_sc_mh_items'] ) : array();
	$hero_items_out = array();
	if ( is_array( $hero_items_raw ) ) {
		foreach ( $hero_items_raw as $row ) {
			$raw_txt = is_array( $row )
				? ( isset( $row['text'] ) ? (string) $row['text'] : '' )
				: (string) $row;
			$txt = tolstenko_kses_html( $raw_txt );
			if ( trim( $txt ) === '' ) {
				continue;
			}
			$hero_items_out[] = $txt;
		}
	}
	$show_promo  = ! empty( $_POST['tolstenko_sc_mh_show_promo'] ) ? '1' : '0';
	$hero_bundle = array(
		'block_main_hero_title'           => isset( $_POST['tolstenko_sc_mh_title'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_mh_title'] ) ) : '',
		'block_main_hero_title_tag'       => 'h1',
		'block_main_hero_text'            => isset( $_POST['tolstenko_sc_mh_text'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_mh_text'] ) ) : '',
		'block_main_hero_items'           => $hero_items_out,
		'block_main_hero_btn_text'        => isset( $_POST['tolstenko_sc_mh_btn_text'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_mh_btn_text'] ) ) : '',
		'block_main_hero_show_promo'      => $show_promo,
		'block_main_hero_promo_text'      => isset( $_POST['tolstenko_sc_mh_promo_text'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_mh_promo_text'] ) ) : '',
		'block_main_hero_present_image'   => isset( $_POST['tolstenko_sc_mh_present_image'] ) ? (int) $_POST['tolstenko_sc_mh_present_image'] : 0,
		'block_main_hero_person_name'     => isset( $_POST['tolstenko_sc_mh_person_name'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_sc_mh_person_name'] ) ) : '',
		'block_main_hero_person_position' => isset( $_POST['tolstenko_sc_mh_person_position'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_sc_mh_person_position'] ) ) : '',
		'block_main_hero_image'           => isset( $_POST['tolstenko_sc_mh_image'] ) ? (int) $_POST['tolstenko_sc_mh_image'] : 0,
	);
	if ( ! tolstenko_sc_main_hero_has_custom_content( $hero_bundle ) ) {
		delete_term_meta( $term_id, '_tolstenko_sc_main_hero' );
	} else {
		update_term_meta( $term_id, '_tolstenko_sc_main_hero', $hero_bundle );
	}
	delete_term_meta( $term_id, '_tolstenko_sc_banner' );
	delete_term_meta( $term_id, '_tolstenko_sc_features' );
	delete_term_meta( $term_id, '_tolstenko_sc_features_banner' );
}
