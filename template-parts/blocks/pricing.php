<?php
/**
 * Блок «Расценки» (pricing): тарифы на продвижение.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'pricing' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_pricing_subtitle'] ) && trim( (string) $block_attrs['block_pricing_subtitle'] ) !== ''
	? (string) $block_attrs['block_pricing_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_pricing_title'] )
	? (string) $block_attrs['block_pricing_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_pricing_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_pricing_text'] ) && trim( (string) $block_attrs['block_pricing_text'] ) !== ''
	? (string) $block_attrs['block_pricing_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_pricing_items'] ) && is_array( $block_attrs['block_pricing_items'] )
	? $block_attrs['block_pricing_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$list = array();
	if ( isset( $it['list'] ) && is_array( $it['list'] ) ) {
		foreach ( $it['list'] as $li ) {
			$li = is_array( $li ) ? (string) ( $li['text'] ?? '' ) : (string) $li;
			$li = trim( $li );
			if ( $li !== '' ) {
				$list[] = $li;
			}
		}
	}
	$row = array(
		'name'     => trim( (string) ( $it['name'] ?? '' ) ),
		'for'      => trim( (string) ( $it['for'] ?? '' ) ),
		'price'    => trim( (string) ( $it['price'] ?? '' ) ),
		'note'     => trim( (string) ( $it['note'] ?? '' ) ),
		'badge'    => trim( (string) ( $it['badge'] ?? '' ) ),
		'btn_text' => trim( (string) ( $it['btn_text'] ?? '' ) ),
		'btn_url'  => trim( (string) ( $it['btn_url'] ?? '' ) ),
		'list'     => $list,
	);
	if ( $row['name'] === '' && $row['for'] === '' && $row['price'] === '' && $row['note'] === '' && $row['badge'] === '' && $row['btn_text'] === '' && empty( $row['list'] ) ) {
		continue;
	}
	$items[] = $row;
}

if ( $subtitle === '' && $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="pricing section">
	<div class="container">
		<div class="pricing__inner">
			<?php if ( $subtitle !== '' || $title !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="pricing__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="pricing__text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="pricing__items">
					<?php foreach ( $items as $item ) : ?>
						<div class="pricing__item<?php echo $item['badge'] !== '' ? ' pricing__item--featured' : ''; ?>">
							<?php if ( $item['badge'] !== '' ) : ?>
								<span class="pricing__item-badge"><?php echo tolstenko_kses_html( $item['badge'] ); ?></span>
							<?php endif; ?>

							<?php if ( $item['name'] !== '' ) : ?>
								<div class="pricing__item-name"><?php echo tolstenko_kses_html( $item['name'] ); ?></div>
							<?php endif; ?>

							<?php if ( $item['for'] !== '' ) : ?>
								<div class="pricing__item-for"><?php echo tolstenko_kses_html( $item['for'] ); ?></div>
							<?php endif; ?>

							<?php if ( $item['price'] !== '' ) : ?>
								<div class="pricing__item-price"><?php echo tolstenko_kses_html( $item['price'] ); ?></div>
							<?php endif; ?>

							<?php if ( $item['note'] !== '' ) : ?>
								<div class="pricing__item-note"><?php echo tolstenko_kses_html( $item['note'] ); ?></div>
							<?php endif; ?>

							<?php if ( $item['btn_text'] !== '' ) : ?>
								<a class="pricing__item-btn default-btn<?php echo $item['badge'] !== '' ? '' : ' default-btn--transparent'; ?>" href="<?php echo $item['btn_url'] !== '' ? esc_url( $item['btn_url'] ) : '#modal'; ?>"><?php echo esc_html( $item['btn_text'] ); ?></a>
							<?php endif; ?>

							<?php if ( ! empty( $item['list'] ) ) : ?>
								<ul class="pricing__item-list">
									<?php foreach ( $item['list'] as $li ) : ?>
										<li>
											<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
											<span><?php echo tolstenko_kses_html( $li ); ?></span>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>