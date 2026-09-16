<?php
/**
 * Блок «А это не слишком долго?» (too-long): сроки SEO + карточки этапов.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'too_long' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_too_long_subtitle'] ) && trim( (string) $block_attrs['block_too_long_subtitle'] ) !== ''
	? (string) $block_attrs['block_too_long_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_too_long_title'] )
	? (string) $block_attrs['block_too_long_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_too_long_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_too_long_text'] ) && trim( (string) $block_attrs['block_too_long_text'] ) !== ''
	? (string) $block_attrs['block_too_long_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_too_long_items'] ) && is_array( $block_attrs['block_too_long_items'] )
	? $block_attrs['block_too_long_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'label' => trim( (string) ( $it['label'] ?? '' ) ),
		'title' => trim( (string) ( $it['title'] ?? '' ) ),
		'text'  => trim( (string) ( $it['text'] ?? '' ) ),
	);
	if ( $row['label'] === '' && $row['title'] === '' && $row['text'] === '' ) {
		continue;
	}
	$items[] = $row;
}

if ( $subtitle === '' && $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="too-long section">
	<div class="container">
		<div class="too-long__inner">
			<?php if ( $subtitle !== '' || $title !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="too-long__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="too-long__splide splide" aria-label="<?php esc_attr_e( 'А это не слишком долго?', 'tolstenko-theme' ); ?>">
					<div class="splide__track swiper">
						<div class="too-long__items splide__list swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="too-long__item splide__slide swiper-slide br-20">
									<?php if ( $item['label'] !== '' ) : ?>
										<span class="too-long__item-label line-caps-bold-13-15"><?php echo tolstenko_kses_html( $item['label'] ); ?></span>
									<?php endif; ?>
									<?php if ( $item['title'] !== '' ) : ?>
										<span class="too-long__item-title lead-20-25"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
									<?php endif; ?>
									<?php if ( $item['text'] !== '' ) : ?>
										<p class="too-long__item-text paragraph-15-25"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
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

			<?php if ( $text !== '' ) : ?>
				<p class="too-long__text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
