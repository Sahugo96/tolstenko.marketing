<?php
/**
 * Блок «Акции, бонусы, подарки» (слайдер): карточки из репитера, ссылка — выбранная запись CPT actions.
 * Разметка/классы — как в tolstenko pages/sections/actions.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'actions' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_actions_title'] ) ) {
	$title = (string) $block_attrs['block_actions_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_actions_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$raw_items = array();
if ( ! empty( $block_attrs['block_actions_items'] ) && is_array( $block_attrs['block_actions_items'] ) ) {
	$raw_items = $block_attrs['block_actions_items'];
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$raw_items = $defaults['items'];
}

$items = array();
foreach ( $raw_items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$type  = trim( (string) ( $row['type'] ?? '' ) );
	$t     = trim( (string) ( $row['title'] ?? '' ) );
	$text  = trim( (string) ( $row['text'] ?? '' ) );
	$aid   = isset( $row['action_id'] ) ? (int) $row['action_id'] : 0;
	if ( $type === '' && $t === '' && $text === '' ) {
		continue;
	}
	$url = '';
	if ( $aid > 0 && get_post_type( $aid ) === 'actions' && get_post_status( $aid ) === 'publish' ) {
		$url = (string) get_permalink( $aid );
	}
	$items[] = array(
		'type'  => $type,
		'title' => $t,
		'text'  => $text,
		'url'   => $url,
	);
	if ( count( $items ) >= 4 ) {
		break;
	}
}

if ( $title === '' && empty( $items ) ) {
	return;
}
?>
<section class="actions section" aria-label="<?php esc_attr_e( 'Акции, бонусы, подарки', 'tolstenko-theme' ); ?>">
	<div class="container">
		<?php if ( $title !== '' ) : ?>
			<div class="section-top">
				<<?php echo esc_attr( $title_tag ); ?> class="section-title actions__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="actions__splide splide" aria-label="<?php esc_attr_e( 'Слайдер акций', 'tolstenko-theme' ); ?>">
				<div class="splide__track swiper">
					<div class="actions__list splide__list swiper-wrapper">
						<?php foreach ( $items as $item ) : ?>
							<?php if ( $item['url'] !== '' ) : ?>
								<a class="actions__list-item splide__slide swiper-slide br-30" href="<?php echo esc_url( $item['url'] ); ?>">
							<?php else : ?>
								<div class="actions__list-item splide__slide swiper-slide br-30">
							<?php endif; ?>
									<?php if ( $item['type'] !== '' ) : ?>
										<p class="actions__list-type"><?php echo esc_html( $item['type'] ); ?></p>
									<?php endif; ?>
									<?php if ( $item['title'] !== '' ) : ?>
										<h3 class="actions__list-title h2"><?php echo tolstenko_kses_html( $item['title'] ); ?></h3>
									<?php endif; ?>
									<?php if ( $item['text'] !== '' ) : ?>
										<p class="actions__list-text line-13-15"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
									<?php endif; ?>
							<?php if ( $item['url'] !== '' ) : ?>
								</a>
							<?php else : ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="splide__bottom">
					<ul class="splide__pagination"></ul>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
