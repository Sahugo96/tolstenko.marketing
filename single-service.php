<?php
/**
 * Шаблон одной услуги (CPT service).
 * Контент — блоки Gutenberg (категория «Блоки темы»). Редактируются в блок-редакторе.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$GLOBALS['tolstenko_service_single_render'] = true;
?>

<main class="main main-content tolstenko-service-main">
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
