<?php
/**
 * Блок «Карты» (.maps): заголовок, вкладки адресов, iframe.
 * Данные: атрибуты Gutenberg → дефолты блоков (Страница контактов).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$block_attrs = tolstenko_block_attributes();

$defaults = tolstenko_block_defaults( 'contacts_maps' );

$maps_title = '';
if ( ! empty( $block_attrs['block_contacts_maps_title'] ) ) {
	$maps_title = (string) $block_attrs['block_contacts_maps_title'];
} elseif ( ! empty( $defaults['title'] ) ) {
	$maps_title = (string) $defaults['title'];
}

$title_tag = tolstenko_block_heading_tag( $block_attrs, 'block_contacts_maps_title_tag', 'h2' );

$maps_items = array();
if ( ! empty( $block_attrs['block_contacts_maps_items'] ) && is_array( $block_attrs['block_contacts_maps_items'] ) ) {
	foreach ( $block_attrs['block_contacts_maps_items'] as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$address = sanitize_text_field( (string) ( $item['address'] ?? '' ) );
		$map     = trim( (string) ( $item['map'] ?? '' ) );
		if ( $address === '' && $map === '' ) {
			continue;
		}
		$maps_items[] = array(
			'address' => $address,
			'map'     => $map,
		);
	}
}
if ( empty( $maps_items ) && ! empty( $defaults['items'] ) && is_array( $defaults['items'] ) ) {
	foreach ( $defaults['items'] as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$address = sanitize_text_field( (string) ( $item['address'] ?? '' ) );
		$map     = trim( (string) ( $item['map'] ?? '' ) );
		if ( $address === '' && $map === '' ) {
			continue;
		}
		$maps_items[] = array(
			'address' => $address,
			'map'     => $map,
		);
	}
}

if ( empty( $maps_items ) && $maps_title === '' ) {
	return;
}
?>
<section class="maps section">
	<div class="container">
		<div class="maps__inner">
			<div class="maps__top section-top">
				<?php if ( $maps_title !== '' ) : ?>
					<<?php echo esc_attr( $title_tag ); ?> class="maps__title h2"><?php echo esc_html( $maps_title ); ?></<?php echo esc_attr( $title_tag ); ?>>
				<?php endif; ?>

				<?php if ( $maps_items ) : ?>
					<div class="maps__tabs tabs" aria-label="<?php esc_attr_e( 'Адреса на карте', 'tolstenko-theme' ); ?>">
						<div class="maps__labels tabs__labels">
							<?php
							$checked = ' checked';
							foreach ( $maps_items as $index => $item ) :
								?>
								<label class="maps__label tabs__label line-caps-bold-13-15">
									<input type="radio" name="maps" value="map-<?php echo esc_attr( (string) $index ); ?>" data-tab-index="<?php echo esc_attr( (string) $index ); ?>"<?php echo $checked; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
									<span><?php echo esc_html( $item['address'] ?? '' ); ?></span>
								</label>
								<?php
								$checked = '';
							endforeach;
							?>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $maps_items ) : ?>
				<div class="maps__items">
					<?php foreach ( $maps_items as $index => $item ) : ?>
						<div class="maps__item<?php echo 0 === (int) $index ? ' is-active' : ''; ?>" data-tab-index="<?php echo esc_attr( (string) $index ); ?>"<?php echo 0 !== (int) $index ? ' hidden' : ''; ?>>
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- iframe from trusted editors / sanitized defaults.
							echo $item['map'] ?? '';
							?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
<script>
(function () {
	var section = document.querySelectorAll('section.maps');
	section = section.length ? section[section.length - 1] : null;
	if (!section) return;
	var radios = section.querySelectorAll('input[name="maps"]');
	var items = section.querySelectorAll('.maps__item');
	if (!radios.length || !items.length) return;
	radios.forEach(function (radio) {
		radio.addEventListener('change', function () {
			var idx = radio.getAttribute('data-tab-index');
			items.forEach(function (item) {
				var on = item.getAttribute('data-tab-index') === idx;
				item.hidden = !on;
				item.classList.toggle('is-active', on);
			});
		});
	});
})();
</script>
