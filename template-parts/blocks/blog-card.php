<?php
/**
 * Карточка статьи (CPT blog) для слайдера / списков.
 *
 * Query vars:
 * - tolstenko_blog_post (WP_Post|null)
 * - tolstenko_blog_card_class (string)
 * - tolstenko_blog_card_same (bool) — модификатор blog-card--same, упрощённые stats
 * - tolstenko_blog_card_show_date (bool|null) — явный показ даты (null = логика same)
 * - tolstenko_blog_card_show_stats (bool|null) — явный показ лайков/просмотров
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_blog_post', null );
if ( ! ( $post instanceof WP_Post ) ) {
	$post = get_post();
}
if ( ! ( $post instanceof WP_Post ) || $post->post_type !== 'blog' ) {
	return;
}

$post_id     = (int) $post->ID;
$extra_class = (string) get_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card blog-card--same swiper-slide' );
$is_same     = (bool) get_query_var( 'tolstenko_blog_card_same', true );
$show_date_q = get_query_var( 'tolstenko_blog_card_show_date', null );
$show_stats_q = get_query_var( 'tolstenko_blog_card_show_stats', null );

$link  = get_permalink( $post_id );
$title = get_the_title( $post_id );

$card_text = (string) get_post_meta( $post_id, 'single-blog_text', true );
if ( trim( $card_text ) === '' ) {
	$card_text = wp_trim_words( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ), 20, '…' );
}

$months = array(
	1  => 'января',
	2  => 'февраля',
	3  => 'марта',
	4  => 'апреля',
	5  => 'мая',
	6  => 'июня',
	7  => 'июля',
	8  => 'августа',
	9  => 'сентября',
	10 => 'октября',
	11 => 'ноября',
	12 => 'декабря',
);
$date_parts     = explode( ' ', get_the_date( 'j n Y', $post_id ) );
$day            = $date_parts[0] ?? '';
$month_num      = isset( $date_parts[1] ) ? (int) $date_parts[1] : 0;
$year           = $date_parts[2] ?? '';
$formatted_date = ( $day && $month_num && $year )
	? $day . ' ' . ( $months[ $month_num ] ?? '' ) . ', ' . $year
	: get_the_date( '', $post_id );

$tags = array();
if ( taxonomy_exists( 'blog_cat' ) ) {
	$terms = get_the_terms( $post_id, 'blog_cat' );
	if ( is_array( $terms ) && ! is_wp_error( $terms ) ) {
		$tags = array_slice( $terms, 0, 2 );
	}
}

$classes = trim( $extra_class );
if ( $is_same && strpos( $classes, 'blog-card--same' ) === false ) {
	$classes .= ' blog-card--same';
}

$show_date = null !== $show_date_q
	? (bool) $show_date_q
	: ( ! $is_same || ! $tags );
$show_stats = null !== $show_stats_q
	? (bool) $show_stats_q
	: ! $is_same;
?>
<article class="<?php echo esc_attr( $classes ); ?>">
	<a class="blog-card__link" href="<?php echo esc_url( $link ); ?>">
		<?php if ( has_post_thumbnail( $post_id ) ) : ?>
			<div class="blog-card__image">
				<?php echo get_the_post_thumbnail( $post_id, 'medium_large' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<svg viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M3.17309 3.41726C3.48384 3.41726 3.73572 3.66411 3.73572 3.96866V10.1167C4.52232 9.06924 5.30923 8.02205 6.08642 6.96803C6.33162 6.63553 6.63297 6.47522 7.05752 6.47879C8.35911 6.49009 9.66102 6.47731 10.9629 6.48207C11.4561 6.48385 11.6606 6.53115 11.7425 6.77294C11.8315 6.88298 11.887 7.02037 11.887 7.17175V9.90673C11.8946 9.94866 11.9025 9.99239 11.9103 10.0403C12.6372 9.05882 13.2936 8.18057 13.9418 7.29666C14.7873 6.1442 15.6121 4.97658 16.4822 3.84226C16.6391 3.63764 16.9641 3.46573 17.2244 3.4461C17.9379 3.39256 18.6584 3.43033 19.3761 3.42795C19.7721 3.42676 19.9448 3.61147 19.9445 4.00226L19.9448 4.00165C19.9417 7.4528 19.9445 10.9043 19.9508 14.3557C19.9514 14.7632 19.7715 14.9592 19.3533 14.9633C18.6857 14.9699 18.0183 14.9933 17.3507 14.9996C16.6369 15.0061 16.5705 14.9407 16.5705 14.2534C16.5705 12.6994 16.5775 11.1457 16.5729 9.59178C16.5723 9.32768 16.528 9.06358 16.5037 8.79949C16.4403 8.78848 16.3765 8.77747 16.3131 8.76646C15.6737 9.61706 15.0282 10.4632 14.3964 11.3188C13.6162 12.3752 12.8423 13.4358 12.0773 14.5026C11.8585 14.8075 11.6117 14.9684 11.2072 14.9508C10.9514 14.9398 10.6953 14.9362 10.4388 14.9353H10.081C9.75572 14.9383 9.43009 14.9452 9.10507 14.9526C9.02465 14.9544 8.95453 14.9475 8.8908 14.9353H8.85924C8.49569 14.9353 8.20103 14.6466 8.20103 14.2903V8.89466C8.14579 8.92411 8.0948 8.95682 8.0599 9.00143C6.71947 10.7282 5.3857 12.8014 4.0568 14.5368C3.94877 14.6781 3.76939 14.9633 3.31418 14.9633C2.85898 14.9633 0.6121 14.9633 0.6121 14.9633C0.301346 14.9633 0.0494623 14.7165 0.0494623 14.4119V3.96866C0.0494623 3.66411 0.301346 3.41726 0.6121 3.41726H3.17309Z" />
					<path d="M19.3418 0C19.705 0 20 0.289096 20 0.645096V1.64586C20 2.00187 19.705 2.29096 19.3418 2.29096H0.658243C0.294988 2.29096 0 2.00187 0 1.64586V0.645096C0 0.289096 0.294988 0 0.658243 0H19.3418Z" />
				</svg>
			</div>
		<?php endif; ?>

		<div class="blog-card__wrapper">
			<?php if ( $tags || ( $show_date && $formatted_date ) ) : ?>
				<div class="blog-card__tags">
					<?php foreach ( $tags as $tag ) : ?>
						<span class="blog-card__tag"><?php echo esc_html( $tag->name ); ?></span>
					<?php endforeach; ?>
					<?php if ( $show_date && $formatted_date ) : ?>
						<span class="blog-card__tag blog-card__tag--date"><?php echo esc_html( $formatted_date ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<h4 class="blog-card__title"><?php echo esc_html( $title ); ?></h4>

			<?php if ( trim( $card_text ) !== '' ) : ?>
				<div class="blog-card__text line-13-15"><?php echo esc_html( $card_text ); ?></div>
			<?php endif; ?>

			<div class="blog-card__stats single-blog__stats">
				<?php
				get_template_part(
					'modules/stats/stats',
					null,
					array(
						'show_likes' => $show_stats,
						'show_views' => $show_stats,
					)
				);
				?>
			</div>
		</div>
	</a>
</article>
<?php
set_query_var( 'tolstenko_blog_post', null );
set_query_var( 'tolstenko_blog_card_class', '' );
set_query_var( 'tolstenko_blog_card_same', null );
set_query_var( 'tolstenko_blog_card_show_date', null );
set_query_var( 'tolstenko_blog_card_show_stats', null );
?>
