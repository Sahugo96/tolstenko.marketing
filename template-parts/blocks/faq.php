<?php
/**
 * Блок «FAQ».
 * Данные: атрибуты Gutenberg → дефолты блоков.
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'faq' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}
$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_faq_title'] )
	? (string) $block_attrs['block_faq_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_faq_title_tag'] ?? 'h2', 'h2' )
	: 'h2';
$text = isset( $block_attrs['block_faq_text'] ) && trim( (string) $block_attrs['block_faq_text'] ) !== ''
	? (string) $block_attrs['block_faq_text']
	: (string) ( $defaults['text'] ?? '' );

$items = array();
$raw_items = ! empty( $block_attrs['block_faq_items'] ) && is_array( $block_attrs['block_faq_items'] )
	? $block_attrs['block_faq_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$q = trim( (string) ( $it['title'] ?? '' ) );
	$a = (string) ( $it['redactor'] ?? '' );
	if ( $q === '' && trim( wp_strip_all_tags( $a ) ) === '' ) {
		continue;
	}
	$items[] = array(
		'title'    => $q,
		'redactor' => $a,
	);
}

$form_title = ! empty( $block_attrs['block_faq_form_title'] )
	? (string) $block_attrs['block_faq_form_title']
	: (string) ( $defaults['form_title'] ?? '' );
$form_text = isset( $block_attrs['block_faq_form_text'] ) && trim( (string) $block_attrs['block_faq_form_text'] ) !== ''
	? (string) $block_attrs['block_faq_form_text']
	: (string) ( $defaults['form_text'] ?? '' );

$foto_id = ! empty( $block_attrs['block_faq_foto'] )
	? (int) $block_attrs['block_faq_foto']
	: (int) ( $defaults['foto'] ?? 0 );
$foto_url = $foto_id ? (string) wp_get_attachment_image_url( $foto_id, 'medium' ) : '';
$foto_alt = $foto_id ? (string) get_post_meta( $foto_id, '_wp_attachment_image_alt', true ) : '';
$foto_text = isset( $block_attrs['block_faq_foto_text'] ) && trim( (string) $block_attrs['block_faq_foto_text'] ) !== ''
	? (string) $block_attrs['block_faq_foto_text']
	: (string) ( $defaults['foto_text'] ?? '' );

$phone = ! empty( $block_attrs['block_faq_phone'] )
	? (string) $block_attrs['block_faq_phone']
	: (string) ( $defaults['phone'] ?? '' );
if ( $phone === '' && ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$phone_href = ! empty( $site['phone_href'] ) && $phone === (string) ( $site['phone'] ?? '' )
	? (string) $site['phone_href']
	: preg_replace( '/[^0-9+]/', '', $phone );

$telegram_url = ! empty( $block_attrs['block_faq_telegram_url'] )
	? (string) $block_attrs['block_faq_telegram_url']
	: (string) ( $defaults['telegram_url'] ?? '' );
if ( $telegram_url === '' && ! empty( $site['telegram'] ) ) {
	$telegram_url = (string) $site['telegram'];
}

if ( $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="faq section" id="faq">
	<div class="container">
		<div class="faq__top section-top">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="faq__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="faq__text lead-20-25"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<div class="faq__inner">
			<?php if ( ! empty( $items ) ) : ?>
				<div class="faq__items br-30 accordion-group">
					<?php foreach ( $items as $index => $item ) : ?>
						<div class="faq__item br-20 accordion<?php echo 0 === $index ? ' active' : ''; ?>">
							<div class="faq__item-top accordion-top">
								<svg class="faq__item-arrow" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path fill-rule="evenodd" clip-rule="evenodd" d="M15 28.75C22.5941 28.75 28.75 22.5941 28.75 15C28.75 7.40588 22.5941 1.25 15 1.25C7.40588 1.25 1.25 7.40588 1.25 15C1.25 22.5941 7.40588 28.75 15 28.75ZM15.6463 10.1463C15.8396 9.95313 16.1017 9.84466 16.375 9.84466C16.6483 9.84466 16.9104 9.95313 17.1037 10.1463L21.2288 14.2713C21.4219 14.4646 21.5303 14.7267 21.5303 15C21.5303 15.2733 21.4219 15.5354 21.2288 15.7287L17.1037 19.8538C17.0093 19.9551 16.8955 20.0363 16.769 20.0927C16.6425 20.1491 16.5059 20.1794 16.3675 20.1818C16.229 20.1843 16.0915 20.1588 15.9631 20.1069C15.8346 20.0551 15.718 19.9779 15.6201 19.8799C15.5221 19.782 15.4449 19.6654 15.3931 19.5369C15.3412 19.4085 15.3157 19.271 15.3182 19.1325C15.3206 18.9941 15.3509 18.8575 15.4073 18.731C15.4637 18.6045 15.5449 18.4907 15.6463 18.3962L18.0112 16.0312H9.5C9.2265 16.0312 8.96419 15.9226 8.7708 15.7292C8.5774 15.5358 8.46875 15.2735 8.46875 15C8.46875 14.7265 8.5774 14.4642 8.7708 14.2708C8.96419 14.0774 9.2265 13.9688 9.5 13.9688H18.0112L15.6463 11.6037C15.4531 11.4104 15.3447 11.1483 15.3447 10.875C15.3447 10.6017 15.4531 10.3396 15.6463 10.1463Z" />
								</svg>
								<p class="faq__item-title line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item['title'] ); ?></p>
								<svg class="faq__item-plus" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M9.99992 3.33398V16.6673M16.6666 10.0007L3.33325 10.0007" stroke-width="2" stroke-linecap="round" />
								</svg>
							</div>
							<div class="faq__item-redactor redactor"><?php echo tolstenko_kses_redactor( $item['redactor'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="faq__right">
				<div class="faq__form form br-20">
					<?php if ( $form_title !== '' ) : ?>
						<div class="faq__form-title line-caps-bold-16-15"><?php echo tolstenko_kses_html( $form_title ); ?></div>
					<?php endif; ?>
					<?php if ( $form_text !== '' ) : ?>
						<div class="faq__form-text line-13-15"><?php echo tolstenko_kses_html( $form_text ); ?></div>
					<?php endif; ?>
					<?php
					echo do_shortcode( '[contact-form-7 id="7d09741" title="Телефон"]' );
					?>
				</div>

				<?php if ( $foto_url !== '' || $foto_text !== '' ) : ?>
					<div class="faq__right-info line-13-15">
						<?php if ( $foto_url !== '' ) : ?>
							<div class="faq__right-foto">
								<img src="<?php echo esc_url( $foto_url ); ?>" alt="<?php echo esc_attr( $foto_alt ); ?>" loading="lazy" decoding="async">
							</div>
						<?php endif; ?>
						<?php if ( $foto_text !== '' ) : ?>
							<?php echo tolstenko_kses_html( $foto_text ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $phone !== '' || $telegram_url !== '' ) : ?>
					<div class="faq__contact-links">
						<?php if ( $phone !== '' && $phone_href !== '' ) : ?>
							<a class="faq__contact-link faq__contact-link--phone default-btn" href="tel:<?php echo esc_attr( $phone_href ); ?>">
								<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M15.4677 11.6429C15.3953 11.1565 15.0073 10.7774 14.5077 10.6644C12.5658 10.2239 12.079 9.14185 11.9969 7.94164C11.6525 7.87966 11.0399 7.81258 10 7.81258C8.96012 7.81258 8.34749 7.87966 8.00312 7.94164C7.92099 9.14185 7.4342 10.2239 5.49233 10.6644C4.99273 10.7781 4.60466 11.1565 4.53232 11.6429L4.15781 14.1527C4.02594 15.0364 4.69659 15.8334 5.62947 15.8334H14.3705C15.3027 15.8334 15.9741 15.0364 15.8422 14.1527L15.4677 11.6429ZM10 14.0047C8.94882 14.0047 8.09656 13.188 8.09656 12.1817C8.09656 11.1755 8.94882 10.3588 10 10.3588C11.0512 10.3588 11.9034 11.1755 11.9034 12.1817C11.9034 13.188 11.0504 14.0047 10 14.0047ZM17.4977 7.08341C17.4796 5.98966 14.6026 4.16748 10 4.16675C5.39663 4.16748 2.51962 5.98966 2.50228 7.08341C2.48495 8.17717 2.51811 9.6005 4.41251 9.36352C6.62867 9.08571 6.49228 8.33685 6.49228 7.26644C6.49228 6.51977 8.29474 6.33966 10 6.33966C11.7053 6.33966 13.507 6.51977 13.5077 7.26644C13.5077 8.33685 13.3713 9.08571 15.5875 9.36352C17.4811 9.6005 17.5151 8.17717 17.4977 7.08341Z"></path>
								</svg>
								<?php esc_html_e( 'Позвонить', 'tolstenko-theme' ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $telegram_url !== '' ) : ?>
							<a class="faq__contact-link default-btn" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener noreferrer">
								<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M10.0001 1.66602C5.40008 1.66602 1.66675 5.39935 1.66675 9.99935C1.66675 14.5993 5.40008 18.3327 10.0001 18.3327C14.6001 18.3327 18.3334 14.5993 18.3334 9.99935C18.3334 5.39935 14.6001 1.66602 10.0001 1.66602ZM13.8667 7.33268C13.7417 8.64935 13.2001 11.8493 12.9251 13.3243C12.8084 13.9493 12.5751 14.1577 12.3584 14.1827C11.8751 14.2243 11.5084 13.866 11.0417 13.5577C10.3084 13.0743 9.89175 12.7743 9.18341 12.3077C8.35841 11.766 8.89175 11.466 9.36675 10.9827C9.49175 10.8577 11.6251 8.91602 11.6667 8.74102C11.6725 8.71451 11.6718 8.68699 11.6645 8.66085C11.6572 8.63471 11.6437 8.61074 11.6251 8.59102C11.5751 8.54935 11.5084 8.56602 11.4501 8.57435C11.3751 8.59102 10.2084 9.36602 7.93341 10.8993C7.60008 11.1243 7.30008 11.241 7.03341 11.2327C6.73341 11.2243 6.16675 11.066 5.74175 10.9243C5.21675 10.7577 4.80841 10.666 4.84175 10.3743C4.85841 10.2243 5.06675 10.0743 5.45841 9.91602C7.89175 8.85768 9.50841 8.15768 10.3167 7.82435C12.6334 6.85768 13.1084 6.69102 13.4251 6.69102C13.4917 6.69102 13.6501 6.70768 13.7501 6.79102C13.8334 6.85768 13.8584 6.94935 13.8667 7.01602C13.8584 7.06602 13.8751 7.21602 13.8667 7.33268Z"></path>
								</svg>
								Telegram
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
