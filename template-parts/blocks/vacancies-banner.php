<?php
/**
 * Блок «Баннер вакансий»: заголовок, текст, изображение.
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'vacancies_banner' );

$title = '';
if ( ! empty( $block_attrs['block_vacancies_banner_title'] ) ) {
	$title = (string) $block_attrs['block_vacancies_banner_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_vacancies_banner_text'] ) && trim( (string) $block_attrs['block_vacancies_banner_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_vacancies_banner_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$image_id = 0;
if ( ! empty( $block_attrs['block_vacancies_banner_image'] ) ) {
	$image_id = (int) $block_attrs['block_vacancies_banner_image'];
} elseif ( ! empty( $defaults['image'] ) ) {
	$image_id = (int) $defaults['image'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_vacancies_banner_title_tag', 'h1' );

$image_url = $image_id > 0 ? (string) wp_get_attachment_image_url( $image_id, 'full' ) : '';
$image_alt = $image_id > 0 ? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';

if ( $title === '' && $text === '' && $image_url === '' ) {
	return;
}
?>
<section class="vacancies-banner section">
	<div class="container">
		<div class="vacancies-banner__inner">
			<?php if ( $title !== '' || $text !== '' ) : ?>
				<div class="vacancies-banner__top section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="vacancies-banner__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $text !== '' ) : ?>
						<p class="vacancies-banner__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $image_url !== '' ) : ?>
				<div class="vacancies-banner__img">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt !== '' ? $image_alt : $title ); ?>">
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
