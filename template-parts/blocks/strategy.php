<?php
/**
 * Блок «Стратегия».
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir   = get_template_directory();
$block_attrs = tolstenko_block_attributes();
$defaults = tolstenko_block_defaults( 'strategy' );
$site = tolstenko_site_data();

$title = ! empty( $block_attrs['block_strategy_title'] ) ? (string) $block_attrs['block_strategy_title'] : (string) ( $defaults['title'] ?? '' );
$subtitle = ! empty( $block_attrs['block_strategy_subtitle'] ) ? (string) $block_attrs['block_strategy_subtitle'] : (string) ( $defaults['subtitle'] ?? '' );
$text = isset( $block_attrs['block_strategy_text'] ) && trim( (string) $block_attrs['block_strategy_text'] ) !== ''
	? (string) $block_attrs['block_strategy_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_strategy_title_tag', 'h2' );

$items = array();
$raw_items = ! empty( $block_attrs['block_strategy_items'] ) && is_array( $block_attrs['block_strategy_items'] )
	? $block_attrs['block_strategy_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$items[] = $t;
	}
}

$btn_text = ! empty( $block_attrs['block_strategy_btn_text'] ) ? (string) $block_attrs['block_strategy_btn_text'] : (string) ( $defaults['btn_text'] ?? '' );
$btn_url  = function_exists( 'tolstenko_url_or_modal' )
	? tolstenko_url_or_modal( ! empty( $block_attrs['block_strategy_btn_url'] ) ? (string) $block_attrs['block_strategy_btn_url'] : (string) ( $defaults['btn_url'] ?? '' ) )
	: ( ! empty( $block_attrs['block_strategy_btn_url'] ) ? (string) $block_attrs['block_strategy_btn_url'] : (string) ( $defaults['btn_url'] ?? '' ) );
$file_text = ! empty( $block_attrs['block_strategy_file_text'] ) ? (string) $block_attrs['block_strategy_file_text'] : (string) ( $defaults['file_text'] ?? '' );
$file_url  = ! empty( $block_attrs['block_strategy_file_url'] ) ? (string) $block_attrs['block_strategy_file_url'] : (string) ( $defaults['file_url'] ?? '' );
$contacts_label = ! empty( $block_attrs['block_strategy_contacts_label'] ) ? (string) $block_attrs['block_strategy_contacts_label'] : (string) ( $defaults['contacts_label'] ?? '' );
$telegram_text = ! empty( $block_attrs['block_strategy_telegram_text'] ) ? (string) $block_attrs['block_strategy_telegram_text'] : (string) ( $defaults['telegram_text'] ?? '' );
$telegram_url = ! empty( $block_attrs['block_strategy_telegram_url'] ) ? (string) $block_attrs['block_strategy_telegram_url'] : (string) ( $defaults['telegram_url'] ?? '' );
if ( $telegram_url === '' && ! empty( $site['telegram'] ) ) {
	$telegram_url = (string) $site['telegram'];
}
$phone = ! empty( $block_attrs['block_strategy_phone'] ) ? (string) $block_attrs['block_strategy_phone'] : (string) ( $defaults['phone'] ?? '' );
if ( $phone === '' && ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$phone_href = preg_replace( '/[\s\(\)\-]+/', '', $phone );
if ( $phone_href !== '' && strpos( $phone_href, '+' ) !== 0 ) {
	$phone_href = '+' . ltrim( $phone_href, '+' );
}

$img_id     = ! empty( $block_attrs['block_strategy_image'] ) ? (int) $block_attrs['block_strategy_image'] : (int) ( $defaults['image'] ?? 0 );
$img_mob_id = ! empty( $block_attrs['block_strategy_image_mob'] ) ? (int) $block_attrs['block_strategy_image_mob'] : (int) ( $defaults['image_mob'] ?? 0 );
$img_src     = $img_id ? (string) wp_get_attachment_image_url( $img_id, 'large' ) : '';
$img_mob_src = $img_mob_id ? (string) wp_get_attachment_image_url( $img_mob_id, 'medium' ) : $img_src;
$img_alt     = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';
$img_mob_alt = $img_mob_id ? (string) get_post_meta( $img_mob_id, '_wp_attachment_image_alt', true ) : $img_alt;

$tg_icon    = $theme_dir . '/assets/img/telegram-icon.svg';
$phone_icon = $theme_dir . '/assets/img/phone-icon.svg';

if ( $title === '' && empty( $items ) && $text === '' ) {
	return;
}
?>
<section class="strategy section" aria-label="<?php esc_attr_e( 'Стратегия', 'tolstenko-theme' ); ?>">
	<div class="container">
		<?php if ( $title !== '' ) : ?>
			<div class="section-top">
				<<?php echo esc_attr( $title_tag ); ?> class="section-title strategy__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="strategy__scroll">
				<div class="strategy__list">
					<?php foreach ( $items as $item_text ) : ?>
						<div class="strategy__list-item line-13-15">
							<svg viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M12.4997 22.9166C13.8679 22.9183 15.2229 22.6496 16.4869 22.126C17.751 21.6024 18.8991 20.8342 19.8653 19.8655C20.834 18.8993 21.6022 17.7512 22.1258 16.4872C22.6494 15.2231 22.918 13.8681 22.9163 12.4999C22.918 11.1317 22.6494 9.77671 22.1258 8.51268C21.6022 7.24865 20.834 6.10054 19.8653 5.1343C18.8991 4.16564 17.751 3.39744 16.4869 2.87384C15.2229 2.35024 13.8679 2.08157 12.4997 2.08326C11.1315 2.08157 9.77647 2.35024 8.51244 2.87384C7.24841 3.39744 6.10029 4.16564 5.13406 5.1343C4.16539 6.10054 3.39719 7.24865 2.8736 8.51268C2.35 9.77671 2.08133 11.1317 2.08302 12.4999C2.08133 13.8681 2.35 15.2231 2.8736 16.4872C3.39719 17.7512 4.16539 18.8993 5.13406 19.8655C6.10029 20.8342 7.24841 21.6024 8.51244 22.126C9.77647 22.6496 11.1315 22.9183 12.4997 22.9166Z" stroke="#FACA64" stroke-width="2" stroke-linejoin="round" />
								<path d="M8.33301 12.5L11.458 15.625L17.708 9.375" stroke="#FACA64" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
							</svg>
							<p class="strategy__list-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="strategy__inner">
			<div class="strategy__left">
				<?php if ( $subtitle !== '' || $text !== '' ) : ?>
					<div class="strategy__info">
						<?php if ( $subtitle !== '' ) : ?>
							<h3 class="strategy__info-title"><?php echo tolstenko_kses_html( $subtitle ); ?></h3>
						<?php endif; ?>
						<?php if ( $text !== '' ) : ?>
							<p class="strategy__info-text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $btn_text !== '' || ( $file_text !== '' && $file_url !== '' ) ) : ?>
					<div class="strategy__links">
						<?php if ( $btn_text !== '' ) : ?>
							<a class="strategy__link default-btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
						<?php endif; ?>
						<?php if ( $file_text !== '' && $file_url !== '' ) : ?>
							<a class="strategy__link strategy__link--shablon default-btn" href="<?php echo esc_url( $file_url ); ?>" download>
								<?php echo esc_html( $file_text ); ?>
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M12 4L12 13M16 10L12.7071 13.2929C12.3166 13.6834 11.6834 13.6834 11.2929 13.2929L8 10M20 20L4 20" stroke-width="2" stroke-linecap="round" />
								</svg>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $telegram_url !== '' || $phone !== '' || $contacts_label !== '' ) : ?>
					<div class="strategy__btns br-30">
						<?php if ( $telegram_url !== '' ) : ?>
							<a class="strategy__btn default-btn line-caps-bold-13-15" href="<?php echo esc_url( $telegram_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php
								if ( is_readable( $tg_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $tg_icon );
								}
								?>
								<span><?php echo esc_html( $telegram_text !== '' ? $telegram_text : __( 'Позвонить в TG', 'tolstenko-theme' ) ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $phone !== '' ) : ?>
							<a class="strategy__btn default-btn line-caps-bold-13-15" href="tel:<?php echo esc_attr( $phone_href ); ?>">
								<?php
								if ( is_readable( $phone_icon ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
									echo file_get_contents( $phone_icon );
								}
								?>
								<span><?php echo esc_html( $phone ); ?></span>
							</a>
						<?php endif; ?>
						<?php if ( $contacts_label !== '' ) : ?>
							<div class="strategy__btns-text"><?php echo tolstenko_kses_html( $contacts_label ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $img_src !== '' || $img_mob_src !== '' ) : ?>
				<div class="strategy__image">
					<?php if ( $img_mob_src !== '' ) : ?>
						<img class="mob" src="<?php echo esc_url( $img_mob_src ); ?>" alt="<?php echo esc_attr( $img_mob_alt ); ?>" loading="lazy" decoding="async">
					<?php endif; ?>
					<?php if ( $img_src !== '' ) : ?>
						<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
