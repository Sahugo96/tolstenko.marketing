<?php
/**
 * Архив категории вакансий: /vacancies/{category}/.
 * Контент — Gutenberg-блоки страницы /vacancies/, фильтр предвыбирает рубрику.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
	include get_template_directory() . '/index.php';
	return;
}

$vacancies_page = get_page_by_path( 'vacancies' );
if ( ! ( $vacancies_page instanceof WP_Post ) || $vacancies_page->post_status !== 'publish' ) {
	include get_template_directory() . '/index.php';
	return;
}

get_header();
?>

<main class="main main-content">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Gutenberg via the_content.
	echo apply_filters( 'the_content', $vacancies_page->post_content );
	?>
</main>

<?php
get_footer();
