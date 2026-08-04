<?php
/**
 * Архив категории статей (blog_cat): /blog/{category}/.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = get_queried_object();
if ( ! ( $term instanceof WP_Term ) ) {
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
	?>

	<section class="blog-section blog-section--archive">
		<div class="container">
			<h1 class="blog-section__title h1"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( ! empty( $term->description ) ) : ?>
				<div class="blog-section__text paragraph-15-25">
					<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="blog-section__list">
					<?php
					while ( have_posts() ) :
						the_post();
						set_query_var( 'tolstenko_blog_post', get_post() );
						set_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card' );
						set_query_var( 'tolstenko_blog_card_same', false );
						get_template_part( 'template-parts/blocks/blog', 'card' );
					endwhile;
					?>
				</div>
				<?php
				the_posts_pagination(
					array(
						'mid_size'  => 1,
						'prev_text' => __( 'Назад', 'tolstenko-theme' ),
						'next_text' => __( 'Вперёд', 'tolstenko-theme' ),
					)
				);
				?>
			<?php else : ?>
				<p class="paragraph-15-25"><?php esc_html_e( 'В этой категории пока нет статей.', 'tolstenko-theme' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
