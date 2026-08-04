<?php
/**
 * Блок «Форматы сотрудничества» (пресс-портрет).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'collaboration' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_collaboration_title'] )
	? (string) $block_attrs['block_collaboration_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_collaboration_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_collaboration_items'] ) && is_array( $block_attrs['block_collaboration_items'] )
	? $block_attrs['block_collaboration_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$items[] = $t;
	}
}

$btn_text = ! empty( $block_attrs['block_collaboration_btn_text'] )
	? (string) $block_attrs['block_collaboration_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = ! empty( $block_attrs['block_collaboration_btn_url'] )
	? (string) $block_attrs['block_collaboration_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
if ( function_exists( 'tolstenko_url_or_modal' ) ) {
	$btn_url = tolstenko_url_or_modal( $btn_url );
} elseif ( $btn_url === '' || $btn_url === '#modal' ) {
	$btn_url = '#modal';
}
$btn_is_modal = ( $btn_url === '#modal' );

$img_id = ! empty( $block_attrs['block_collaboration_image'] )
	? (int) $block_attrs['block_collaboration_image']
	: (int) ( $defaults['image'] ?? 0 );
$img_url = $img_id ? (string) wp_get_attachment_image_url( $img_id, 'large' ) : '';
$img_alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';

if ( $title === '' && empty( $items ) && $img_url === '' && $btn_text === '' ) {
	return;
}
?>
<section class="collaboration section">
	<div class="container">
		<div class="collaboration__inner br-30">
			<?php if ( $img_url !== '' ) : ?>
				<div class="collaboration__img">
					<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>

			<div class="collaboration__right">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="collaboration__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>

				<?php if ( ! empty( $items ) ) : ?>
					<div class="collaboration__items">
						<?php foreach ( $items as $item_text ) : ?>
							<div class="collaboration__item">
								<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
									<path d="M5 12H18M13 6L18.2929 11.2929C18.6834 11.6834 18.6834 12.3166 18.2929 12.7071L13 18" stroke-width="2" stroke-linecap="round" />
								</svg>
								<span class="collaboration__item-text line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item_text ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $btn_text !== '' ) : ?>
					<a
						class="collaboration__btn default-btn line-caps-bold-13-15"
						href="<?php echo esc_url( $btn_url ); ?>">
						<?php echo esc_html( $btn_text ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
