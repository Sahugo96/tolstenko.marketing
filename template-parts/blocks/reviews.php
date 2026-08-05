<?php
/**
 * Блок «Отзывы»: заголовок, текст, выбранные CPT review, опционально reviews__items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'reviews' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$block_title = '';
if ( ! empty( $block_attrs['block_reviews_title'] ) ) {
	$block_title = (string) $block_attrs['block_reviews_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$block_title = (string) $defaults['title'];
}
if ( $block_title === '' ) {
	$block_title = 'Отзывы';
}

$block_text = '';
if ( isset( $block_attrs['block_reviews_text'] ) && trim( (string) $block_attrs['block_reviews_text'] ) !== '' ) {
	$block_text = (string) $block_attrs['block_reviews_text'];
} elseif ( ! empty( $defaults['text'] ) ) {
	$block_text = (string) $defaults['text'];
}

$title_tag = function_exists( 'tolstenko_normalize_heading_tag' )
	? tolstenko_normalize_heading_tag( $block_attrs['block_reviews_title_tag'] ?? 'h2', 'h2' )
	: 'h2';

$show_items = true;
if ( array_key_exists( 'block_reviews_show_items', $block_attrs ) ) {
	$show_items = (bool) $block_attrs['block_reviews_show_items'];
} elseif ( array_key_exists( 'show_items', $defaults ) ) {
	$show_items = (bool) $defaults['show_items'];
}

$post_ids = array();
if ( ! empty( $block_attrs['block_reviews_ids'] ) && is_array( $block_attrs['block_reviews_ids'] ) ) {
	foreach ( $block_attrs['block_reviews_ids'] as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$post_ids[] = $id;
		}
	}
	$post_ids = array_values( array_unique( $post_ids ) );
} elseif ( ! empty( $defaults['ids'] ) && is_array( $defaults['ids'] ) ) {
	foreach ( $defaults['ids'] as $id ) {
		$id = (int) $id;
		if ( $id > 0 ) {
			$post_ids[] = $id;
		}
	}
	$post_ids = array_values( array_unique( $post_ids ) );
}

$cards = array();
if ( $show_items ) {
	if ( ! empty( $defaults['cards'] ) && is_array( $defaults['cards'] ) ) {
		foreach ( $defaults['cards'] as $card ) {
			if ( ! is_array( $card ) ) {
				continue;
			}
			$title = trim( (string) ( $card['title'] ?? '' ) );
			if ( $title === '' ) {
				continue;
			}
			$rating = isset( $card['rating'] ) ? (int) $card['rating'] : 5;
			if ( $rating < 1 ) {
				$rating = 5;
			}
			if ( $rating > 5 ) {
				$rating = 5;
			}
			$cards[] = array(
				'title'  => $title,
				'url'    => esc_url_raw( (string) ( $card['url'] ?? '' ) ),
				'rating' => $rating,
			);
		}
	}
}

$grouped = function_exists( 'tolstenko_get_reviews_grouped' )
	? tolstenko_get_reviews_grouped( $post_ids )
	: array(
		'thanks'     => array(),
		'video'      => array(),
		'text'       => array(),
		'messengers' => array(),
	);

$thanks_items = array();
foreach ( $grouped['thanks'] as $post ) {
	$img = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_thenks', $post->ID ) );
	if ( $img['url'] === '' ) {
		$img = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_thanks_image', $post->ID ) );
	}
	if ( $img['url'] === '' ) {
		$img = tolstenko_review_image_attrs( get_post_thumbnail_id( $post->ID ) );
	}
	if ( $img['url'] !== '' ) {
		$thanks_items[] = array(
			'post'  => $post,
			'image' => $img,
		);
	}
}

$video_items = array();
foreach ( $grouped['video'] as $post ) {
	$raw = (string) tolstenko_get_review_field( 'review_video', $post->ID );
	if ( $raw === '' ) {
		$raw = (string) get_post_meta( $post->ID, 'review_embed_url', true );
	}
	$video_src = function_exists( 'tolstenko_parse_video_embed_src' ) ? tolstenko_parse_video_embed_src( $raw ) : '';
	if ( $video_src === '' ) {
		continue;
	}
	$logo         = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_logo', $post->ID ) );
	$video_name   = trim( (string) tolstenko_get_review_field( 'review_video_name', $post->ID ) );
	$video_text   = trim( (string) tolstenko_get_review_field( 'review_video_text', $post->ID ) );
	$video_poster = function_exists( 'tolstenko_get_video_embed_poster' ) ? tolstenko_get_video_embed_poster( $video_src ) : '';
	$preview      = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_preview_image', $post->ID ) );
	if ( $video_poster === '' && $preview['url'] !== '' ) {
		$video_poster = $preview['url'];
	}
	$video_items[] = array(
		'post'      => $post,
		'src'       => $video_src,
		'rutube_id' => function_exists( 'tolstenko_get_rutube_video_id' ) ? tolstenko_get_rutube_video_id( $video_src ) : '',
		'poster'    => $video_poster,
		'name'      => $video_name,
		'text'      => $video_text,
		'logo'      => $logo,
		'has_meta'  => ( $video_name !== '' && $video_text !== '' ),
	);
}

$text_items = array();
foreach ( $grouped['text'] as $post ) {
	$name     = trim( (string) tolstenko_get_review_field( 'review_name', $post->ID ) );
	$position = trim( (string) tolstenko_get_review_field( 'review_position', $post->ID ) );
	$rating   = (int) tolstenko_get_review_field( 'review_rating', $post->ID );
	if ( $rating < 1 ) {
		$rating = (int) get_post_meta( $post->ID, 'review_text_rating', true );
	}
	if ( $rating < 1 ) {
		$rating = 5;
	}
	if ( $rating > 5 ) {
		$rating = 5;
	}
	$redactor = (string) tolstenko_get_review_field( 'review_redactor', $post->ID );
	if ( trim( wp_strip_all_tags( $redactor ) ) === '' ) {
		$redactor = (string) get_the_content( null, false, $post );
	}
	if ( trim( wp_strip_all_tags( $redactor ) ) === '' ) {
		continue;
	}
	if ( $name === '' ) {
		$name = get_the_title( $post );
	}
	$photo   = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_photo', $post->ID ) );
	$contact = tolstenko_get_review_field( 'review_contact', $post->ID );
	$case    = tolstenko_get_review_field( 'review_case', $post->ID );
	if ( is_array( $case ) && ! empty( $case['url'] ) ) {
		$case = $case['url'];
	}
	$case = is_string( $case ) ? $case : '';

	// Legacy: ссылка на отзыв как контакт.
	$legacy_link = (string) get_post_meta( $post->ID, 'review_text_link', true );
	if ( ( ! is_array( $contact ) || empty( $contact['url'] ) ) && $legacy_link !== '' ) {
		$contact = array(
			'url'   => $legacy_link,
			'title' => (string) ( get_post_meta( $post->ID, 'review_text_source_name', true ) ?: __( 'Смотреть отзыв', 'tolstenko-theme' ) ),
		);
	}

	$text_items[] = array(
		'post'     => $post,
		'name'     => $name,
		'position' => $position,
		'rating'   => $rating,
		'redactor' => $redactor,
		'photo'    => $photo,
		'contact'  => is_array( $contact ) ? $contact : null,
		'case'     => $case,
	);
}

$messenger_items = array();
foreach ( $grouped['messengers'] as $post ) {
	$img = tolstenko_review_image_attrs( tolstenko_get_review_field( 'review_screen', $post->ID ) );
	if ( $img['url'] === '' ) {
		$img = tolstenko_review_image_attrs( get_post_thumbnail_id( $post->ID ) );
	}
	if ( $img['url'] !== '' ) {
		$messenger_items[] = array(
			'post'  => $post,
			'image' => $img,
		);
	}
}

$has_thanks     = ! empty( $thanks_items );
$has_video      = ! empty( $video_items );
$has_text       = ! empty( $text_items );
$has_messengers = ! empty( $messenger_items );
$any_tabs       = $has_thanks || $has_video || $has_text || $has_messengers;

if ( ! $any_tabs && empty( $cards ) && $block_title === '' ) {
	return;
}

$render_stars = static function ( $rating ) {
	$rating = max( 0, min( 5, (int) $rating ) );
	for ( $i = 1; $i <= 5; $i++ ) {
		$class = $i <= $rating ? 'filled' : 'empty';
		echo '<span class="star ' . esc_attr( $class ) . '">★</span>';
	}
};

$render_splide_nav = static function () {
	?>
	<div class="splide__bottom">
		<ul class="splide__pagination"></ul>
		<div class="splide__arrows splide__arrows--ltr">
			<button class="splide__arrow splide__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Назад', 'tolstenko-theme' ); ?>">
				<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
				</svg>
			</button>
			<button class="splide__arrow splide__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Вперёд', 'tolstenko-theme' ); ?>">
				<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path d="M15.8332 10H4.99987M9.16654 5L4.7558 9.41074C4.43036 9.73618 4.43036 10.2638 4.7558 10.5893L9.16654 15" stroke-width="2" stroke-linecap="round" />
				</svg>
			</button>
		</div>
	</div>
	<?php
};

$checked = 'checked';
?>
<section class="reviews section" aria-label="<?php esc_attr_e( 'Отзывы', 'tolstenko-theme' ); ?>">
	<div class="container">
		<div class="reviews__inner br-30">
			<div class="reviews__top section-top">
				<<?php echo esc_attr( $title_tag ); ?> class="reviews__title h2"><?php echo tolstenko_kses_html( $block_title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php if ( $block_text !== '' ) : ?>
					<p class="reviews__text paragraph-15-15"><?php echo tolstenko_kses_html( $block_text ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $show_items && ! empty( $cards ) ) : ?>
				<div class="reviews__items">
					<?php foreach ( $cards as $card ) : ?>
						<?php
						$card_url = isset( $card['url'] ) ? trim( (string) $card['url'] ) : '';
						if ( $card_url === '' ) {
							$card_url = '#';
						}
						?>
						<a class="reviews__item" href="<?php echo esc_url( $card_url ); ?>">
							<span class="reviews__item-number line-caps-bold-16-15"><span><?php echo (int) $card['rating']; ?></span>/5</span>
							<div class="reviews__list-rating">
								<?php $render_stars( $card['rating'] ); ?>
							</div>
							<div class="reviews__item-link line-caps-bold-16-15"><?php echo tolstenko_kses_html( $card['title'] ); ?></div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( $any_tabs ) : ?>
				<div class="reviews__tabs">
					<div class="reviews__labels">
						<?php if ( $has_thanks ) : ?>
							<label class="reviews__label line-caps-bold-13-15">
								<input type="radio" value="thenks" name="reviews" <?php echo $checked ? 'checked' : ''; ?>>
								<span><?php esc_html_e( 'Благодарности', 'tolstenko-theme' ); ?></span>
							</label>
							<?php $checked = ''; ?>
						<?php endif; ?>
						<?php if ( $has_video ) : ?>
							<label class="reviews__label line-caps-bold-13-15">
								<input type="radio" value="video" name="reviews" <?php echo $checked ? 'checked' : ''; ?>>
								<span><?php esc_html_e( 'Видео', 'tolstenko-theme' ); ?></span>
							</label>
							<?php $checked = ''; ?>
						<?php endif; ?>
						<?php if ( $has_text ) : ?>
							<label class="reviews__label line-caps-bold-13-15">
								<input type="radio" value="text" name="reviews" <?php echo $checked ? 'checked' : ''; ?>>
								<span><?php esc_html_e( 'Текстовые', 'tolstenko-theme' ); ?></span>
							</label>
							<?php $checked = ''; ?>
						<?php endif; ?>
						<?php if ( $has_messengers ) : ?>
							<label class="reviews__label line-caps-bold-13-15">
								<input type="radio" value="messengers" name="reviews" <?php echo $checked ? 'checked' : ''; ?>>
								<span><?php esc_html_e( 'Месседжеры', 'tolstenko-theme' ); ?></span>
							</label>
							<?php $checked = ''; ?>
						<?php endif; ?>
					</div>
				</div>

				<?php if ( $has_thanks ) : ?>
					<div class="reviews__splide reviews__splide--thenks splide">
						<div class="splide__track swiper">
							<div class="reviews__list splide__list swiper-wrapper">
								<?php foreach ( $thanks_items as $item ) : ?>
									<div class="reviews__list-item splide__slide swiper-slide">
										<div class="reviews__list-image">
											<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['image']['alt'] ); ?>" data-fancybox="reviews-thenks">
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php $render_splide_nav(); ?>
					</div>
				<?php endif; ?>

				<?php if ( $has_video ) : ?>
					<div class="reviews__splide reviews__splide--video splide">
						<div class="splide__track swiper">
							<div class="reviews__list splide__list swiper-wrapper">
								<?php foreach ( $video_items as $item ) : ?>
									<div class="reviews__list-item reviews__list-item--video<?php echo $item['has_meta'] ? ' reviews__list-item--video-meta' : ''; ?> splide__slide swiper-slide">
										<button
											type="button"
											class="reviews__list-preview reviews__list-preview--embed"
											data-video-src="<?php echo esc_url( $item['src'] ); ?>"
											<?php echo $item['rutube_id'] !== '' ? 'data-rutube-id="' . esc_attr( $item['rutube_id'] ) . '"' : ''; ?>
											aria-label="<?php echo $item['name'] !== '' ? esc_attr( $item['name'] ) : esc_attr__( 'Открыть видео', 'tolstenko-theme' ); ?>"
										>
											<div class="reviews__list-embed">
												<img
													class="reviews__list-poster"
													<?php echo $item['poster'] !== '' ? 'src="' . esc_url( $item['poster'] ) . '"' : ''; ?>
													alt="<?php echo esc_attr( $item['name'] ); ?>"
													loading="lazy"
													decoding="async"
												>
											</div>
											<span class="reviews__list-play video__btn" aria-hidden="true">
												<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
													<path fill-rule="evenodd" clip-rule="evenodd" d="M3.82263 0.0285661C3.40033 0.0281482 2.98488 0.135329 2.61549 0.339995C2.23667 0.533082 1.91754 0.82562 1.6923 1.18625C1.46706 1.54688 1.34421 1.96201 1.33691 2.38714V17.64C1.34421 18.0651 1.46706 18.4803 1.6923 18.8409C1.91754 19.2015 2.23667 19.4941 2.61549 19.6871C2.99158 19.896 3.41545 20.0038 3.84565 19.9998C4.27584 19.9957 4.69764 19.8802 5.06977 19.6643L17.3983 12.0386C17.7788 11.8468 18.0985 11.5532 18.3219 11.1904C18.5452 10.8276 18.6634 10.4099 18.6633 9.98384C18.6631 9.5578 18.5446 9.14017 18.321 8.77753C18.0974 8.41488 17.7775 8.12146 17.3969 7.92999L5.06834 0.361423L5.04549 0.348566C4.67225 0.138309 4.25101 0.0280782 3.82263 0.0285661Z" />
												</svg>
											</span>
										</button>

										<?php if ( $item['has_meta'] ) : ?>
											<div class="reviews__list-video-meta">
												<?php if ( $item['logo']['url'] !== '' ) : ?>
													<div class="reviews__list-video-logo">
														<img src="<?php echo esc_url( $item['logo']['url'] ); ?>" alt="<?php echo esc_attr( $item['logo']['alt'] ); ?>">
													</div>
												<?php endif; ?>
												<div class="reviews__list-video-text">
													<div class="reviews__list-video-name paragraph-15-25"><?php echo esc_html( $item['name'] ); ?></div>
													<div class="reviews__list-video-desc paragraph-13-20"><?php echo esc_html( $item['text'] ); ?></div>
												</div>
											</div>
										<?php elseif ( $item['logo']['url'] !== '' ) : ?>
											<div class="reviews__list-logo">
												<img src="<?php echo esc_url( $item['logo']['url'] ); ?>" alt="<?php echo esc_attr( $item['logo']['alt'] ); ?>">
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php $render_splide_nav(); ?>
					</div>
				<?php endif; ?>

				<?php if ( $has_text ) : ?>
					<div class="reviews__splide reviews__splide--text splide">
						<div class="splide__track swiper">
							<div class="reviews__list splide__list swiper-wrapper">
								<?php foreach ( $text_items as $item ) : ?>
									<div class="reviews__list-item reviews__list-item--text splide__slide swiper-slide">
										<div class="reviews__list-top">
											<?php if ( $item['photo']['url'] !== '' ) : ?>
												<div class="reviews__list-photo">
													<img src="<?php echo esc_url( $item['photo']['url'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>">
												</div>
											<?php endif; ?>
											<div class="reviews__list-wrapper">
												<div class="reviews__list-name"><?php echo esc_html( $item['name'] ); ?></div>
												<?php if ( $item['position'] !== '' ) : ?>
													<div class="reviews__list-position"><?php echo esc_html( $item['position'] ); ?></div>
												<?php endif; ?>
												<div class="reviews__list-rating">
													<?php $render_stars( $item['rating'] ); ?>
												</div>
											</div>
										</div>

										<div class="reviews__list-redactor redactor">
											<?php echo function_exists( 'tolstenko_kses_redactor' ) ? tolstenko_kses_redactor( $item['redactor'] ) : wp_kses_post( $item['redactor'] ); ?>
										</div>

										<?php
										$contact      = is_array( $item['contact'] ) ? $item['contact'] : array();
										$case         = is_string( $item['case'] ) ? trim( $item['case'] ) : '';
										$contact_title = trim( (string) ( $contact['title'] ?? '' ) );
										$contact_url   = trim( (string) ( $contact['url'] ?? '' ) );
										$show_contact  = ( $contact_title !== '' || $contact_url !== '' );
										$show_case     = ( $case !== '' );
										if ( $show_contact || $show_case ) :
											?>
											<div class="reviews__list-btns">
												<?php if ( $show_contact ) : ?>
													<?php
													$c_url   = function_exists( 'tolstenko_url_or_modal' ) ? tolstenko_url_or_modal( $contact_url ) : ( $contact_url !== '' ? $contact_url : '#modal' );
													$c_title = $contact_title !== '' ? $contact_title : __( 'Связаться', 'tolstenko-theme' );
													?>
													<a class="reviews__list-btn reviews__list-btn--transparent default-btn" href="<?php echo esc_url( $c_url ); ?>">
														<!-- <svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
															<path d="M13.9999 15.0003C14.9333 15.0003 15.4 15.0003 15.7566 14.8187C16.0702 14.6589 16.3251 14.4039 16.4849 14.0903C16.6666 13.7338 16.6666 13.2671 16.6666 12.3337V4.33366C16.6666 3.40024 16.6666 2.93353 16.4849 2.57701C16.3251 2.2634 16.0702 2.00844 15.7566 1.84865C15.4 1.66699 14.9333 1.66699 13.9999 1.66699H9.33325C8.39983 1.66699 7.93312 1.66699 7.5766 1.84865C7.263 2.00844 7.00803 2.2634 6.84824 2.57701C6.66659 2.93353 6.66659 3.40024 6.66659 4.33366M5.99992 18.3337H10.6666C11.6 18.3337 12.0667 18.3337 12.4232 18.152C12.7368 17.9922 12.9918 17.7372 13.1516 17.4236C13.3333 17.0671 13.3333 16.6004 13.3333 15.667V7.66699C13.3333 6.73357 13.3333 6.26686 13.1516 5.91034C12.9918 5.59674 12.7368 5.34177 12.4232 5.18198C12.0667 5.00033 11.6 5.00033 10.6666 5.00033H5.99992C5.0665 5.00033 4.59979 5.00033 4.24327 5.18198C3.92966 5.34177 3.6747 5.59674 3.51491 5.91034C3.33325 6.26686 3.33325 6.73357 3.33325 7.66699V15.667C3.33325 16.6004 3.33325 17.0671 3.51491 17.4236C3.6747 17.7372 3.92966 17.9922 4.24327 18.152C4.59979 18.3337 5.0665 18.3337 5.99992 18.3337Z" stroke-width="2" />
														</svg> -->
														<?php echo esc_html( $c_title ); ?>
													</a>
												<?php endif; ?>
												<?php if ( $show_case ) : ?>
													<a class="reviews__list-btn reviews__list-btn--shablon default-btn" href="<?php echo esc_url( $case ); ?>">
														<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
															<path d="M12 4L12 13M16 10L12.7071 13.2929C12.3166 13.6834 11.6834 13.6834 11.2929 13.2929L8 10M20 20L4 20" stroke-width="2" stroke-linecap="round" />
														</svg>
														<?php esc_html_e( 'Получить кейс', 'tolstenko-theme' ); ?>
													</a>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php $render_splide_nav(); ?>
					</div>
				<?php endif; ?>

				<?php if ( $has_messengers ) : ?>
					<div class="reviews__splide reviews__splide--messengers splide">
						<div class="splide__track swiper">
							<div class="reviews__list splide__list swiper-wrapper">
								<?php foreach ( $messenger_items as $item ) : ?>
									<div class="reviews__list-item splide__slide swiper-slide">
										<div class="reviews__list-screenshot">
											<img src="<?php echo esc_url( $item['image']['url'] ); ?>" alt="<?php echo esc_attr( $item['image']['alt'] ); ?>" data-fancybox="reviews-messengers">
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
						<?php $render_splide_nav(); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</section>
