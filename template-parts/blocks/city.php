<?php
/**
 * Блок «Города»: заголовок, текст, сетка CPT city + «Показать ещё».
 * Данные секции: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'city' );

$title = '';
if ( ! empty( $block_attrs['block_city_title'] ) ) {
	$title = (string) $block_attrs['block_city_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_city_text'] ) && trim( (string) $block_attrs['block_city_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_city_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_city_title_tag', 'h2' );

$query = new WP_Query(
	array(
		'post_type'      => 'city',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	)
);

$cities_count = (int) $query->post_count;

if ( ! $query->have_posts() && $title === '' && $text === '' ) {
	return;
}
?>
<section class="city section city--city">
	<div class="container">
		<?php if ( $title !== '' || $text !== '' ) : ?>
			<div class="city__top section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="city__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="city__text"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $query->have_posts() ) : ?>
			<div class="city__items more-content">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					?>
					<div class="city__item">
						<a class="city__link line-caps-bold-15-15" href="<?php echo esc_url( get_permalink() ); ?>">
							<span><?php the_title(); ?></span>
						</a>
					</div>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>

			<?php if ( $cities_count > 4 ) : ?>
				<button type="button" class="city__more more-btn">
					<?php esc_html_e( 'Показать еще', 'tolstenko-theme' ); ?>
					<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M11.25 6.25L7.94194 9.55806C7.69786 9.80214 7.30214 9.80214 7.05806 9.55806L3.75 6.25" stroke-linecap="round" />
					</svg>
				</button>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
