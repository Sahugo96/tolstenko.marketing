<?php
/**
 * Блок «Команда» (разметка как в Tolstenko team + Swiper вместо Splide).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'team_cards' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_team_cards_title'] ) ? (string) $block_attrs['block_team_cards_title'] : (string) ( $defaults['title'] ?? '' );
$text  = isset( $block_attrs['block_team_cards_text'] ) && trim( (string) $block_attrs['block_team_cards_text'] ) !== ''
	? (string) $block_attrs['block_team_cards_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_team_cards_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_team_cards_items'] ) && is_array( $block_attrs['block_team_cards_items'] )
	? $block_attrs['block_team_cards_items']
	: (array) ( $defaults['items'] ?? array() );

foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$name   = trim( (string) ( $it['name'] ?? '' ) );
	$img_id = 0;
	if ( ! empty( $it['id'] ) ) {
		$img_id = (int) $it['id'];
	} elseif ( ! empty( $it['image'] ) ) {
		$img_id = (int) $it['image'];
	}
	$url = $img_id ? (string) wp_get_attachment_image_url( $img_id, 'medium' ) : (string) ( $it['url'] ?? '' );
	if ( $name === '' && $url === '' ) {
		continue;
	}
	$btn_url = (string) ( $it['btn_url'] ?? '' );
	$items[] = array(
		'name'     => $name,
		'position' => (string) ( $it['position'] ?? '' ),
		'exp'      => (string) ( $it['exp'] ?? '' ),
		'text'     => (string) ( $it['text'] ?? '' ),
		'btn_text' => (string) ( $it['btn_text'] ?? '' ),
		'btn_url'  => $btn_url,
		'image'    => $url,
	);
}

if ( $title === '' && empty( $items ) ) {
	return;
}
?>
<section class="team section">
	<div class="container">
		<div class="team__inner">
			<?php if ( $title !== '' || $text !== '' ) : ?>
				<div class="team__top section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="team__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="team__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="team__splide splide" aria-label="<?php esc_attr_e( 'Команда', 'tolstenko-theme' ); ?>">
					<div class="splide__track swiper">
						<div class="team__list splide__list swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="team__item splide__slide swiper-slide border-card br-20">
									<?php if ( $item['image'] !== '' ) : ?>
										<div class="team__item-img">
											<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
										</div>
									<?php endif; ?>

									<div class="team__item-info">
										<?php if ( $item['name'] !== '' ) : ?>
											<p class="team__item-name line-caps-bold-15-15"><?php echo esc_html( $item['name'] ); ?></p>
										<?php endif; ?>

										<?php if ( $item['position'] !== '' || $item['exp'] !== '' ) : ?>
											<p class="team__item-position footnote-12-10">
												<?php echo esc_html( $item['position'] ); ?>
												<?php if ( $item['exp'] !== '' ) : ?>
													<span><?php echo esc_html( $item['exp'] ); ?></span>
												<?php endif; ?>
											</p>
										<?php endif; ?>

										<?php if ( $item['text'] !== '' ) : ?>
											<p class="team__item-text"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
										<?php endif; ?>
									</div>

									<?php if ( $item['btn_text'] !== '' ) : ?>
										<?php $item_btn_url = tolstenko_url_or_modal( $item['btn_url'] ); ?>
										<a class="team__item-btn default-btn" href="<?php echo esc_url( $item_btn_url ); ?>"><?php echo esc_html( $item['btn_text'] ); ?></a>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="splide__bottom">
						<div class="swiper-pagination splide__pagination"></div>

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
