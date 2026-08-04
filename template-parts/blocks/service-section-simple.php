<?php
/**
 * Блок «Слайдер услуг»: заголовок, текст, слайдер карточек без фильтра.
 * Карточки через service-card.php / REST-рендерер.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'service_section' );

$title = '';
if ( ! empty( $block_attrs['block_service_section_title'] ) ) {
	$title = (string) $block_attrs['block_service_section_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_service_section_text'] ) && trim( (string) $block_attrs['block_service_section_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_service_section_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_service_section_title_tag', 'h2' );

$posts_per_page = isset( $block_attrs['block_service_section_posts_per_page'] )
	? (int) $block_attrs['block_service_section_posts_per_page']
	: ( isset( $defaults['posts_per_page'] ) ? (int) $defaults['posts_per_page'] : 6 );
if ( $posts_per_page === 0 ) {
	$posts_per_page = 6;
}

$post_ids = array();
if ( ! empty( $block_attrs['block_service_section_ids'] ) && is_array( $block_attrs['block_service_section_ids'] ) ) {
	foreach ( $block_attrs['block_service_section_ids'] as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$post_ids[] = $id;
		}
	}
	$post_ids = array_values( array_unique( $post_ids ) );
} elseif ( ! empty( $defaults['ids'] ) && is_array( $defaults['ids'] ) ) {
	$post_ids = function_exists( 'tolstenko_sanitize_service_section_ids' )
		? tolstenko_sanitize_service_section_ids( $defaults['ids'] )
		: array_values( array_unique( array_filter( array_map( 'intval', $defaults['ids'] ) ) ) );
}

$section_id = 'services_simple_' . wp_unique_id();
$post_type  = 'service';
$card       = 'service';

$items_html = function_exists( 'tolstenko_render_filtered_posts_html' )
	? tolstenko_render_filtered_posts_html(
		array(
			'post_type'      => $post_type,
			'taxonomy'       => 'service_category',
			'term'           => '',
			'posts_per_page' => $posts_per_page,
			'card'           => $card,
			'post_ids'       => $post_ids,
		)
	)
	: '';

if ( $title === '' && $text === '' && $items_html === '' ) {
	return;
}
?>
<section class="service-section service-section--slider section" data-section-id="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="service-section__top section-top">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="service-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="service-section__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $items_html !== '' ) : ?>
			<div
				class="service-section__splide splide is-overflow"
				id="<?php echo esc_attr( $section_id ); ?>-container"
				aria-label="<?php esc_attr_e( 'Услуги', 'tolstenko-theme' ); ?>"
			>
				<div class="splide__track swiper">
					<div class="service-section__splide-list swiper-wrapper">
						<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped card template. ?>
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
		<?php endif; ?>
	</div>
</section>
<?php
set_query_var( 'tolstenko_service_post', null );
set_query_var( 'tolstenko_service_card_class', '' );
set_query_var( 'tolstenko_service_card_selected_category', '' );
?>
