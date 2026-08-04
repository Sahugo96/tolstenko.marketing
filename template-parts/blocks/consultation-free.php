<?php
/**
 * Блок «Бесплатная консультация» (форма CF7 + контакты).
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
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'consultation_free' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_consultation_free_title'] )
	? (string) $block_attrs['block_consultation_free_title']
	: (string) ( $defaults['title'] ?? '' );
$text = isset( $block_attrs['block_consultation_free_text'] ) && trim( (string) $block_attrs['block_consultation_free_text'] ) !== ''
	? (string) $block_attrs['block_consultation_free_text']
	: (string) ( $defaults['text'] ?? '' );
$subtitle = ! empty( $block_attrs['block_consultation_free_subtitle'] )
	? (string) $block_attrs['block_consultation_free_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );
$contacts_label = ! empty( $block_attrs['block_consultation_free_contacts_label'] )
	? (string) $block_attrs['block_consultation_free_contacts_label']
	: (string) ( $defaults['contacts_label'] ?? '' );

$phone = ! empty( $block_attrs['block_consultation_free_phone'] )
	? (string) $block_attrs['block_consultation_free_phone']
	: (string) ( $defaults['phone'] ?? '' );
if ( $phone === '' && ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$telegram_url = ! empty( $block_attrs['block_consultation_free_telegram_url'] )
	? (string) $block_attrs['block_consultation_free_telegram_url']
	: (string) ( $defaults['telegram_url'] ?? '' );
if ( $telegram_url === '' && ! empty( $site['telegram'] ) ) {
	$telegram_url = (string) $site['telegram'];
}
$whatsapp_url = ! empty( $block_attrs['block_consultation_free_whatsapp_url'] )
	? (string) $block_attrs['block_consultation_free_whatsapp_url']
	: (string) ( $defaults['whatsapp_url'] ?? '' );
if ( $whatsapp_url === '' && ! empty( $site['whatsapp'] ) ) {
	$whatsapp_url = (string) $site['whatsapp'];
}
$vk_url = ! empty( $block_attrs['block_consultation_free_vk_url'] )
	? (string) $block_attrs['block_consultation_free_vk_url']
	: (string) ( $defaults['vk_url'] ?? '' );
if ( $vk_url === '' && ! empty( $site['vk'] ) ) {
	$vk_url = (string) $site['vk'];
}

$img_id = ! empty( $block_attrs['block_consultation_free_image'] )
	? (int) $block_attrs['block_consultation_free_image']
	: (int) ( $defaults['image'] ?? 0 );
$img_src = $img_id > 0 ? (string) wp_get_attachment_image_url( $img_id, 'large' ) : '';
$img_alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_consultation_free_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$phone_href = ! empty( $site['phone_href'] ) && $phone === (string) ( $site['phone'] ?? '' )
	? (string) $site['phone_href']
	: preg_replace( '/[\s\(\)\-]+/', '', $phone );
if ( $phone_href !== '' && strpos( $phone_href, '+' ) !== 0 ) {
	$phone_href = '+' . ltrim( $phone_href, '+' );
}

$thanks_page = get_page_by_path( 'thanks' );
$thanks_url  = $thanks_page ? get_permalink( $thanks_page ) : '';

$phone_icon = $theme_dir . '/assets/img/phone-icon.svg';
$tg_icon    = $theme_dir . '/assets/img/telegram-icon.svg';
$vk_icon    = $theme_dir . '/assets/img/vk-icon.svg';
$wa_icon    = $theme_dir . '/assets/img/whatsapp-ion.svg';
?>
<section class="consultation-free section"<?php echo $thanks_url ? ' data-thanks-url="' . esc_url( $thanks_url ) . '"' : ''; ?>>
	<div class="container">
		<div class="consultation-free__inner br-30">
			<div class="consultation-free__left">
				<div class="consultation-free__top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="consultation-free__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<div class="consultation-free__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></div>
					<?php endif; ?>
				</div>

				<div class="consultation-free__form form br-20">
					<?php if ( $subtitle !== '' ) : ?>
						<div class="consultation-free__subtitle line-caps-bold-16-15"><?php echo tolstenko_kses_html( $subtitle ); ?></div>
					<?php endif; ?>
					<?php
					if ( function_exists( 'wpcf7_contact_form' ) ) {
						echo do_shortcode( '[contact-form-7 id="c4693f2" title="Бесплатная консультация"]' );
					}
					?>
				</div>

				<?php if ( $phone !== '' || $telegram_url !== '' || $whatsapp_url !== '' || $vk_url !== '' ) : ?>
					<div class="consultation-free__btns">
						<?php if ( $contacts_label !== '' ) : ?>
							<div class="consultation-free__btns-text line-caps-bold-13-15"><?php echo tolstenko_kses_html( $contacts_label ); ?></div>
						<?php endif; ?>

						<?php if ( $phone !== '' ) : ?>
							<a class="consultation-free__btn consultation-free__btn--tel default-btn line-caps-bold-13-15" href="tel:<?php echo esc_attr( $phone_href ); ?>">
								<?php
								if ( is_readable( $phone_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $phone_icon );
								}
								?>
								<span><?php echo esc_html( $phone ); ?></span>
							</a>
						<?php endif; ?>

						<?php if ( $telegram_url !== '' ) : ?>
							<a class="consultation-free__btn default-btn line-caps-bold-13-15" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram">
								<?php
								if ( is_readable( $tg_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $tg_icon );
								}
								?>
							</a>
						<?php endif; ?>

						<?php if ( $vk_url !== '' ) : ?>
							<a class="consultation-free__btn default-btn line-caps-bold-13-15" href="<?php echo esc_url( $vk_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="VK">
								<?php
								if ( is_readable( $vk_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $vk_icon );
								}
								?>
							</a>
						<?php endif; ?>

						<?php if ( $whatsapp_url !== '' ) : ?>
							<a class="consultation-free__btn default-btn line-caps-bold-13-15" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp">
								<?php
								if ( is_readable( $wa_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $wa_icon );
								}
								?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="consultation-free__img">
				<?php if ( $img_src !== '' ) : ?>
					<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
