<?php
/**
 * Текстовый блок подкатегории: секции для шаблона template-parts/blocks/article.php.
 *
 * @package tolstenko-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, string>
 */
function tolstenko_sc_article_section_type_labels() {
	return array(
		'media_left'  => __( 'Картинка слева, текст справа', 'tolstenko-theme' ),
		'media_right' => __( 'Картинка справа, текст слева', 'tolstenko-theme' ),
		'text_2col'   => __( 'Текст в 2 колонки', 'tolstenko-theme' ),
		'text_full'   => __( 'Текст во всю ширину', 'tolstenko-theme' ),
	);
}

/**
 * @param mixed $raw POST rows.
 * @return array<int, array{type: string, text: string, image_id: int}>
 */
function tolstenko_sc_sanitize_article_sections( $raw ) {
	$labels = tolstenko_sc_article_section_type_labels();
	$out    = array();
	if ( ! is_array( $raw ) ) {
		return $out;
	}
	foreach ( $raw as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		$type = isset( $row['type'] ) ? sanitize_key( (string) $row['type'] ) : '';
		if ( ! isset( $labels[ $type ] ) ) {
			continue;
		}
		$text     = isset( $row['text'] ) ? wp_kses_post( (string) $row['text'] ) : '';
		$image_id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
		if ( trim( wp_strip_all_tags( $text ) ) === '' && $image_id === 0 ) {
			continue;
		}
		if ( $type === 'text_2col' || $type === 'text_full' ) {
			$image_id = 0;
		}
		$out[] = array(
			'type'     => $type,
			'text'     => $text,
			'image_id' => $image_id,
		);
	}
	return $out;
}

/**
 * @param string $text HTML or plain text.
 * @return string
 */
function tolstenko_sc_format_article_section_text( $text ) {
	$text = trim( (string) $text );
	if ( $text === '' ) {
		return '';
	}
	if ( function_exists( 'tolstenko_format_review_text_content' ) ) {
		return tolstenko_format_review_text_content( $text );
	}
	if ( preg_match( '/<(p|h[1-6]|ul|ol|blockquote|figure|div)\b/i', $text ) || false !== strpos( $text, '<!-- wp:' ) ) {
		return apply_filters( 'the_content', $text );
	}
	return wpautop( $text );
}

/**
 * @param array<int, array{type: string, text: string, image_id: int}> $sections Sections.
 * @param bool                                                         $showmore Show-more buttons on collapsible blocks.
 * @return string HTML fragment for article-content-builder.
 */
function tolstenko_sc_render_article_sections_html( $sections, $showmore = true ) {
	if ( ! is_array( $sections ) || empty( $sections ) ) {
		return '';
	}
	$html = '';

	foreach ( $sections as $section ) {
		if ( ! is_array( $section ) ) {
			continue;
		}
		$type = isset( $section['type'] ) ? (string) $section['type'] : '';
		$text = isset( $section['text'] ) ? tolstenko_sc_format_article_section_text( $section['text'] ) : '';
		if ( $text === '' && empty( $section['image_id'] ) ) {
			continue;
		}
		$image_id = isset( $section['image_id'] ) ? (int) $section['image_id'] : 0;
		$img_url  = $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

		if ( $type === 'media_left' || $type === 'media_right' ) {
			$reverse = ( $type === 'media_right' );
			$html   .= '<div class="article-teaser' . ( $reverse ? ' article-teaser--reverse' : '' ) . '">';
			if ( $img_url ) {
				$html .= '<div class="article-teaser-img-wrap"><img class="article-teaser-img" src="' . esc_url( $img_url ) . '" alt=""></div>';
			}
			$html .= '<div class="article-teaser-body"><div class="article-teaser-text">' . wp_kses_post( $text ) . '</div></div></div>';
			continue;
		}

		if ( $type === 'text_2col' && $text !== '' ) {
			$collapsible = $showmore ? ' article-2col-text--collapsible' : '';
			$html       .= '<div class="article-2col">';
			$html       .= '<div class="article-2col-text' . $collapsible . '">' . wp_kses_post( $text ) . '</div>';
			if ( $showmore ) {
				$html .= '<button type="button" class="article-2col-showmore article-2col-showmore-builder">' . esc_html__( 'Показать ещё', 'tolstenko-theme' ) . '</button>';
			}
			$html .= '</div>';
			continue;
		}

		if ( $type === 'text_full' && $text !== '' ) {
			$collapsible = $showmore ? ' article-content-collapsible' : '';
			$html       .= '<div class="article-text-1col">';
			$html       .= '<div class="article-text-1col-text' . $collapsible . '">' . wp_kses_post( $text ) . '</div>';
			if ( $showmore ) {
				$html .= '<button type="button" class="article-content-showmore">' . esc_html__( 'Показать ещё', 'tolstenko-theme' ) . '</button>';
			}
			$html .= '</div>';
		}
	}

	return $html;
}

