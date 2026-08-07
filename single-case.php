<?php
/**
 * Single template: кейс.
 */

get_header();
?>

<main class="main">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}

	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>

<?php
get_footer();
