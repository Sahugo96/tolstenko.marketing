<?php
/**
 * Блок «Консультация WhatsApp».
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir   = get_template_directory();
$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'consultation_whatsapp' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_consultation_whatsapp_title'] )
	? (string) $block_attrs['block_consultation_whatsapp_title']
	: (string) ( $defaults['title'] ?? '' );
$text = isset( $block_attrs['block_consultation_whatsapp_text'] ) && trim( (string) $block_attrs['block_consultation_whatsapp_text'] ) !== ''
	? (string) $block_attrs['block_consultation_whatsapp_text']
	: (string) ( $defaults['text'] ?? '' );
$btn_text = ! empty( $block_attrs['block_consultation_whatsapp_btn_text'] )
	? (string) $block_attrs['block_consultation_whatsapp_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = ! empty( $block_attrs['block_consultation_whatsapp_btn_url'] )
	? (string) $block_attrs['block_consultation_whatsapp_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
if ( $btn_url === '' && ! empty( $site['whatsapp'] ) ) {
	$btn_url = (string) $site['whatsapp'];
}
$color = ! empty( $block_attrs['block_consultation_whatsapp_color'] )
	? (string) $block_attrs['block_consultation_whatsapp_color']
	: (string) ( $defaults['color'] ?? '#25D366' );
$color_hover = ! empty( $block_attrs['block_consultation_whatsapp_color_hover'] )
	? (string) $block_attrs['block_consultation_whatsapp_color_hover']
	: (string) ( $defaults['color_hover'] ?? '#1EBE57' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_consultation_whatsapp_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

if ( $title === '' && $text === '' && $btn_url === '' ) {
	return;
}

$wa_icon     = $theme_dir . '/assets/img/whatsapp-icon-white.svg';
$blog_inline = (bool) get_query_var( 'tolstenko_block_blog_inline', false );

$btn_html = '';
if ( $btn_url !== '' && $btn_text !== '' ) {
	ob_start();
	?>
	<a
		class="whatsapp-block__btn default-btn default-btn--whatsapp"
		href="<?php echo esc_url( $btn_url ); ?>"
		target="_blank"
		rel="noopener noreferrer"
		style="--color-btn: <?php echo esc_attr( $color ); ?>; --color-hover-btn: <?php echo esc_attr( $color_hover ); ?>;"
	>
		<?php
		if ( is_readable( $wa_icon ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
			echo file_get_contents( $wa_icon );
		}
		?>
		<span><?php echo esc_html( $btn_text ); ?></span>
	</a>
	<?php
	$btn_html = ob_get_clean();
}

if ( $blog_inline ) :
	?>
	<div class="single-blog__content-block whatsapp-block whatsapp-block--short br-30">
		<div class="whatsapp-block__wrapper">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="whatsapp-block__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="whatsapp-block__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>
		<?php echo $btn_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>
	<?php
	return;
endif;
?>
<section class="consultation-whatsapp section">
	<div class="container">
		<div class="consultation-whatsapp__inner">
			<div class="consultation-whatsapp__wrapper whatsapp-block br-30">
				<div class="whatsapp-block__wrapper">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="whatsapp-block__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="whatsapp-block__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
				<?php echo $btn_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
</section>
