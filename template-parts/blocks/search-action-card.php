<?php
/**
 * Карточка акции для результатов поиска.
 * Внешний вид — как actions-section__item (карточка «Акции»).
 *
 * Query var: tolstenko_search_action_post (WP_Post)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_search_action_post' );
if ( ! $post instanceof WP_Post || $post->post_type !== 'actions' ) {
	return;
}

$post_id = (int) $post->ID;
$permalink   = get_permalink( $post_id );
$title       = get_the_title( $post_id );
$description = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $post_id, 'action_description' ) : (string) get_post_meta( $post_id, 'action_description', true );
$same_cost   = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $post_id, 'action_same_cost' ) : (string) get_post_meta( $post_id, 'action_same_cost', true );
$cost        = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $post_id, 'action_cost' ) : (string) get_post_meta( $post_id, 'action_cost', true );
?>
<article class="actions-section__item">
	<div class="actions-section__link">
		<a class="actions-section__img" href="<?php echo esc_url( $permalink ); ?>">
			<?php if ( has_post_thumbnail( $post_id ) ) : ?>
				<?php echo get_the_post_thumbnail( $post_id, 'full' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php else : ?>
				<?php echo tolstenko_get_card_placeholder_image_html( 'large', $title ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</a>
		<div class="actions-section__wrapper">
			<a class="actions-section__title line-caps-bold-16-15" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
			<?php if ( $description !== '' ) : ?>
				<div class="actions-section__text"><?php echo tolstenko_kses_html( $description ); ?></div>
			<?php endif; ?>
			<?php if ( $same_cost !== '' ) : ?>
				<div class="actions-section__cost">
					<?php
					printf(
						/* translators: %s: price from */
						esc_html__( 'Цена от %s₽', 'tolstenko-theme' ),
						esc_html( $same_cost )
					);
					?>
					<?php if ( $cost !== '' ) : ?>
						<span><?php echo esc_html( $cost ); ?>₽</span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="actions-section__btns">
			<a class="actions-section__btn default-btn" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Подробнее', 'tolstenko-theme' ); ?>
			</a>
			<a class="actions-section__btn default-btn" href="#modal">
				<?php esc_html_e( 'Консультация', 'tolstenko-theme' ); ?>
			</a>
		</div>
	</div>
</article>
<?php
set_query_var( 'tolstenko_search_action_post', null );