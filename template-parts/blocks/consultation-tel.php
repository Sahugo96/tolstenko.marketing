<?php
/**
 * Блок «Консультация по телефону».
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir   = get_template_directory();
$block_attrs = tolstenko_block_attributes();
$defaults = tolstenko_block_defaults( 'consultation_tel' );

$site = tolstenko_site_data();

$title = ! empty( $block_attrs['block_consultation_tel_title'] )
	? (string) $block_attrs['block_consultation_tel_title']
	: (string) ( $defaults['title'] ?? '' );
$message = isset( $block_attrs['block_consultation_tel_message'] ) && trim( (string) $block_attrs['block_consultation_tel_message'] ) !== ''
	? (string) $block_attrs['block_consultation_tel_message']
	: (string) ( $defaults['message'] ?? '' );
$position = ! empty( $block_attrs['block_consultation_tel_position'] )
	? (string) $block_attrs['block_consultation_tel_position']
	: (string) ( $defaults['position'] ?? '' );
$btn_tel_text = ! empty( $block_attrs['block_consultation_tel_btn_tel_text'] )
	? (string) $block_attrs['block_consultation_tel_btn_tel_text']
	: (string) ( $defaults['btn_tel_text'] ?? '' );
$btn_messenger_text = ! empty( $block_attrs['block_consultation_tel_btn_messenger_text'] )
	? (string) $block_attrs['block_consultation_tel_btn_messenger_text']
	: (string) ( $defaults['btn_messenger_text'] ?? '' );
$btn_messenger_url = ! empty( $block_attrs['block_consultation_tel_btn_messenger_url'] )
	? (string) $block_attrs['block_consultation_tel_btn_messenger_url']
	: (string) ( $defaults['btn_messenger_url'] ?? '' );
if ( $btn_messenger_url === '' && ! empty( $site['telegram'] ) ) {
	$btn_messenger_url = (string) $site['telegram'];
}
$phone = ! empty( $block_attrs['block_consultation_tel_phone'] )
	? (string) $block_attrs['block_consultation_tel_phone']
	: (string) ( $defaults['phone'] ?? '' );
if ( $phone === '' && ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$color = ! empty( $block_attrs['block_consultation_tel_color'] )
	? (string) $block_attrs['block_consultation_tel_color']
	: (string) ( $defaults['color'] ?? '#25D366' );
$color_hover = ! empty( $block_attrs['block_consultation_tel_color_hover'] )
	? (string) $block_attrs['block_consultation_tel_color_hover']
	: (string) ( $defaults['color_hover'] ?? '#1EBE57' );

$img_id = ! empty( $block_attrs['block_consultation_tel_image'] )
	? (int) $block_attrs['block_consultation_tel_image']
	: (int) ( $defaults['image'] ?? 0 );
$img_src = $img_id > 0 ? (string) wp_get_attachment_image_url( $img_id, 'medium' ) : '';
$img_alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';
$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_consultation_tel_title_tag', 'h2' );

$phone_href = ! empty( $site['phone_href'] ) && $phone === (string) ( $site['phone'] ?? '' )
	? (string) $site['phone_href']
	: preg_replace( '/[\s\(\)\-]+/', '', $phone );
if ( $phone_href !== '' && strpos( $phone_href, '+' ) !== 0 && strpos( $phone_href, 'tel:' ) !== 0 ) {
	$phone_href = '+' . ltrim( $phone_href, '+' );
}

if ( $title === '' && $message === '' && $phone === '' ) {
	return;
}

$phone_icon = $theme_dir . '/assets/img/phone-icon.svg';
$tg_icon    = $theme_dir . '/assets/img/telegram-icon-white.svg';
?>
<section class="consultation-tel section">
	<div class="container">
		<div class="consultation-tel__inner">
			<div class="consultation-tel__left">
				<div class="consultation-tel__left-top">
					<?php if ( $img_src !== '' ) : ?>
						<div class="consultation-tel__img">
							<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
						</div>
					<?php endif; ?>

					<?php if ( $message !== '' || $position !== '' ) : ?>
						<div class="consultation-tel__message br-20">
							<?php if ( $message !== '' ) : ?>
								<div class="consultation-tel__message-text"><?php echo tolstenko_kses_html( $message ); ?></div>
							<?php endif; ?>
							<?php if ( $position !== '' ) : ?>
								<div class="consultation-tel__message-bottom paragraph-15-15">
									<?php echo esc_html( $position ); ?>
									<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M2.5 10.625L5.625 13.75L13.125 6.25M10 12.5L11.25 13.75L18.75 6.25" stroke="#2B271F" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</div>
							<?php endif; ?>
							<svg class="consultation-tel__message-angle" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M0 0L20 15V0H0Z" />
							</svg>
						</div>
					<?php endif; ?>
				</div>

				<div class="consultation-tel__btns">
					<?php if ( $phone_href !== '' && $btn_tel_text !== '' ) : ?>
						<a class="consultation-tel__btn consultation-tel__btn--tel default-btn line-caps-bold-13-15" href="tel:<?php echo esc_attr( $phone_href ); ?>">
							<?php
							if ( is_readable( $phone_icon ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
								echo file_get_contents( $phone_icon );
							}
							?>
							<span><?php echo esc_html( $btn_tel_text ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( $btn_messenger_url !== '' && $btn_messenger_text !== '' ) : ?>
						<a
							class="consultation-tel__btn default-btn default-btn--whatsapp line-caps-bold-13-15"
							href="<?php echo esc_url( $btn_messenger_url ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							style="--color-btn: <?php echo esc_attr( $color ); ?>; --color-hover-btn: <?php echo esc_attr( $color_hover ); ?>;"
						>
							<?php
							if ( is_readable( $tg_icon ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
								echo file_get_contents( $tg_icon );
							}
							?>
							<span><?php echo esc_html( $btn_messenger_text ); ?></span>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="consultation-tel__right">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="consultation-tel__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<svg class="consultation-tel__quote" viewBox="0 0 35 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path fill-rule="evenodd" clip-rule="evenodd" d="M22.3571 29.7308C29.866 24.7733 34.0377 19.0027 34.872 12.4193C36.1713 2.17096 27.2564 -2.84505 22.739 1.66146C18.2217 6.16798 20.9291 11.8893 24.1999 13.4537C27.4708 15.018 29.4711 14.4727 29.1221 16.5629C28.7731 18.6532 24.1202 24.4462 20.0316 27.1444C19.887 27.2712 19.7947 27.4499 19.7736 27.6441C19.7524 27.8383 19.8041 28.0336 19.9179 28.1901L20.9291 29.5422C21.3679 30.1286 21.7887 30.106 22.3571 29.7319M2.58657 29.7308C10.0955 24.7733 14.2671 19.0027 15.1015 12.4193C16.4018 2.17096 7.48683 -2.84505 2.96951 1.66146C-1.54782 6.16798 1.15958 11.8893 4.4314 13.4537C7.70322 15.018 9.70261 14.4727 9.35359 16.5629C9.00457 18.6532 4.35063 24.4462 0.2621 27.1444C0.117573 27.2713 0.0254824 27.4501 0.00455495 27.6443C-0.0163763 27.8385 0.0354196 28.0337 0.149414 28.1901L1.15958 29.5422C1.59835 30.1286 2.01917 30.106 2.58657 29.7319" />
				</svg>
			</div>
		</div>
	</div>
</section>
