<?php
/**
 * Блок «Рекомендации» (партнёрская секция recomendation).
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'recomendation' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_recomendation_title'] )
	? (string) $block_attrs['block_recomendation_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_recomendation_title_tag'] ?? 'h2', 'h2' )
	: 'h2';
$text = isset( $block_attrs['block_recomendation_text'] ) && trim( (string) $block_attrs['block_recomendation_text'] ) !== ''
	? (string) $block_attrs['block_recomendation_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_recomendation_items'] ) && is_array( $block_attrs['block_recomendation_items'] )
	? $block_attrs['block_recomendation_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$item_title = trim( (string) ( $it['title'] ?? '' ) );
	$item_text  = trim( (string) ( $it['text'] ?? '' ) );
	$ico        = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
	if ( $item_title === '' && $item_text === '' && ! $ico ) {
		continue;
	}
	$items[] = array(
		'title' => $item_title,
		'text'  => $item_text,
		'ico'   => $ico,
	);
}

$list_title = ! empty( $block_attrs['block_recomendation_list_title'] )
	? (string) $block_attrs['block_recomendation_list_title']
	: (string) ( $defaults['list_title'] ?? '' );

$list     = array();
$raw_list = ! empty( $block_attrs['block_recomendation_list'] ) && is_array( $block_attrs['block_recomendation_list'] )
	? $block_attrs['block_recomendation_list']
	: (array) ( $defaults['list'] ?? array() );
foreach ( $raw_list as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$list[] = $t;
	}
}

$btn_text = ! empty( $block_attrs['block_recomendation_btn_text'] )
	? (string) $block_attrs['block_recomendation_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = ! empty( $block_attrs['block_recomendation_btn_url'] )
	? (string) $block_attrs['block_recomendation_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
if ( function_exists( 'tolstenko_url_or_modal' ) ) {
	$btn_url = tolstenko_url_or_modal( $btn_url );
} elseif ( $btn_url === '' || $btn_url === '#modal' ) {
	$btn_url = '#modal';
}
$btn_is_modal = ( $btn_url === '#modal' );

if ( $title === '' && $text === '' && empty( $items ) && empty( $list ) && $btn_text === '' ) {
	return;
}

/**
 * Вывод иконки из вложения (SVG inline или img).
 *
 * @param int $attachment_id ID вложения.
 */
$render_ico = static function ( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id <= 0 ) {
		return;
	}
	if ( tolstenko_render_attachment_inline_svg( $attachment_id ) ) {
		return;
	}
	$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">';
	}
};
?>
<section class="recomendation section">
	<div class="container">
		<div class="recomendation__inner br-30">
			<div class="section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="recomendation__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="recomendation__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>

			<div class="recomendation__wrapper">
				<?php if ( ! empty( $items ) ) : ?>
					<div class="recomendation__items">
						<?php foreach ( $items as $item ) : ?>
							<div class="recomendation__item border-card">
								<div class="recomendation__item-top">
									<?php $render_ico( $item['ico'] ); ?>
									<?php if ( $item['title'] !== '' ) : ?>
										<span class="recomendation__item-title line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
									<?php endif; ?>
								</div>
								<?php if ( $item['text'] !== '' ) : ?>
									<p class="recomendation__item-text paragraph-15-25"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $list_title !== '' || ! empty( $list ) || $btn_text !== '' ) : ?>
					<div class="recomendation__right">
						<?php if ( $list_title !== '' ) : ?>
							<span class="recomendation__right-title"><?php echo tolstenko_kses_html( $list_title ); ?></span>
						<?php endif; ?>

						<?php if ( ! empty( $list ) ) : ?>
							<div class="recomendation__list">
								<?php foreach ( $list as $list_text ) : ?>
									<div class="recomendation__list-item border-card">
										<svg viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M15 28.75C22.5941 28.75 28.75 22.5941 28.75 15C28.75 7.40588 22.5941 1.25 15 1.25C7.40588 1.25 1.25 7.40588 1.25 15C1.25 22.5941 7.40588 28.75 15 28.75ZM15.6463 10.1463C15.8396 9.95313 16.1017 9.84466 16.375 9.84466C16.6483 9.84466 16.9104 9.95313 17.1037 10.1463L21.2288 14.2713C21.4219 14.4646 21.5303 14.7267 21.5303 15C21.5303 15.2733 21.4219 15.5354 21.2288 15.7287L17.1037 19.8537C17.0093 19.9551 16.8955 20.0363 16.769 20.0927C16.6425 20.1491 16.5059 20.1794 16.3675 20.1818C16.229 20.1843 16.0915 20.1588 15.9631 20.1069C15.8346 20.0551 15.718 19.9779 15.6201 19.8799C15.5221 19.782 15.4449 19.6654 15.3931 19.5369C15.3412 19.4085 15.3157 19.271 15.3182 19.1325C15.3206 18.9941 15.3509 18.8575 15.4073 18.731C15.4637 18.6045 15.5449 18.4907 15.6463 18.3963L18.0112 16.0312H9.5C9.2265 16.0312 8.96419 15.9226 8.7708 15.7292C8.5774 15.5358 8.46875 15.2735 8.46875 15C8.46875 14.7265 8.5774 14.4642 8.7708 14.2708C8.96419 14.0774 9.2265 13.9688 9.5 13.9688H18.0112L15.6463 11.6037C15.4531 11.4104 15.3447 11.1483 15.3447 10.875C15.3447 10.6017 15.4531 10.3396 15.6463 10.1463Z" />
										</svg>
										<p class="recomendation__list-text line-13-15"><?php echo tolstenko_kses_html( $list_text ); ?></p>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>

						<?php if ( $btn_text !== '' ) : ?>
							<a
								class="recomendation__btn default-btn line-caps-bold-13-15"
								href="<?php echo esc_url( $btn_url ); ?>">
								<?php echo esc_html( $btn_text ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
