<?php
/**
 * Блок «Статьи плитка» — разметка как marketing blog-archive:
 * первая крупная карточка + сайдбар + сетка остальных + пагинация.
 * Табы: «Все записи» → /blog/, рубрики → /blog/{cat}/ (не AJAX).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'blog_section_tile' ) : array();
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

$posts_per_page = isset( $block_attrs['block_blog_section_posts_per_page'] )
	? (int) $block_attrs['block_blog_section_posts_per_page']
	: ( isset( $defaults['posts_per_page'] ) ? (int) $defaults['posts_per_page'] : 9 );
if ( $posts_per_page === 0 ) {
	$posts_per_page = 9;
}

$post_ids = array();
if ( ! empty( $block_attrs['block_blog_section_ids'] ) && is_array( $block_attrs['block_blog_section_ids'] ) ) {
	foreach ( $block_attrs['block_blog_section_ids'] as $id ) {
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

$section_id = 'blog_tile_' . wp_unique_id();
$taxonomy   = 'blog_cat';
$post_type  = 'blog';
$card       = 'blog_tile';

$all_url = home_url( '/blog/' );
if ( function_exists( 'tolstenko_get_cpt_listing_breadcrumb' ) ) {
	$listing = tolstenko_get_cpt_listing_breadcrumb( 'blog' );
	if ( is_array( $listing ) && ! empty( $listing['url'] ) ) {
		$all_url = (string) $listing['url'];
	}
}

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

$payload = function_exists( 'tolstenko_render_filtered_posts_payload' )
	? tolstenko_render_filtered_posts_payload(
		array(
			'post_type'      => $post_type,
			'taxonomy'       => $taxonomy,
			'term'           => $active_term,
			'posts_per_page' => $posts_per_page,
			'card'           => $card,
			'post_ids'       => $post_ids,
			'paged'          => 1,
			'paginate'       => true,
		)
	)
	: array(
		'html'       => '',
		'pagination' => '',
		'max_pages'  => 0,
		'page'       => 1,
	);

$items_html      = (string) ( $payload['html'] ?? '' );
$pagination_html = (string) ( $payload['pagination'] ?? '' );

if ( $title === '' && $text === '' && $items_html === '' && empty( $categories_with_posts ) ) {
	return;
}
?>
<section
	class="blog-section blog-section--blog section"
	data-tolstenko-filter
	data-tolstenko-layout="tile"
	data-tolstenko-paginate="1"
	data-section-id="<?php echo esc_attr( $section_id ); ?>"
	data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>"
	data-post-type="<?php echo esc_attr( $post_type ); ?>"
	data-posts-per-page="<?php echo esc_attr( (string) $posts_per_page ); ?>"
	data-card="<?php echo esc_attr( $card ); ?>"
	data-post-ids="<?php echo esc_attr( implode( ',', $post_ids ) ); ?>"
	data-active-term="<?php echo esc_attr( $active_term ); ?>"
	data-page="1"
>
	<div class="container">
		<div class="blog-section__top section-top">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="blog-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>
			<?php if ( $text !== '' ) : ?>
				<p class="blog-section__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $categories_with_posts ) ) : ?>
			<div class="blog-section__filter filter filter--blog">
				<div class="filter__form">
					<a
						class="filter__link<?php echo $active_term === '' ? ' active' : ''; ?>"
						href="<?php echo esc_url( $all_url ); ?>"
						data-term=""
					>
						<span class="filter__label"><?php esc_html_e( 'Все записи', 'tolstenko-theme' ); ?></span>
					</a>
					<?php foreach ( $categories_with_posts as $cat ) : ?>
						<?php
						$term_url = get_term_link( $cat );
						if ( is_wp_error( $term_url ) || ! $term_url ) {
							continue;
						}
						?>
						<a
							class="filter__link<?php echo $active_term === $cat->slug ? ' active' : ''; ?>"
							href="<?php echo esc_url( $term_url ); ?>"
							data-term="<?php echo esc_attr( $cat->slug ); ?>"
						>
							<span class="filter__label"><?php echo esc_html( $cat->name ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $items_html !== '' ) : ?>
			<div
				class="blog-section__list fade-in-container"
				id="<?php echo esc_attr( $section_id ); ?>-container"
				data-tolstenko-filter-container
			>
				<?php echo $items_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- archive layout from escaped templates. ?>
			</div>

			<div data-tolstenko-pagination>
				<?php echo $pagination_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pagination helper. ?>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
set_query_var( 'tolstenko_blog_post', null );
set_query_var( 'tolstenko_blog_card_class', '' );
set_query_var( 'tolstenko_blog_card_same', null );
set_query_var( 'tolstenko_blog_card_show_date', null );
set_query_var( 'tolstenko_blog_card_show_stats', null );
?>
