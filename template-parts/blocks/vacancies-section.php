<?php
/**
 * Блок «Секция вакансий»: заголовок, текст, фильтр vacancy_cat, карточки.
 * Фильтр — REST /tolstenko/v1/filter-posts, карточки через vacancy-card.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'vacancies_section' );

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

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_vacancies_section_title_tag', 'h2' );

$section_id     = 'vacancy_' . wp_unique_id();
$taxonomy       = 'vacancy_cat';
$post_type      = 'vacancy';
$posts_per_page = -1;
$card           = 'vacancy';

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

$items_html = function_exists( 'tolstenko_render_filtered_posts_html' )
	? tolstenko_render_filtered_posts_html(
		array(
			'post_type'      => $post_type,
			'taxonomy'       => $taxonomy,
			'term'           => '',
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
				<div class="vacancies-section__filter filter">
					<div class="filter__form">
						<label class="filter__radio">
							<input
								type="radio"
								name="<?php echo esc_attr( $section_id ); ?>_category"
								value=""
								data-section-id="<?php echo esc_attr( $section_id ); ?>"
								class="tolstenko-filter-radio"
								checked
							>
							<span class="filter__label"><?php esc_html_e( 'Все вакансии', 'tolstenko-theme' ); ?></span>
						</label>
						<?php foreach ( $categories_with_posts as $cat ) : ?>
							<label class="filter__radio">
								<input
									type="radio"
									name="<?php echo esc_attr( $section_id ); ?>_category"
									value="<?php echo esc_attr( $cat->slug ); ?>"
									data-section-id="<?php echo esc_attr( $section_id ); ?>"
									class="tolstenko-filter-radio"
								>
								<span class="filter__label"><?php echo esc_html( $cat->name ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
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
