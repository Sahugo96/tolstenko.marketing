<?php
/**
 * Блок «Наш подход» (usp): заголовок + сетка принципов.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'usp' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_usp_subtitle'] ) && trim( (string) $block_attrs['block_usp_subtitle'] ) !== ''
	? (string) $block_attrs['block_usp_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_usp_title'] )
	? (string) $block_attrs['block_usp_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_usp_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_usp_text'] ) && trim( (string) $block_attrs['block_usp_text'] ) !== ''
	? (string) $block_attrs['block_usp_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_usp_items'] ) && is_array( $block_attrs['block_usp_items'] )
	? $block_attrs['block_usp_items']
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
<section class="section" id="usp">
	<div class="container">
		<div class="wrap">
			<?php if ( $subtitle !== '' || $title !== '' || $text !== '' ) : ?>
				<div class="h-lead center">
					<?php if ( $subtitle !== '' ) : ?>
						<span class="eyebrow"><?php echo tolstenko_kses_html( $subtitle ); ?></span>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?>><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="section-sub"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="usp-grid">
					<?php foreach ( $items as $item ) : ?>
						<div class="usp">
							<div class="head">
								<span class="n" aria-hidden="true"></span>
								<?php if ( $item['title'] !== '' ) : ?>
									<b><?php echo tolstenko_kses_html( $item['title'] ); ?></b>
								<?php endif; ?>
							</div>
							<?php if ( $item['text'] !== '' ) : ?>
								<p><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
