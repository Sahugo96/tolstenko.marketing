<?php
/**
 * Админ-поля: SEO продвижение для подкатегории.
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
function tolstenko_sc_admin_print_seo_section_panel( $term ) {
	$saved = get_term_meta( $term->term_id, '_tolstenko_sc_seo_section', true );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$title     = isset( $saved['block_seo_section_title'] ) ? (string) $saved['block_seo_section_title'] : '';
	$subtitle  = isset( $saved['block_seo_section_subtitle'] ) ? (string) $saved['block_seo_section_subtitle'] : '';
	$more_text = isset( $saved['block_seo_section_more_text'] ) ? (string) $saved['block_seo_section_more_text'] : '';
	$redactor  = '';
	if ( ! empty( $saved['block_seo_section_blocks'] ) && is_array( $saved['block_seo_section_blocks'] ) ) {
		foreach ( $saved['block_seo_section_blocks'] as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$layout = (string) ( $row['layout'] ?? '' );
			if ( $layout === 'redactor' && trim( (string) ( $row['redactor'] ?? '' ) ) !== '' ) {
				$redactor = (string) $row['redactor'];
				break;
			}
			if ( $layout === 'text' && trim( (string) ( $row['text'] ?? '' ) ) !== '' ) {
				$redactor = (string) $row['text'];
				break;
			}
		}
	}
	?>
	<div class="tolstenko-sc-panel" data-panel="seo-section">
		<p class="description" style="margin-bottom:10px;"><?php esc_html_e( 'Как блок «SEO продвижение». Пустые поля не затирают дефолты сайта / блок на странице услуг.', 'tolstenko-theme' ); ?></p>
		<label><strong><?php esc_html_e( 'Заголовок (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<input type="text" name="tolstenko_sc_seo_title" value="<?php echo esc_attr( $title ); ?>" style="width:100%;margin-bottom:8px;">
		<label><strong><?php esc_html_e( 'Подзаголовок (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<textarea name="tolstenko_sc_seo_subtitle" rows="3" style="width:100%;margin-bottom:8px;"><?php echo esc_textarea( $subtitle ); ?></textarea>
		<label><strong><?php esc_html_e( 'Текст кнопки «Читать далее»', 'tolstenko-theme' ); ?></strong></label>
		<input type="text" name="tolstenko_sc_seo_more_text" value="<?php echo esc_attr( $more_text ); ?>" style="width:100%;margin-bottom:8px;" placeholder="<?php esc_attr_e( 'Читать далее', 'tolstenko-theme' ); ?>">
		<label><strong><?php esc_html_e( 'Текст секции (HTML)', 'tolstenko-theme' ); ?></strong></label>
		<textarea name="tolstenko_sc_seo_redactor" rows="10" style="width:100%;margin-bottom:8px;"><?php echo esc_textarea( $redactor ); ?></textarea>
	</div>
	<?php
}

/**
 * @param int $term_id Term ID.
 * @return void
 */
function tolstenko_sc_admin_save_seo_section_meta( $term_id ) {
	$title     = isset( $_POST['tolstenko_sc_seo_title'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_seo_title'] ) ) : '';
	$subtitle  = isset( $_POST['tolstenko_sc_seo_subtitle'] ) ? tolstenko_kses_html( wp_unslash( $_POST['tolstenko_sc_seo_subtitle'] ) ) : '';
	$more_text = isset( $_POST['tolstenko_sc_seo_more_text'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_sc_seo_more_text'] ) ) : '';
	$redactor  = isset( $_POST['tolstenko_sc_seo_redactor'] )
		? ( function_exists( 'tolstenko_kses_redactor' ) ? tolstenko_kses_redactor( wp_unslash( $_POST['tolstenko_sc_seo_redactor'] ) ) : wp_kses_post( wp_unslash( $_POST['tolstenko_sc_seo_redactor'] ) ) )
		: '';

	$blocks = array();
	if ( trim( wp_strip_all_tags( $redactor ) ) !== '' ) {
		$blocks[] = array(
			'layout'   => 'redactor',
			'redactor' => $redactor,
		);
	}

	$bundle = array(
		'block_seo_section_title'     => $title,
		'block_seo_section_title_tag' => 'h2',
		'block_seo_section_subtitle'  => $subtitle,
		'block_seo_section_more_text' => $more_text,
		'block_seo_section_blocks'    => $blocks,
	);

	if ( ! function_exists( 'tolstenko_sc_seo_section_has_custom_content' ) || ! tolstenko_sc_seo_section_has_custom_content( $bundle ) ) {
		delete_term_meta( $term_id, '_tolstenko_sc_seo_section' );
		return;
	}
	update_term_meta( $term_id, '_tolstenko_sc_seo_section', $bundle );
}
