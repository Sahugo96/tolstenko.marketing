<?php
/**
 * Блок «Автор» (.author): фото, имя, список, показатели, ссылки, опциональный нижний блок.
 * Данные: атрибуты Gutenberg → дефолты блоков.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'author' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$str = static function ( $attr_key, $def_key, $block_attrs, $defaults ) {
	if ( isset( $block_attrs[ $attr_key ] ) && trim( (string) $block_attrs[ $attr_key ] ) !== '' ) {
		return (string) $block_attrs[ $attr_key ];
	}
	return (string) ( $defaults[ $def_key ] ?? '' );
};

$int = static function ( $attr_key, $def_key, $block_attrs, $defaults ) {
	if ( ! empty( $block_attrs[ $attr_key ] ) ) {
		return (int) $block_attrs[ $attr_key ];
	}
	return (int) ( $defaults[ $def_key ] ?? 0 );
};

$name = $str( 'block_author_name', 'name', $block_attrs, $defaults );
$name_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_author_name_tag'] ?? 'h2', 'h2' )
	: 'h2';

$photo_id  = $int( 'block_author_photo', 'photo', $block_attrs, $defaults );
$photo_url = $photo_id ? (string) wp_get_attachment_image_url( $photo_id, 'large' ) : '';
$photo_alt = $photo_id ? (string) get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) : '';
if ( $photo_alt === '' ) {
	$photo_alt = $name !== '' ? $name : __( 'Автор', 'tolstenko-theme' );
}

$btn_text = $str( 'block_author_btn_text', 'btn_text', $block_attrs, $defaults );
$btn_url  = tolstenko_url_or_modal( $str( 'block_author_btn_url', 'btn_url', $block_attrs, $defaults ) );

$links_label = $str( 'block_author_links_label', 'links_label', $block_attrs, $defaults );
if ( $links_label === '' ) {
	$links_label = __( 'Делюсь экспертизой', 'tolstenko-theme' );
}

$show_bottom = true;
if ( array_key_exists( 'block_author_show_bottom', $block_attrs ) ) {
	$show_bottom = (bool) $block_attrs['block_author_show_bottom'];
} elseif ( array_key_exists( 'show_bottom', $defaults ) ) {
	$show_bottom = (bool) $defaults['show_bottom'];
}

$subtitle      = $str( 'block_author_subtitle', 'subtitle', $block_attrs, $defaults );
$bottom_text   = $str( 'block_author_text', 'text', $block_attrs, $defaults );
$btn_more_text = $str( 'block_author_btn_more_text', 'btn_more_text', $block_attrs, $defaults );
$btn_more_url  = $str( 'block_author_btn_more_url', 'btn_more_url', $block_attrs, $defaults );
$award_text    = $str( 'block_author_award', 'award', $block_attrs, $defaults );
$invite_text   = $str( 'block_author_btn_invite_text', 'btn_invite_text', $block_attrs, $defaults );
$invite_url    = $str( 'block_author_btn_invite_url', 'btn_invite_url', $block_attrs, $defaults );

$award_id  = $int( 'block_author_award_image', 'award_image', $block_attrs, $defaults );
$award_url = $award_id ? (string) wp_get_attachment_image_url( $award_id, 'medium' ) : '';
$right_id  = $int( 'block_author_right_image', 'right_image', $block_attrs, $defaults );
$right_url = $right_id ? (string) wp_get_attachment_image_url( $right_id, 'large' ) : '';

$normalize_text_list = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $it ) {
		$text = is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it;
		$text = trim( $text );
		if ( $text === '' ) {
			continue;
		}
		$out[] = $text;
	}
	return $out;
};

$normalize_stats = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$value = (string) ( $it['value'] ?? '' );
		$text  = (string) ( $it['text'] ?? '' );
		if ( trim( wp_strip_all_tags( $value ) ) === '' && trim( wp_strip_all_tags( $text ) ) === '' ) {
			continue;
		}
		$out[] = array(
			'value' => $value,
			'text'  => $text,
		);
	}
	return $out;
};

$normalize_links = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$title = (string) ( $it['title'] ?? '' );
		$url   = (string) ( $it['url'] ?? '' );
		$icon  = (int) ( $it['icon'] ?? 0 );
		if ( $title === '' && $url === '' && $icon <= 0 ) {
			continue;
		}
		$out[] = array(
			'title' => $title,
			'url'   => $url,
			'icon'  => $icon,
		);
	}
	return $out;
};

$normalize_speeches = static function ( $raw ) {
	$out = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$image = (int) ( $it['image'] ?? 0 );
		$text  = (string) ( $it['text'] ?? '' );
		if ( $image <= 0 && trim( $text ) === '' ) {
			continue;
		}
		$out[] = array(
			'image' => $image,
			'text'  => $text,
		);
	}
	return $out;
};

$list = $normalize_text_list(
	! empty( $block_attrs['block_author_list'] ) && is_array( $block_attrs['block_author_list'] )
		? $block_attrs['block_author_list']
		: ( $defaults['list'] ?? array() )
);
$stats = $normalize_stats(
	! empty( $block_attrs['block_author_items'] ) && is_array( $block_attrs['block_author_items'] )
		? $block_attrs['block_author_items']
		: ( $defaults['items'] ?? array() )
);
$links = $normalize_links(
	! empty( $block_attrs['block_author_links'] ) && is_array( $block_attrs['block_author_links'] )
		? $block_attrs['block_author_links']
		: ( $defaults['links'] ?? array() )
);
$sublist = $normalize_text_list(
	! empty( $block_attrs['block_author_sublist'] ) && is_array( $block_attrs['block_author_sublist'] )
		? $block_attrs['block_author_sublist']
		: ( $defaults['sublist'] ?? array() )
);
$speeches = $normalize_speeches(
	! empty( $block_attrs['block_author_speeches'] ) && is_array( $block_attrs['block_author_speeches'] )
		? $block_attrs['block_author_speeches']
		: ( $defaults['speeches'] ?? array() )
);

$render_icon = static function ( $attachment_id ) {
	$id = (int) $attachment_id;
	if ( $id <= 0 ) {
		return;
	}
	$path = get_attached_file( $id );
	if ( $path && is_readable( $path ) && preg_match( '/\.svg$/i', $path ) ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- admin-uploaded SVG icon.
		$svg = file_get_contents( $path );
		if ( is_string( $svg ) && $svg !== '' ) {
			$svg = preg_replace( '/<script\b[^>]*>.*?<\/script>/is', '', $svg );
			echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted SVG from media library.
			return;
		}
	}
	$url = wp_get_attachment_image_url( $id, 'thumbnail' );
	if ( $url ) {
		echo '<img src="' . esc_url( $url ) . '" alt="" width="20" height="20">';
	}
};

if (
	$name === ''
	&& $photo_url === ''
	&& empty( $list )
	&& empty( $stats )
	&& empty( $links )
	&& ! $show_bottom
) {
	return;
}
?>
<section class="author section">
	<div class="container">
		<div class="author__inner">
			<div class="author__top">
				<div class="author__left">
					<?php if ( $photo_url !== '' ) : ?>
						<div class="author__photo">
							<img src="<?php echo esc_url( $photo_url ); ?>" alt="<?php echo esc_attr( $photo_alt ); ?>" loading="lazy">
						</div>
					<?php endif; ?>

					<?php if ( $btn_text !== '' ) : ?>
						<a
							class="author__btn default-btn default-btn--white line-caps-bold-13-15"
							href="<?php echo esc_url( $btn_url ); ?>"
							<?php echo $btn_url !== '#modal' ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>
						><?php echo esc_html( $btn_text ); ?></a>
					<?php endif; ?>
				</div>

				<div class="author__info">
					<?php if ( $name !== '' ) : ?>
						<<?php echo esc_attr( $name_tag ); ?> class="author__name h2"><?php echo tolstenko_kses_html( $name ); ?></<?php echo esc_attr( $name_tag ); ?>>
					<?php endif; ?>

					<?php if ( ! empty( $list ) ) : ?>
						<ul class="author__items">
							<?php foreach ( $list as $item_text ) : ?>
								<li class="author__item">
									<p class="author__item-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( ! empty( $stats ) ) : ?>
						<div class="author__splide splide" aria-label="<?php esc_attr_e( 'Показатели', 'tolstenko-theme' ); ?>">
							<div class="splide__track swiper">
								<div class="author__list splide__list swiper-wrapper">
									<?php foreach ( $stats as $stat ) : ?>
										<div class="author__list-item splide__slide swiper-slide">
											<?php if ( $stat['value'] !== '' ) : ?>
												<p class="author__list-value"><?php echo tolstenko_kses_html( $stat['value'] ); ?></p>
											<?php endif; ?>
											<?php if ( $stat['text'] !== '' ) : ?>
												<p class="author__list-text line-13-15"><?php echo tolstenko_kses_html( $stat['text'] ); ?></p>
											<?php endif; ?>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
							<div class="splide__bottom">
								<div class="swiper-pagination splide__pagination"></div>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $links ) ) : ?>
						<div class="author__links">
							<?php if ( $links_label !== '' ) : ?>
								<div class="author__links-text paragraph-15-25"><?php echo esc_html( $links_label ); ?></div>
							<?php endif; ?>
							<div class="author__links-wrapper">
								<?php foreach ( $links as $link ) : ?>
									<?php if ( $link['url'] === '' ) : ?>
										<?php continue; ?>
									<?php endif; ?>
									<a class="author__link default-btn" href="<?php echo esc_url( $link['url'] ); ?>"<?php echo strpos( $link['url'], 'http' ) === 0 ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
										<?php $render_icon( $link['icon'] ); ?>
										<?php if ( $link['title'] !== '' ) : ?>
											<span><?php echo esc_html( $link['title'] ); ?></span>
										<?php endif; ?>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $show_bottom ) : ?>
				<div class="author__bottom">
					<div class="author__wrapper">
						<div class="author__wrapper-left">
							<?php if ( $subtitle !== '' ) : ?>
								<h3 class="author__subtitle h3"><?php echo tolstenko_kses_html( $subtitle ); ?></h3>
							<?php endif; ?>

							<?php if ( $bottom_text !== '' ) : ?>
								<p class="author__bottom-text paragraph-15-25"><?php echo tolstenko_kses_html( $bottom_text ); ?></p>
							<?php endif; ?>

							<?php if ( ! empty( $sublist ) ) : ?>
								<ul class="author__items">
									<?php foreach ( $sublist as $item_text ) : ?>
										<li class="author__item">
											<p class="author__item-text line-13-15"><?php echo tolstenko_kses_html( $item_text ); ?></p>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>

							<?php if ( $btn_more_text !== '' && $btn_more_url !== '' ) : ?>
								<a class="author__btn-more default-btn" href="<?php echo esc_url( $btn_more_url ); ?>">
									<span><?php echo esc_html( $btn_more_text ); ?></span>
									<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M4.16699 10H15.0003M10.8337 5L15.2444 9.41074C15.5698 9.73618 15.5698 10.2638 15.2444 10.5893L10.8337 15" stroke-width="2" stroke-linecap="round" />
									</svg>
								</a>
							<?php endif; ?>
						</div>

						<?php if ( $award_url !== '' || $award_text !== '' ) : ?>
							<div class="author__award">
								<?php if ( $award_url !== '' ) : ?>
									<img src="<?php echo esc_url( $award_url ); ?>" alt="" loading="lazy">
								<?php endif; ?>
								<?php if ( $award_text !== '' ) : ?>
									<div class="author__award-text caption-8-10"><?php echo tolstenko_kses_html( $award_text ); ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $right_url !== '' ) : ?>
							<div class="author__right-image">
								<img src="<?php echo esc_url( $right_url ); ?>" alt="" loading="lazy">
							</div>
						<?php endif; ?>
					</div>

					<?php if ( ! empty( $speeches ) || ( $invite_text !== '' && $invite_url !== '' ) ) : ?>
						<div class="author__speeches splide" aria-label="<?php esc_attr_e( 'Выступления', 'tolstenko-theme' ); ?>">
							<?php if ( ! empty( $speeches ) ) : ?>
								<div class="splide__track swiper">
									<div class="author__speeches-list splide__list swiper-wrapper">
										<?php foreach ( $speeches as $speech ) : ?>
											<?php
											$speech_url = $speech['image'] > 0
												? (string) wp_get_attachment_image_url( $speech['image'], 'medium' )
												: '';
											?>
											<div class="author__speech splide__slide swiper-slide">
												<?php if ( $speech_url !== '' ) : ?>
													<div class="author__speech-image">
														<img src="<?php echo esc_url( $speech_url ); ?>" alt="<?php echo esc_attr( wp_strip_all_tags( $speech['text'] ) ); ?>" loading="lazy">
													</div>
												<?php endif; ?>
												<?php if ( $speech['text'] !== '' ) : ?>
													<p class="author__speech-text paragraph-15-15"><?php echo tolstenko_kses_html( $speech['text'] ); ?></p>
												<?php endif; ?>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
								<div class="splide__bottom">
									<div class="swiper-pagination splide__pagination"></div>
								</div>
							<?php endif; ?>

							<?php if ( $invite_text !== '' && $invite_url !== '' ) : ?>
								<a class="author__invite default-btn" href="<?php echo esc_url( $invite_url ); ?>"><?php echo esc_html( $invite_text ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
