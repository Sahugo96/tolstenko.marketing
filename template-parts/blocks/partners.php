<?php
/**
 * Блок «Партнёры».
 * Разметка/классы — как в tolstenko (BEM с __), слайдер — Swiper.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'partners' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_partners_title'] )
	? (string) $block_attrs['block_partners_title']
	: (string) ( $defaults['title'] ?? '' );
$text = isset( $block_attrs['block_partners_text'] ) && trim( (string) $block_attrs['block_partners_text'] ) !== ''
	? (string) $block_attrs['block_partners_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_partners_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = array();
if ( ! empty( $block_attrs['block_partners_items'] ) && is_array( $block_attrs['block_partners_items'] ) ) {
	$raw_items = $block_attrs['block_partners_items'];
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$raw_items = $defaults['items'];
}

foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$url    = '';
	$alt    = isset( $it['title'] ) ? (string) $it['title'] : '';
	$img_id = 0;
	if ( ! empty( $it['id'] ) ) {
		$img_id = (int) $it['id'];
	} elseif ( ! empty( $it['image'] ) ) {
		$img_id = (int) $it['image'];
	}
	if ( $img_id > 0 ) {
		$url = (string) wp_get_attachment_image_url( $img_id, 'medium' );
		if ( $alt === '' ) {
			$alt = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		}
	} elseif ( ! empty( $it['url'] ) ) {
		$url = (string) $it['url'];
	}
	if ( $url === '' ) {
		continue;
	}
	$items[] = array(
		'url'   => $url,
		'title' => $alt,
	);
}

if ( $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="partners section" aria-label="<?php esc_attr_e( 'Партнёры', 'tolstenko-theme' ); ?>">
	<div class="container">
		<div class="partners__inner br-30">
			<?php if ( $title !== '' || $text !== '' ) : ?>
				<div class="partners__top section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="partners__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="partners__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="partners__splide splide" aria-label="<?php esc_attr_e( 'Слайдер партнёров', 'tolstenko-theme' ); ?>">
					<div class="splide__track swiper">
						<div class="partners__list splide__list swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="partners__list-item splide__slide swiper-slide">
									<div class="partners__list-image">
										<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" decoding="async">
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="splide__bottom">
						<ul class="splide__pagination"></ul>
						<div class="splide__arrows splide__arrows--ltr">
							<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Назад', 'tolstenko-theme' ); ?>">
								<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
								</svg>
							</button>
							<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Вперёд', 'tolstenko-theme' ); ?>">
								<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
								</svg>
							</button>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
