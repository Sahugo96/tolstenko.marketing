<?php
/**
 * Шаблон архива подкатегории услуг (service-category/slug/).
 *
 * URL вида: /service-category/road-signs/
 * Порядок блоков как у CPT «Услуга» (register_post_type → template).
 * Редактирование доп. блоков — в админке: Услуги → Категории услуг → вкладки «Блоки страницы».
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! $term || ! isset( $term->term_id ) ) {
	// Fallback на index, если по какой-то причине термина нет
	include get_template_directory() . '/index.php';
	return;
}

// Контекст категории услуг для блоков на архиве термина.
set_query_var( 'tolstenko_service_category_term', $term );

$GLOBALS['tolstenko_service_category_render'] = true;

get_header();
?>

<main class="main main-content tolstenko-sc-category-main">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}
	// Текст для блока article — секции из вкладки «Текстовый блок» или описание категории
	set_query_var( 'tolstenko_article_term', $term );

	$blocks = function_exists( 'tolstenko_sc_get_service_category_block_slugs' )
		? tolstenko_sc_get_service_category_block_slugs()
		: array( 'main-hero', 'reviews', 'article', 'contacts' );
	foreach ( $blocks as $slug ) {
		$path = get_template_directory() . '/template-parts/blocks/' . $slug . '.php';
		if ( file_exists( $path ) ) {
			get_template_part( 'template-parts/blocks/' . $slug );
		}
	}
	?>
</main>

<?php
get_footer();
