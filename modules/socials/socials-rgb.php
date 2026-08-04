<?php
/**
 * Цветные соцсети из «Настройки сайта → Контактные данные».
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = array();
if ( function_exists( 'tolstenko_get_contact_data' ) ) {
	$data  = tolstenko_get_contact_data( true );
	$items = is_array( $data['socials_rgb'] ?? null ) ? $data['socials_rgb'] : array();
}

if ( empty( $items ) ) {
	return;
}
?>
<div class="socials">
	<?php foreach ( $items as $item ) :
		$link = isset( $item['link'] ) ? trim( (string) $item['link'] ) : '';
		$icon = isset( $item['icon'] ) ? trim( (string) $item['icon'] ) : '';
		$text = isset( $item['text'] ) ? trim( (string) $item['text'] ) : '';
		if ( $link === '' ) {
			continue;
		}
		$icon_url = function_exists( 'tolstenko_contact_resolve_icon_url' )
			? tolstenko_contact_resolve_icon_url( $icon )
			: $icon;
		if ( $icon_url === '' && $text === '' ) {
			continue;
		}
		?>
		<a href="<?php echo esc_url( $link ); ?>" class="socials__link" target="_blank" rel="noopener noreferrer"<?php echo $text !== '' ? ' title="' . esc_attr( $text ) . '"' : ''; ?>>
			<?php if ( $icon_url !== '' ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="<?php echo esc_attr( $text !== '' ? $text : 'Social' ); ?>">
			<?php else : ?>
				<span><?php echo esc_html( $text ); ?></span>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
