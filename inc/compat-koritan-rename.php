<?php
/**
 * Совместимость после переименования koritan → tolstenko.
 * Мигрирует options/meta/контент блоков и регистрирует алиасы koritan/*.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Заменить namespace блоков koritan/ → tolstenko/ в строке/массиве.
 *
 * @param mixed $value Value.
 * @return mixed
 */
function tolstenko_remap_koritan_namespace( $value ) {
	if ( is_string( $value ) ) {
		// Порядок важен: сначала более длинные префиксы.
		$search  = array( 'wp:koritan/', '_koritan_', 'koritan/', 'koritan_' );
		$replace = array( 'wp:tolstenko/', '_tolstenko_', 'tolstenko/', 'tolstenko_' );
		return str_replace( $search, $replace, $value );
	}
	if ( is_array( $value ) ) {
		$out = array();
		foreach ( $value as $k => $v ) {
			$nk         = is_string( $k ) ? tolstenko_remap_koritan_namespace( $k ) : $k;
			$out[ $nk ] = tolstenko_remap_koritan_namespace( $v );
		}
		return $out;
	}
	return $value;
}

/**
 * Одноразовая миграция данных БД.
 */
function tolstenko_run_koritan_rename_migration() {
	if ( get_option( 'tolstenko_koritan_rename_migrated' ) === '2' ) {
		return;
	}

	global $wpdb;

	$option_map = array(
		'koritan_block_defaults'         => 'tolstenko_block_defaults',
		'koritan_blog_authors'           => 'tolstenko_blog_authors',
		'koritan_blog_allowed_blocks'    => 'tolstenko_blog_allowed_blocks',
		'koritan_pvc_blog_enabled'       => 'tolstenko_pvc_blog_enabled',
		'koritan_actions_rewrite_flushed'=> 'tolstenko_actions_rewrite_flushed',
		'koritan_review_rewrite_flushed' => 'tolstenko_review_rewrite_flushed',
	);

	foreach ( $option_map as $old => $new ) {
		$new_exists = get_option( $new, null );
		$old_val    = get_option( $old, null );
		if ( $old_val === null ) {
			continue;
		}
		if ( $new_exists === null || $new_exists === false || $new_exists === array() || $new_exists === '' ) {
			update_option( $new, tolstenko_remap_koritan_namespace( $old_val ), false );
		} elseif ( $new === 'tolstenko_blog_allowed_blocks' && is_array( $new_exists ) ) {
			update_option( $new, tolstenko_remap_koritan_namespace( $new_exists ), false );
		}
	}

	// postmeta keys.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$postmeta = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
			$wpdb->esc_like( '_koritan_' ) . '%',
			$wpdb->esc_like( 'koritan_' ) . '%'
		)
	);
	if ( is_array( $postmeta ) ) {
		foreach ( $postmeta as $row ) {
			$new_key = (string) tolstenko_remap_koritan_namespace( $row->meta_key );
			if ( $new_key === '' || $new_key === $row->meta_key ) {
				continue;
			}
			if ( ! metadata_exists( 'post', (int) $row->post_id, $new_key ) ) {
				$val = maybe_unserialize( $row->meta_value );
				update_post_meta( (int) $row->post_id, $new_key, tolstenko_remap_koritan_namespace( $val ) );
			}
		}
	}

	// termmeta keys.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$termmeta = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
			$wpdb->esc_like( '_koritan_' ) . '%',
			$wpdb->esc_like( 'koritan_' ) . '%'
		)
	);
	if ( is_array( $termmeta ) ) {
		foreach ( $termmeta as $row ) {
			$new_key = (string) tolstenko_remap_koritan_namespace( $row->meta_key );
			if ( $new_key === '' || $new_key === $row->meta_key ) {
				continue;
			}
			if ( ! metadata_exists( 'term', (int) $row->term_id, $new_key ) ) {
				$val = maybe_unserialize( $row->meta_value );
				update_term_meta( (int) $row->term_id, $new_key, tolstenko_remap_koritan_namespace( $val ) );
			}
		}
	}

	// Gutenberg content: <!-- wp:koritan/... -->
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$ids = $wpdb->get_col(
		"SELECT ID FROM {$wpdb->posts}
		WHERE post_content LIKE '%wp:koritan/%' OR post_content LIKE '%\"koritan/%'"
	);
	if ( is_array( $ids ) ) {
		foreach ( $ids as $post_id ) {
			$post_id = (int) $post_id;
			$content = get_post_field( 'post_content', $post_id );
			if ( ! is_string( $content ) || $content === '' ) {
				continue;
			}
			$new = str_replace( 'wp:koritan/', 'wp:tolstenko/', $content );
			$new = str_replace( '"koritan/', '"tolstenko/', $new );
			if ( $new !== $content ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update(
					$wpdb->posts,
					array( 'post_content' => $new ),
					array( 'ID' => $post_id ),
					array( '%s' ),
					array( '%d' )
				);
				clean_post_cache( $post_id );
			}
		}
	}

	update_option( 'tolstenko_koritan_rename_migrated', '2', false );
}
add_action( 'init', 'tolstenko_run_koritan_rename_migration', 1 );

/**
 * Алиасы старых имён блоков koritan/* → тот же render.
 */
function tolstenko_register_koritan_block_aliases() {
	if ( ! function_exists( 'register_block_type' ) || ! class_exists( 'WP_Block_Type_Registry' ) ) {
		return;
	}
	$registry = WP_Block_Type_Registry::get_instance();
	$all      = $registry->get_all_registered();
	foreach ( $all as $name => $block ) {
		if ( strpos( $name, 'tolstenko/' ) !== 0 ) {
			continue;
		}
		$alias = 'koritan/' . substr( $name, strlen( 'tolstenko/' ) );
		if ( $registry->is_registered( $alias ) ) {
			continue;
		}
		$args = array(
			'api_version'     => isset( $block->api_version ) ? $block->api_version : 3,
			'title'           => $block->title,
			'category'        => $block->category,
			'icon'            => $block->icon,
			'description'     => $block->description,
			'render_callback' => $block->render_callback,
			'attributes'      => $block->attributes,
			'supports'        => $block->supports,
			'editor_script'   => $block->editor_script,
		);
		register_block_type( $alias, $args );
	}
}
add_action( 'init', 'tolstenko_register_koritan_block_aliases', 30 );

/**
 * На фронте/в редакторе старые blockName koritan/* рендерятся как tolstenko/*.
 *
 * @param array $parsed Parsed block.
 * @return array
 */
function tolstenko_remap_parsed_block_name( $parsed ) {
	if ( ! empty( $parsed['blockName'] ) && is_string( $parsed['blockName'] ) && strpos( $parsed['blockName'], 'koritan/' ) === 0 ) {
		$parsed['blockName'] = 'tolstenko/' . substr( $parsed['blockName'], strlen( 'koritan/' ) );
	}
	return $parsed;
}
add_filter( 'render_block_data', 'tolstenko_remap_parsed_block_name', 5 );
