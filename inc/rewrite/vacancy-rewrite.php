<?php
/**
 * Rewrite для /vacancies/{category}/.
 * Явные правила рубрик с приоритетом над CPT vacancies/{slug}.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Правила /vacancies/{cat}/ и /vacancies/{cat}/page/N/.
 */
function tolstenko_vacancy_cat_rewrite_rules() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'vacancy_cat',
			'hide_empty' => false,
		)
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
			'^vacancies/' . $slug . '/page/([0-9]{1,})/?$',
			'index.php?vacancy_cat=' . rawurlencode( $term->slug ) . '&paged=$matches[1]',
			'top'
		);
		add_rewrite_rule(
			'^vacancies/' . $slug . '/?$',
			'index.php?vacancy_cat=' . rawurlencode( $term->slug ),
			'top'
		);
	}
}
add_action( 'init', 'tolstenko_vacancy_cat_rewrite_rules', 20 );

/**
 * @param string  $termlink Term link.
 * @param WP_Term $term     Term.
 * @param string  $taxonomy Taxonomy.
 * @return string
 */
function tolstenko_vacancy_cat_term_link( $termlink, $term, $taxonomy ) {
	if ( $taxonomy !== 'vacancy_cat' || ! ( $term instanceof WP_Term ) ) {
		return $termlink;
	}
	return home_url( user_trailingslashit( 'vacancies/' . $term->slug ) );
}
add_filter( 'term_link', 'tolstenko_vacancy_cat_term_link', 10, 3 );

/**
 * /vacancies/{slug}/: если это не рубрика — одиночная вакансия (CPT).
 *
 * @param array<string, string> $query_vars Query vars.
 * @return array<string, string>
 */
function tolstenko_request_single_vacancy_under_vacancies_prefix( $query_vars ) {
	if ( empty( $query_vars['vacancy_cat'] ) || is_array( $query_vars['vacancy_cat'] ) ) {
		return $query_vars;
	}
	if (
		! empty( $query_vars['name'] )
		&& ! empty( $query_vars['post_type'] )
		&& $query_vars['post_type'] === 'vacancy'
	) {
		return $query_vars;
	}
	$slug = $query_vars['vacancy_cat'];
	if ( $slug === '' || strpos( $slug, '/' ) !== false ) {
		return $query_vars;
	}
	$term = get_term_by( 'slug', $slug, 'vacancy_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return $query_vars;
	}
	$ids = get_posts(
		array(
			'post_type'              => 'vacancy',
			'name'                   => $slug,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		)
	);
	if ( empty( $ids ) ) {
		return $query_vars;
	}
	unset( $query_vars['vacancy_cat'] );
	$query_vars['post_type'] = 'vacancy';
	$query_vars['name']      = $slug;
	return $query_vars;
}
add_filter( 'request', 'tolstenko_request_single_vacancy_under_vacancies_prefix', 5 );

/**
 * Flush при изменении рубрик вакансий.
 */
function tolstenko_vacancy_cat_flush_rewrite_rules() {
	tolstenko_vacancy_cat_rewrite_rules();
	flush_rewrite_rules( false );
}
add_action( 'created_vacancy_cat', 'tolstenko_vacancy_cat_flush_rewrite_rules' );
add_action( 'edited_vacancy_cat', 'tolstenko_vacancy_cat_flush_rewrite_rules' );
add_action( 'delete_vacancy_cat', 'tolstenko_vacancy_cat_flush_rewrite_rules' );
add_action( 'after_switch_theme', 'tolstenko_vacancy_cat_flush_rewrite_rules' );
