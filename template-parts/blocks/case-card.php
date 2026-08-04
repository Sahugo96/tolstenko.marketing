<?php
/**
 * Карточка кейса (слайд секции «Кейсы»).
 * Перед вызовом: set_query_var( 'tolstenko_case_post', $post ).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_case_post' );
if ( ! $post instanceof WP_Post || $post->post_type !== 'case' ) {
	return;
}

$post_id = (int) $post->ID;
$data    = function_exists( 'tolstenko_get_case_card_data' )
	? tolstenko_get_case_card_data( $post_id )
	: array();

$title       = (string) ( $data['title'] ?? get_the_title( $post ) );
$text        = (string) ( $data['text'] ?? '' );
$items       = is_array( $data['items'] ?? null ) ? $data['items'] : array();
$image_url   = (string) ( $data['image_url'] ?? '' );
$image_alt   = (string) ( $data['image_alt'] ?? $title );
$service_url = (string) ( $data['service_url'] ?? '' );
$link        = get_permalink( $post_id );
$extra_class = (string) get_query_var( 'tolstenko_case_card_class', 'case-section__item case-card fade-in-element splide__slide swiper-slide' );
?>
<article class="<?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $image_url !== '' ) : ?>
		<div class="case-card__image">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
		</div>
	<?php endif; ?>

	<div class="case-card__wrapper">
		<?php if ( $title !== '' ) : ?>
			<h3 class="case-card__title"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<?php if ( $text !== '' ) : ?>
			<div class="case-card__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></div>
		<?php endif; ?>

		<?php if ( ! empty( $items ) ) : ?>
			<div class="case-card__list">
				<?php foreach ( $items as $item ) : ?>
					<div class="case-card__list-item">
						<?php if ( ! empty( $item['value'] ) ) : ?>
							<span class="case-card__list-value"><?php echo esc_html( (string) $item['value'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<span class="case-card__list-text line-13-15"><?php echo esc_html( (string) $item['text'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="case-card__btns">
			<a class="case-card__btn case-card__btn--transparent default-btn" href="<?php echo esc_url( $link ); ?>">
				<?php esc_html_e( 'Разобрать кейс', 'tolstenko-theme' ); ?>
			</a>
			<?php if ( $service_url !== '' ) : ?>
				<a class="case-card__btn case-card__btn--service default-btn" href="<?php echo esc_url( $service_url ); ?>">
					<?php esc_html_e( 'Подробнее об услуге', 'tolstenko-theme' ); ?>
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M4.16675 10H15.0001M10.8334 5L15.2442 9.41074C15.5696 9.73618 15.5696 10.2638 15.2442 10.5893L10.8334 15" stroke-width="2" stroke-linecap="round" />
					</svg>
				</a>
			<?php endif; ?>
		</div>
	</div>
</article>
