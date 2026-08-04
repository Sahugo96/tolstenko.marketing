<?php
/**
 * Модалка заявки (CF7).
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title       = __( 'Оставить заявку', 'tolstenko-theme' );
$text        = __( 'Оставьте контакты — мы свяжемся с вами в ближайшее время.', 'tolstenko-theme' );
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
?>

<div class="modal" id="modal">
	<div class="modal__inner">
		<div class="modal__wrapper">
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
