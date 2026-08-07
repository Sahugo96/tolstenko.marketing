<?php
/**
 * Rewrite для /services/{category}/.
 * Известные рубрики — явные правила; catch-all + request-фильтр — услуга без рубрики.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Правила /services/{cat}/ и /services/{cat}/page/N/.
 */
function tolstenko_service_category_rewrite_rules() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'service_category',
			'hide_empty' => false,
		)
	);

	// Сначала catch-all (при 'top' окажется ниже узких правил).
	add_rewrite_rule(
		'^services/([^/]+)/?$',
		'index.php?service_category=$matches[1]',
		'top'
	);
	add_rewrite_rule(
		'^services/([^/]+)/page/([0-9]{1,})/?$',
		'index.php?service_category=$matches[1]&paged=$matches[2]',
		'top'
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return;
	}

	foreach ( $terms as $term ) {
		if ( ! ( $term instanceof WP_Term ) || $term->slug === '' ) {
			continue;
		}
		$slug = preg_quote( $term->slug, '/' );

		add_rewrite_rule(
			'^services/' . $slug . '/page/([0-9]{1,})/?$',
			'index.php?service_category=' . rawurlencode( $term->slug ) . '&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^services/' . $slug . '/?$',
			'index.php?service_category=' . rawurlencode( $term->slug ),
			'top'
		);
	}
}
add_action( 'init', 'tolstenko_service_category_rewrite_rules', 20 );

/**
 * @param string  $termlink Term link.
 * @param WP_Term $term     Term.
 * @param string  $taxonomy Taxonomy.
 * @return string
 */
function tolstenko_service_category_term_link( $termlink, $term, $taxonomy ) {
	if ( $taxonomy !== 'service_category' || ! ( $term instanceof WP_Term ) ) {
		return $termlink;
	}
	return home_url( user_trailingslashit( 'services/' . $term->slug ) );
}
add_filter( 'term_link', 'tolstenko_service_category_term_link', 10, 3 );

/**
 * Flush при изменении рубрик услуг.
 */
function tolstenko_service_category_flush_rewrite_rules() {
	tolstenko_service_category_rewrite_rules();
	flush_rewrite_rules( false );
}
add_action( 'created_service_category', 'tolstenko_service_category_flush_rewrite_rules' );
add_action( 'edited_service_category', 'tolstenko_service_category_flush_rewrite_rules' );
add_action( 'delete_service_category', 'tolstenko_service_category_flush_rewrite_rules' );
add_action( 'after_switch_theme', 'tolstenko_service_category_flush_rewrite_rules' );
