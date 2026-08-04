<?php
/**
 * Футер: вёрстка как в Tolstenko-marketing.
 * Данные — «Настройки сайта → Контактные данные».
 * Меню «Услуги» / «О нас» — Внешний вид → Меню (footer_menu_1 / footer_menu_2).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_template_directory_uri();
$theme_dir = get_template_directory();
$site_hf   = tolstenko_site_data();
$contact   = function_exists( 'tolstenko_get_contact_data' ) ? tolstenko_get_contact_data( true ) : array();

$phone = '';
if ( ! empty( $contact['phone'] ) ) {
	$phone = (string) $contact['phone'];
} elseif ( ! empty( $site_hf['phone'] ) ) {
	$phone = (string) $site_hf['phone'];
}

$phone_href = '';
if ( ! empty( $contact['phone_href'] ) ) {
	$phone_href = (string) $contact['phone_href'];
} elseif ( ! empty( $site_hf['phone_href'] ) ) {
	$phone_href = (string) $site_hf['phone_href'];
} else {
	$phone_href = preg_replace( '/\D+/', '', $phone );
}

$email = '';
if ( ! empty( $contact['email'] ) ) {
	$email = (string) $contact['email'];
} elseif ( ! empty( $site_hf['email'] ) ) {
	$email = (string) $site_hf['email'];
}

$telegram = '';
if ( ! empty( $contact['telegram'] ) ) {
	$telegram = (string) $contact['telegram'];
} elseif ( ! empty( $site_hf['telegram'] ) ) {
	$telegram = (string) $site_hf['telegram'];
} elseif ( ! empty( $contact['socials'] ) && is_array( $contact['socials'] ) ) {
	// Если отдельное поле не заполнено — берём первую ссылку t.me / telegram из соцсетей.
	foreach ( $contact['socials'] as $social ) {
		$link = is_array( $social ) ? trim( (string) ( $social['link'] ?? '' ) ) : '';
		if ( $link !== '' && preg_match( '#(?:t\\.me/|telegram\\.me/|telegram\\.org/)#i', $link ) ) {
			$telegram = $link;
			break;
		}
	}
}

$footer_name      = trim( (string) ( $contact['footer_name'] ?? '' ) );
$footer_inn       = trim( (string) ( $contact['footer_inn'] ?? '' ) );
$footer_ogrn      = trim( (string) ( $contact['footer_ogrn'] ?? '' ) );
$footer_address   = trim( (string) ( $contact['footer_address'] ?? '' ) );
$footer_copyright = trim( (string) ( $contact['footer_copyright'] ?? '' ) );

$footer_links = array();
if ( ! empty( $contact['footer_links'] ) && is_array( $contact['footer_links'] ) ) {
	foreach ( $contact['footer_links'] as $link ) {
		if ( ! is_array( $link ) ) {
			continue;
		}
		$title = isset( $link['title'] ) ? trim( (string) $link['title'] ) : '';
		$url   = isset( $link['url'] ) ? trim( (string) $link['url'] ) : '';
		if ( $title === '' || $url === '' ) {
			continue;
		}
		$footer_links[] = array(
			'title' => $title,
			'url'   => $url,
		);
	}
}

if ( $footer_copyright === '' ) {
	$footer_copyright = '© ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' );
}

$logo_url  = $theme_uri . '/assets/img/logo_footer.svg';
$audit_url = $theme_uri . '/assets/img/footer-audit-button-img.png';

$quick_socials = array();
if ( ! empty( $contact['socials'] ) && is_array( $contact['socials'] ) ) {
	$quick_socials = array_slice( $contact['socials'], 0, 2 );
} elseif ( ! empty( $site_hf['socials_footer_1'] ) && is_array( $site_hf['socials_footer_1'] ) ) {
	$quick_socials = $site_hf['socials_footer_1'];
}

$echo_theme_svg = static function ( $file ) use ( $theme_dir ) {
	$path = $theme_dir . '/assets/img/' . ltrim( (string) $file, '/' );
	if ( is_readable( $path ) ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo file_get_contents( $path );
	}
};

$render_quick_social = static function ( $social ) {
	$link = isset( $social['link'] ) ? trim( (string) $social['link'] ) : '';
	$icon = isset( $social['icon'] ) ? trim( (string) $social['icon'] ) : '';
	$text = isset( $social['text'] ) ? trim( (string) $social['text'] ) : '';
	if ( $link === '' ) {
		return;
	}
	$svg_path = function_exists( 'tolstenko_contact_resolve_svg_path' ) ? tolstenko_contact_resolve_svg_path( $icon ) : '';
	$icon_url = function_exists( 'tolstenko_contact_resolve_icon_url' ) ? tolstenko_contact_resolve_icon_url( $icon ) : $icon;
	?>
	<a class="footer__right-link default-btn line-caps-bold-13-15" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"<?php echo $text !== '' ? ' title="' . esc_attr( $text ) . '"' : ''; ?>>
		<?php
		if ( $svg_path !== '' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo file_get_contents( $svg_path );
		} elseif ( $icon_url !== '' ) {
			?>
			<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $text !== '' ? $text : 'Social' ); ?>">
			<?php
		}
		?>
	</a>
	<?php
};

$services_menu = wp_nav_menu(
	array(
		'theme_location' => 'footer_menu_1',
		'container'      => false,
		'menu_class'     => 'footer__menu-list menu__list',
		'fallback_cb'    => false,
		'echo'           => false,
		'depth'          => 1,
	)
);
$about_menu = wp_nav_menu(
	array(
		'theme_location' => 'footer_menu_2',
		'container'      => false,
		'menu_class'     => 'footer__menu-list menu__list',
		'fallback_cb'    => false,
		'echo'           => false,
		'depth'          => 1,
	)
);
?>

<footer class="footer" id="footer">
	<div class="container">
		<div class="footer__body">
			<div class="footer__left">
				<a class="footer__logo logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<img class="logo__img" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
				</a>

				<div class="footer__info">
					<?php if ( $footer_name !== '' ) : ?>
						<span><?php echo esc_html( $footer_name ); ?></span>
					<?php endif; ?>
					<?php if ( $footer_inn !== '' ) : ?>
						<span><?php echo esc_html( $footer_inn ); ?></span>
					<?php endif; ?>
					<?php if ( $footer_ogrn !== '' ) : ?>
						<span><?php echo esc_html( $footer_ogrn ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( $footer_address !== '' ) : ?>
					<address class="footer__address"><?php echo esc_html( $footer_address ); ?></address>
				<?php endif; ?>
			</div>

			<div class="footer__menu line-13-15">
				<div class="footer__menu-title paragraph-15-15"><?php esc_html_e( 'Услуги', 'tolstenko-theme' ); ?></div>
				<?php echo $services_menu ? $services_menu : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="footer__menu line-13-15">
				<div class="footer__menu-title paragraph-15-15"><?php esc_html_e( 'О нас', 'tolstenko-theme' ); ?></div>
				<?php echo $about_menu ? $about_menu : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>

			<div class="footer__right">
				<div class="footer__right-links">
					<?php if ( $phone !== '' ) : ?>
						<a class="footer__right-link footer__right-link--tel default-btn line-caps-bold-13-15" href="tel:<?php echo esc_attr( $phone_href ); ?>">
							<?php $echo_theme_svg( 'footer-phone-icon.svg' ); ?>
							<span><?php echo esc_html( $phone ); ?></span>
						</a>
					<?php endif; ?>

					<?php foreach ( $quick_socials as $social ) : ?>
						<?php $render_quick_social( $social ); ?>
					<?php endforeach; ?>
				</div>

				<a class="footer__audit default-btn footnote-12-10" href="#modal">
					<img src="<?php echo esc_url( $audit_url ); ?>" alt="">
					<?php esc_html_e( 'Бесплатный аудит', 'tolstenko-theme' ); ?>
				</a>

				<div class="footer__contacts">
					<?php if ( $email !== '' ) : ?>
						<div class="footer__contacts-item">
							<span><?php esc_html_e( 'Эл.почта', 'tolstenko-theme' ); ?></span>
							<a class="footer__contacts-link" href="mailto:<?php echo esc_attr( $email ); ?>">
								<?php $echo_theme_svg( 'footer-email-icon.svg' ); ?>
								<span><?php echo esc_html( $email ); ?></span>
							</a>
						</div>
					<?php endif; ?>

					<div class="footer__contacts-item">
						<span><?php esc_html_e( 'Соц.сети', 'tolstenko-theme' ); ?></span>
						<?php get_template_part( 'modules/socials/socials' ); ?>
					</div>
				</div>

				<?php if ( $telegram !== '' ) : ?>
					<a class="footer__right-btn footnote-12-10" href="<?php echo esc_url( $telegram ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Написать ТГ', 'tolstenko-theme' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer__bottom">
			<span class="footer__copyrite line-13-15"><?php echo esc_html( $footer_copyright ); ?></span>
			<?php foreach ( $footer_links as $bottom_link ) : ?>
				<a class="footer__bottom-link line-13-15" href="<?php echo esc_url( $bottom_link['url'] ); ?>">
					<?php echo esc_html( $bottom_link['title'] ); ?>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</footer>
