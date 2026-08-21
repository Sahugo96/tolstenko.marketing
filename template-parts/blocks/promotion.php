<?php
/**
 * Блок «Продвижение» (promotion): что вы получаете от продвижения.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'promotion' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$subtitle = isset( $block_attrs['block_promotion_subtitle'] ) && trim( (string) $block_attrs['block_promotion_subtitle'] ) !== ''
	? (string) $block_attrs['block_promotion_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$title = ! empty( $block_attrs['block_promotion_title'] )
	? (string) $block_attrs['block_promotion_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_promotion_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$text = isset( $block_attrs['block_promotion_text'] ) && trim( (string) $block_attrs['block_promotion_text'] ) !== ''
	? (string) $block_attrs['block_promotion_text']
	: (string) ( $defaults['text'] ?? '' );

$items     = array();
$raw_items = ! empty( $block_attrs['block_promotion_items'] ) && is_array( $block_attrs['block_promotion_items'] )
	? $block_attrs['block_promotion_items']
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

$micro_title = isset( $block_attrs['block_promotion_micro_title'] ) && trim( (string) $block_attrs['block_promotion_micro_title'] ) !== ''
	? (string) $block_attrs['block_promotion_micro_title']
	: (string) ( $defaults['micro_title'] ?? '' );

$list     = array();
$raw_list = ! empty( $block_attrs['block_promotion_list'] ) && is_array( $block_attrs['block_promotion_list'] )
	? $block_attrs['block_promotion_list']
	: (array) ( $defaults['list'] ?? array() );
foreach ( $raw_list as $li ) {
	if ( is_array( $li ) ) {
		$li = trim( (string) ( $li['text'] ?? '' ) );
	} else {
		$li = trim( (string) $li );
	}
	if ( $li !== '' ) {
		$list[] = $li;
	}
}

if ( $subtitle === '' && $title === '' && $text === '' && empty( $items ) && $micro_title === '' && empty( $list ) ) {
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
<section class="promotion section">
	<div class="container">
		<div class="promotion__inner">
			<?php if ( $subtitle !== '' || $title !== '' ) : ?>
				<div class="section-top">
					<?php if ( $subtitle !== '' ) : ?>
						<p class="section-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></p>
					<?php endif; ?>
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="promotion__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $text !== '' || ! empty( $items ) || $micro_title !== '' || ! empty( $list ) ) : ?>
				<div class="promotion__wrap">
					<div class="promotion__left">
						<?php if ( $text !== '' ) : ?>
							<p class="promotion__text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $items ) ) : ?>
							<div class="promotion__items">
								<?php foreach ( $items as $item ) : ?>
									<div class="promotion__item br-20">
										<?php if ( $has_item_ico( $item ) ) : ?>
											<div class="promotion__item-ico">
												<?php $render_item_ico( $item ); ?>
											</div>
										<?php endif; ?>
										<div class="promotion__item-body">
											<?php if ( $item['title'] !== '' ) : ?>
												<span class="promotion__item-title"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
											<?php endif; ?>
											<?php if ( $item['text'] !== '' ) : ?>
												<span class="promotion__item-text"><?php echo tolstenko_kses_html( $item['text'] ); ?></span>
											<?php endif; ?>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $micro_title !== '' || ! empty( $list ) ) : ?>
						<div class="promotion__right br-20">
							<?php if ( $micro_title !== '' ) : ?>
								<h3 class="promotion__micro-title"><?php echo tolstenko_kses_html( $micro_title ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $list ) ) : ?>
								<div class="promotion__list">
									<?php foreach ( $list as $index => $li ) : ?>
										<div class="promotion__list-item">
											<span class="promotion__list-dot"></span>
											<span class="promotion__list-text"><?php echo tolstenko_kses_html( $li ); ?></span>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>