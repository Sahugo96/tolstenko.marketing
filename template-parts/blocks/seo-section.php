<?php
/**
 * Блок «SEO продвижение».
 * Данные: атрибуты Gutenberg → дефолты блоков.
 * Тело секции обрезано по max-height, кнопка «Читать далее» снимает обрезку (.is-expanded, script.js).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'seo_section' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$title = ! empty( $block_attrs['block_seo_section_title'] )
	? (string) $block_attrs['block_seo_section_title']
	: (string) ( $defaults['title'] ?? '' );
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_seo_section_title_tag'] ?? 'h2', 'h2' )
	: 'h2';
$subtitle = isset( $block_attrs['block_seo_section_subtitle'] ) && trim( (string) $block_attrs['block_seo_section_subtitle'] ) !== ''
	? (string) $block_attrs['block_seo_section_subtitle']
	: (string) ( $defaults['subtitle'] ?? '' );
$more_text = isset( $block_attrs['block_seo_section_more_text'] ) && trim( (string) $block_attrs['block_seo_section_more_text'] ) !== ''
	? (string) $block_attrs['block_seo_section_more_text']
	: (string) ( $defaults['more_text'] ?? '' );
if ( trim( $more_text ) === '' ) {
	$more_text = __( 'Читать далее', 'tolstenko-theme' );
}

$raw_blocks = ! empty( $block_attrs['block_seo_section_blocks'] ) && is_array( $block_attrs['block_seo_section_blocks'] )
	? $block_attrs['block_seo_section_blocks']
	: (array) ( $defaults['blocks'] ?? array() );

$blocks = function_exists( 'tolstenko_normalize_seo_section_blocks' )
	? tolstenko_normalize_seo_section_blocks( $raw_blocks )
	: array();

if ( $title === '' && $subtitle === '' && empty( $blocks ) ) {
	return;
}
?>
<section class="seo-section section">
	<div class="container">
		<div class="seo-section__body">
			<?php if ( $title !== '' || $subtitle !== '' ) : ?>
				<div class="seo-section__head section-top">
					<?php if ( $title !== '' ) : ?>
						<<?php echo esc_attr( $title_tag ); ?> class="seo-section__title h2"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
					<?php endif; ?>
					<?php if ( $subtitle !== '' ) : ?>
						<div class="seo-section__subtitle paragraph-15-25"><?php echo tolstenko_kses_html( $subtitle ); ?></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $blocks ) ) : ?>
				<div class="seo-section__blocks">
					<?php
					foreach ( $blocks as $item ) {
						get_template_part(
							'template-parts/blocks/seo-section/' . $item['partial'],
							null,
							array( 'block' => $item )
						);
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $blocks ) ) : ?>
			<button type="button" class="seo-section__more more-btn">
				<?php echo esc_html( $more_text ); ?>
				<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M11.25 6.25L7.94194 9.55806C7.69786 9.80214 7.30214 9.80214 7.05806 9.55806L3.75 6.25" stroke-linecap="round" />
				</svg>
			</button>
		<?php endif; ?>
	</div>
</section>
