<?php
/**
 * Блок «Главный баннер» (.hero): заголовок, текст, список, CTA, персона, изображение.
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$category_term = get_query_var( 'tolstenko_service_category_term' );
if ( $category_term instanceof WP_Term && function_exists( 'tolstenko_sc_resolve_category_block_attributes' ) ) {
	$block_attrs = array_merge(
		$block_attrs,
		tolstenko_sc_resolve_category_block_attributes( 'main_hero', $category_term, '_tolstenko_sc_main_hero' )
	);
}

$defaults = tolstenko_block_defaults( 'main_hero' );

$title = '';
if ( ! empty( $block_attrs['block_main_hero_title'] ) ) {
	$title = (string) $block_attrs['block_main_hero_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$text = '';
if ( isset( $block_attrs['block_main_hero_text'] ) && trim( (string) $block_attrs['block_main_hero_text'] ) !== '' ) {
	$text = (string) $block_attrs['block_main_hero_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$text = (string) $defaults['text'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_main_hero_title_tag', 'h1' );

$btn_text = '';
if ( ! empty( $block_attrs['block_main_hero_btn_text'] ) ) {
	$btn_text = (string) $block_attrs['block_main_hero_btn_text'];
} elseif ( ! empty( $defaults['btn_text'] ) ) {
	$btn_text = (string) $defaults['btn_text'];
}

$show_promo_raw = isset( $block_attrs['block_main_hero_show_promo'] ) ? trim( (string) $block_attrs['block_main_hero_show_promo'] ) : '';
if ( $show_promo_raw === '1' || $show_promo_raw === '0' ) {
	$show_promo = $show_promo_raw === '1';
} else {
	$show_promo = ! empty( $defaults['show_promo'] );
}

$promo_text = '';
if ( isset( $block_attrs['block_main_hero_promo_text'] ) && trim( (string) $block_attrs['block_main_hero_promo_text'] ) !== '' ) {
	$promo_text = (string) $block_attrs['block_main_hero_promo_text'];
} elseif ( ! empty( $defaults['promo_text'] ) ) {
	$promo_text = (string) $defaults['promo_text'];
}

$person_name = '';
if ( ! empty( $block_attrs['block_main_hero_person_name'] ) ) {
	$person_name = (string) $block_attrs['block_main_hero_person_name'];
} elseif ( ! empty( $defaults['person_name'] ) ) {
	$person_name = (string) $defaults['person_name'];
}

$person_position = '';
if ( ! empty( $block_attrs['block_main_hero_person_position'] ) ) {
	$person_position = (string) $block_attrs['block_main_hero_person_position'];
} elseif ( ! empty( $defaults['person_position'] ) ) {
	$person_position = (string) $defaults['person_position'];
}

$image_url = '';
$image_id  = isset( $block_attrs['block_main_hero_image'] ) ? (int) $block_attrs['block_main_hero_image'] : 0;
if ( $image_id <= 0 && ! empty( $defaults['image'] ) ) {
	$image_id = (int) $defaults['image'];
}
if ( $image_id > 0 ) {
	$image_url = (string) wp_get_attachment_image_url( $image_id, 'full' );
}

$present_url = '';
$present_id  = isset( $block_attrs['block_main_hero_present_image'] ) ? (int) $block_attrs['block_main_hero_present_image'] : 0;
if ( $present_id <= 0 && ! empty( $defaults['present_image'] ) ) {
	$present_id = (int) $defaults['present_image'];
}
if ( $present_id > 0 ) {
	$present_url = (string) wp_get_attachment_image_url( $present_id, 'medium' );
}

$items = array();
if ( ! empty( $block_attrs['block_main_hero_items'] ) && is_array( $block_attrs['block_main_hero_items'] ) ) {
	foreach ( $block_attrs['block_main_hero_items'] as $item ) {
		if ( is_string( $item ) ) {
			$item_text = trim( $item );
		} elseif ( is_array( $item ) ) {
			$item_text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';
		} else {
			$item_text = '';
		}
		if ( $item_text !== '' ) {
			$items[] = $item_text;
		}
	}
}
if ( empty( $items ) && ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	foreach ( $defaults['items'] as $item ) {
		$item_text = is_string( $item ) ? trim( $item ) : ( is_array( $item ) ? trim( (string) ( $item['text'] ?? '' ) ) : '' );
		if ( $item_text !== '' ) {
			$items[] = $item_text;
		}
	}
}

if (
	$title === ''
	&& $text === ''
	&& empty( $items )
	&& $btn_text === ''
	&& $image_url === ''
	&& $person_name === ''
	&& $person_position === ''
) {
	return;
}

$list_svg = '<svg class="hero__list-svg" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M15 28.75C22.5941 28.75 28.75 22.5941 28.75 15C28.75 7.40588 22.5941 1.25 15 1.25C7.40588 1.25 1.25 7.40588 1.25 15C1.25 22.5941 7.40588 28.75 15 28.75ZM15.6463 10.1463C15.8396 9.95313 16.1017 9.84466 16.375 9.84466C16.6483 9.84466 16.9104 9.95313 17.1037 10.1463L21.2288 14.2713C21.4219 14.4646 21.5303 14.7267 21.5303 15C21.5303 15.2733 21.4219 15.5354 21.2288 15.7287L17.1037 19.8537C17.0093 19.9551 16.8955 20.0363 16.769 20.0927C16.6425 20.1491 16.5059 20.1794 16.3675 20.1818C16.229 20.1843 16.0915 20.1588 15.9631 20.1069C15.8346 20.0551 15.718 19.9779 15.6201 19.8799C15.5221 19.782 15.4449 19.6654 15.3931 19.5369C15.3412 19.4085 15.3157 19.271 15.3182 19.1325C15.3206 18.9941 15.3509 18.8575 15.4073 18.731C15.4637 18.6045 15.5449 18.4907 15.6463 18.3963L18.0112 16.0312H9.5C9.2265 16.0312 8.96419 15.9226 8.7708 15.7292C8.5774 15.5358 8.46875 15.2735 8.46875 15C8.46875 14.7265 8.5774 14.4642 8.7708 14.2708C8.96419 14.0774 9.2265 13.9688 9.5 13.9688H18.0112L15.6463 11.6037C15.4531 11.4104 15.3447 11.1483 15.3447 10.875C15.3447 10.6017 15.4531 10.3396 15.6463 10.1463Z" /></svg>';
?>
<section class="hero">
	<div class="container">
		<div class="hero__inner">
			<?php if ( $title !== '' ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="hero__title h1"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>

			<?php if ( $text !== '' ) : ?>
				<div class="hero__text line-13-15"><?php echo tolstenko_kses_html( $text ); ?></div>
			<?php endif; ?>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="hero__list">
					<?php foreach ( $items as $item_text ) : ?>
						<div class="hero__list-item">
							<?php echo $list_svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG ?>
							<p class="hero__list-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $btn_text !== '' || ( $show_promo && $promo_text !== '' ) ) : ?>
				<div class="hero__bottom">
					<?php if ( $btn_text !== '' ) : ?>
						<a
							class="hero__btn default-btn default-btn--huge"
							href="#modal"
						><?php echo tolstenko_kses_html( $btn_text ); ?></a>
					<?php endif; ?>

					<?php if ( $show_promo && $promo_text !== '' ) : ?>
						<div class="hero__bottom-text line-13-15"><?php echo tolstenko_kses_html( $promo_text ); ?></div>
						<?php if ( $present_url !== '' ) : ?>
							<img
								class="hero__bottom-present"
								src="<?php echo esc_url( $present_url ); ?>"
								alt=""
							>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( $image_url !== '' ) : ?>
				<div class="hero__img">
					<img src="<?php echo esc_url( $image_url ); ?>" alt="">
				</div>
			<?php endif; ?>

			<?php if ( $person_name !== '' || $person_position !== '' ) : ?>
				<div class="hero__person">
					<div class="hero__person-wrapper">
						<?php if ( $person_name !== '' ) : ?>
							<span class="hero__person-name line-caps-bold-13-15"><?php echo esc_html( $person_name ); ?></span>
						<?php endif; ?>
						<?php if ( $person_position !== '' ) : ?>
							<span class="hero__person-position paragraph-13-20"><?php echo esc_html( $person_position ); ?></span>
						<?php endif; ?>
					</div>
					<div class="hero__person-svg">
						<svg viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M7.13293 0L9.30186 4.51473L14.2659 5.18237L10.6423 8.64027L11.5413 13.5676L7.13293 11.19L2.72455 13.5676L3.62354 8.64027L1.04904e-05 5.18237L4.96401 4.51473L7.13293 0Z" />
						</svg>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
