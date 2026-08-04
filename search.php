<?php
/**
 * Шаблон результатов поиска.
 * Форма в шапке: method="get", action="home_url()", поле name="s" — запрос уходит в ?s=...
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$query = get_search_query();
?>

<main class="main main-content search-results">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}
	?>
	<div class="container">
		<h1 class="search-results-title">
			<?php
			if ( $query ) {
				printf(
					/* translators: %s: search query */
					esc_html__( 'Результаты поиска: %s', 'tolstenko-theme' ),
					'<span class="search-results-query">' . esc_html( $query ) . '</span>'
				);
			} else {
				esc_html_e( 'Поиск', 'tolstenko-theme' );
			}
			?>
		</h1>

		<?php if ( have_posts() ) : ?>
			<ul class="search-results-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="search-results-item">
						<a href="<?php the_permalink(); ?>" class="search-results-link">
							<?php the_title(); ?>
						</a>
						<?php if ( get_the_excerpt() ) : ?>
							<p class="search-results-excerpt"><?php echo wp_kses_post( get_the_excerpt() ); ?></p>
						<?php endif; ?>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php
			the_posts_pagination(
				array(
					'prev_text' => '&larr;',
					'next_text' => '&rarr;',
				)
			);
			?>
		<?php else : ?>
			<p class="search-results-empty">
				<?php esc_html_e( 'По вашему запросу ничего не найдено.', 'tolstenko-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
