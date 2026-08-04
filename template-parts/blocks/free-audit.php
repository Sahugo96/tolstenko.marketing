<?php
/**
 * Блок «Бесплатный аудит»: список плюсов + кнопка.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $block_attrs ) ) {
	$block_attrs = array();
}
$defaults = function_exists( 'tolstenko_get_block_defaults' ) ? tolstenko_get_block_defaults( 'free_audit' ) : array();
if ( ! is_array( $defaults ) ) {
	$defaults = array();
}

$items     = array();
$raw_items = array();
if ( ! empty( $block_attrs['block_free_audit_items'] ) && is_array( $block_attrs['block_free_audit_items'] ) ) {
	$raw_items = $block_attrs['block_free_audit_items'];
} elseif ( ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	$raw_items = $defaults['items'];
}
foreach ( $raw_items as $it ) {
	$text = is_array( $it ) ? trim( (string) ( $it['text'] ?? '' ) ) : trim( (string) $it );
	if ( $text !== '' ) {
		$items[] = $text;
	}
}

$btn_text = ! empty( $block_attrs['block_free_audit_btn_text'] )
	? (string) $block_attrs['block_free_audit_btn_text']
	: (string) ( $defaults['btn_text'] ?? '' );
$btn_url  = ! empty( $block_attrs['block_free_audit_btn_url'] )
	? (string) $block_attrs['block_free_audit_btn_url']
	: (string) ( $defaults['btn_url'] ?? '' );
$btn_url  = tolstenko_url_or_modal( $btn_url );

if ( empty( $items ) && $btn_text === '' ) {
	return;
}
?>
<section class="free-audit section">
	<div class="free-audit__inner">
		<?php foreach ( $items as $text ) : ?>
			<div class="free-audit__item line-13-15">
				<svg class="free-audit__item-svg" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
					<path class="fill" d="M12.4999 22.9167C13.8681 22.9184 15.2231 22.6497 16.4872 22.1261C17.7512 21.6025 18.8993 20.8343 19.8655 19.8657C20.8342 18.8994 21.6024 17.7513 22.126 16.4873C22.6496 15.2233 22.9183 13.8682 22.9166 12.5C22.9183 11.1319 22.6496 9.77683 22.126 8.5128C21.6024 7.24877 20.8342 6.10066 19.8655 5.13442C18.8993 4.16576 17.7512 3.39756 16.4872 2.87396C15.2231 2.35037 13.8681 2.08169 12.4999 2.08338C11.1317 2.08169 9.77671 2.35037 8.51268 2.87396C7.24865 3.39756 6.10054 4.16576 5.1343 5.13442C4.16564 6.10066 3.39744 7.24877 2.87384 8.5128C2.35024 9.77683 2.08157 11.1319 2.08326 12.5C2.08157 13.8682 2.35024 15.2233 2.87384 16.4873C3.39744 17.7513 4.16564 18.8994 5.1343 19.8657C6.10054 20.8343 7.24865 21.6025 8.51268 22.1261C9.77671 22.6497 11.1317 22.9184 12.4999 22.9167Z" />
					<path class="stroke" d="M8.33325 12.5L11.4583 15.625L17.7083 9.375" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
				</svg>
				<?php echo tolstenko_kses_html( $text ); ?>
			</div>
		<?php endforeach; ?>

		<?php if ( $btn_text !== '' ) : ?>
			<a class="free-audit__btn default-btn default-btn--red" href="<?php echo esc_url( $btn_url ); ?>"><?php echo esc_html( $btn_text ); ?></a>
		<?php endif; ?>
	</div>
</section>
