<?php
/**
 * Главная категория (primary term) для типов записей с иерархическими таксономиями.
 * Исключение: CPT review (отзывы).
 *
 * Meta: _tolstenko_primary_{taxonomy} = term_id
 * Используется в permalink /blog/{cat}/…, /services/{cat}/… и хлебных крошках.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Карта post_type → список таксономий, для которых нужна главная категория.
 *
 * @return array<string, string[]>
 */
function tolstenko_primary_term_taxonomy_map() {
	$map = array(
		'blog'    => array( 'blog_cat' ),
		'service' => array( 'service_category' ),
		'vacancy' => array( 'vacancy_cat' ),
		'case'    => array( 'case_cat' ),
		'post'    => array( 'category' ),
	);

	/**
	 * @param array<string, string[]> $map Map.
	 */
	return apply_filters( 'tolstenko_primary_term_taxonomy_map', $map );
}

/**
 * Meta key для primary term.
 *
 * @param string $taxonomy Taxonomy.
 * @return string
 */
function tolstenko_primary_term_meta_key( $taxonomy ) {
	return '_tolstenko_primary_' . sanitize_key( $taxonomy );
}

/**
 * @param string $post_type Post type.
 * @return bool
 */
function tolstenko_post_type_supports_primary_term( $post_type ) {
	if ( $post_type === 'review' ) {
		return false;
	}
	$map = tolstenko_primary_term_taxonomy_map();
	return ! empty( $map[ $post_type ] );
}

/**
 * Регистрация post meta для REST / Gutenberg.
 */
function tolstenko_register_primary_term_meta() {
	$map = tolstenko_primary_term_taxonomy_map();
	foreach ( $map as $post_type => $taxonomies ) {
		if ( $post_type === 'review' || ! post_type_exists( $post_type ) ) {
			continue;
		}
		foreach ( $taxonomies as $taxonomy ) {
			if ( ! taxonomy_exists( $taxonomy ) ) {
				continue;
			}
			register_post_meta(
				$post_type,
				tolstenko_primary_term_meta_key( $taxonomy ),
				array(
					'type'              => 'integer',
					'single'            => true,
					'default'           => 0,
					'show_in_rest'      => true,
					'auth_callback'     => static function () {
						return current_user_can( 'edit_posts' );
					},
					'sanitize_callback' => 'absint',
				)
			);
		}
	}
}
add_action( 'init', 'tolstenko_register_primary_term_meta', 30 );

/**
 * Gutenberg пишет meta только если у типа есть custom-fields (или meta зарегистрирована).
 * У vacancy/case его не было — добавляем для primary term.
 */
function tolstenko_primary_term_ensure_custom_fields_support() {
	foreach ( array_keys( tolstenko_primary_term_taxonomy_map() ) as $post_type ) {
		if ( $post_type === 'review' || ! post_type_exists( $post_type ) ) {
			continue;
		}
		add_post_type_support( $post_type, 'custom-fields' );
	}
}
add_action( 'init', 'tolstenko_primary_term_ensure_custom_fields_support', 31 );

/**
 * Выбрать термин по умолчанию, если primary не задан: самый «верхний» (меньшая глубина).
 *
 * @param WP_Term[] $terms Terms.
 * @return WP_Term|null
 */
function tolstenko_pick_shallowest_term( array $terms ) {
	$best       = null;
	$best_depth = PHP_INT_MAX;
	foreach ( $terms as $term ) {
		if ( ! ( $term instanceof WP_Term ) ) {
			continue;
		}
		$ancestors = get_ancestors( (int) $term->term_id, $term->taxonomy, 'taxonomy' );
		$depth     = is_array( $ancestors ) ? count( $ancestors ) : 0;
		if ( $depth < $best_depth || ( $depth === $best_depth && $best && (int) $term->term_id < (int) $best->term_id ) ) {
			$best_depth = $depth;
			$best       = $term;
		}
	}
	return $best;
}

/**
 * Главный термин записи для таксономии.
 *
 * @param int|WP_Post $post     Post.
 * @param string      $taxonomy Taxonomy.
 * @return WP_Term|null
 */
function tolstenko_get_primary_term( $post, $taxonomy ) {
	$post = get_post( $post );
	if ( ! ( $post instanceof WP_Post ) || $taxonomy === '' ) {
		return null;
	}
	if ( $post->post_type === 'review' ) {
		return null;
	}

	$terms = get_the_terms( $post, $taxonomy );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return null;
	}

	$primary_id = (int) get_post_meta( $post->ID, tolstenko_primary_term_meta_key( $taxonomy ), true );
	if ( $primary_id > 0 ) {
		foreach ( $terms as $term ) {
			if ( $term instanceof WP_Term && (int) $term->term_id === $primary_id ) {
				return $term;
			}
		}
	}

	$fallback = tolstenko_pick_shallowest_term( $terms );
	return $fallback instanceof WP_Term ? $fallback : null;
}

/**
 * После сохранения: primary должен быть среди назначенных терминов.
 *
 * @param int $post_id Post ID.
 */
