<?php
/**
 * Блок «Секция вакансий»: заголовок, текст, фильтр vacancy_cat, карточки.
 * Фильтр — REST /tolstenko/v1/filter-posts, карточки через vacancy-card.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'vacancies_section' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_vacancies_section_title'] ) ) {
	$title = (string) $block_attrs['block_vacancies_section_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_vacancies_section_text'] ) && trim( (string) $block_attrs['block_vacancies_section_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_vacancies_section_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_vacancies_section_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$section_id     = 'vacancy_' . wp_unique_id();
$taxonomy       = 'vacancy_cat';
$post_type      = 'vacancy';
$posts_per_page = -1;
$card           = 'vacancy';

$categories_with_posts = function_exists( 'tolstenko_get_filter_top_level_terms' )
	? tolstenko_get_filter_top_level_terms( $taxonomy )
	: array();

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
	class="vacancies-section section"
	data-tolstenko-filter
	data-section-id="<?php echo esc_attr( $section_id ); ?>"
	data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
	data-post-type="<?php echo esc_attr( $post_type ); ?>"
	data-posts-per-page="<?php echo esc_attr( (string) $posts_per_page ); ?>"
	data-card="<?php echo esc_attr( $card ); ?>"
>
	<div class="container">
		<div class="vacancies-section__inner br-30">
			<?php if ( $title !== '' || $text !== '' ) : ?>
				<div class="vacancies-section__top section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="vacancies-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="vacancies-section__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $categories_with_posts ) ) : ?>
				<div class="vacancies-section__filter filter filter--slider">
					<div class="filter__form swiper">
						<div class="swiper-wrapper">
							<label class="filter__radio swiper-slide">
								<input
									type="radio"
									name="<?php echo esc_attr( $section_id ); ?>_category"
									value=""
									data-section-id="<?php echo esc_attr( $section_id ); ?>"
									class="tolstenko-filter-radio"
									<?php checked( $active_term, '' ); ?>
								>
								<span class="filter__label"><?php esc_html_e( 'Все вакансии', 'tolstenko-theme' ); ?></span>
							</label>
							<?php foreach ( $categories_with_posts as $cat ) : ?>
								<label class="filter__radio swiper-slide">
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
					<?php get_template_part( 'template-parts/filter-arrows' ); ?>
				</div>
			<?php endif; ?>

			<div
				class="fade-in-container vacancies-section__items"
				id="<?php echo esc_attr( $section_id ); ?>-container"
				data-tolstenko-filter-container
			>
				<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped card template. ?>
			</div>
		</div>
	</div>
</section>
