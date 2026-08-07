<?php
/**
 * Блок «Услуги (плитка)»: заголовок, фильтр категорий, сетка всех услуг, «Показать ещё».
 * Фильтр — REST /tolstenko/v1/filter-posts, карточки через service-card.php (card=service_tile).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'service_section_tile' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

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

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_service_section_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$section_id = 'services_tile_' . wp_unique_id();
$toggle_id  = $section_id . '_toggle';
$taxonomy   = 'service_category';
$post_type  = 'service';
$card       = 'service_tile';
$posts_per_page = -1;

$all_categories = taxonomy_exists( $taxonomy )
	? get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	)
	: array();

$categories_with_posts = array();
if ( ! is_wp_error( $all_categories ) && is_array( $all_categories ) ) {
	foreach ( $all_categories as $cat ) {
		if ( $cat instanceof WP_Term && (int) $cat->count > 0 ) {
			$categories_with_posts[] = $cat;
		}
	}
}

$active_term = function_exists( 'tolstenko_get_filter_active_term_slug' )
	? tolstenko_get_filter_active_term_slug( $taxonomy )
	: '';
if ( function_exists( 'tolstenko_ensure_filter_term_in_categories' ) ) {
	$categories_with_posts = tolstenko_ensure_filter_term_in_categories( $categories_with_posts, $taxonomy, $active_term );
}

$items_html = function_exists( 'tolstenko_render_filtered_posts_html' )
	? tolstenko_render_filtered_posts_html(
		array(
			'post_type'      => $post_type,
			'taxonomy'       => $taxonomy,
			'term'           => $active_term,
			'posts_per_page' => $posts_per_page,
			'card'           => $card,
		)
	)
	: '';

if ( $title === '' && $text === '' && $items_html === '' && empty( $categories_with_posts ) ) {
	return;
}
?>
<section
	class="service-section service-section--archive section"
	data-tolstenko-filter
	data-tolstenko-layout="tile"
	data-section-id="<?php echo esc_attr( $section_id ); ?>"
	data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
	data-post-type="<?php echo esc_attr( $post_type ); ?>"
	data-posts-per-page="<?php echo esc_attr( (string) $posts_per_page ); ?>"
	data-card="<?php echo esc_attr( $card ); ?>"
>
	<div class="container">
		<div class="service-section__top section-top">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="service-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="service-section__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $categories_with_posts ) ) : ?>
			<div class="service-section__filter filter">
				<div class="filter__form">
					<label class="filter__radio">
						<input
							type="radio"
							name="<?php echo esc_attr( $section_id ); ?>_category"
							value=""
							data-section-id="<?php echo esc_attr( $section_id ); ?>"
							class="tolstenko-filter-radio"
							<?php checked( $active_term, '' ); ?>
						>
						<span class="filter__label"><?php esc_html_e( 'Все услуги', 'tolstenko-theme' ); ?></span>
					</label>
					<?php foreach ( $categories_with_posts as $cat ) : ?>
						<label class="filter__radio">
							<input
								type="radio"
								name="<?php echo esc_attr( $section_id ); ?>_category"
								value="<?php echo esc_attr( $cat->slug ); ?>"
								data-section-id="<?php echo esc_attr( $section_id ); ?>"
								class="tolstenko-filter-radio"
								<?php checked( $active_term, $cat->slug ); ?>
							>
							<span class="filter__label"><?php echo esc_html( $cat->name ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $items_html !== '' ) : ?>
			<input type="checkbox" class="service-section__toggle" id="<?php echo esc_attr( $toggle_id ); ?>" hidden>

			<div
				class="fade-in-container service-section__grid"
				id="<?php echo esc_attr( $section_id ); ?>-container"
				data-tolstenko-filter-container
			>
				<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped card template. ?>
			</div>

			<label class="service-section__more more-btn" for="<?php echo esc_attr( $toggle_id ); ?>">
				<?php esc_html_e( 'Показать еще', 'tolstenko-theme' ); ?>
				<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M11.25 6.25L7.94194 9.55806C7.69786 9.80214 7.30214 9.80214 7.05806 9.55806L3.75 6.25" stroke-linecap="round" />
				</svg>
			</label>
		<?php endif; ?>
	</div>
</section>
<?php
set_query_var( 'tolstenko_service_post', null );
set_query_var( 'tolstenko_service_card_class', '' );
set_query_var( 'tolstenko_service_card_selected_category', '' );
?>
