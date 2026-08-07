<?php
/**
 * Шапка: вёрстка как в Tolstenko-marketing, данные/меню — из текущего проекта.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$theme_uri = get_template_directory_uri();
$site_hf   = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();
$contact   = function_exists( 'tolstenko_get_contact_data' ) ? tolstenko_get_contact_data( true ) : array();

$phone = '';
if ( ! empty( $contact['phone'] ) ) {
	$phone = (string) $contact['phone'];
} elseif ( ! empty( $site_hf['phone'] ) ) {
	$phone = (string) $site_hf['phone'];
}
$phone_href = '';
if ( ! empty( $contact['phone_href'] ) && $phone === (string) ( $contact['phone'] ?? '' ) ) {
	$phone_href = (string) $contact['phone_href'];
} elseif ( ! empty( $site_hf['phone_href'] ) ) {
	$phone_href = (string) $site_hf['phone_href'];
} else {
	$phone_href = preg_replace( '/\D+/', '', $phone );
}

$logo_url       = $theme_uri . '/assets/img/logo.svg';
$logo_short_url       = $theme_uri . '/assets/img/logo_short.svg';
$contacts_page  = get_page_by_path( 'contacts' );
$contacts_url   = ( $contacts_page instanceof WP_Post ) ? (string) get_permalink( $contacts_page ) : home_url( '/' );
if ( $contacts_url === '' ) {
	$contacts_url = home_url( '/' );
}

// Кнопки справа от телефона (иконки): socials_header_2 или первые 2 из contact socials.
$quick_socials = array();
if ( ! empty( $site_hf['socials_header_2'] ) && is_array( $site_hf['socials_header_2'] ) ) {
	$quick_socials = $site_hf['socials_header_2'];
} elseif ( ! empty( $contact['socials'] ) && is_array( $contact['socials'] ) ) {
	$quick_socials = array_slice( $contact['socials'], 0, 2 );
}

$header_menu = wp_nav_menu(
	array(
		'theme_location' => 'header_top',
		'container'      => false,
		'menu_class'     => 'header__menu-list menu__list',
		'fallback_cb'    => false,
		'echo'           => false,
	)
);

$header_service_menu = wp_nav_menu(
	array(
		'theme_location' => 'header_main',
		'container'      => false,
		'menu_class'     => 'header__menu-services menu__list',
		'fallback_cb'    => false,
		'echo'           => false,
	)
);

/**
 * Рендер иконки соцсети (inline SVG).
 *
 * @param array{icon?: string, link?: string, text?: string} $social Social item.
 * @param string                                            $btn_class CSS class for the link.
 */
$render_quick_social = static function ( $social, $btn_class = 'header__top-btn' ) {
	$link = isset( $social['link'] ) ? trim( (string) $social['link'] ) : '';
	$icon = isset( $social['icon'] ) ? trim( (string) $social['icon'] ) : '';
	$text = isset( $social['text'] ) ? trim( (string) $social['text'] ) : '';
	if ( $link === '' ) {
		return;
	}
	$svg = function_exists( 'tolstenko_contact_get_inline_svg' )
		? tolstenko_contact_get_inline_svg( $icon )
		: '';
	if ( $svg === '' && function_exists( 'tolstenko_contact_resolve_svg_path' ) ) {
		$svg_path = tolstenko_contact_resolve_svg_path( $icon );
		if ( $svg_path !== '' ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local SVG.
			$svg = (string) file_get_contents( $svg_path );
		}
	}
	?>
	<a class="<?php echo esc_attr( $btn_class ); ?> default-btn" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"<?php echo $text !== '' ? ' title="' . esc_attr( $text ) . '"' : ''; ?>>
		<?php
		if ( $svg !== '' ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- local SVG from media/theme.
			echo $svg;
		} else {
			echo esc_html( $text !== '' ? $text : '•' );
		}
		?>
	</a>
	<?php
};
?>

