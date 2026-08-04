<?php
/**
 * Карточка вакансии. Данные: attrs героя записи → шаблон вакансии.
 * Перед вызовом: set_query_var( 'tolstenko_vacancy_post', $post ).
 * Разметка/классы — как в tolstenko (BEM с __).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_vacancy_post' );
if ( ! $post instanceof WP_Post || $post->post_type !== 'vacancy' ) {
	return;
}

$post_id = (int) $post->ID;
$data    = function_exists( 'tolstenko_get_vacancy_card_data' )
	? tolstenko_get_vacancy_card_data( $post_id )
	: array( 'cost' => '', 'conditions' => array(), 'btn_text' => '', 'btn_url' => '' );

$cost         = (string) ( $data['cost'] ?? '' );
$conditions   = is_array( $data['conditions'] ?? null ) ? $data['conditions'] : array();
$btn_text     = (string) ( $data['btn_text'] ?? '' );
$btn_url      = tolstenko_url_or_modal( (string) ( $data['btn_url'] ?? '' ) );
$link         = get_permalink( $post_id );
$extra_class  = (string) get_query_var( 'tolstenko_vacancy_card_class', '' );
?>
<article class="vacancies-card<?php echo $extra_class !== '' ? ' ' . esc_attr( $extra_class ) : ''; ?>">
	<svg class="vacancies-card__svg" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
		<path d="M3.17309 3.41726C3.48384 3.41726 3.73572 3.66411 3.73572 3.96866V10.1167C4.52232 9.06924 5.30923 8.02205 6.08642 6.96803C6.33162 6.63553 6.63297 6.47522 7.05752 6.47879C8.35911 6.49009 9.66102 6.47731 10.9629 6.48207C11.4561 6.48385 11.6606 6.53115 11.7425 6.77294C11.8315 6.88298 11.887 7.02037 11.887 7.17175V9.90673C11.8946 9.94866 11.9025 9.99239 11.9103 10.0403C12.6372 9.05882 13.2936 8.18057 13.9418 7.29666C14.7873 6.1442 15.6121 4.97658 16.4822 3.84226C16.6391 3.63764 16.9641 3.46573 17.2244 3.4461C17.9379 3.39256 18.6584 3.43033 19.3761 3.42795C19.7721 3.42676 19.9448 3.61147 19.9445 4.00226L19.9448 4.00165C19.9417 7.4528 19.9445 10.9043 19.9508 14.3557C19.9514 14.7632 19.7715 14.9592 19.3533 14.9633C18.6857 14.9699 18.0183 14.9933 17.3507 14.9996C16.6369 15.0061 16.5705 14.9407 16.5705 14.2534C16.5705 12.6994 16.5775 11.1457 16.5729 9.59178C16.5723 9.32768 16.528 9.06358 16.5037 8.79949C16.4403 8.78848 16.3765 8.77747 16.3131 8.76646C15.6737 9.61706 15.0282 10.4632 14.3964 11.3188C13.6162 12.3752 12.8423 13.4358 12.0773 14.5026C11.8585 14.8075 11.6117 14.9684 11.2072 14.9508C10.9514 14.9398 10.6953 14.9362 10.4388 14.9353H10.081C9.75572 14.9383 9.43009 14.9452 9.10507 14.9526C9.02465 14.9544 8.95453 14.9475 8.8908 14.9353H8.85924C8.49569 14.9353 8.20103 14.6466 8.20103 14.2903V8.89466C8.14579 8.92411 8.0948 8.95682 8.0599 9.00143C6.71947 10.7282 5.3857 12.8014 4.0568 14.5368C3.94877 14.6781 3.76939 14.9633 3.31418 14.9633C2.85898 14.9633 0.6121 14.9633 0.6121 14.9633C0.301346 14.9633 0.0494623 14.7165 0.0494623 14.4119V3.96866C0.0494623 3.66411 0.301346 3.41726 0.6121 3.41726H3.17309Z" />
		<path d="M19.3418 0C19.705 0 20 0.289096 20 0.645096V1.64586C20 2.00187 19.705 2.29096 19.3418 2.29096H0.658243C0.294988 2.29096 0 2.00187 0 1.64586V0.645096C0 0.289096 0.294988 0 0.658243 0H19.3418Z" />
	</svg>

	<h4 class="vacancies-card__title line-caps-bold-16-15"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h4>

	<?php if ( $cost !== '' ) : ?>
		<div class="vacancies-card__cost paragraph-15-15"><?php echo esc_html( $cost ); ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $conditions ) ) : ?>
		<div class="vacancies-card__list">
			<?php foreach ( $conditions as $condition ) : ?>
				<div class="vacancies-card__list-item line-13-15"><?php echo tolstenko_kses_html( $condition ); ?></div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="vacancies-card__btns">
		<a class="vacancies-card__btn default-btn default-btn--transparent" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Подробнее', 'tolstenko-theme' ); ?></a>
		<?php if ( $btn_text !== '' ) : ?>
			<a class="vacancies-card__btn default-btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
		<?php endif; ?>
	</div>
</article>
