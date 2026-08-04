<?php
/**
 * Карточка услуги (слайд секции «Слайдер услуг»).
 * Перед вызовом: set_query_var( 'tolstenko_service_post', $post ).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_service_post' );
if ( ! $post instanceof WP_Post || $post->post_type !== 'service' ) {
	return;
}

$post_id = (int) $post->ID;
$data    = function_exists( 'tolstenko_get_service_card_data' )
	? tolstenko_get_service_card_data( $post_id )
	: array();

$title         = (string) ( $data['title'] ?? get_the_title( $post ) );
$description   = (string) ( $data['description'] ?? '' );
$price_from    = (string) ( $data['price_from'] ?? '' );
$price_old     = (string) ( $data['price_old'] ?? '' );
$is_hit        = ! empty( $data['is_hit'] );
$discount      = (string) ( $data['discount'] ?? '' );
$tag_name      = (string) ( $data['tag_name'] ?? '' );
$link          = get_permalink( $post_id );
$extra_class   = (string) get_query_var( 'tolstenko_service_card_class', 'service-section__item service-card swiper-slide' );
$has_thumb     = has_post_thumbnail( $post_id );
?>
<article class="<?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $has_thumb ) : ?>
		<a class="service-card__image" href="<?php echo esc_url( $link ); ?>">
			<?php echo get_the_post_thumbnail( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core thumbnail HTML. ?>

			<?php if ( $tag_name !== '' ) : ?>
				<span class="service-card__tag caption-8-10"><?php echo esc_html( $tag_name ); ?></span>
			<?php endif; ?>

			<span class="service-card__hit caption-8-10">
				<?php esc_html_e( 'хит', 'tolstenko-theme' ); ?>
				<?php if ( $is_hit && $discount !== '' ) : ?>
					<span><?php echo esc_html( $discount ); ?>%</span>
				<?php endif; ?>
			</span>
		</a>
	<?php endif; ?>

	<div class="service-card__wrapper">
		<a class="service-card__title paragraph-15-15" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>

		<?php if ( $description !== '' ) : ?>
			<div class="service-card__text line-13-15"><?php echo tolstenko_kses_html( $description ); ?></div>
		<?php endif; ?>

		<?php if ( $price_from !== '' ) : ?>
			<div class="service-card__cost line-13-15">
				<?php
				printf(
					/* translators: %s: price from */
					esc_html__( 'Цена от %s₽', 'tolstenko-theme' ),
					esc_html( $price_from )
				);
				?>
				<?php if ( $price_old !== '' ) : ?>
					<span><?php echo esc_html( $price_old ); ?>₽</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="service-card__btns">
		<a class="service-card__btn default-btn" href="<?php echo esc_url( $link ); ?>">
			<?php esc_html_e( 'Подробнее', 'tolstenko-theme' ); ?>
		</a>
		<a class="service-card__btn default-btn" href="#modal">
			<?php esc_html_e( 'Консультация', 'tolstenko-theme' ); ?>
		</a>
	</div>
</article>