<div class="header__top">
	<div class="container">
		<a class="header__location footnote-12-10" href="<?php echo esc_url( $contacts_url ); ?>">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M7.03875 13.8338C7.03875 13.8338 2.5 10.0113 2.5 6.25C2.5 4.92392 3.02678 3.65215 3.96447 2.71447C4.90215 1.77678 6.17392 1.25 7.5 1.25C8.82608 1.25 10.0979 1.77678 11.0355 2.71447C11.9732 3.65215 12.5 4.92392 12.5 6.25C12.5 10.0113 7.96125 13.8338 7.96125 13.8338C7.70875 14.0663 7.29313 14.0638 7.03875 13.8338ZM7.5 8.4375C7.78727 8.4375 8.07172 8.38092 8.33712 8.27099C8.60252 8.16105 8.84367 7.99992 9.0468 7.7968C9.24992 7.59367 9.41105 7.35252 9.52099 7.08712C9.63092 6.82172 9.6875 6.53727 9.6875 6.25C9.6875 5.96273 9.63092 5.67828 9.52099 5.41288C9.41105 5.14748 9.24992 4.90633 9.0468 4.7032C8.84367 4.50008 8.60252 4.33895 8.33712 4.22901C8.07172 4.11908 7.78727 4.0625 7.5 4.0625C6.91984 4.0625 6.36344 4.29297 5.9532 4.7032C5.54297 5.11344 5.3125 5.66984 5.3125 6.25C5.3125 6.83016 5.54297 7.38656 5.9532 7.7968C6.36344 8.20703 6.91984 8.4375 7.5 8.4375Z" />
			</svg>
			<span class="header__location-text"><?php esc_html_e( 'Москва', 'tolstenko-theme' ); ?></span>
		</a>

		<nav class="header__top-menu menu">
			<?php echo $header_menu ? $header_menu : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</nav>

		<div class="header__socials">
			<?php get_template_part( 'modules/socials/socials' ); ?>
		</div>

		<div class="header__top-right">
			<?php if ( $phone !== '' ) : ?>
				<a class="header__top-btn header__top-btn--tel default-btn caption-8-10" href="tel:<?php echo esc_attr( $phone_href ); ?>">
					<span><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>
			<?php
			foreach ( array_slice( $quick_socials, 0, 2 ) as $social ) {
				if ( is_array( $social ) ) {
					$render_quick_social( $social );
				}
			}
			?>
		</div>
	</div>
</div>

