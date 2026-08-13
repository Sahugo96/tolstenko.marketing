<?php
/**
 * Комментарии: CF7-форма + кураторский список (разметка как в Tolstenko).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = (int) get_the_ID();
$pt      = $post_id ? (string) get_post_type( $post_id ) : '';
// Кейсы без комментариев, даже если CPT в content body.
$ok_pt = in_array( $pt, array( 'blog', 'actions' ), true );
if ( ! $post_id || ! $ok_pt ) {
	return;
}

$comments = get_post_meta( $post_id, 'blog_comments', true );
if ( ! is_array( $comments ) ) {
	$comments = array();
}

if ( ! function_exists( 'tolstenko_render_blog_comment_item' ) ) {
	/**
	 * Разметка кураторского комментария.
	 *
	 * @param array $item Comment row.
	 */
	function tolstenko_render_blog_comment_item( array $item ) {
		$photo = function_exists( 'tolstenko_get_image_attrs' ) ? tolstenko_get_image_attrs( $item['photo'] ?? array(), 'thumbnail' ) : null;
		$name  = trim( (string) ( $item['name'] ?? '' ) );
		$date  = trim( (string) ( $item['date'] ?? '' ) );
		$text  = trim( (string) ( $item['text'] ?? '' ) );
		?>
		<li class="comments__item">
			<article class="comments__article">
				<?php if ( $photo ) : ?>
					<div class="comments__avatar">
						<img
							class="comments__avatar-img"
							src="<?php echo esc_url( $photo['url'] ); ?>"
							alt="<?php echo esc_attr( $photo['alt'] ?: $name ); ?>"
							loading="lazy"
							decoding="async"
						>
					</div>
				<?php endif; ?>

				<div class="comments__body">
					<div class="comments__meta">
						<?php if ( $name !== '' ) : ?>
							<div class="comments__author line-caps-bold-13-15"><?php echo esc_html( $name ); ?></div>
						<?php endif; ?>

						<?php if ( $date !== '' ) : ?>
							<div class="comments__date-time">
								<span class="comments__date">
									<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
										<path d="M5.333 1.333V3.333M10.667 1.333V3.333M2.667 6.667H13.333M3.333 2.667H12.667C13.403 2.667 14 3.264 14 4V13.333C14 14.069 13.403 14.667 12.667 14.667H3.333C2.597 14.667 2 14.069 2 13.333V4C2 3.264 2.597 2.667 3.333 2.667Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
									<?php echo esc_html( $date ); ?>
								</span>
							</div>
						<?php endif; ?>
					</div>

					<?php if ( $text !== '' ) : ?>
						<div class="comments__text paragraph-15-15">
							<?php echo nl2br( esc_html( $text ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			</article>

			<?php
			$replies = $item['replies'] ?? array();
			if ( is_array( $replies ) && $replies ) :
				?>
				<ol class="children">
					<?php foreach ( $replies as $reply ) : ?>
						<?php tolstenko_render_blog_comment_item( $reply ); ?>
					<?php endforeach; ?>
				</ol>
			<?php endif; ?>
		</li>
		<?php
	}
}
?>

<section class="comments section" id="comments">
	<div class="container">
		<div class="comments__wrapper">
			<div class="comments__inner br-30">
				<h2 class="comments__title line-caps-bold-16-15">Комментарий</h2>

				<div class="comments__form form">
					<span class="comments__form-title line-caps-bold-16-15">ОСТАВИТЬ КОММЕНТАРИЙ</span>

					<?php echo do_shortcode( '[contact-form-7 id="5bfc3f8" title="Комментарий"]' ); ?>
				</div>
			</div>

			<?php if ( $comments ) : ?>
				<div class="comments__list-card br-30">
					<ol class="comments__list">
						<?php foreach ( $comments as $comment ) : ?>
							<?php tolstenko_render_blog_comment_item( $comment ); ?>
						<?php endforeach; ?>
					</ol>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
