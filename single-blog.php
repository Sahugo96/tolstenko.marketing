<?php
/**
 * Single template: статья (CPT blog) — hybrid hero + Gutenberg + comments.
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
		get_template_part( 'pages/single-blog/sections/single-blog' );

		$services_attrs = function_exists( 'tolstenko_get_blog_services_block_attrs' )
			? tolstenko_get_blog_services_block_attrs( get_the_ID() )
			: array();
		if ( empty( $services_attrs['_tolstenko_hidden'] ) ) {
			set_query_var( 'tolstenko_block_attributes', $services_attrs );
			get_template_part( 'template-parts/blocks/service-section-simple' );
			set_query_var( 'tolstenko_block_attributes', array() );
		}

		$faq_attrs = function_exists( 'tolstenko_get_blog_faq_block_attrs' )
			? tolstenko_get_blog_faq_block_attrs( get_the_ID() )
			: array();
		if ( empty( $faq_attrs['_tolstenko_faq_hidden'] ) ) {
			set_query_var( 'tolstenko_block_attributes', $faq_attrs );
			get_template_part( 'template-parts/blocks/faq' );
			set_query_var( 'tolstenko_block_attributes', array() );
		}

		$related_attrs = function_exists( 'tolstenko_get_blog_related_block_attrs' )
			? tolstenko_get_blog_related_block_attrs( get_the_ID() )
			: array();
		if ( empty( $related_attrs['_tolstenko_hidden'] ) ) {
			set_query_var( 'tolstenko_block_attributes', $related_attrs );
			get_template_part( 'template-parts/blocks/blog-section-simple' );
			set_query_var( 'tolstenko_block_attributes', array() );
		}

		get_template_part( 'pages/single-blog/sections/comments' );
		get_template_part( 'template-parts/blocks/consultation-free' );
	}
	?>
</main>

<?php
get_footer();
