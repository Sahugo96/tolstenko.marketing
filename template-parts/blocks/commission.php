<?php
/**
 * Блок «Вознаграждение» (партнёрская секция commission).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'commission' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_commission_title'] )
	? (string) $block_attrs['block_commission_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_commission_title_tag'] ?? 'h2', 'h2' )
	: 'h2';
$text = isset( $block_attrs['block_commission_text'] ) && trim( (string) $block_attrs['block_commission_text'] ) !== ''
	? (string) $block_attrs['block_commission_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_commission_items'] ) && is_array( $block_attrs['block_commission_items'] )
	? $block_attrs['block_commission_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'ico'        => isset( $it['ico'] ) ? (int) $it['ico'] : 0,
		'title'      => trim( (string) ( $it['title'] ?? '' ) ),
		'summa'      => trim( (string) ( $it['summa'] ?? '' ) ),
		'time'       => trim( (string) ( $it['time'] ?? '' ) ),
		'commission' => trim( (string) ( $it['commission'] ?? '' ) ),
		'remark'     => trim( (string) ( $it['remark'] ?? '' ) ),
	);
	if ( $row['title'] === '' && $row['summa'] === '' && $row['commission'] === '' && ! $row['ico'] ) {
		continue;
	}
	$items[] = $row;
}

if ( $title === '' && $text === '' && empty( $items ) ) {
	return;
}

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
<section class="commission section">
	<div class="container">
		<div class="commission__inner">
			<div class="section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="cases__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="cases__text"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="commission__items">
					<?php foreach ( $items as $item ) : ?>
						<div class="commission__item br-30">
							<div class="commission__item-top">
								<div class="commission__item-svg">
									<?php $render_ico( $item['ico'] ); ?>
								</div>
								<?php if ( $item['title'] !== '' ) : ?>
									<span class="commission__item-title"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
								<?php endif; ?>
							</div>

							<div class="commission__item-list">
								<span class="commission__item-elem paragraph-15-25">
									<?php esc_html_e( 'Клиент заказал', 'tolstenko-theme' ); ?>
									<span class="lead-20-25"><?php echo tolstenko_kses_html( $item['summa'] ); ?></span>
								</span>

								<span class="commission__item-elem paragraph-15-25">
									<?php
									echo ( $item['time'] === 'Разовая' )
										? esc_html__( 'Тип услуги', 'tolstenko-theme' )
										: esc_html__( 'Сроки', 'tolstenko-theme' );
									?>
									<span class="lead-20-25"><?php echo tolstenko_kses_html( $item['time'] ); ?></span>
								</span>
							</div>

							<span class="commission__item-commission paragraph-15-25">
								<span class="line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item['commission'] ); ?></span>
								<?php echo tolstenko_kses_html( $item['remark'] ); ?>
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
