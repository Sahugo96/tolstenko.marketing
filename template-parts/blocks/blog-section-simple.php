<?php
/**
 * «Похожие статьи» (.blog-section--same).
 * Выборка: до 12 ID — ручные первыми, недостающее добивается свежими по дате.
 * Карточки через blog-card.php / REST-рендерер (card=blog).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'blog_section' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_blog_section_title'] ) ) {
	$title = (string) $block_attrs['block_blog_section_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_blog_section_text'] ) && trim( (string) $block_attrs['block_blog_section_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_blog_section_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_blog_section_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

/** Максимум карточек в слайдере «Похожие статьи». */
$posts_limit = 12;

$manual_ids = array();
if ( ! empty( $block_attrs['block_blog_section_ids'] ) && is_array( $block_attrs['block_blog_section_ids'] ) ) {
	foreach ( $block_attrs['block_blog_section_ids'] as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$manual_ids[] = $id;
		}
	}
	$manual_ids = array_values( array_unique( $manual_ids ) );
} elseif ( ! empty( $defaults['ids'] ) && is_array( $defaults['ids'] ) ) {
	$manual_ids = function_exists( 'tolstenko_sanitize_service_section_ids' )
		? tolstenko_sanitize_service_section_ids( $defaults['ids'] )
		: array_values( array_unique( array_filter( array_map( 'intval', $defaults['ids'] ) ) ) );
}

$exclude = array();
if ( ! empty( $block_attrs['block_blog_section_exclude'] ) && is_array( $block_attrs['block_blog_section_exclude'] ) ) {
	foreach ( $block_attrs['block_blog_section_exclude'] as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$exclude[] = $id;
		}
	}
} elseif ( function_exists( 'tolstenko_is_content_body_singular' ) ? tolstenko_is_content_body_singular() : is_singular( array( 'blog', 'actions' ) ) ) {
	$current = (int) get_the_ID();
	if ( $current > 0 ) {
		$exclude[] = $current;
	}
}
$exclude = array_values( array_unique( $exclude ) );

// Ручные: только опубликованные blog, без exclude; порядок сохраняем.
$selected = array();
foreach ( $manual_ids as $id ) {
	if ( in_array( $id, $exclude, true ) || in_array( $id, $selected, true ) ) {
		continue;
	}
	$candidate = get_post( $id );
	if ( ! ( $candidate instanceof WP_Post ) || $candidate->post_type !== 'blog' || $candidate->post_status !== 'publish' ) {
		continue;
	}
	$selected[] = $id;
	if ( count( $selected ) >= $posts_limit ) {
		break;
	}
}

$post_ids = $selected;
$need     = $posts_limit - count( $post_ids );
if ( $need > 0 ) {
	$fresh_query = new WP_Query(
		array(
			'post_type'              => 'blog',
			'post_status'            => 'publish',
			'posts_per_page'         => $need,
			'orderby'                => 'date',
			'order'                  => 'DESC',
			'post__not_in'           => array_values( array_unique( array_merge( $exclude, $post_ids ) ) ),
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( ! empty( $fresh_query->posts ) ) {
		foreach ( $fresh_query->posts as $fid ) {
			$fid = (int) $fid;
			if ( $fid > 0 ) {
				$post_ids[] = $fid;
			}
		}
	}
}
$post_ids = array_slice( array_values( array_unique( $post_ids ) ), 0, $posts_limit );

$section_id = 'blog_simple_' . wp_unique_id();

$items_html = ( $post_ids && function_exists( 'tolstenko_render_filtered_posts_html' ) )
	? tolstenko_render_filtered_posts_html(
		array(
			'post_type'      => 'blog',
			'taxonomy'       => '',
			'term'           => '',
			'posts_per_page' => count( $post_ids ),
			'card'           => 'blog',
			'post_ids'       => $post_ids,
			'exclude'        => array(),
		)
	)
	: '';

if ( $title === '' && $text === '' && $items_html === '' ) {
	return;
}
?>
<section class="blog-section blog-section--same section" data-section-id="<?php echo esc_attr( $section_id ); ?>">
	<div class="container">
		<div class="blog-section__top section-top">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="blog-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="blog-section__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $items_html !== '' ) : ?>
			<div
				class="blog-section__splide splide is-overflow"
				id="<?php echo esc_attr( $section_id ); ?>-container"
				aria-label="<?php esc_attr_e( 'Похожие статьи', 'tolstenko-theme' ); ?>"
			>
				<div class="splide__track swiper">
					<div class="blog-section__splide-list splide__list swiper-wrapper">
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
set_query_var( 'tolstenko_blog_post', null );
set_query_var( 'tolstenko_blog_card_class', '' );
set_query_var( 'tolstenko_blog_card_same', null );
?>
