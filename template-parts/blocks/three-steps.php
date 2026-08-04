<?php
/**
 * Блок «Три шага» (разметка как в Tolstenko three-steps).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'three_steps' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_three_steps_title'] ) ? (string) $block_attrs['block_three_steps_title'] : (string) ( $defaults['title'] ?? '' );
$text  = isset( $block_attrs['block_three_steps_text'] ) && trim( (string) $block_attrs['block_three_steps_text'] ) !== ''
	? (string) $block_attrs['block_three_steps_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_three_steps_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_three_steps_items'] ) && is_array( $block_attrs['block_three_steps_items'] )
	? $block_attrs['block_three_steps_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$items[] = $t;
	}
}

if ( $title === '' && empty( $items ) ) {
	return;
}
?>
<section class="three-steps section">
	<div class="container">
		<div class="three-steps__inner br-30">
			<div class="three-steps__top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="three-steps__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="three-steps__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="three-steps__list">
					<?php foreach ( $items as $item_text ) : ?>
						<div class="three-steps__list-item br-20">
							<p class="three-steps__list-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
