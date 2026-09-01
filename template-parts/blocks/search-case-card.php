<?php
/**
 * Карточка кейса для результатов поиска.
 * Внешний вид — 1в1 как карточка «Акции» (actions-section__item),
 * но заголовок и текст берутся из данных кейса (case_title / case_text).
 *
 * Query var: tolstenko_search_case_post (WP_Post)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_query_var( 'tolstenko_search_case_post' );
if ( ! $post instanceof WP_Post || $post->post_type !== 'case' ) {
	return;
}

$post_id = (int) $post->ID;
$data    = function_exists( 'tolstenko_get_case_card_data' )
	? tolstenko_get_case_card_data( $post_id )
	: array();

$title     = (string) ( $data['title'] ?? get_the_title( $post ) );
$text      = (string) ( $data['text'] ?? '' );
$link      = (string) ( $data['link'] ?? get_permalink( $post_id ) );
$image_url = (string) ( $data['image_url'] ?? '' );
$image_alt = (string) ( $data['image_alt'] ?? $title );

if ( $image_url === '' ) {
	$image_url = tolstenko_get_card_placeholder_image_url( 'large' );
	$image_alt = $title;
}
?>
<article class="actions-section__item fade-in-element">
	<div class="actions-section__link">
		<a class="actions-section__img" href="<?php echo esc_url( $link ); ?>">
			<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
		</a>
		<div class="actions-section__wrapper">
			<a class="actions-section__title line-caps-bold-16-15" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
			<?php if ( $text !== '' ) : ?>
				<div class="actions-section__text"><?php echo tolstenko_kses_html( $text ); ?></div>
			<?php endif; ?>
		</div>
		<div class="actions-section__btns">
			<a class="actions-section__btn default-btn" href="<?php echo esc_url( $link ); ?>">
				<?php esc_html_e( 'Подробнее', 'tolstenko-theme' ); ?>
			</a>
			<a class="actions-section__btn default-btn" href="#modal">
				<?php esc_html_e( 'Консультация', 'tolstenko-theme' ); ?>
			</a>
		</div>
	</div>
</article>
<?php
set_query_var( 'tolstenko_search_case_post', null );