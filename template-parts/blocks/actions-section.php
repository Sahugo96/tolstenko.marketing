<?php
/**
 * Блок «Плитка акций»: заголовок, текст, сетка CPT actions.
 * Данные секции: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'actions_section' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_actions_section_title'] ) ) {
	$title = (string) $block_attrs['block_actions_section_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_actions_section_text'] ) && trim( (string) $block_attrs['block_actions_section_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_actions_section_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_actions_section_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$query = new WP_Query(
	array(
		'post_type'      => 'actions',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	)
);

if ( ! $query->have_posts() && $title === '' && $text === '' ) {
	return;
}
?>
<section class="actions-section section">
	<div class="container">
		<?php if ( $title !== '' || $text !== '' ) : ?>
			<div class="actions-section__top section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="actions-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="actions-section__text"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( $query->have_posts() ) : ?>
			<div class="actions-section__items more-content">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$action_id   = get_the_ID();
					$permalink   = get_permalink( $action_id );
					$description = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $action_id, 'action_description' ) : (string) get_post_meta( $action_id, 'action_description', true );
					$same_cost   = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $action_id, 'action_same_cost' ) : (string) get_post_meta( $action_id, 'action_same_cost', true );
					$cost        = function_exists( 'tolstenko_get_action_field' ) ? tolstenko_get_action_field( $action_id, 'action_cost' ) : (string) get_post_meta( $action_id, 'action_cost', true );
					?>
					<article class="actions-section__item">
						<a class="actions-section__link" href="<?php echo esc_url( $permalink ); ?>">
							<div class="actions-section__img">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( 'full' );
								}
								?>
							</div>
							<div class="actions-section__wrapper">
								<span class="actions-section__title"><?php the_title(); ?></span>
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
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		<?php endif; ?>
	</div>
</section>
