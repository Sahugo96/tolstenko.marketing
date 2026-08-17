<?php
/**
 * Блок «Знакомая ситуация» (familiar): заголовок + сетка карточек (слайдер на мобиле).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'familiar' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_familiar_subtitle'] ) && trim( (string) $block_attrs['block_familiar_subtitle'] ) !== ''
	? (string) $block_attrs['block_familiar_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_familiar_title'] )
	? (string) $block_attrs['block_familiar_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_familiar_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_familiar_text'] ) && trim( (string) $block_attrs['block_familiar_text'] ) !== ''
	? (string) $block_attrs['block_familiar_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_familiar_items'] ) && is_array( $block_attrs['block_familiar_items'] )
	? $block_attrs['block_familiar_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'title' => trim( (string) ( $it['title'] ?? '' ) ),
		'text'  => trim( (string) ( $it['text'] ?? '' ) ),
	);
	if ( $row['title'] === '' && $row['text'] === '' ) {
		continue;
	}
	$items[] = $row;
}

if ( $subtitle === '' && $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="familiar section">
	<div class="container">
		<div class="familiar__inner">
			<?php if ( $subtitle !== '' || $title !== '' || $text !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="familiar__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="familiar__text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="familiar__splide splide" aria-label="<?php esc_attr_e( 'Знакомая ситуация', 'tolstenko-theme' ); ?>">
					<div class="splide__track swiper">
						<div class="familiar__items splide__list swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="familiar__item splide__slide swiper-slide br-20">
									<div class="familiar__item-top">
										<span class="familiar__number lead-20-25" aria-hidden="true"></span>
										<?php if ( $item['title'] !== '' ) : ?>
											<span class="familiar__item-title lead-20-25"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $item['text'] !== '' ) : ?>
										<p class="familiar__item-text paragraph-15-25"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="splide__bottom">
						<div class="swiper-pagination splide__pagination"></div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
