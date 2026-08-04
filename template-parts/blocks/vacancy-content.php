<?php
/**
 * Блок «Контент вакансии».
 * Данные: атрибуты Gutenberg → «Шаблон вакансии».
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();
$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'vacancy_content' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = '';
if ( ! empty( $block_attrs['block_vacancy_content_title'] ) ) {
	$title = (string) $block_attrs['block_vacancy_content_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
} elseif ( $post_id ) {
	$title = (string) get_the_title( $post_id );
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_vacancy_content_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$content = isset( $block_attrs['block_vacancy_content_html'] ) && trim( (string) $block_attrs['block_vacancy_content_html'] ) !== ''
	? (string) $block_attrs['block_vacancy_content_html']
	: (string) ( $defaults['content'] ?? '' );

$apply_btn = ! empty( $block_attrs['block_vacancy_content_apply_text'] )
	? (string) $block_attrs['block_vacancy_content_apply_text']
	: (string) ( $defaults['apply_text'] ?? '' );
if ( $apply_btn === '' ) {
	$apply_btn = __( 'Отправить заявку', 'tolstenko-theme' );
}
$apply_url = tolstenko_url_or_modal(
	! empty( $block_attrs['block_vacancy_content_apply_url'] )
		? (string) $block_attrs['block_vacancy_content_apply_url']
		: (string) ( $defaults['apply_url'] ?? '' )
);

$name = ! empty( $block_attrs['block_vacancy_content_sidebar_name'] )
	? (string) $block_attrs['block_vacancy_content_sidebar_name']
	: (string) ( $defaults['sidebar_name'] ?? '' );
$text = isset( $block_attrs['block_vacancy_content_sidebar_text'] ) && trim( (string) $block_attrs['block_vacancy_content_sidebar_text'] ) !== ''
	? (string) $block_attrs['block_vacancy_content_sidebar_text']
	: (string) ( $defaults['sidebar_text'] ?? '' );
$sidebar_btn = ! empty( $block_attrs['block_vacancy_content_sidebar_btn'] )
	? (string) $block_attrs['block_vacancy_content_sidebar_btn']
	: (string) ( $defaults['sidebar_btn'] ?? '' );
if ( $sidebar_btn === '' ) {
	$sidebar_btn = __( 'Бесплатный аудит', 'tolstenko-theme' );
}
$sidebar_btn_url = tolstenko_url_or_modal(
	! empty( $block_attrs['block_vacancy_content_sidebar_btn_url'] )
		? (string) $block_attrs['block_vacancy_content_sidebar_btn_url']
		: (string) ( $defaults['sidebar_btn_url'] ?? '' )
);

$photo_id = ! empty( $block_attrs['block_vacancy_content_sidebar_photo'] )
	? (int) $block_attrs['block_vacancy_content_sidebar_photo']
	: (int) ( $defaults['sidebar_photo'] ?? 0 );
$photo_url = $photo_id ? (string) wp_get_attachment_image_url( $photo_id, 'medium' ) : '';
$photo_alt = $photo_id ? (string) get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) : '';

$has_contact_socials_rgb = false;
if ( function_exists( 'tolstenko_get_contact_data' ) ) {
	$cd = tolstenko_get_contact_data( true );
	$has_contact_socials_rgb = ! empty( $cd['socials_rgb'] ) && is_array( $cd['socials_rgb'] );
}

$has_sidebar = ( $photo_url !== '' || $name !== '' || $text !== '' || $has_contact_socials_rgb );

if ( $title === '' && $content === '' && ! $has_sidebar ) {
	return;
}
?>
<section class="single-vacancy section">
	<div class="container">
		<div class="single-vacancy__wrapper">
			<div class="single-vacancy__content">
				<div class="single-vacancy__content-blocks">
					<div class="single-vacancy__content-article br-30">
						<?php if ( $title !== '' ) : ?>
							<<?php echo esc_attr( $title_tag ); ?> class="single-vacancy__content-article-title lead-20-25"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
						<?php endif; ?>

						<div class="single-vacancy__content-article-items redactor">
							<?php if ( $content !== '' ) : ?>
								<?php echo wp_kses_post( $content ); ?>
							<?php endif; ?>
							<a class="single-vacancy__btn default-btn" href="<?php echo esc_url( $apply_url ); ?>"><?php echo esc_html( $apply_btn ); ?></a>
						</div>
					</div>
				</div>
			</div>

			<?php if ( $has_sidebar ) : ?>
				<div class="single-vacancy__right">
					<div class="single-vacancy__right-info">
						<div class="single-vacancy__right-wrapper">
							<?php if ( $photo_url !== '' ) : ?>
								<img class="single-vacancy__right-photo" src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $photo_alt ); ?>" loading="lazy" decoding="async">
							<?php endif; ?>
							<?php if ( $name !== '' ) : ?>
								<div class="single-vacancy__right-name line-caps-bold-13-15"><?php echo esc_html( $name ); ?></div>
							<?php endif; ?>
							<?php if ( $text !== '' ) : ?>
								<div class="single-vacancy__right-text paragraph-15-25"><?php echo tolstenko_kses_html( $text ); ?></div>
							<?php endif; ?>
							<?php get_template_part( 'modules/socials/socials-rgb' ); ?>
							<a class="free-audit__btn default-btn default-btn--red" href="<?php echo esc_url( $sidebar_btn_url ); ?>"><?php echo esc_html( $sidebar_btn ); ?></a>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
