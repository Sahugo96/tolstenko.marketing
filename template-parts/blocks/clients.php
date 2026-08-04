<?php
/**
 * Блок «Клиенты» (пресс-портрет).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'clients' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_clients_title'] )
	? (string) $block_attrs['block_clients_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_clients_title_tag'] ?? 'h2', 'h2' )
	: 'h2';
$text = isset( $block_attrs['block_clients_text'] ) && trim( (string) $block_attrs['block_clients_text'] ) !== ''
	? (string) $block_attrs['block_clients_text']
	: (string) ( $defaults['text'] ?? '' );
$subtitle = ! empty( $block_attrs['block_clients_subtitle'] )
	? (string) $block_attrs['block_clients_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );

$normalize_logo = static function ( $raw_list ) {
	$out = array();
	foreach ( (array) $raw_list as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$img_id = ! empty( $it['image'] ) ? (int) $it['image'] : ( ! empty( $it['id'] ) ? (int) $it['id'] : 0 );
		$name   = trim( (string) ( $it['name'] ?? '' ) );
		$link   = trim( (string) ( $it['link'] ?? '' ) );
		$url    = '';
		if ( $img_id > 0 ) {
			$url = (string) wp_get_attachment_image_url( $img_id, 'medium' );
			if ( $name === '' ) {
				$name = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
			}
		}
		if ( $url === '' ) {
			continue;
		}
		$out[] = array(
			'url'  => $url,
			'name' => $name,
			'link' => $link,
		);
	}
	return $out;
};

$items = $normalize_logo(
	! empty( $block_attrs['block_clients_items'] ) && is_array( $block_attrs['block_clients_items'] )
		? $block_attrs['block_clients_items']
		: ( $defaults['items'] ?? array() )
);
$smi = $normalize_logo(
	! empty( $block_attrs['block_clients_smi'] ) && is_array( $block_attrs['block_clients_smi'] )
		? $block_attrs['block_clients_smi']
		: ( $defaults['smi'] ?? array() )
);

if ( $title === '' && $text === '' && empty( $items ) && $subtitle === '' && empty( $smi ) ) {
	return;
}

$render_splide = static function ( $root_class, $list_class, $item_class, $rows, $with_link ) {
	if ( empty( $rows ) ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( $root_class ); ?> splide" aria-label="<?php esc_attr_e( 'Клиенты', 'tolstenko-theme' ); ?>">
		<div class="splide__track swiper">
			<div class="<?php echo esc_attr( $list_class ); ?> splide__list swiper-wrapper">
				<?php foreach ( $rows as $item ) : ?>
					<?php if ( $with_link ) : ?>
						<?php if ( ! empty( $item['link'] ) ) : ?>
							<a class="<?php echo esc_attr( $item_class ); ?> splide__slide swiper-slide" href="<?php echo esc_url( $item['link'] ); ?>" target="_blank" rel="noopener noreferrer">
								<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
							</a>
						<?php else : ?>
							<span class="<?php echo esc_attr( $item_class ); ?> splide__slide swiper-slide">
								<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
							</span>
						<?php endif; ?>
					<?php else : ?>
						<div class="<?php echo esc_attr( $item_class ); ?> splide__slide swiper-slide">
							<div class="clients__list-image">
								<img src="<?php echo esc_url( $item['url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" decoding="async">
							</div>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="splide__bottom">
			<div class="swiper-pagination splide__pagination"></div>
			<div class="splide__arrows splide__arrows--ltr">
				<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Назад', 'tolstenko-theme' ); ?>">
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
					</svg>
				</button>
				<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Вперёд', 'tolstenko-theme' ); ?>">
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
					</svg>
				</button>
			</div>
		</div>
	</div>
	<?php
};
?>
<section class="clients section">
	<div class="container">
		<div class="clients__inner br-30">
			<?php if ( $title !== '' || $text !== '' ) : ?>
				<div class="clients__top section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="clients__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="clients__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php $render_splide( 'clients__splide', 'clients__list', 'clients__list-item', $items, false ); ?>

			<?php if ( $subtitle !== '' ) : ?>
				<h2 class="clients__subtitle h2"><?php echo tolstenko_kses_html( $subtitle ); ?></h2>
			<?php endif; ?>

			<?php $render_splide( 'clients__smi-splide', 'clients__smi-list', 'clients__smi-item', $smi, true ); ?>
		</div>
	</div>
</section>
