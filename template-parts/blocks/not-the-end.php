<?php
/**
 * Блок «Ещё не конец» (not-the-end): тизер комментариев.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'not_the_end' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_not_the_end_title'] )
	? (string) $block_attrs['block_not_the_end_title']
	: (string) ( $defaults['title'] ?? '' );

$text = isset( $block_attrs['block_not_the_end_text'] ) && trim( (string) $block_attrs['block_not_the_end_text'] ) !== ''
	? (string) $block_attrs['block_not_the_end_text']
	: (string) ( $defaults['text'] ?? '' );

$icon_id = isset( $block_attrs['block_not_the_end_icon'] ) ? (int) $block_attrs['block_not_the_end_icon'] : 0;
if ( $icon_id <= 0 ) {
	$icon_id = isset( $defaults['icon'] ) ? (int) $defaults['icon'] : 0;
}

$avatars     = array();
$raw_avatars = isset( $block_attrs['block_not_the_end_avatars'] ) && is_array( $block_attrs['block_not_the_end_avatars'] ) && ! empty( $block_attrs['block_not_the_end_avatars'] )
	? $block_attrs['block_not_the_end_avatars']
	: (array) ( $defaults['avatars'] ?? array() );
foreach ( $raw_avatars as $it ) {
	if ( is_numeric( $it ) ) {
		$avatars[] = array( 'image' => (int) $it );
		continue;
	}
	if ( ! is_array( $it ) ) {
		continue;
	}
	$avatars[] = array(
		'image' => isset( $it['image'] ) ? (int) $it['image'] : ( isset( $it['id'] ) ? (int) $it['id'] : 0 ),
	);
}

$btn_text = ! empty( $block_attrs['block_not_the_end_btn_text'] )
	? (string) $block_attrs['block_not_the_end_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = isset( $block_attrs['block_not_the_end_btn_url'] ) && trim( (string) $block_attrs['block_not_the_end_btn_url'] ) !== ''
	? (string) $block_attrs['block_not_the_end_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );

if ( $title === '' && $text === '' && $btn_text === '' && empty( $avatars ) && $icon_id <= 0 ) {
	return;
}

$render_attachment_ico = static function ( $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	if ( $attachment_id <= 0 ) {
		return false;
	}
	$path = get_attached_file( $attachment_id );
	if ( $path && is_readable( $path ) && preg_match( '/\.svg$/i', $path ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$svg = file_get_contents( $path );
		if ( $svg ) {
			echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return true;
		}
	}
	$url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="" loading="lazy" decoding="async">';
		return true;
	}
	return false;
};

$card_tag   = $btn_url !== '' ? 'a' : 'div';
$card_attrs = '';
if ( $btn_url !== '' ) {
	$href       = isset( $btn_url[0] ) && '#' === $btn_url[0] ? esc_attr( $btn_url ) : esc_url( $btn_url );
	$card_attrs = ' href="' . $href . '"';
}
?>
<section class="not-the-end section">
	<div class="container">
		<<?php echo esc_attr( $card_tag ); ?> class="not-the-end__card"<?php echo $card_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>>
			<span class="not-the-end__icon">
				<?php
				if ( ! $render_attachment_ico( $icon_id ) ) :
					?>
					<svg viewBox="0 0 24 24" fill="none" stroke-width="2" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-8.5 8.5 8.5 8.5 0 01-3.8-.9L3 21l1.9-5.7a8.5 8.5 0 013.3-11.5 8.38 8.38 0 018.8 0A8.38 8.38 0 0121 11.5z"/><circle cx="8.5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="12" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="15.5" cy="12" r="1" fill="currentColor" stroke="none"/></svg>
				<?php endif; ?>
			</span>

			<?php if ( $title !== '' ) : ?>
				<div class="not-the-end__head">
					<p class="not-the-end__title"><?php echo tolstenko_kses_html( $title ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $text !== '' ) : ?>
				<p class="not-the-end__desc"><?php echo tolstenko_kses_html( $text ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $avatars ) ) : ?>
				<div class="not-the-end__chat" aria-hidden="true">
					<?php foreach ( $avatars as $index => $ava ) : ?>
						<?php
						$ava_id  = (int) ( $ava['image'] ?? 0 );
						$ava_url = $ava_id ? wp_get_attachment_image_url( $ava_id, 'thumbnail' ) : '';
						$row_mod = ( $index % 2 === 1 ) ? ' not-the-end__row--reverse' : '';
						?>
						<div class="not-the-end__row<?php echo esc_attr( $row_mod ); ?>">
							<span class="not-the-end__ava">
								<?php if ( $ava_url ) : ?>
									<img src="<?php echo esc_url( $ava_url ); ?>" alt="" loading="lazy" decoding="async">
								<?php endif; ?>
							</span>
							<span class="not-the-end__bubble"><i></i><i></i></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $btn_text !== '' ) : ?>
				<span class="not-the-end__btn">
					<?php echo esc_html( $btn_text ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke-width="3" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
				</span>
			<?php endif; ?>
		</<?php echo esc_attr( $card_tag ); ?>>
	</div>
</section>
