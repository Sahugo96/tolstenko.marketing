<?php
/**
 * Статья: предупреждения / заметки (layout warning).
 *
 * Типы:
 * - warn / pin / ide / err — встроенные иконки + цветная плашка.
 * - custom — своя иконка (необязательна). Без иконки: тёмная плашка.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attrs = get_query_var( 'tolstenko_block_attributes', array() );
if ( ! is_array( $attrs ) ) {
	$attrs = array();
}
$defaults = function_exists( 'tolstenko_get_blog_content_defaults' )
	? tolstenko_get_blog_content_defaults( 'blog_warning' )
	: array();

$items = isset( $attrs['block_blog_warning_items'] ) && is_array( $attrs['block_blog_warning_items'] )
	? $attrs['block_blog_warning_items']
	: array();

$allowed_types = function_exists( 'tolstenko_blog_warning_allowed_types' )
	? tolstenko_blog_warning_allowed_types()
	: array( 'warn', 'pin', 'ide', 'err', 'custom' );

/**
 * @param array  $raw     Raw items.
 * @param string[] $allowed Allowed types.
 * @return array<int, array{type:string,title:string,text:string,icon:int}>
 */
$normalize_warning_items = static function ( $raw, $allowed ) {
	$clean = array();
	if ( ! is_array( $raw ) ) {
		return $clean;
	}
	foreach ( $raw as $it ) {
		if ( ! is_array( $it ) ) {
			continue;
		}
		$text = (string) ( $it['text'] ?? '' );
		if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
			continue;
		}
		$type = sanitize_key( (string) ( $it['type'] ?? 'warn' ) );
		if ( ! in_array( $type, $allowed, true ) ) {
			$type = 'warn';
		}
		$clean[] = array(
			'type'  => $type,
			'title' => sanitize_text_field( (string) ( $it['title'] ?? '' ) ),
			'text'  => $text,
			'icon'  => isset( $it['icon'] ) ? (int) $it['icon'] : 0,
		);
	}
	return $clean;
};

$clean = $normalize_warning_items( $items, $allowed_types );
if ( ! $clean ) {
	$clean = $normalize_warning_items( $defaults['items'] ?? array(), $allowed_types );
}
if ( ! $clean ) {
	return;
}

/**
 * HTML иконки или пустая строка.
 *
 * @param string $type Icon type.
 * @param int    $icon Attachment ID for custom.
 * @return string
 */
$get_warning_icon_html = static function ( $type, $icon ) {
	$type = sanitize_key( (string) $type );
	$icon = (int) $icon;
	$wrap = static function ( $class, $svg ) {
		return '<span class="single-blog__warnings-icon" aria-hidden="true">' . $svg . '</span>';
	};

	if ( $type === 'custom' ) {
		if ( $icon <= 0 ) {
			return '';
		}

		$inner = '';
		$path  = get_attached_file( $icon );
		if ( $path && is_readable( $path ) && preg_match( '/\.svg$/i', $path ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local attachment.
			$svg = (string) file_get_contents( $path );
			$svg = trim( $svg );
			if ( $svg !== '' && stripos( $svg, '<svg' ) !== false ) {
				if ( ! preg_match( '/<svg\b[^>]*\bclass=/i', $svg ) ) {
					$svg = preg_replace( '/<svg\b/i', '<svg class="custom"', $svg, 1 );
				} else {
					$svg = preg_replace(
						'/(<svg\b[^>]*\bclass=["\'])([^"\']*)(["\'])/i',
						'$1$2 custom$3',
						$svg,
						1
					);
				}
				$inner = $svg;
			}
		}

		if ( $inner === '' ) {
			$url = wp_get_attachment_image_url( $icon, 'thumbnail' );
			if ( ! $url ) {
				$url = wp_get_attachment_image_url( $icon, 'full' );
			}
			if ( $url ) {
				$inner = '<img class="custom" src="' . esc_url( $url ) . '" alt="" width="55" height="55" loading="lazy" decoding="async">';
			}
		}

		if ( $inner === '' ) {
			return '';
		}

		return '<span class="custom single-blog__warnings-icon" aria-hidden="true">' . $inner . '</span>';
	}

	if ( $type === 'pin' ) {
		return $wrap(
			'pin',
			'<svg class="pin" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 13.5V9.5M5.2 6.2C5.2 4.7 6.4 3.5 8 3.5C9.6 3.5 10.8 4.7 10.8 6.2C10.8 7.4 10 8.4 8.9 8.8L8.5 9.5H7.5L7.1 8.8C6 8.4 5.2 7.4 5.2 6.2Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>'
		);
	}

	if ( $type === 'ide' ) {
		return $wrap(
			'ide',
			'<svg class="ide" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6.2 11.5h3.6M6.6 13h2.8M8 2.5c1.9 0 3.4 1.5 3.4 3.3 0 1.3-.8 2.4-1.9 2.9v1.3H6.5V8.7C5.4 8.2 4.6 7.1 4.6 5.8 4.6 4 6.1 2.5 8 2.5Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>'
		);
	}

	if ( $type === 'err' ) {
		return $wrap(
			'err',
			'<svg class="err" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M5 5l6 6M11 5l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
		);
	}

	return $wrap(
		'warn',
		'<svg class="warn" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8 4.2v5.2M8 11.8h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
	);
};
?>
<div class="">
	<ul class="single-blog__warnings">
		<?php foreach ( $clean as $item ) : ?>
			<?php
			$type       = (string) $item['type'];
			$icon_html  = $get_warning_icon_html( $type, (int) $item['icon'] );
			$card_title = trim( (string) ( $item['title'] ?? '' ) );
			if ( $card_title === '' && function_exists( 'tolstenko_blog_warning_card_title' ) ) {
				$card_title = tolstenko_blog_warning_card_title( $type );
			}
			$classes   = array(
				'single-blog__warnings-item',
				'single-blog__warnings-item--' . sanitize_html_class( $type ),
			);

			if ( $type === 'custom' && $icon_html === '' ) {
				$classes[] = 'single-blog__warnings-item--no-icon';
			}
			?>
			<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<?php
				if ( $icon_html !== '' ) {
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG/img built above with escaped URL.
					echo $icon_html;
				}
				?>
				<div class="single-blog__warnings-body">
					<?php if ( $card_title !== '' ) : ?>
						<p class="single-blog__warnings-title"><?php echo esc_html( $card_title ); ?></p>
					<?php endif; ?>
					<div class="single-blog__warnings-text"><?php echo tolstenko_kses_html( $item['text'] ); ?></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php
