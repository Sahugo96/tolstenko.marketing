<?php
/**
 * Шаблон одной вакансии (CPT vacancy).
 * Контент — блоки Gutenberg: hero-vacancy, vacancy-content, consultation-free, same-vacancy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="main main-content tolstenko-vacancy-main">
<?php
if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
	tolstenko_render_breadcrumb();
}
if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
endif;
?>
</main>

<?php
get_footer();
