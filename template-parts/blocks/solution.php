<?php
/**
 * Блок «Решение» (.solution): заголовок + два ряда карточек (Swiper).
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'solution' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_solution_title'] ) ) {
	$title = (string) $block_attrs['block_solution_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_solution_text'] ) && trim( (string) $block_attrs['block_solution_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_solution_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_solution_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$normalize_items = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $it ) {
		$text = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
		if ( $text !== '' ) {
			$out[] = $text;
		}
	}
	return $out;
};

$items = array();
if ( ! empty( $block_attrs['block_solution_items'] ) && is_array( $block_attrs['block_solution_items'] ) ) {
	$items = $normalize_items( $block_attrs['block_solution_items'] );
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$items = $normalize_items( $defaults['items'] );
}

$items_second = array();
if ( ! empty( $block_attrs['block_solution_items_second'] ) && is_array( $block_attrs['block_solution_items_second'] ) ) {
	$items_second = $normalize_items( $block_attrs['block_solution_items_second'] );
} elseif ( ! empty( $defaults['items_second'] ) && is_array( $defaults['items_second'] ) ) {
	$items_second = $normalize_items( $defaults['items_second'] );
}

if ( $title === '' && $text === '' && empty( $items ) && empty( $items_second ) ) {
	return;
}

$list_svg = '<svg viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M15 28.75C22.5941 28.75 28.75 22.5941 28.75 15C28.75 7.40588 22.5941 1.25 15 1.25C7.40588 1.25 1.25 7.40588 1.25 15C1.25 22.5941 7.40588 28.75 15 28.75ZM15.6463 10.1463C15.8396 9.95313 16.1017 9.84466 16.375 9.84466C16.6483 9.84466 16.9104 9.95313 17.1037 10.1463L21.2288 14.2713C21.4219 14.4646 21.5303 14.7267 21.5303 15C21.5303 15.2733 21.4219 15.5354 21.2288 15.7287L17.1037 19.8538C17.0093 19.9551 16.8955 20.0363 16.769 20.0927C16.6425 20.1491 16.5059 20.1794 16.3675 20.1818C16.229 20.1843 16.0915 20.1588 15.9631 20.1069C15.8346 20.0551 15.718 19.9779 15.6201 19.8799C15.5221 19.782 15.4449 19.6654 15.3931 19.5369C15.3412 19.4085 15.3157 19.271 15.3182 19.1325C15.3206 18.9941 15.3509 18.8575 15.4073 18.731C15.4637 18.6045 15.5449 18.4907 15.6463 18.3962L18.0112 16.0312H9.5C9.2265 16.0312 8.96419 15.9226 8.7708 15.7292C8.5774 15.5358 8.46875 15.2735 8.46875 15C8.46875 14.7265 8.5774 14.4642 8.7708 14.2708C8.96419 14.0774 9.2265 13.9688 9.5 13.9688H18.0112L15.6463 11.6037C15.4531 11.4104 15.3447 10.875C15.3447 10.6017 15.4531 10.3396 15.6463 10.1463Z" fill="#FACA64" /></svg>';

$render_items = static function ( $rows ) use ( $list_svg ) {
	foreach ( $rows as $text ) {
		?>
		<div class="solution__list-item swiper-slide">
			<?php echo $list_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
			<p class="solution__list-text line-13-15"><?php echo tolstenko_kses_html( $text ); ?></p>
		</div>
		<?php
	}
};

$has_second = ! empty( $items_second );
?>
<section class="solution section">
	<div class="container">
		<?php if ( $title !== '' || $text !== '' ) : ?>
			<div class="solution__top section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="section-title solution__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="solution__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="solution__splide splide swiper">
				<div class="solution__list swiper-wrapper">
					<?php $render_items( $items ); ?>
				</div>
				<?php if ( ! $has_second ) : ?>
					<div class="splide__bottom">
						<div class="swiper-pagination splide__pagination"></div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $has_second ) : ?>
			<div class="solution__splide-second splide swiper">
				<div class="solution__list swiper-wrapper">
					<?php $render_items( $items_second ); ?>
				</div>
				<div class="splide__bottom">
					<div class="swiper-pagination splide__pagination"></div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