function tolstenko_sync_primary_terms_on_save( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}

	$post = get_post( $post_id );
	if ( ! ( $post instanceof WP_Post ) || $post->post_type === 'review' ) {
		return;
	}

	$map = tolstenko_primary_term_taxonomy_map();
	if ( empty( $map[ $post->post_type ] ) ) {
		return;
	}

	foreach ( $map[ $post->post_type ] as $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$meta_key   = tolstenko_primary_term_meta_key( $taxonomy );
		$primary_id = (int) get_post_meta( $post_id, $meta_key, true );
		$term_ids   = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( is_wp_error( $term_ids ) ) {
			$term_ids = array();
		}
		$term_ids = array_map( 'intval', $term_ids );

		if ( empty( $term_ids ) ) {
			if ( $primary_id ) {
				delete_post_meta( $post_id, $meta_key );
			}
			continue;
		}

		if ( $primary_id && in_array( $primary_id, $term_ids, true ) ) {
			continue;
		}

		// Primary сброшен или невалиден — берём самый верхний из назначенных.
		$terms = array();
		foreach ( $term_ids as $tid ) {
			$t = get_term( $tid, $taxonomy );
			if ( $t instanceof WP_Term ) {
				$terms[] = $t;
			}
		}
		$pick = tolstenko_pick_shallowest_term( $terms );
		if ( $pick instanceof WP_Term ) {
			update_post_meta( $post_id, $meta_key, (int) $pick->term_id );
		}
	}
}
add_action( 'save_post', 'tolstenko_sync_primary_terms_on_save', 20 );

/**
 * Данные для редактора.
 *
 * @return array{taxonomies: array<int, array{name: string, label: string, metaKey: string, hierarchical: bool}>}|null
 */
function tolstenko_get_primary_term_editor_config() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	$post_type = '';
	if ( $screen && ! empty( $screen->post_type ) ) {
		$post_type = (string) $screen->post_type;
	} elseif ( ! empty( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$p = get_post( (int) $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( $p instanceof WP_Post ) {
			$post_type = $p->post_type;
		}
	} elseif ( ! empty( $_GET['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = sanitize_key( wp_unslash( $_GET['post_type'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	if ( $post_type === '' || $post_type === 'review' || ! tolstenko_post_type_supports_primary_term( $post_type ) ) {
		return null;
	}

	$map = tolstenko_primary_term_taxonomy_map();
	$taxonomies = array();
	foreach ( $map[ $post_type ] as $taxonomy ) {
		$tax = get_taxonomy( $taxonomy );
		if ( ! $tax || empty( $tax->hierarchical ) ) {
			continue;
		}
		$taxonomies[] = array(
			'name'         => $taxonomy,
			'label'        => $tax->labels->singular_name ? (string) $tax->labels->singular_name : $taxonomy,
			'panelLabel'   => $tax->labels->name ? (string) $tax->labels->name : $taxonomy,
			'metaKey'      => tolstenko_primary_term_meta_key( $taxonomy ),
			'hierarchical' => true,
		);
	}

	if ( empty( $taxonomies ) ) {
		return null;
	}

	return array(
		'postType'   => $post_type,
		'taxonomies' => $taxonomies,
		'i18n'       => array(
			'panelTitle'  => __( 'Главная категория', 'tolstenko-theme' ),
			'makePrimary' => __( 'Сделать главной', 'tolstenko-theme' ),
			'isPrimary'   => __( 'Главная', 'tolstenko-theme' ),
			'help'        => __( 'Главная рубрика используется в URL и хлебных крошках.', 'tolstenko-theme' ),
			'none'        => __( 'Нет выбранных категорий', 'tolstenko-theme' ),
		),
	);
}

/**
 * Скрипт и стили в блочном редакторе.
 */
function tolstenko_enqueue_primary_term_editor() {
	$config = tolstenko_get_primary_term_editor_config();
	if ( ! $config ) {
		return;
	}

	$uri  = get_template_directory_uri();
	$path = get_template_directory() . '/assets/js/primary-term.js';
	$ver  = file_exists( $path ) ? (string) filemtime( $path ) : '1.0';

	wp_enqueue_script(
		'tolstenko-primary-term',
		$uri . '/assets/js/primary-term.js',
		array(
			'wp-element',
			'wp-components',
			'wp-data',
			'wp-edit-post',
			'wp-plugins',
			'wp-compose',
			'wp-i18n',
		),
		$ver,
		true
	);
	wp_localize_script( 'tolstenko-primary-term', 'tolstenkoPrimaryTerm', $config );

	$css = '
		.tolstenko-primary-term-badge{display:inline-block;margin-left:6px;padding:0 6px;border-radius:3px;background:#2271b1;color:#fff;font-size:11px;line-height:1.6;vertical-align:middle}
		.tolstenko-primary-term-btn{margin-left:6px;padding:0;border:0;background:none;color:#2271b1;cursor:pointer;font-size:11px;text-decoration:underline}
		.tolstenko-primary-term-btn:hover{color:#135e96}
		.tolstenko-primary-term-panel .components-base-control{margin-bottom:8px}
	';
	wp_register_style( 'tolstenko-primary-term', false, array(), $ver );
	wp_enqueue_style( 'tolstenko-primary-term' );
	wp_add_inline_style( 'tolstenko-primary-term', $css );
}
add_action( 'enqueue_block_editor_assets', 'tolstenko_enqueue_primary_term_editor' );
