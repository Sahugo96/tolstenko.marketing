<?php
/**
 * Header template
 *
 * Внешний вид — Tolstenko-marketing; данные и функционал — текущий проект.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$site_hf = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array(
	'phone'             => '',
	'phone_href'        => '',
	'promo_notice_html' => '',
);
$show_promo_notice = ! empty( $site_hf['promo_notice_html'] );
if ( $show_promo_notice && ! empty( $_COOKIE['tolstenko_promo_notice_closed_until'] ) ) {
	$closed_until = (int) $_COOKIE['tolstenko_promo_notice_closed_until'];
	if ( $closed_until > time() ) {
		$show_promo_notice = false;
	}
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<div class="wrapper <?php echo esc_attr( tolstenko_get_root_page_class() ); ?>">
	<header class="header">
		<?php get_template_part( 'modules/header-menu' ); ?>
		<?php get_template_part( 'modules/guide-banner/guide-banner' ); ?>
	</header>

	<?php if ( $show_promo_notice ) : ?>
		<div class="promo-notice" data-promo-cookie-key="tolstenko_promo_notice_closed_until">
			<div class="container">
				<div class="promo-notice-inner">
					<div class="promo-notice-close-fake"></div>
					<div class="promo-notice-text">
						<div class="promo-notice-text-content">
							<span><?php echo wp_kses_post( $site_hf['promo_notice_html'] ); ?></span>
						</div>
					</div>
					<div class="promo-notice-close">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/promo-notice-close.svg' ); ?>" alt="">
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