<div class="header__mobile">
	<div class="header__mobile-close" role="button" tabindex="0" aria-label="<?php esc_attr_e( 'Закрыть меню', 'tolstenko-theme' ); ?>"></div>
	<div class="header__mobile-inner">
		<a class="header__location footnote-12-10" href="<?php echo esc_url( $contacts_url ); ?>">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M7.03875 13.8338C7.03875 13.8338 2.5 10.0113 2.5 6.25C2.5 4.92392 3.02678 3.65215 3.96447 2.71447C4.90215 1.77678 6.17392 1.25 7.5 1.25C8.82608 1.25 10.0979 1.77678 11.0355 2.71447C11.9732 3.65215 12.5 4.92392 12.5 6.25C12.5 10.0113 7.96125 13.8338 7.96125 13.8338C7.70875 14.0663 7.29313 14.0638 7.03875 13.8338ZM7.5 8.4375C7.78727 8.4375 8.07172 8.38092 8.33712 8.27099C8.60252 8.16105 8.84367 7.99992 9.0468 7.7968C9.24992 7.59367 9.41105 7.35252 9.52099 7.08712C9.63092 6.82172 9.6875 6.53727 9.6875 6.25C9.6875 5.96273 9.63092 5.67828 9.52099 5.41288C9.41105 5.14748 9.24992 4.90633 9.0468 4.7032C8.84367 4.50008 8.60252 4.33895 8.33712 4.22901C8.07172 4.11908 7.78727 4.0625 7.5 4.0625C6.91984 4.0625 6.36344 4.29297 5.9532 4.7032C5.54297 5.11344 5.3125 5.66984 5.3125 6.25C5.3125 6.83016 5.54297 7.38656 5.9532 7.7968C6.36344 8.20703 6.91984 8.4375 7.5 8.4375Z" />
			</svg>
			<span class="header__location-text"><?php esc_html_e( 'Москва', 'tolstenko-theme' ); ?></span>
		</a>

		<?php
		echo $header_menu ? $header_menu : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$mobile_main = wp_nav_menu(
			array(
				'theme_location' => 'mobile_main',
				'container'      => false,
				'menu_class'     => 'header__menu-list menu__list',
				'fallback_cb'    => false,
				'echo'           => false,
			)
		);
		if ( $mobile_main ) {
			echo $mobile_main; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		$mobile_services = wp_nav_menu(
			array(
				'theme_location' => 'mobile_services',
				'container'      => false,
				'menu_class'     => 'header__menu-list menu__list',
				'fallback_cb'    => false,
				'echo'           => false,
			)
		);
		if ( $mobile_services ) {
			echo $mobile_services; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<div class="header__top-right">
			<?php if ( $phone !== '' ) : ?>
				<a class="header__top-btn header__top-btn--tel default-btn caption-8-10" href="tel:<?php echo esc_attr( $phone_href ); ?>">
					<span><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>
			<?php
			foreach ( array_slice( $quick_socials, 0, 2 ) as $social ) {
				if ( is_array( $social ) ) {
					$render_quick_social( $social );
				}
			}
			?>
		</div>

		<div class="header__socials">
			<?php get_template_part( 'modules/socials/socials' ); ?>
		</div>
	</div>
</div>

<div class="header__bottom">
	<div class="container">
		<a class="header__logo logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img class="logo__img" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<img class="logo__img logo__img--short" src="<?php echo esc_url( $logo_short_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		</a>

		<div class="header__bottom-inner">
			<?php if ( $phone !== '' ) : ?>
				<a class="header__bottom-btn header__bottom-btn--tel default-btn caption-8-10" href="tel:<?php echo esc_attr( $phone_href ); ?>">
					<span><?php echo esc_html( $phone ); ?></span>
				</a>
			<?php endif; ?>

			<?php
			foreach ( array_slice( $quick_socials, 0, 2 ) as $social ) {
				if ( is_array( $social ) ) {
					$render_quick_social( $social, 'header__bottom-btn' );
				}
			}
			?>

			<button class="header__bottom-service default-btn default-btn--red" type="button" aria-expanded="false" aria-controls="header-services-panel">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M9.67166 9.67182H10.3279M9.67166 10.3281H10.3279M9.67166 3.54682H10.3279M9.67166 4.20307H10.3279M3.54666 9.67182H4.20291M3.54666 10.3281H4.20291M3.54666 3.54682H4.20291M3.54666 4.20307H4.20291M15.7967 9.67182H16.4529M15.7967 10.3281H16.4529M15.7967 3.54682H16.4529M15.7967 4.20307H16.4529M9.67166 15.7968H10.3279M9.67166 16.4531H10.3279M3.54666 15.7968H4.20291M3.54666 16.4531H4.20291M15.7967 15.7968H16.4529M15.7967 16.4531H16.4529M10.875 10C10.875 10.4832 10.4832 10.875 10 10.875C9.51675 10.875 9.125 10.4832 9.125 10C9.125 9.51675 9.51675 9.125 10 9.125C10.4832 9.125 10.875 9.51675 10.875 10ZM10.875 3.875C10.875 4.35825 10.4832 4.75 10 4.75C9.51675 4.75 9.125 4.35825 9.125 3.875C9.125 3.39175 9.51675 3 10 3C10.4832 3 10.875 3.39175 10.875 3.875ZM4.75 10C4.75 10.4832 4.35825 10.875 3.875 10.875C3.39175 10.875 3 10.4832 3 10C3 9.51675 3.39175 9.125 3.875 9.125C4.35825 9.125 4.75 9.51675 4.75 10ZM4.75 3.875C4.75 4.35825 4.35825 4.75 3.875 4.75C3.39175 4.75 3 4.35825 3 3.875C3 3.39175 3.39175 3 3.875 3C4.35825 3 4.75 3.39175 4.75 3.875ZM17 10C17 10.4832 16.6082 10.875 16.125 10.875C15.6418 10.875 15.25 10.4832 15.25 10C15.25 9.51675 15.6418 9.125 16.125 9.125C16.6082 9.125 17 9.51675 17 10ZM17 3.875C17 4.35825 16.6082 4.75 16.125 4.75C15.6418 4.75 15.25 4.35825 15.25 3.875C15.25 3.39175 15.6418 3 16.125 3C16.6082 3 17 3.39175 17 3.875ZM10.875 16.125C10.875 16.6082 10.4832 17 10 17C9.51675 17 9.125 16.6082 9.125 16.125C9.125 15.6418 9.51675 15.25 10 15.25C10.4832 15.25 10.875 15.6418 10.875 16.125ZM4.75 16.125C4.75 16.6082 4.35825 17 3.875 17C3.39175 17 3 16.6082 3 16.125C3 15.6418 3.39175 15.25 3.875 15.25C4.35825 15.25 4.75 15.6418 4.75 16.125ZM17 16.125C17 16.6082 16.6082 17 16.125 17C15.6418 17 15.25 16.6082 15.25 16.125C15.25 15.6418 15.6418 15.25 16.125 15.25C16.6082 15.25 17 15.6418 17 16.125Z" stroke="white" stroke-width="2" stroke-linecap="round" />
				</svg>
				<?php esc_html_e( 'услуги', 'tolstenko-theme' ); ?>
			</button>

			<div class="header__bottom-tools">
				<div class="header__search header-search">
					<button type="button" class="header__search-btn js-header-search" aria-label="<?php esc_attr_e( 'Поиск', 'tolstenko-theme' ); ?>">
						<img src="<?php echo esc_url( $theme_uri . '/assets/img/header-search-icon.svg' ); ?>" alt="">
					</button>
					<form class="header-search-form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<input type="text" name="s" placeholder="<?php esc_attr_e( 'Найти', 'tolstenko-theme' ); ?>" class="header-search-input">
						<button type="button" class="header-search-form-close js-header-search-close" aria-label="<?php esc_attr_e( 'Закрыть поиск', 'tolstenko-theme' ); ?>">
							<img src="<?php echo esc_url( $theme_uri . '/assets/img/promo-notice-close.svg' ); ?>" alt="">
						</button>
					</form>
				</div>

				<?php echo $header_service_menu ? $header_service_menu : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

				<a class="header__modal-btn" href="#modal">
					<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M12 4L12 13M16 10L12.7071 13.2929C12.3166 13.6834 11.6834 13.6834 11.2929 13.2929L8 10M20 20L4 20" stroke-width="2" stroke-linecap="round" />
					</svg>
					<?php esc_html_e( 'Оставить заявку', 'tolstenko-theme' ); ?>
				</a>
			</div>
		</div>

		<button type="button" class="header__burger" aria-label="<?php esc_attr_e( 'Открыть меню', 'tolstenko-theme' ); ?>">
			<span></span>
		</button>
	</div>
</div>

<?php get_template_part( 'modules/header-services-panel' ); ?>
