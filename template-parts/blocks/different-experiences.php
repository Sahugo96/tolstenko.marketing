<?php
/**
 * Блок «Разный опыт»: заголовок, текст, список, кнопки Telegram / заявка.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir   = get_template_directory();
$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'different_experiences' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_different_experiences_title'] )
	? (string) $block_attrs['block_different_experiences_title']
	: (string) ( $defaults['title'] ?? '' );
$text = isset( $block_attrs['block_different_experiences_text'] ) && trim( (string) $block_attrs['block_different_experiences_text'] ) !== ''
	? (string) $block_attrs['block_different_experiences_text']
	: (string) ( $defaults['text'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_different_experiences_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items = array();
$raw_items = array();
if ( ! empty( $block_attrs['block_different_experiences_items'] ) && is_array( $block_attrs['block_different_experiences_items'] ) ) {
	$raw_items = $block_attrs['block_different_experiences_items'];
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$raw_items = $defaults['items'];
}
foreach ( $raw_items as $it ) {
	$t = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $t !== '' ) {
		$items[] = $t;
	}
}

$tg_text = ! empty( $block_attrs['block_different_experiences_tg_text'] )
	? (string) $block_attrs['block_different_experiences_tg_text']
	: (string) ( $defaults['tg_text'] ?? '' );
$tg_url = ! empty( $block_attrs['block_different_experiences_tg_url'] )
	? (string) $block_attrs['block_different_experiences_tg_url']
	: (string) ( $defaults['tg_url'] ?? '' );
if ( $tg_url === '' && ! empty( $site['telegram'] ) ) {
	$tg_url = (string) $site['telegram'];
}

$modal_text = ! empty( $block_attrs['block_different_experiences_modal_text'] )
	? (string) $block_attrs['block_different_experiences_modal_text']
	: (string) ( $defaults['modal_text'] ?? '' );
$modal_url = ! empty( $block_attrs['block_different_experiences_modal_url'] )
	? (string) $block_attrs['block_different_experiences_modal_url']
	: (string) ( $defaults['modal_url'] ?? '' );
$modal_url = tolstenko_url_or_modal( $modal_url );

if ( $title === '' && $text === '' && empty( $items ) ) {
	return;
}
?>
<section class="different-experiences section">
	<div class="container">
		<div class="different-experiences__inner br-30">
			<div class="different-experiences__top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="different-experiences__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="different-experiences__text paragraph-15-15"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="different-experiences__list">
					<?php foreach ( $items as $item_text ) : ?>
						<div class="different-experiences__list-item">
							<p class="different-experiences__list-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ( $tg_url !== '' && $tg_text !== '' ) || $modal_text !== '' ) : ?>
				<div class="different-experiences__btns">
					<?php if ( $tg_url !== '' && $tg_text !== '' ) : ?>
						<a class="different-experiences__btn default-btn" href="<?php echo esc_url( $tg_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $tg_text ); ?>
							<?php
							$tg_icon = $theme_dir . '/assets/img/telegram-icon.svg';
							if ( is_readable( $tg_icon ) ) {
								// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- theme SVG asset.
								echo file_get_contents( $tg_icon ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							}
							?>
						</a>
					<?php endif; ?>
					<?php if ( $modal_text !== '' ) : ?>
						<a class="different-experiences__btn different-experiences__btn--modal default-btn" href="<?php echo esc_url( $modal_url ); ?>">
							<?php echo esc_html( $modal_text ); ?>
							<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M21.2929 13.7071C21.6834 14.0976 22.3166 14.0976 22.7071 13.7071C23.0976 13.3166 23.0976 12.6834 22.7071 12.2929L22 13L21.2929 13.7071ZM4.09202 15.782L4.54601 14.891L4.54601 14.891L4.09202 15.782ZM3.21799 14.908L2.32698 15.362V15.362L3.21799 14.908ZM20.362 16.673C20.8541 16.4223 21.0497 15.8201 20.799 15.328C20.5483 14.8359 19.9461 14.6403 19.454 14.891L19.908 15.782L20.362 16.673ZM4.09202 3.21799L4.54601 4.109L4.54601 4.109L4.09202 3.21799ZM3.21799 4.09202L4.10899 4.54601L4.10899 4.54601L3.21799 4.09202ZM18 22C18.5523 22 19 21.5523 19 21C19 20.4477 18.5523 20 18 20L18 21L18 22ZM6 20C5.44772 20 5 20.4477 5 21C5 21.5523 5.44772 22 6 22L6 21L6 20ZM9 4C9.55228 4 10 3.55229 10 3C10 2.44772 9.55228 2 9 2L9 3L9 4ZM20.2353 7.11765L19.2353 7.11765C19.2353 8.83948 17.8395 10.2353 16.1176 10.2353L16.1176 11.2353L16.1176 12.2353C18.944 12.2353 21.2353 9.94405 21.2353 7.11765L20.2353 7.11765ZM16.1176 11.2353L16.1176 10.2353C14.3958 10.2353 13 8.83948 13 7.11765L12 7.11765L11 7.11765C11 9.94405 13.2912 12.2353 16.1176 12.2353L16.1176 11.2353ZM12 7.11765L13 7.11765C13 5.39582 14.3958 4 16.1176 4L16.1176 3L16.1176 2C13.2912 2 11 4.29125 11 7.11765L12 7.11765ZM16.1176 3L16.1176 4C17.8395 4 19.2353 5.39582 19.2353 7.11765L20.2353 7.11765L21.2353 7.11765C21.2353 4.29125 18.944 2 16.1176 2L16.1176 3ZM19.0588 10.0588L18.3517 10.7659L21.2929 13.7071L22 13L22.7071 12.2929L19.7659 9.35172L19.0588 10.0588ZM17.8 16L17.8 15L12 15L12 16L12 17L17.8 17L17.8 16ZM12 16L12 15L6.2 15L6.2 16L6.2 17L12 17L12 16ZM3 12.8L4 12.8L4 6.2L3 6.2L2 6.2L2 12.8L3 12.8ZM6.2 16L6.2 15C5.62345 15 5.25117 14.9992 4.96784 14.9761C4.69617 14.9539 4.59545 14.9162 4.54601 14.891L4.09202 15.782L3.63803 16.673C4.01641 16.8658 4.40963 16.9371 4.80497 16.9694C5.18864 17.0008 5.65645 17 6.2 17L6.2 16ZM3 12.8L2 12.8C2 13.3436 1.99922 13.8114 2.03057 14.195C2.06287 14.5904 2.13419 14.9836 2.32698 15.362L3.21799 14.908L4.10899 14.454C4.0838 14.4045 4.04612 14.3038 4.02393 14.0322C4.00078 13.7488 4 13.3766 4 12.8L3 12.8ZM4.09202 15.782L4.54601 14.891C4.35785 14.7951 4.20487 14.6422 4.10899 14.454L3.21799 14.908L2.32698 15.362C2.6146 15.9265 3.07354 16.3854 3.63803 16.673L4.09202 15.782ZM17.8 16L17.8 17C18.3436 17 18.8114 17.0008 19.195 16.9694C19.5904 16.9371 19.9836 16.8658 20.362 16.673L19.908 15.782L19.454 14.891C19.4045 14.9162 19.3038 14.9539 19.0322 14.9761C18.7488 14.9992 18.3766 15 17.8 15L17.8 16ZM6.2 3L6.2 2C5.65645 2 5.18864 1.99922 4.80497 2.03057C4.40963 2.06287 4.01641 2.13419 3.63803 2.32698L4.09202 3.21799L4.54601 4.109C4.59545 4.0838 4.69617 4.04612 4.96784 4.02393C5.25117 4.00078 5.62345 4 6.2 4L6.2 3ZM3 6.2L4 6.2C4 5.62345 4.00078 5.25118 4.02393 4.96784C4.04612 4.69617 4.0838 4.59546 4.10899 4.54601L3.21799 4.09202L2.32698 3.63803C2.13419 4.01641 2.06287 4.40963 2.03057 4.80497C1.99922 5.18865 2 5.65645 2 6.2L3 6.2ZM4.09202 3.21799L3.63803 2.32698C3.07354 2.6146 2.6146 3.07354 2.32698 3.63803L3.21799 4.09202L4.10899 4.54601C4.20487 4.35785 4.35785 4.20487 4.54601 4.109L4.09202 3.21799ZM12 16L11 16L11 21L12 21L13 21L13 16L12 16ZM12 21L12 22L18 22L18 21L18 20L12 20L12 21ZM12 21L12 20L6 20L6 21L6 22L12 22L12 21ZM6.2 3L6.2 4L9 4L9 3L9 2L6.2 2L6.2 3Z" />
							</svg>
						</a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
