<?php
/**
 * Блок «Похожие вакансии».
 * Данные: атрибуты Gutenberg → «Шаблон вакансии» → последние вакансии.
 * Разметка/классы — как в tolstenko (BEM с __); слайдер — Swiper (как в теме).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();
$block_attrs = tolstenko_block_attributes();
$defaults = tolstenko_block_defaults( 'same_vacancy' );

$title = '';
if ( ! empty( $block_attrs['block_same_vacancy_title'] ) ) {
	$title = (string) $block_attrs['block_same_vacancy_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}
if ( $title === '' ) {
	$title = __( 'Другие вакансии', 'tolstenko-theme' );
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_same_vacancy_title_tag', 'h2' );

$post_ids = array();
$raw_ids = ! empty( $block_attrs['block_same_vacancy_items'] ) && is_array( $block_attrs['block_same_vacancy_items'] )
	? $block_attrs['block_same_vacancy_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_ids as $id ) {
	$id = (int) $id;
	if ( $id > 0 && ( ! $post_id || $id !== (int) $post_id ) ) {
		$post_ids[] = $id;
	}
}
$post_ids = array_values( array_unique( $post_ids ) );

if ( empty( $post_ids ) ) {
	$fallback = get_posts(
		array(
			'post_type'      => 'vacancy',
			'posts_per_page' => 4,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
			'post__not_in'   => $post_id ? array( (int) $post_id ) : array(),
			'no_found_rows'  => true,
		)
	);
	$post_ids = array_map( 'intval', $fallback );
}

if ( empty( $post_ids ) ) {
	return;
}

$vacancies = get_posts(
	array(
		'post_type'      => 'vacancy',
		'post__in'       => $post_ids,
		'posts_per_page' => -1,
		'orderby'        => 'post__in',
		'post_status'    => 'publish',
	)
);

if ( empty( $vacancies ) ) {
	return;
}
?>
<section class="same-vacancy section">
	<div class="container">
		<?php if ( $title !== '' ) : ?>
			<<?php echo esc_attr( $title_tag ); ?> class="same-vacancy__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
		<?php endif; ?>

		<div class="same-vacancy__splide splide swiper">
			<div class="swiper-wrapper same-vacancy__splide-list">
				<?php foreach ( $vacancies as $vacancy_post ) : ?>
					<?php
					set_query_var( 'tolstenko_vacancy_post', $vacancy_post );
					set_query_var( 'tolstenko_vacancy_card_class', 'same-vacancy__item br-30 swiper-slide' );
					get_template_part( 'template-parts/blocks/vacancy-card' );
					?>
				<?php endforeach; ?>
			</div>

			<div class="splide__bottom">
				<div class="swiper-pagination splide__pagination"></div>
				<div class="splide__arrows splide__arrows--ltr">
					<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Назад', 'tolstenko-theme' ); ?>">
						<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
						</svg>
					</button>
					<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Вперёд', 'tolstenko-theme' ); ?>">
						<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M4.16683 10H15.0001M10.8335 5L15.2442 9.41074C15.5696 9.73618 15.5696 10.2638 15.2442 10.5893L10.8335 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
						</svg>
					</button>
				</div>
			</div>
		</div>
	</div>
</section>
<?php
set_query_var( 'tolstenko_vacancy_post', null );
set_query_var( 'tolstenko_vacancy_card_class', '' );
?>