/**
 * @param int|string $index Row index or "__INDEX__" for JS template.
 * @return string
 */
function tolstenko_sc_get_article_section_editor_id( $index ) {
	if ( $index === '__INDEX__' ) {
		return 'tolstenko_sc_article_text___INDEX__';
	}
	if ( $index === 'template' ) {
		return 'tolstenko_sc_article_text_template';
	}
	return 'tolstenko_sc_article_text_' . (int) $index;
}

/**
 * @param int|string $index Row index or "__INDEX__".
 * @param string     $text  Editor content.
 */
function tolstenko_sc_render_article_section_text_field( $index, $text ) {
	$editor_id = tolstenko_sc_get_article_section_editor_id( $index );
	if ( $index === '__INDEX__' ) {
		?>
		<div class="tolstenko-sc-article-editor-wrap">
			<textarea id="<?php echo esc_attr( $editor_id ); ?>" class="tolstenko-sc-article-text" rows="8" style="width:100%;"></textarea>
		</div>
		<?php
		return;
	}
	?>
	<div class="tolstenko-sc-article-editor-wrap">
		<?php
		wp_editor(
			$text,
			$editor_id,
			array(
				'textarea_name'    => '',
				'textarea_class'   => 'tolstenko-sc-article-text',
				'teeny'            => false,
				'media_buttons'    => true,
				'quicktags'        => true,
				'editor_height'    => 180,
				'drag_drop_upload' => false,
			)
		);
		?>
	</div>
	<?php
}

/**
 * Одна строка репитера в админке категории.
 *
 * @param int    $index Row index.
 * @param array  $row   type, text, image_id.
 */
function tolstenko_sc_render_article_section_admin_row( $index, $row ) {
	$labels = tolstenko_sc_article_section_type_labels();
	$type   = isset( $row['type'] ) ? (string) $row['type'] : 'media_left';
	if ( ! isset( $labels[ $type ] ) ) {
		$type = 'media_left';
	}
	$text     = isset( $row['text'] ) ? (string) $row['text'] : '';
	$image_id = isset( $row['image_id'] ) ? (int) $row['image_id'] : 0;
	$img_url  = $image_id > 0 ? wp_get_attachment_image_url( $image_id, 'thumbnail' ) : '';
	$needs_img = ( $type === 'media_left' || $type === 'media_right' );
	?>
	<div class="tolstenko-sc-article-row" style="margin-bottom:10px;padding:10px;border:1px solid #ddd;background:#fff;" data-needs-image="<?php echo $needs_img ? '1' : '0'; ?>">
		<label style="display:block;margin-bottom:6px;font-weight:600;"><?php esc_html_e( 'Тип блока', 'tolstenko-theme' ); ?></label>
		<select class="tolstenko-sc-article-type" style="width:100%;max-width:420px;margin-bottom:10px;">
			<?php foreach ( $labels as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<div class="tolstenko-sc-article-image-wrap" style="margin-bottom:10px;<?php echo $needs_img ? '' : 'display:none;'; ?>">
			<input type="hidden" value="<?php echo (int) $image_id; ?>" class="tolstenko-sc-article-image-id">
			<button type="button" class="button tolstenko-sc-article-pick-image"><?php esc_html_e( 'Выбрать картинку', 'tolstenko-theme' ); ?></button>
			<button type="button" class="button tolstenko-sc-article-clear-image"<?php echo $image_id > 0 ? '' : ' style="display:none;"'; ?>><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
			<div class="tolstenko-sc-article-preview" style="margin-top:8px;"><?php if ( $img_url ) : ?><img src="<?php echo esc_url( $img_url ); ?>" alt="" style="max-width:120px;height:auto;"><?php endif; ?></div>
		</div>
		<label style="display:block;margin-bottom:6px;font-weight:600;"><?php esc_html_e( 'Текст', 'tolstenko-theme' ); ?></label>
		<?php tolstenko_sc_render_article_section_text_field( $index, $text ); ?>
		<p style="margin:8px 0 0;"><button type="button" class="button tolstenko-sc-remove-row"><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button></p>
	</div>
	<?php
}

function tolstenko_sc_get_category_article_builder_html( $term ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	$sections = get_term_meta( $term->term_id, '_tolstenko_sc_article_sections', true );
	if ( ! is_array( $sections ) || empty( $sections ) ) {
		return '';
	}
	return tolstenko_sc_render_article_sections_html( $sections, true );
}
