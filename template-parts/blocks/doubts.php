<?php
/**
 * Блок «Сомнения» (doubts): заголовок + сетка карточек (слайдер на мобиле).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'doubts' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_doubts_subtitle'] ) && trim( (string) $block_attrs['block_doubts_subtitle'] ) !== ''
	? (string) $block_attrs['block_doubts_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_doubts_title'] )
	? (string) $block_attrs['block_doubts_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_doubts_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_doubts_items'] ) && is_array( $block_attrs['block_doubts_items'] )
	? $block_attrs['block_doubts_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'badge' => trim( (string) ( $it['badge'] ?? '' ) ),
		'title' => trim( (string) ( $it['title'] ?? '' ) ),
		'text'  => trim( (string) ( $it['text'] ?? '' ) ),
	);
	if ( $row['badge'] === '' && $row['title'] === '' && $row['text'] === '' ) {
		continue;
	}
	$items[] = $row;
}

if ( $subtitle === '' && $title === '' && empty( $items ) ) {
	return;
}
?>
<section class="doubts section">
	<div class="container">
		<div class="doubts__inner">
			<?php if ( $subtitle !== '' || $title !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="doubts__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="doubts__splide splide" aria-label="<?php esc_attr_e( 'Возражения', 'tolstenko-theme' ); ?>">
					<div class="splide__track swiper">
						<div class="doubts__items splide__list swiper-wrapper">
							<?php foreach ( $items as $item ) : ?>
								<div class="doubts__item splide__slide swiper-slide br-20">
									<div class="doubts__item-top">
										<?php if ( $item['badge'] !== '' ) : ?>
											<span class="doubts__badge line-caps-bold-13-15"><?php echo tolstenko_kses_html( $item['badge'] ); ?></span>
										<?php endif; ?>
										<?php if ( $item['title'] !== '' ) : ?>
											<span class="doubts__item-title lead-20-25"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
										<?php endif; ?>
									</div>
									<?php if ( $item['text'] !== '' ) : ?>
										<p class="doubts__item-text paragraph-15-25"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
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
