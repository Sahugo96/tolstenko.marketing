<?php
/**
 * Hybrid single: hero/meta + Gutenberg body (TOC) + sidebar.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	return;
}

$bem = function_exists( 'tolstenko_get_single_content_bem' )
	? tolstenko_get_single_content_bem()
	: 'single-blog';

$raw_content = (string) get_post_field( 'post_content', $post_id );
$body_html   = $raw_content !== '' ? apply_filters( 'the_content', $raw_content ) : '';
$toc_data    = function_exists( 'tolstenko_prepare_blog_toc' )
	? tolstenko_prepare_blog_toc( $body_html )
	: array(
		'html'  => $body_html,
		'items' => array(),
	);
$body_html = $toc_data['html'];
$toc_items = $toc_data['items'];

if ( function_exists( 'tolstenko_adapt_single_content_classes' ) ) {
	$body_html = tolstenko_adapt_single_content_classes( $body_html );
}

$pretext = (string) get_post_meta( $post_id, 'single-blog_text', true );

$thumbnail_url    = '';
$thumbnail_srcset = '';
$thumbnail_alt    = get_the_title( $post_id );
if ( has_post_thumbnail( $post_id ) ) {
	$thumbnail_id     = (int) get_post_thumbnail_id( $post_id );
	$thumbnail_url    = (string) wp_get_attachment_image_url( $thumbnail_id, 'full' );
	$thumbnail_srcset = (string) ( wp_get_attachment_image_srcset( $thumbnail_id, 'full' ) ?: '' );
	$thumb_alt        = (string) get_post_meta( $thumbnail_id, '_wp_attachment_image_alt', true );
	if ( $thumb_alt !== '' ) {
		$thumbnail_alt = $thumb_alt;
	}
}

$director       = function_exists( 'tolstenko_get_single_blog_director' )
	? tolstenko_get_single_blog_director( $post_id )
	: array();
$director_photo = ! empty( $director['photo'] ) && function_exists( 'tolstenko_get_image_attrs' )
	? tolstenko_get_image_attrs( $director['photo'] )
	: null;

$sidebar_person = function_exists( 'tolstenko_get_vacancy_sidebar_person' )
	? tolstenko_get_vacancy_sidebar_person()
	: array(
		'photo_id' => 0,
		'name'     => '',
		'text'     => '',
	);
$sidebar_defaults = function_exists( 'tolstenko_get_block_defaults' )
	? tolstenko_get_block_defaults( 'vacancy_content' )
	: array();
$sidebar_photo_id  = (int) ( $sidebar_person['photo_id'] ?? 0 );
$sidebar_photo_url = $sidebar_photo_id ? (string) wp_get_attachment_image_url( $sidebar_photo_id, 'medium' ) : '';
$sidebar_photo_alt = $sidebar_photo_id ? (string) get_post_meta( $sidebar_photo_id, '_wp_attachment_image_alt', true ) : '';
$sidebar_name      = trim( (string) ( $sidebar_person['name'] ?? '' ) );
$sidebar_text      = (string) ( $sidebar_person['text'] ?? '' );
$sidebar_btn       = trim( (string) ( $sidebar_defaults['sidebar_btn'] ?? '' ) );
if ( $sidebar_btn === '' ) {
	$sidebar_btn = __( 'Бесплатный аудит', 'tolstenko-theme' );
}
$sidebar_btn_url = function_exists( 'tolstenko_url_or_modal' )
	? tolstenko_url_or_modal( (string) ( $sidebar_defaults['sidebar_btn_url'] ?? '' ) )
	: '#modal';

// Если сайдбар вакансии пуст — берём автора статьи (выбранного в метабоксе).
if ( $sidebar_photo_url === '' && $director_photo ) {
	$sidebar_photo_url = (string) ( $director_photo['url'] ?? '' );
	$sidebar_photo_alt = (string) ( $director_photo['alt'] ?? ( $director['name'] ?? '' ) );
}
if ( $sidebar_name === '' && ! empty( $director['name'] ) ) {
	$sidebar_name = (string) $director['name'];
}
if ( trim( wp_strip_all_tags( $sidebar_text ) ) === '' && ! empty( $director['description'] ) ) {
	$sidebar_text = (string) $director['description'];
}

$has_contact_socials_rgb = false;
if ( function_exists( 'tolstenko_get_contact_data' ) ) {
	$cd = tolstenko_get_contact_data( true );
	$has_contact_socials_rgb = ! empty( $cd['socials_rgb'] ) && is_array( $cd['socials_rgb'] );
}

$blog_actions = get_post_meta( $post_id, 'blog_actions', true );
$actions_ids  = array();
if ( is_array( $blog_actions ) ) {
	foreach ( $blog_actions as $item ) {
		if ( is_numeric( $item ) ) {
			$actions_ids[] = (int) $item;
		}
	}
}
$actions_ids = array_values( array_filter( array_unique( $actions_ids ) ) );

$has_sidebar = (
	$sidebar_photo_url !== ''
	|| $sidebar_name !== ''
	|| $sidebar_text !== ''
	|| $has_contact_socials_rgb
	|| ! empty( $actions_ids )
);
?>
<section class="<?php echo esc_attr( $bem ); ?> section">
	<div class="container">
		<div class="<?php echo esc_attr( $bem ); ?>__wrapper">
			<div class="<?php echo esc_attr( $bem ); ?>__content">
				<div class="<?php echo esc_attr( $bem ); ?>__top">
					<?php if ( $thumbnail_url !== '' ) : ?>
						<img
							class="<?php echo esc_attr( $bem ); ?>__top-img"
							src="<?php echo esc_url( $thumbnail_url ); ?>"
							<?php if ( $thumbnail_srcset !== '' ) : ?>
								srcset="<?php echo esc_attr( $thumbnail_srcset ); ?>"
							<?php endif; ?>
							alt="<?php echo esc_attr( $thumbnail_alt ); ?>"
							decoding="async"
							fetchpriority="high"
						>
					<?php endif; ?>

					<h1 class="<?php echo esc_attr( $bem ); ?>__title h2"><?php the_title(); ?></h1>

					<?php if ( $pretext !== '' ) : ?>
						<div class="<?php echo esc_attr( $bem ); ?>__pretext paragraph-15-25"><?php echo esc_html( $pretext ); ?></div>
					<?php endif; ?>

					<div class="blog-card__stats">
						<?php get_template_part( 'modules/stats/stats' ); ?>
					</div>

					<?php if ( $director_photo && ( ( $director['name'] ?? '' ) !== '' || ( $director['title'] ?? '' ) !== '' ) ) : ?>
						<div class="<?php echo esc_attr( $bem ); ?>__director">
							<img
								class="<?php echo esc_attr( $bem ); ?>__director-photo"
								src="<?php echo esc_url( $director_photo['url'] ); ?>"
								<?php if ( ! empty( $director_photo['srcset'] ) ) : ?>
									srcset="<?php echo esc_attr( $director_photo['srcset'] ); ?>"
								<?php endif; ?>
								alt="<?php echo esc_attr( $director_photo['alt'] ?: ( $director['name'] ?? '' ) ); ?>"
								loading="lazy"
								decoding="async"
							>
							<div class="<?php echo esc_attr( $bem ); ?>__director-wrapper">
								<?php if ( ( $director['title'] ?? '' ) !== '' ) : ?>
									<div class="<?php echo esc_attr( $bem ); ?>__director-title paragraph-15-25"><?php echo esc_html( $director['title'] ); ?></div>
								<?php endif; ?>
								<?php if ( ( $director['name'] ?? '' ) !== '' ) : ?>
									<div class="<?php echo esc_attr( $bem ); ?>__director-name line-caps-bold-13-15"><?php echo esc_html( $director['name'] ); ?></div>
								<?php endif; ?>
							</div>
							<?php if ( ! empty( $director['show_quest'] ) ) : ?>
								<a class="<?php echo esc_attr( $bem ); ?>__director-btn default-btn" href="#comments"><?php esc_html_e( 'Задать вопрос', 'tolstenko-theme' ); ?></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="<?php echo esc_attr( $bem ); ?>__content-blocks">
					<?php if ( $toc_items ) : ?>
						<div class="<?php echo esc_attr( $bem ); ?>__content-article br-30">
							<div class="<?php echo esc_attr( $bem ); ?>__content-article-title line-caps-bold-16-15"><?php echo esc_html( $bem === 'single-actions' ? __( 'Содержание', 'tolstenko-theme' ) : __( 'Содержание статьи', 'tolstenko-theme' ) ); ?></div>
							<div class="<?php echo esc_attr( $bem ); ?>__content-article-items">
								<ul class="<?php echo esc_attr( $bem ); ?>__toc">
									<?php foreach ( $toc_items as $item ) : ?>
										<li class="<?php echo esc_attr( $bem ); ?>__toc-item <?php echo esc_attr( $bem ); ?>__toc-item--h<?php echo (int) $item['level']; ?>">
											<a class="<?php echo esc_attr( $bem ); ?>__toc-link paragraph-15-25" href="#<?php echo esc_attr( $item['id'] ); ?>">
												<?php echo esc_html( $item['text'] ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $body_html !== '' ) : ?>
						<div class="<?php echo esc_attr( $bem ); ?>__content-contents redactor">
							<?php echo $body_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered via the_content. ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $has_sidebar ) : ?>
				<div class="<?php echo esc_attr( $bem ); ?>__right">
					<div class="<?php echo esc_attr( $bem ); ?>__right-info">
						<div class="<?php echo esc_attr( $bem ); ?>__right-wrapper">
							<?php if ( $sidebar_photo_url !== '' ) : ?>
								<img class="<?php echo esc_attr( $bem ); ?>__right-photo" src="<?php echo esc_url( $sidebar_photo_url ); ?>" alt="<?php echo esc_attr( $sidebar_photo_alt ); ?>" loading="lazy" decoding="async">
							<?php endif; ?>
							<?php if ( $sidebar_name !== '' ) : ?>
								<div class="<?php echo esc_attr( $bem ); ?>__right-name line-caps-bold-13-15"><?php echo esc_html( $sidebar_name ); ?></div>
							<?php endif; ?>
							<?php if ( $sidebar_text !== '' ) : ?>
								<div class="<?php echo esc_attr( $bem ); ?>__right-text paragraph-15-25"><?php echo nl2br( esc_html( wp_strip_all_tags( $sidebar_text ) ) ); ?></div>
							<?php endif; ?>
							<?php get_template_part( 'modules/socials/socials-rgb' ); ?>
							<a class="<?php echo esc_attr( $bem ); ?>__right-btn default-btn default-btn--red" href="<?php echo esc_url( $sidebar_btn_url ); ?>"><?php echo esc_html( $sidebar_btn ); ?></a>
							<a class="<?php echo esc_attr( $bem ); ?>__right-btn default-btn" href="#comments"><?php esc_html_e( 'Задать вопрос', 'tolstenko-theme' ); ?></a>
						</div>

						<?php if ( ! empty( $actions_ids ) ) : ?>
							<?php
							$actions_query = new WP_Query(
								array(
									'post_type'      => 'actions',
									'post__in'       => $actions_ids,
									'orderby'        => 'post__in',
									'posts_per_page' => count( $actions_ids ),
									'post_status'    => 'publish',
								)
							);
							?>
							<?php if ( $actions_query->have_posts() ) : ?>
								<div class="<?php echo esc_attr( $bem ); ?>__right-actions splide">
									<div class="splide__track swiper">
										<div class="<?php echo esc_attr( $bem ); ?>__right-list splide__list swiper-wrapper">
											<?php
											while ( $actions_query->have_posts() ) :
												$actions_query->the_post();
												$action_thumb = get_the_post_thumbnail( get_the_ID(), 'medium' );
												$action_desc  = (string) get_post_meta( get_the_ID(), 'action_description', true );
												?>
												<article class="<?php echo esc_attr( $bem ); ?>__right-list-item splide__slide swiper-slide">
													<a class="<?php echo esc_attr( $bem ); ?>__right-list-link line-caps-bold-15-15" href="<?php echo esc_url( get_permalink() ); ?>">
														<div class="<?php echo esc_attr( $bem ); ?>__right-list-img<?php echo $action_thumb ? '' : ' ' . esc_attr( $bem ) . '__right-list-img--empty'; ?>">
															<?php
															if ( $action_thumb ) {
																echo $action_thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
															}
															?>
														</div>
														<span class="<?php echo esc_attr( $bem ); ?>__right-list-name"><?php the_title(); ?></span>
													</a>
												</article>
											<?php endwhile; ?>
											<?php wp_reset_postdata(); ?>
										</div>
									</div>
									<div class="splide__bottom">
										<div class="splide__pagination"></div>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
