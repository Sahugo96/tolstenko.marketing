<?php
/**
 * Default page template
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
		?>
		<?php if ( is_page( 'politika-konfidenczialnosti' ) || is_page( 'soglasie-na-obrabotku-personalnyh-dannyh' ) ) : ?>
			<div class="container policy-container redactor">
		<?php endif; ?>
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
		endif;
		?>
		<?php if ( is_page( 'politika-konfidenczialnosti' ) || is_page( 'soglasie-na-obrabotku-personalnyh-dannyh' ) ) : ?>
			</div>
		<?php endif; ?>
    </main>

<?php
get_footer();

