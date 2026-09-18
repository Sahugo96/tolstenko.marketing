<?php
/**
 * Статья: определение / термин.
 *
 * Вид 1 (define): метка + термин + текст; тире между термином и текстом — в шаблоне.
 * Вид 2 (card): заголовок + текст.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_definition' )
	: array();

$mode = sanitize_key( (string) ( $attrs['block_blog_definition_mode'] ?? '' ) );
if ( $mode === '' ) {
	$mode = sanitize_key( (string) ( $defaults['mode'] ?? 'define' ) );
}
if ( ! in_array( $mode, array( 'define', 'card' ), true ) ) {
	$mode = 'define';
}

$kicker = trim( (string) ( $attrs['block_blog_definition_kicker'] ?? '' ) );
$term   = trim( (string) ( $attrs['block_blog_definition_term'] ?? '' ) );
$suffix = trim( (string) ( $attrs['block_blog_definition_suffix'] ?? '' ) );
$title  = trim( (string) ( $attrs['block_blog_definition_title'] ?? '' ) );
$text   = trim( (string) ( $attrs['block_blog_definition_text'] ?? '' ) );

if ( $kicker === '' ) {
	$kicker = trim( (string) ( $defaults['kicker'] ?? '' ) );
}
if ( $term === '' ) {
	$term = trim( (string) ( $defaults['term'] ?? '' ) );
}
if ( $suffix === '' ) {
	$suffix = trim( (string) ( $defaults['suffix'] ?? '' ) );
}
if ( $title === '' ) {
	$title = trim( (string) ( $defaults['title'] ?? '' ) );
}
if ( $text === '' ) {
	$text = trim( (string) ( $defaults['text'] ?? '' ) );
}

$suffix = preg_replace( '/^[\s—\-–]+/u', '', $suffix );

if ( $mode === 'define' ) {
	if ( $kicker === '' ) {
		$kicker = __( 'Определение', 'tolstenko-theme' );
	}
	if ( $term === '' && $suffix === '' ) {
		return;
	}
	?>
	<div class="single-blog__define single-blog__define--term">
		<?php if ( $kicker !== '' ) : ?>
			<p class="single-blog__define-kicker">
				<span class="single-blog__define-kicker-icon" aria-hidden="true"></span>
				<?php echo esc_html( $kicker ); ?>
			</p>
		<?php endif; ?>
		<p class="single-blog__define-lead">
			<?php if ( $term !== '' ) : ?>
				<strong class="single-blog__define-term"><?php echo esc_html( $term ); ?></strong>
			<?php endif; ?>
			<?php if ( $suffix !== '' ) : ?>
				<span class="single-blog__define-fixed"><?php echo ( $term !== '' ? ' — ' : '' ) . esc_html( $suffix ); ?></span>
			<?php endif; ?>
		</p>
	</div>
	<?php
	return;
}

if ( $title === '' && $text === '' ) {
	return;
}
?>
<div class="single-blog__define single-blog__define--note">
	<?php if ( $title !== '' ) : ?>
		<p class="single-blog__define-title"><?php echo esc_html( $title ); ?></p>
	<?php endif; ?>
	<?php if ( $text !== '' ) : ?>
		<p class="single-blog__define-text"><?php echo esc_html( $text ); ?></p>
	<?php endif; ?>
</div>
<?php
