<?php
/**
 * Блок «Результат» (result): гарантии в договоре.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'result' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_result_subtitle'] ) && trim( (string) $block_attrs['block_result_subtitle'] ) !== ''
	? (string) $block_attrs['block_result_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_result_title'] )
	? (string) $block_attrs['block_result_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_result_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_result_text'] ) && trim( (string) $block_attrs['block_result_text'] ) !== ''
	? (string) $block_attrs['block_result_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_result_items'] ) && is_array( $block_attrs['block_result_items'] )
	? $block_attrs['block_result_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'ico'   => isset( $it['ico'] ) ? (int) $it['ico'] : 0,
		'svg'   => trim( (string) ( $it['svg'] ?? '' ) ),
		'title' => trim( (string) ( $it['title'] ?? '' ) ),
		'text'  => trim( (string) ( $it['text'] ?? '' ) ),
	);
	if ( ! $row['ico'] && $row['svg'] === '' && $row['title'] === '' && $row['text'] === '' ) {
		continue;
	}
	$items[] = $row;
}

if ( $subtitle === '' && $title === '' && $text === '' && empty( $items ) ) {
	return;
}

$render_attachment_ico = static function ( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id <= 0 ) {
		return false;
	}
	$path = get_attached_file( $attachment_id );
	if ( $path && is_readable( $path ) && preg_match( '/\.svg$/i', $path ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg = file_get_contents( $path );
		if ( $svg ) {
			echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return true;
		}
	}
	$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">';
		return true;
	}
	return false;
};

$render_item_ico = static function ( $item ) use ( $render_attachment_ico ) {
	$svg = function_exists( 'tolstenko_sanitize_inline_svg' )
		? tolstenko_sanitize_inline_svg( $item['svg'] ?? '' )
		: trim( (string) ( $item['svg'] ?? '' ) );
	if ( $svg !== '' ) {
		echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return true;
	}
	return $render_attachment_ico( (int) ( $item['ico'] ?? 0 ) );
};

$has_item_ico = static function ( $item ) use ( $render_item_ico ) {
	if ( trim( (string) ( $item['svg'] ?? '' ) ) !== '' ) {
		return true;
	}
	return (int) ( $item['ico'] ?? 0 ) > 0;
};
?>
<section class="result section">
	<div class="container">
		<div class="result__inner">
			<?php if ( $subtitle !== '' || $title !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="result__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="result__items">
					<?php foreach ( $items as $item ) : ?>
						<div class="result__item br-20">
							<div class="result__item-top">
								<?php if ( $has_item_ico( $item ) ) : ?>
									<div class="result__item-ico">
										<?php $render_item_ico( $item ); ?>
									</div>
								<?php endif; ?>
								<?php if ( $item['title'] !== '' ) : ?>
									<span class="result__item-title lead-20-25"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
								<?php endif; ?>
							</div>
							<?php if ( $item['text'] !== '' ) : ?>
								<p class="result__item-text paragraph-15-25"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $text !== '' ) : ?>
				<p class="result__text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>