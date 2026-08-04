<?php
/**
 * Шаблон одной записи CPT city.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="main main-content">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}

	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
					<?php the_content(); ?>
			<?php
		endwhile;
	endif;
	?>
</main>

<?php
get_footer();
