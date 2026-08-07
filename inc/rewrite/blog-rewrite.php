<?php
/**
 * Rewrite для /blog/{category}/ и конфликт CPT blog vs blog_cat.
 *
 * Авто-rewrite таксономии отключён: иначе CPT-правило blog/{slug}
 * перехватывает рубрики раньше и отдаёт 404 (ищет пост, а не термин).
 * Для каждой известной рубрики — явное правило с приоритетом top.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Регистрация правил /blog/{cat}/ и /blog/{cat}/page/N/.
 */
function tolstenko_blog_cat_rewrite_rules() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'blog_cat',
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
			'^blog/' . $slug . '/?$',
			'index.php?blog_cat=' . rawurlencode( $term->slug ),
			'top'
		);

		add_rewrite_rule(
			'^blog/' . $slug . '/page/([0-9]{1,})/?$',
			'index.php?blog_cat=' . rawurlencode( $term->slug ) . '&paged=$matches[1]',
			'top'
		);
	}
}
add_action( 'init', 'tolstenko_blog_cat_rewrite_rules', 20 );

/**
 * Канонический URL рубрики: /blog/{slug}/.
 *
 * @param string  $termlink Term link.
 * @param WP_Term $term     Term.
 * @param string  $taxonomy Taxonomy.
 * @return string
 */
function tolstenko_blog_cat_term_link( $termlink, $term, $taxonomy ) {
	if ( $taxonomy !== 'blog_cat' || ! ( $term instanceof WP_Term ) ) {
		return $termlink;
	}
	return home_url( user_trailingslashit( 'blog/' . $term->slug ) );
}
add_filter( 'term_link', 'tolstenko_blog_cat_term_link', 10, 3 );

/**
 * Flush rewrite при изменении рубрик статей.
 */
function tolstenko_blog_cat_flush_rewrite_rules() {
	tolstenko_blog_cat_rewrite_rules();
	flush_rewrite_rules( false );
}
add_action( 'created_blog_cat', 'tolstenko_blog_cat_flush_rewrite_rules' );
add_action( 'edited_blog_cat', 'tolstenko_blog_cat_flush_rewrite_rules' );
add_action( 'delete_blog_cat', 'tolstenko_blog_cat_flush_rewrite_rules' );
add_action( 'after_switch_theme', 'tolstenko_blog_cat_flush_rewrite_rules' );
