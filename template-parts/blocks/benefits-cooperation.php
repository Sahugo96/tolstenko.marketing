<?php
/**
 * Блок «Преимущества» (партнёрская секция benefits-cooperation).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'benefits_cooperation' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_benefits_cooperation_title'] )
	? (string) $block_attrs['block_benefits_cooperation_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_benefits_cooperation_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$columns   = array();
$raw_items = ! empty( $block_attrs['block_benefits_cooperation_items'] ) && is_array( $block_attrs['block_benefits_cooperation_items'] )
	? $block_attrs['block_benefits_cooperation_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $col ) {
	if ( ! is_array( $col ) ) {
		continue;
	}
	$list = array();
	foreach ( (array) ( $col['list'] ?? array() ) as $elem ) {
		if ( ! is_array( $elem ) ) {
			continue;
		}
		$et = trim( (string) ( $elem['title'] ?? '' ) );
		$ex = trim( (string) ( $elem['text'] ?? '' ) );
		if ( $et === '' && $ex === '' ) {
			continue;
		}
		$list[] = array(
			'title' => $et,
			'text'  => $ex,
		);
	}
	$btn_text = trim( (string) ( $col['btn_text'] ?? '' ) );
	$btn_url  = trim( (string) ( $col['btn_url'] ?? '' ) );
	if ( empty( $list ) && $btn_text === '' ) {
		continue;
	}
	$columns[] = array(
		'list'     => $list,
		'btn_text' => $btn_text,
		'btn_url'  => $btn_url,
	);
}

if ( $title === '' && empty( $columns ) ) {
	return;
}
?>
<section class="benefits-cooperation section">
	<div class="container">
		<div class="benefits-cooperation__inner">
			<div class="benefits-cooperation__top section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="benefits-cooperation__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $columns ) ) : ?>
				<div class="benefits-cooperation__wrapper">
					<?php foreach ( $columns as $col ) : ?>
						<?php
						$btn_url = $col['btn_url'];
						if ( function_exists( 'tolstenko_url_or_modal' ) ) {
							$btn_url = tolstenko_url_or_modal( $btn_url );
						} elseif ( $btn_url === '' || $btn_url === '#modal' ) {
							$btn_url = '#modal';
						}
						$btn_is_modal = ( $btn_url === '#modal' );
						?>
						<div class="benefits-cooperation__item">
							<?php if ( ! empty( $col['list'] ) ) : ?>
								<div class="benefits-cooperation__list">
									<?php foreach ( $col['list'] as $elem ) : ?>
										<div class="benefits-cooperation__elem">
											<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
												<path class="cyrcle" d="M12.5007 22.9168C13.8688 22.9185 15.2239 22.6499 16.4879 22.1263C17.7519 21.6027 18.9 20.8345 19.8663 19.8658C20.8349 18.8996 21.6031 17.7514 22.1267 16.4874C22.6503 15.2234 22.919 13.8684 22.9173 12.5002C22.919 11.132 22.6503 9.77696 22.1267 8.51293C21.6031 7.2489 20.8349 6.10078 19.8663 5.13455C18.9 4.16588 17.7519 3.39768 16.4879 2.87408C15.2239 2.35049 13.8688 2.08182 12.5007 2.0835C11.1325 2.08182 9.77744 2.35049 8.51341 2.87408C7.24938 3.39768 6.10127 4.16588 5.13503 5.13455C4.16637 6.10078 3.39817 7.2489 2.87457 8.51293C2.35098 9.77696 2.08231 11.132 2.08399 12.5002C2.08231 13.8684 2.35098 15.2234 2.87457 16.4874C3.39817 17.7514 4.16637 18.8996 5.13503 19.8658C6.10127 20.8345 7.24938 21.6027 8.51341 22.1263C9.77744 22.6499 11.1325 22.9185 12.5007 22.9168Z" />
												<path class="check" d="M8.33398 12.5L11.459 15.625L17.709 9.375" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
											</svg>
											<div class="benefits-cooperation__elem-wrapper">
												<?php if ( $elem['title'] !== '' ) : ?>
													<span class="benefits-cooperation__elem-title paragraph-15-25"><?php echo tolstenko_kses_html( $elem['title'] ); ?></span>
												<?php endif; ?>
												<?php if ( $elem['text'] !== '' ) : ?>
													<span class="benefits-cooperation__elem-text line-13-15"><?php echo tolstenko_kses_html( $elem['text'] ); ?></span>
												<?php endif; ?>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php if ( $col['btn_text'] !== '' ) : ?>
								<a
									class="benefits-cooperation__btn default-btn default-btn--tg line-caps-bold-16-15"
									href="<?php echo esc_url( $btn_url ); ?>">
									<?php echo esc_html( $col['btn_text'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
