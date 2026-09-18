<?php
/**
 * Архив категории услуг: /services/{category}/.
 * Контент — Gutenberg-блоки страницы /services/, фильтр предвыбирает рубрику.
 * Термин может переопределить главный баннер и SEO продвижение; скрытый H1 — из поля категории.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	include get_template_directory() . '/index.php';
	return;
}

$services_page = get_page_by_path( 'services' );
if ( ! ( $services_page instanceof WP_Post ) || $services_page->post_status !== 'publish' ) {
	include get_template_directory() . '/index.php';
	return;
}

set_query_var( 'tolstenko_service_category_term', $term );
$GLOBALS['tolstenko_service_category_render'] = true;

$hidden_h1 = function_exists( 'tolstenko_sc_get_hidden_h1' )
	? tolstenko_sc_get_hidden_h1( $term )
	: (string) $term->name;

get_header();
?>

<main class="main main-content">
	<?php if ( $hidden_h1 !== '' ) : ?>
		<h1 class="hide"><?php echo esc_html( $hidden_h1 ); ?></h1>
	<?php endif; ?>
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}

	$page_html = apply_filters( 'the_content', $services_page->post_content );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gutenberg via the_content.
	echo $page_html;

	$has_seo_in_page = is_string( $page_html ) && strpos( $page_html, 'seo-section' ) !== false;
	$raw_seo         = get_term_meta( $term->term_id, '_tolstenko_sc_seo_section', true );
	$need_seo        = ! $has_seo_in_page && function_exists( 'tolstenko_sc_seo_section_has_custom_content' ) && tolstenko_sc_seo_section_has_custom_content( $raw_seo );
	if ( $need_seo ) {
		set_query_var(
			'tolstenko_block_attributes',
			function_exists( 'tolstenko_sc_resolve_category_block_attributes' )
				? tolstenko_sc_resolve_category_block_attributes( 'seo_section', $term, '_tolstenko_sc_seo_section' )
				: array()
		);
		get_template_part( 'template-parts/blocks/seo-section' );
	}
	?>
</main>

<?php
get_footer();
