<?php
/**
 * Блок «Одна команда» (.one-team): заголовок, кнопка, статистика.
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'one_team' );

$title = '';
if ( ! empty( $block_attrs['block_one_team_title'] ) ) {
	$title = (string) $block_attrs['block_one_team_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$title = (string) $defaults['title'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_one_team_title_tag', 'h2' );

$btn_text = ! empty( $block_attrs['block_one_team_btn_text'] )
	? (string) $block_attrs['block_one_team_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = ! empty( $block_attrs['block_one_team_btn_url'] )
	? (string) $block_attrs['block_one_team_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
$btn_url = tolstenko_url_or_modal( $btn_url );

$items     = array();
$raw_items = array();
if ( ! empty( $block_attrs['block_one_team_items'] ) && is_array( $block_attrs['block_one_team_items'] ) ) {
	$raw_items = $block_attrs['block_one_team_items'];
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$raw_items = $defaults['items'];
}
foreach ( $raw_items as $row ) {
	if ( ! is_array( $row ) ) {
		continue;
	}
	$value = isset( $row['value'] ) ? trim( (string) $row['value'] ) : '';
	$text  = isset( $row['text'] ) ? trim( (string) $row['text'] ) : '';
	if ( $value === '' && $text === '' ) {
		continue;
	}
	$items[] = array(
		'value' => $value,
		'text'  => $text,
	);
}

if ( $title === '' && $btn_text === '' && empty( $items ) ) {
	return;
}
?>
<section class="one-team section">
	<div class="container">
		<div class="one-team__inner br-30">
			<div class="one-team__left">
				<div class="one-team__left-wrapper">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="one-team__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>

					<?php if ( $btn_text !== '' ) : ?>
						<a class="one-team__btn default-btn" href="<?php echo esc_url( $btn_url ); ?>"><?php echo tolstenko_kses_html( $btn_text ); ?></a>
					<?php endif; ?>
				</div>

				<div class="one-team__svg" aria-hidden="true">
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M4.16675 10H15.0001M10.8334 5L15.2442 9.41074C15.5696 9.73618 15.5696 10.2638 15.2442 10.5893L10.8334 15" stroke-width="2" stroke-linecap="round" />
					</svg>
				</div>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="one-team__list">
					<?php foreach ( $items as $item ) : ?>
						<div class="one-team__list-item">
							<?php if ( $item['value'] !== '' ) : ?>
								<div class="one-team__list-value h2"><?php echo tolstenko_kses_html( $item['value'] ); ?></div>
							<?php endif; ?>
							<?php if ( $item['text'] !== '' ) : ?>
								<p class="one-team__list-text line-13-15"><?php echo tolstenko_kses_html( $item['text'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
