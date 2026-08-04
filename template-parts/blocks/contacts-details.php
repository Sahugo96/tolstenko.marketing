<?php
/**
 * Блок «Реквизиты» (.details): заголовок, пункты, форма.
 * Данные: атрибуты Gutenberg → дефолты блоков (Страница контактов).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'contacts_details' );

$details_title = '';
if ( ! empty( $block_attrs['block_contacts_details_title'] ) ) {
	$details_title = (string) $block_attrs['block_contacts_details_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$details_title = (string) $defaults['title'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_contacts_details_title_tag', 'h2' );

$details_items = array();
if ( ! empty( $block_attrs['block_contacts_details_items'] ) && is_array( $block_attrs['block_contacts_details_items'] ) ) {
	foreach ( $block_attrs['block_contacts_details_items'] as $item ) {
		if ( is_string( $item ) ) {
			$item_text = trim( $item );
		} elseif ( is_array( $item ) ) {
			$item_text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';
		} else {
			$item_text = '';
		}
		if ( $item_text !== '' ) {
			$details_items[] = $item_text;
		}
	}
}
if ( empty( $details_items ) && ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	foreach ( $defaults['items'] as $item ) {
		$item_text = is_string( $item ) ? trim( $item ) : ( is_array( $item ) ? trim( (string) ( $item['text'] ?? '' ) ) : '' );
		if ( $item_text !== '' ) {
			$details_items[] = $item_text;
		}
	}
}

$form_title = '';
if ( ! empty( $block_attrs['block_contacts_details_form_title'] ) ) {
	$form_title = (string) $block_attrs['block_contacts_details_form_title'];
} elseif ( ! empty( $defaults['form_title'] ) ) {
	$form_title = (string) $defaults['form_title'];
}
if ( $form_title === '' ) {
	$form_title = __( 'Свяжитесь с нами', 'tolstenko-theme' );
}

$form_text = '';
if ( isset( $block_attrs['block_contacts_details_form_text'] ) && trim( (string) $block_attrs['block_contacts_details_form_text'] ) !== '' ) {
	$form_text = (string) $block_attrs['block_contacts_details_form_text'];
} elseif ( ! empty( $defaults['form_text'] ) ) {
	$form_text = (string) $defaults['form_text'];
}
if ( $form_text === '' ) {
	$form_text = __( 'Оставьте заявку и мы свяжемся с вами', 'tolstenko-theme' );
}

$form_sc = '[contact-form-7 id="c3a0a63" title="Контакты"]';
?>
<section class="details section">
	<div class="container">
		<div class="details__inner">
			<div class="details__info br-30">
				<?php if ( $details_title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="details__title h2"><?php echo esc_html( $details_title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>

				<div class="details__items">
					<?php foreach ( $details_items as $text ) :
						if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
							continue;
						}
						?>
						<div class="details__item redactor paragraph-15-15">
							<?php echo wp_kses_post( wpautop( $text ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="details__right br-30">
				<div class="details__right-top">
					<h3 class="details__right-title h2"><?php echo esc_html( $form_title ); ?></h3>
					<span class="details__right-text line-caps-bold-15-15"><?php echo esc_html( $form_text ); ?></span>
				</div>
				<div class="details__right-form form">
					<?php echo do_shortcode( $form_sc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
	</div>
</section>
