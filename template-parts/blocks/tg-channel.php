<?php
/**
 * Блок «Telegram-канал».
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
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'tg_channel' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}
$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_tg_channel_title'] ) ? (string) $block_attrs['block_tg_channel_title'] : (string) ( $defaults['title'] ?? '' );
$text  = isset( $block_attrs['block_tg_channel_text'] ) && trim( (string) $block_attrs['block_tg_channel_text'] ) !== ''
	? (string) $block_attrs['block_tg_channel_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_tg_channel_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_tg_channel_items'] ) && is_array( $block_attrs['block_tg_channel_items'] )
	? $block_attrs['block_tg_channel_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$items[] = $t;
	}
}

$btn_text = ! empty( $block_attrs['block_tg_channel_btn_text'] ) ? (string) $block_attrs['block_tg_channel_btn_text'] : (string) ( $defaults['btn_text'] ?? '' );
$btn_url  = ! empty( $block_attrs['block_tg_channel_btn_url'] ) ? (string) $block_attrs['block_tg_channel_btn_url'] : (string) ( $defaults['btn_url'] ?? '' );
if ( $btn_url === '' && ! empty( $site['telegram'] ) ) {
	$btn_url = (string) $site['telegram'];
}

$img_id  = ! empty( $block_attrs['block_tg_channel_image'] ) ? (int) $block_attrs['block_tg_channel_image'] : (int) ( $defaults['image'] ?? 0 );
$img_src = $img_id ? (string) wp_get_attachment_image_url( $img_id, 'large' ) : '';
$img_alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

$tg_icon = $theme_dir . '/assets/img/telegram-icon.svg';

if ( $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="tg-channel section" aria-label="<?php esc_attr_e( 'Telegram-канал', 'tolstenko-theme' ); ?>">
	<div class="container">
		<div class="tg-channel__inner br-30">
			<div class="tg-channel__circle" aria-hidden="true"></div>

			<?php if ( $img_src !== '' ) : ?>
				<div class="tg-channel__img">
					<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>

			<div class="tg-channel__wrapper">
				<div class="tg-channel__top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="tg-channel__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="tg-channel__text lead-20-25"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $items ) ) : ?>
					<div class="tg-channel__list">
						<?php foreach ( $items as $item_text ) : ?>
							<div class="tg-channel__item line-13-15">
								<svg class="tg-channel__item-svg" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M12.5 22.9163C13.8682 22.918 15.2233 22.6494 16.4873 22.1258C17.7513 21.6022 18.8994 20.834 19.8657 19.8653C20.8343 18.8991 21.6025 17.751 22.1261 16.4869C22.6497 15.2229 22.9184 13.8679 22.9167 12.4997C22.9184 11.1315 22.6497 9.77647 22.1261 8.51244C21.6025 7.24841 20.8343 6.10029 19.8657 5.13406C18.8994 4.16539 17.7513 3.39719 16.4873 2.8736C15.2233 2.35 13.8682 2.08133 12.5 2.08302C11.1319 2.08133 9.77683 2.35 8.5128 2.8736C7.24877 3.39719 6.10066 4.16539 5.13442 5.13406C4.16576 6.10029 3.39756 7.24841 2.87396 8.51244C2.35037 9.77647 2.08169 11.1315 2.08338 12.4997C2.08169 13.8679 2.35037 15.2229 2.87396 16.4869C3.39756 17.751 4.16576 18.8991 5.13442 19.8653C6.10066 20.834 7.24877 21.6022 8.5128 22.1258C9.77683 22.6494 11.1319 22.918 12.5 22.9163Z" fill="#FACA64" />
									<path d="M8.33337 12.5L11.4584 15.625L17.7084 9.375" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
								<?php echo tolstenko_kses_html( $item_text ); ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $btn_text !== '' && $btn_url !== '' ) : ?>
					<a class="tg-channel__btn default-btn default-btn--huge default-btn--tg line-caps-bold-16-15" href="<?php echo esc_url( $btn_url ); ?>" target="_blank" rel="noopener noreferrer">
						<?php echo esc_html( $btn_text ); ?>
						<?php
						if ( is_readable( $tg_icon ) ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
							echo file_get_contents( $tg_icon );
						}
						?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
