<?php
/**
 * Блок «Контакты» (.contacts): заголовок, адреса (вкладки) с пунктами + галереей.
 * Данные: атрибуты Gutenberg → дефолты блоков (Страница контактов).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}

$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'contacts_page' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$contacts_title = '';
if ( ! empty( $block_attrs['block_contacts_page_title'] ) ) {
	$contacts_title = (string) $block_attrs['block_contacts_page_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$contacts_title = (string) $defaults['title'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_contacts_page_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$normalize_items = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$title = sanitize_text_field( (string) ( $item['title'] ?? '' ) );
		$icon  = $item['icon'] ?? 0;
		if ( is_numeric( $icon ) ) {
			$icon = (int) $icon > 0 ? (int) $icon : 0;
		} else {
			$icon = sanitize_text_field( (string) $icon );
		}
		$links = array();
		if ( ! empty( $item['links'] ) && is_array( $item['links'] ) ) {
			foreach ( $item['links'] as $link ) {
				if ( ! is_array( $link ) ) {
					continue;
				}
				$text = sanitize_text_field( (string) ( $link['text'] ?? '' ) );
				$url  = esc_url_raw( (string) ( $link['link'] ?? '' ) );
				if ( $text === '' && $url === '' ) {
					continue;
				}
				$links[] = array(
					'text' => $text,
					'link' => $url,
				);
			}
		}
		if ( $title === '' && empty( $links ) && ( ! $icon || $icon === '0' ) ) {
			continue;
		}
		$out[] = array(
			'title' => $title,
			'icon'  => $icon,
			'links' => $links,
		);
	}
	return $out;
};

$normalize_addresses = static function ( $raw ) use ( $normalize_items ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $addr ) {
		if ( ! is_array( $addr ) ) {
			continue;
		}
		$address = sanitize_text_field( (string) ( $addr['address'] ?? '' ) );
		$gallery = array();
		if ( ! empty( $addr['gallery'] ) && is_array( $addr['gallery'] ) ) {
			foreach ( $addr['gallery'] as $gid ) {
				if ( is_array( $gid ) ) {
					$id = absint( $gid['id'] ?? ( $gid['image'] ?? 0 ) );
				} else {
					$id = absint( $gid );
				}
				if ( $id > 0 ) {
					$gallery[] = $id;
				}
			}
		}
		$items = $normalize_items( $addr['items'] ?? array() );
		if ( $address === '' && empty( $gallery ) && empty( $items ) ) {
			continue;
		}
		$out[] = array(
			'address' => $address,
			'gallery' => $gallery,
			'items'   => $items,
		);
	}
	return $out;
};

$legacy_items = array();
if ( ! empty( $block_attrs['block_contacts_page_items'] ) && is_array( $block_attrs['block_contacts_page_items'] ) ) {
	$legacy_items = $normalize_items( $block_attrs['block_contacts_page_items'] );
}
if ( empty( $legacy_items ) && ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$legacy_items = $normalize_items( $defaults['items'] );
}

$addresses = array();
if ( ! empty( $block_attrs['block_contacts_page_addresses'] ) && is_array( $block_attrs['block_contacts_page_addresses'] ) ) {
	$addresses = $normalize_addresses( $block_attrs['block_contacts_page_addresses'] );
}
if ( empty( $addresses ) && ! empty( $defaults['addresses'] ) && is_array( $defaults['addresses'] ) ) {
	$addresses = $normalize_addresses( $defaults['addresses'] );
}

// Старые данные: общие пункты → в адреса без своих items.
if ( $legacy_items ) {
	foreach ( $addresses as $i => $addr ) {
		if ( empty( $addr['items'] ) ) {
			$addresses[ $i ]['items'] = $legacy_items;
		}
	}
	if ( empty( $addresses ) ) {
		$addresses[] = array(
			'address' => '',
			'gallery' => array(),
			'items'   => $legacy_items,
		);
	}
}

$has_addresses = false;
foreach ( $addresses as $addr ) {
	if ( trim( (string) ( $addr['address'] ?? '' ) ) !== '' ) {
		$has_addresses = true;
		break;
	}
}
$has_any = ! empty( $addresses );

$site = function_exists( 'tolstenko_get_site_header_footer_data' ) ? tolstenko_get_site_header_footer_data() : array();
$cd   = function_exists( 'tolstenko_get_contact_data' ) ? tolstenko_get_contact_data( true ) : array();

$phone = '';
if ( ! empty( $cd['phone'] ) ) {
	$phone = (string) $cd['phone'];
} elseif ( ! empty( $site['phone'] ) ) {
	$phone = (string) $site['phone'];
}
$phone_href = '';
if ( ! empty( $cd['phone_href'] ) ) {
	$phone_href = (string) $cd['phone_href'];
} elseif ( ! empty( $site['phone_href'] ) ) {
	$phone_href = (string) $site['phone_href'];
} else {
	$phone_href = preg_replace( '/\D+/', '', $phone );
}

$telegram = '';
if ( ! empty( $cd['telegram'] ) ) {
	$telegram = (string) $cd['telegram'];
} elseif ( ! empty( $site['telegram'] ) ) {
	$telegram = (string) $site['telegram'];
}

$theme_dir = get_template_directory();

if ( ! $has_any ) {
	return;
}

$render_info_items = static function ( $contact_items ) {
	if ( empty( $contact_items ) || ! is_array( $contact_items ) ) {
		return;
	}
	echo '<div class="contacts__info-list">';
	foreach ( $contact_items as $contact_item ) {
		$contact_title = trim( (string) ( $contact_item['title'] ?? '' ) );
		$icon_raw      = $contact_item['icon'] ?? '';
		$contact_links = ! empty( $contact_item['links'] ) && is_array( $contact_item['links'] ) ? $contact_item['links'] : array();
		$icon_url      = '';
		if ( $icon_raw !== '' && $icon_raw !== 0 && $icon_raw !== '0' ) {
			if ( is_numeric( $icon_raw ) ) {
				$icon_url = (string) wp_get_attachment_image_url( (int) $icon_raw, 'thumbnail' );
			} else {
				$icon_url = (string) $icon_raw;
			}
		}
		if ( $contact_title === '' && empty( $contact_links ) && $icon_url === '' ) {
			continue;
		}
		echo '<div class="contacts__info-item">';
		if ( $icon_url !== '' ) {
			echo '<span class="contacts__info-icon" aria-hidden="true"><img src="' . esc_url( $icon_url ) . '" alt="' . esc_attr( $contact_title ) . '" loading="lazy" decoding="async"></span>';
		}
		echo '<div class="contacts__info-content">';
		if ( $contact_title !== '' ) {
			echo '<div class="contacts__info-label line-caps-bold-13-15">' . esc_html( $contact_title ) . '</div>';
		}
		if ( $contact_links ) {
			echo '<div class="contacts__info-text paragraph-15-25">';
			foreach ( $contact_links as $link_item ) {
				$link_text = trim( (string) ( $link_item['text'] ?? '' ) );
				$link_href = trim( (string) ( $link_item['link'] ?? '' ) );
				if ( $link_text === '' ) {
					continue;
				}
				if ( $link_href !== '' ) {
					echo '<a href="' . esc_url( $link_href ) . '">' . esc_html( $link_text ) . '</a><br>';
				} else {
					echo '<span>' . esc_html( $link_text ) . '</span><br>';
				}
			}
			echo '</div>';
		}
		echo '</div></div>';
	}
	echo '</div>';
};
?>
<section class="contacts section">
	<div class="container">
		<div class="contacts__inner">
			<?php if ( $has_addresses ) : ?>
				<div class="contacts__tabs tabs" aria-label="<?php esc_attr_e( 'Адреса', 'tolstenko-theme' ); ?>">
					<div class="contacts__labels tabs__labels">
						<?php
						$checked = ' checked';
						foreach ( $addresses as $index => $item ) :
							$tab_label = trim( (string) ( $item['address'] ?? '' ) );
							if ( $tab_label === '' ) {
								continue;
							}
							$tab_id = 'contacts-tab-' . (int) $index;
							?>
							<label class="contacts__label tabs__label line-caps-bold-13-15">
								<input type="radio" name="contacts" value="<?php echo esc_attr( $tab_id ); ?>" data-tab-index="<?php echo esc_attr( (string) $index ); ?>"<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
								<span><?php echo esc_html( $tab_label ); ?></span>
							</label>
							<?php
							$checked = '';
						endforeach;
						?>
					</div>
				</div>
			<?php endif; ?>

			<div class="contacts__card">
				<div class="contacts__grid">
					<div class="contacts__info-panels">
						<?php foreach ( $addresses as $index => $item ) : ?>
							<div class="contacts__info-panel<?php echo 0 === (int) $index ? ' is-active' : ''; ?>" data-tab-index="<?php echo esc_attr( (string) $index ); ?>"<?php echo 0 !== (int) $index ? ' hidden' : ''; ?>>
								<div class="contacts__info br-30">
									<?php if ( $contacts_title !== '' ) : ?>
										<<?php echo esc_attr( $title_tag ); ?> class="contacts__info-title h2"><?php echo esc_html( $contacts_title ); ?></<?php echo esc_attr( $title_tag ); ?>>
									<?php endif; ?>

									<?php $render_info_items( $item['items'] ?? array() ); ?>

									<?php if ( $phone_href !== '' || $telegram !== '' ) : ?>
										<div class="contacts__actions">
											<?php if ( $phone_href !== '' ) : ?>
												<a class="contacts__action contacts__action--tel default-btn line-caps-bold-13-15" href="tel:<?php echo esc_attr( $phone_href ); ?>">
													<?php
													$phone_svg = $theme_dir . '/assets/img/phone-icon.svg';
													if ( is_readable( $phone_svg ) ) {
														// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
														echo file_get_contents( $phone_svg );
													}
													?>
													<span><?php esc_html_e( 'Позвонить сейчас', 'tolstenko-theme' ); ?></span>
												</a>
											<?php endif; ?>
											<?php if ( $telegram !== '' ) : ?>
												<a class="contacts__action contacts__action--tg default-btn default-btn--tg line-caps-bold-13-15" href="<?php echo esc_url( $telegram ); ?>" target="_blank" rel="noopener noreferrer">
													<?php
													$tg_svg = $theme_dir . '/assets/img/telegram-icon.svg';
													if ( is_readable( $tg_svg ) ) {
														// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
														echo file_get_contents( $tg_svg );
													}
													?>
													<span><?php esc_html_e( 'Написать в Telegram', 'tolstenko-theme' ); ?></span>
												</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>

					<div class="contacts__media">
						<div class="contacts__galleries">
							<?php
							foreach ( $addresses as $index => $item ) :
								$address = trim( (string) ( $item['address'] ?? '' ) );
								$gallery = ! empty( $item['gallery'] ) && is_array( $item['gallery'] ) ? $item['gallery'] : array();
								$images  = array();
								foreach ( $gallery as $img_id ) {
									$url = wp_get_attachment_image_url( (int) $img_id, 'large' );
									if ( ! $url ) {
										continue;
									}
									$images[] = array(
										'url' => $url,
										'alt' => $address !== '' ? $address : (string) get_post_meta( (int) $img_id, '_wp_attachment_image_alt', true ),
									);
								}
								?>
								<div class="contacts__gallery-panel<?php echo 0 === (int) $index ? ' is-active' : ''; ?>" data-tab-index="<?php echo esc_attr( (string) $index ); ?>"<?php echo 0 !== (int) $index ? ' hidden' : ''; ?>>
									<?php if ( $images ) : ?>
										<?php $images_count = count( $images ); ?>
										<div class="contacts__gallery" data-contacts-gallery>
											<div class="contacts__gallery-main splide">
												<div class="splide__track swiper">
													<div class="splide__list swiper-wrapper">
														<?php foreach ( $images as $image ) : ?>
															<div class="contacts__gallery-slide splide__slide swiper-slide">
																<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( (string) $image['alt'] ); ?>" loading="lazy" decoding="async">
															</div>
														<?php endforeach; ?>
													</div>
												</div>
												<?php if ( $images_count > 1 ) : ?>
													<div class="splide__arrows splide__arrows--ltr">
														<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Предыдущее фото', 'tolstenko-theme' ); ?>">
															<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
																<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
															</svg>
														</button>
														<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Следующее фото', 'tolstenko-theme' ); ?>">
															<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
																<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
															</svg>
														</button>
													</div>
												<?php endif; ?>
											</div>
											<?php if ( $images_count > 1 ) : ?>
												<div class="contacts__gallery-thumbs splide">
													<div class="splide__track swiper">
														<div class="splide__list swiper-wrapper">
															<?php foreach ( $images as $image ) : ?>
																<div class="contacts__gallery-thumb splide__slide swiper-slide">
																	<img src="<?php echo esc_url( $image['url'] ); ?>" alt="" loading="lazy" decoding="async">
																</div>
															<?php endforeach; ?>
														</div>
													</div>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
