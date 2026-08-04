<?php
/**
 * Блок «Образование» (пресс-портрет, aducation).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'aducation' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_aducation_title'] )
	? (string) $block_attrs['block_aducation_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_aducation_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$items     = array();
$raw_items = ! empty( $block_attrs['block_aducation_items'] ) && is_array( $block_attrs['block_aducation_items'] )
	? $block_attrs['block_aducation_items']
	: (array) ( $defaults['items'] ?? array() );
foreach ( $raw_items as $it ) {
	if ( ! is_array( $it ) ) {
		continue;
	}
	$row = array(
		'year'        => trim( (string) ( $it['year'] ?? '' ) ),
		'type'        => trim( (string) ( $it['type'] ?? '' ) ),
		'title'       => trim( (string) ( $it['title'] ?? '' ) ),
		'speciality'  => trim( (string) ( $it['speciality'] ?? '' ) ),
	);
	if ( $row['year'] === '' && $row['title'] === '' && $row['type'] === '' ) {
		continue;
	}
	$items[] = $row;
}

$images    = array();
$raw_imgs  = ! empty( $block_attrs['block_aducation_images'] ) && is_array( $block_attrs['block_aducation_images'] )
	? $block_attrs['block_aducation_images']
	: (array) ( $defaults['images'] ?? array() );
foreach ( $raw_imgs as $it ) {
	$img_id = 0;
	if ( is_array( $it ) ) {
		$img_id = ! empty( $it['image'] ) ? (int) $it['image'] : ( ! empty( $it['id'] ) ? (int) $it['id'] : 0 );
	} else {
		$img_id = (int) $it;
	}
	if ( $img_id <= 0 ) {
		continue;
	}
	$url = (string) wp_get_attachment_image_url( $img_id, 'large' );
	if ( $url === '' ) {
		continue;
	}
	$alt = (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true );
	$images[] = array(
		'url' => $url,
		'alt' => $alt,
	);
}

if ( $title === '' && empty( $items ) ) {
	return;
}
?>
<section class="aducation section">
	<div class="container">
		<div class="aducation__inner br-30">
			<div class="aducation__top section-top">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="aducation__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $items ) ) : ?>
				<div class="aducation__wrapper">
					<div class="aducation__items">
						<?php foreach ( $items as $item ) : ?>
							<div class="aducation__item">
								<div class="aducation__item-left">
									<?php if ( $item['year'] !== '' ) : ?>
										<span class="aducation__item-year"><?php echo esc_html( $item['year'] ); ?></span>
									<?php endif; ?>
									<svg class="aducation__item-svg" width="24" height="136" viewBox="0 0 24 136" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M12 121L12 1" stroke="#B2B2B2" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 4" />
										<path d="M18 122L12.7071 127.293C12.3166 127.683 11.6834 127.683 11.2929 127.293L6 122" stroke="#B2B2B2" stroke-width="2" stroke-linecap="round" />
									</svg>
								</div>
								<div class="aducation__item-wrapper">
									<?php if ( $item['type'] !== '' ) : ?>
										<span class="aducation__item-type"><?php echo tolstenko_kses_html( $item['type'] ); ?></span>
									<?php endif; ?>
									<?php if ( $item['title'] !== '' ) : ?>
										<span class="aducation__item-title line-caps-bold-15-15"><?php echo tolstenko_kses_html( $item['title'] ); ?></span>
									<?php endif; ?>
									<?php if ( $item['speciality'] !== '' ) : ?>
										<span class="aducation__item-speciality paragraph-15-25"><?php echo tolstenko_kses_html( $item['speciality'] ); ?></span>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<?php if ( ! empty( $images ) ) : ?>
						<div class="aducation__imgs">
							<?php foreach ( $images as $image ) : ?>
								<div class="aducation__img">
									<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>" loading="lazy" decoding="async">
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
