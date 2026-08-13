<?php
/**
 * Отдельная модалка по таймеру: логотип, телефон, заголовок со скрина + CF7 как у #modal.
 * Память показа — как у guide-banner.
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() ) {
	return;
}

$timed = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'timed_modal' ) : array();
if ( empty( $timed['enabled'] ) ) {
	return;
}

$delay = isset( $timed['delay_seconds'] ) ? (int) $timed['delay_seconds'] : 40;
if ( $delay < 5 ) {
	$delay = 5;
}
if ( $delay > 600 ) {
	$delay = 600;
}

$title = isset( $timed['title'] ) ? trim( (string) $timed['title'] ) : '';
if ( $title === '' ) {
	$title = __( 'Не уходите без ответов!', 'tolstenko-theme' );
}

$text = isset( $timed['text'] ) ? trim( (string) $timed['text'] ) : '';
if ( $text === '' ) {
	$text = __( 'Получите консультацию по привлечению клиентов — оставьте контакты, и мы перезвоним.', 'tolstenko-theme' );
}

$thanks      = __( 'Спасибо за заявку!', 'tolstenko-theme' );
$thanks_text = __( 'Мы свяжемся с вами в ближайшее время.', 'tolstenko-theme' );
if ( function_exists( 'tolstenko_get_block_defaults' ) ) {
	$thanks_defaults = tolstenko_get_block_defaults( 'thanks' );
	if ( is_array( $thanks_defaults ) ) {
		if ( ! empty( $thanks_defaults['title'] ) ) {
			$thanks = (string) $thanks_defaults['title'];
		}
		if ( ! empty( $thanks_defaults['description'] ) ) {
			$thanks_text = (string) $thanks_defaults['description'];
		}
	}
}

$site  = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();
$phone = isset( $timed['phone'] ) ? trim( (string) $timed['phone'] ) : '';
if ( $phone === '' && ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$phone_href = ! empty( $site['phone_href'] ) ? (string) $site['phone_href'] : '';
if ( $phone !== '' && $phone_href === '' ) {
	$phone_href = preg_replace( '/[^\d+]/', '', $phone );
}
if ( $phone_href !== '' && strpos( $phone_href, '+' ) !== 0 && strpos( $phone_href, '8' ) !== 0 ) {
	$phone_href = '+' . ltrim( $phone_href, '+' );
}

$theme_uri      = get_template_directory_uri();
$theme_dir      = get_template_directory();
$logo_url       = $theme_uri . '/assets/img/logo.svg';
$phone_icon     = $theme_dir . '/assets/img/phone-icon.svg';
$privacy_url    = function_exists( 'tolstenko_cf7_privacy_url' ) ? tolstenko_cf7_privacy_url() : home_url( '/privacy-policy/' );
?>

<div
	class="modal modal--timed"
	id="modal-timed"
	hidden
	aria-hidden="true"
	data-timed-delay="<?php echo esc_attr( (string) $delay ); ?>"
>
	<div class="modal__inner">
		<div class="modal__wrapper">
			<div class="modal__brand">
				<a class="modal__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="180" height="40">
				</a>

				<?php if ( $phone !== '' ) : ?>
					<a class="modal__phone" href="tel:<?php echo esc_attr( $phone_href ); ?>">
						<?php
						if ( is_readable( $phone_icon ) ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG.
							echo file_get_contents( $phone_icon );
						}
						?>
						<span><?php echo esc_html( $phone ); ?></span>
					</a>
				<?php endif; ?>
			</div>

			<h2 class="modal__title h2"><?php echo esc_html( $title ); ?></h2>

			<span class="modal__subtitle">
				<?php echo esc_html( $text ); ?>
			</span>

			<div class="modal__form form">
				<?php echo do_shortcode( '[contact-form-7 id="83e976b" title="Модалка"]' ); ?>
			</div>
		</div>

		<div class="modal__thanks">
			<h2 class="modal__title h2"><?php echo esc_html( $thanks ); ?></h2>

			<span class="modal__subtitle">
				<?php echo esc_html( $thanks_text ); ?>
			</span>

			<a class="modal__link default-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'На главную', 'tolstenko-theme' ); ?>
			</a>
		</div>

		<svg class="modal__close" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Закрыть', 'tolstenko-theme' ); ?>">
			<path d="M9.33398 9.33334L22.6673 22.6667" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
			<path d="M9.33398 22.6667L22.6673 9.33334" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
	</div>
</div>
