<?php
/**
 * Блок «Консультация Telegram».
 * Разметка/классы — как в tolstenko (BEM с __).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_dir   = get_template_directory();
$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$blog_inline = (bool) get_query_var( 'tolstenko_block_blog_inline', false );
if ( $blog_inline && function_exists( 'tolstenko_get_article_consultation_defaults' ) ) {
	$defaults = tolstenko_get_article_consultation_defaults( 'article_consultation_tg', 'consultation_tg' );
} else {
	$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'consultation_tg' ) : array();
}
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();

$title = ! empty( $block_attrs['block_consultation_tg_title'] )
	? (string) $block_attrs['block_consultation_tg_title']
	: (string) ( $defaults['title'] ?? '' );
$text = isset( $block_attrs['block_consultation_tg_text'] ) && trim( (string) $block_attrs['block_consultation_tg_text'] ) !== ''
	? (string) $block_attrs['block_consultation_tg_text']
	: (string) ( $defaults['text'] ?? '' );
$btn_text = ! empty( $block_attrs['block_consultation_tg_btn_text'] )
	? (string) $block_attrs['block_consultation_tg_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url = ! empty( $block_attrs['block_consultation_tg_btn_url'] )
	? (string) $block_attrs['block_consultation_tg_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
if ( $btn_url === '' && ! empty( $site['telegram'] ) ) {
	$btn_url = (string) $site['telegram'];
}
$text_btn = isset( $block_attrs['block_consultation_tg_text_btn'] ) && trim( (string) $block_attrs['block_consultation_tg_text_btn'] ) !== ''
	? (string) $block_attrs['block_consultation_tg_text_btn']
	: (string) ( $defaults['text_btn'] ?? '' );
$color = ! empty( $block_attrs['block_consultation_tg_color'] )
	? (string) $block_attrs['block_consultation_tg_color']
	: (string) ( $defaults['color'] ?? '#2AABEE' );
$color_hover = ! empty( $block_attrs['block_consultation_tg_color_hover'] )
	? (string) $block_attrs['block_consultation_tg_color_hover']
	: (string) ( $defaults['color_hover'] ?? '#229ED9' );

$img_id = ! empty( $block_attrs['block_consultation_tg_image'] )
	? (int) $block_attrs['block_consultation_tg_image']
	: (int) ( $defaults['image'] ?? 0 );
$img_src = $img_id > 0 ? (string) wp_get_attachment_image_url( $img_id, 'medium' ) : '';
$img_alt = $img_id ? (string) get_post_meta( $img_id, '_wp_attachment_image_alt', true ) : '';
$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_consultation_tg_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

if ( $title === '' && $text === '' && $btn_url === '' ) {
	return;
}

$tg_icon = $theme_dir . '/assets/img/telegram-icon-white.svg';
?>
<?php if ( $blog_inline ) : ?>
	<?php
	$desc = $text !== '' ? $text : $text_btn;
	?>
	<div class="article-cta article-cta--telegram" style="--article-cta-accent: <?php echo esc_attr( $color ); ?>;">
		<?php if ( $title !== '' || $desc !== '' ) : ?>
			<div class="article-cta__content">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="article-cta__title"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $desc !== '' ) : ?>
					<p class="article-cta__text"><?php echo tolstenko_kses_html( $desc ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php if ( $btn_url !== '' && $btn_text !== '' ) : ?>
			<a
				class="article-cta__btn default-btn default-btn--tg"
				href="<?php echo esc_url( $btn_url ); ?>"
				target="_blank"
				rel="noopener noreferrer"
				style="--color-btn: <?php echo esc_attr( $color ); ?>; --color-hover-btn: <?php echo esc_attr( $color_hover ); ?>;"
			>
				<?php
				if ( is_readable( $tg_icon ) ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
					echo file_get_contents( $tg_icon );
				}
				?>
				<?php echo esc_html( $btn_text ); ?>
			</a>
		<?php endif; ?>
	</div>
<?php else : ?>
<section class="consultation-tg section">
	<div class="container">
		<div class="consultation-tg__inner">
			<?php if ( $img_src !== '' ) : ?>
				<div class="consultation-tg__img">
					<img src="<?php echo esc_url( $img_src ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy" decoding="async">
				</div>
			<?php endif; ?>

			<div class="consultation-tg__wrapper tg-block">
				<?php if ( $title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="tg-block__title <?php echo esc_attr( $title_tag ); ?>"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>
				<?php if ( $text !== '' ) : ?>
					<p class="tg-block__content"><?php echo tolstenko_kses_html( $text ); ?></p>
				<?php endif; ?>

				<div class="tg-block__bottom">
					<?php if ( $btn_url !== '' && $btn_text !== '' ) : ?>
						<a
							class="tg-block__btn default-btn default-btn--tg"
							href="<?php echo esc_url( $btn_url ); ?>"
							target="_blank"
							rel="noopener noreferrer"
							style="--color-btn: <?php echo esc_attr( $color ); ?>; --color-hover-btn: <?php echo esc_attr( $color_hover ); ?>;"
						>
							<?php
							if ( is_readable( $tg_icon ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- theme SVG asset.
								echo file_get_contents( $tg_icon );
							}
							?>
							<?php echo esc_html( $btn_text ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $text_btn !== '' ) : ?>
						<p class="tg-block__text line-13-15"><?php echo tolstenko_kses_html( $text_btn ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>
