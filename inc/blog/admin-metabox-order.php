<?php
/**
 * Порядок метабоксов в редакторе CPT blog: Yoast — последним (центр + сайдбар).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wpseo_metabox_prio', 'tolstenko_yoast_metabox_prio_low' );
add_action( 'add_meta_boxes', 'tolstenko_reorder_blog_metaboxes', 99999 );
add_filter( 'get_user_option_meta-box-order_blog', 'tolstenko_metabox_order_option_move_yoast_last', 99 );
add_filter( 'default_user_option_meta-box-order_blog', 'tolstenko_metabox_order_option_move_yoast_last', 99 );
add_action( 'admin_enqueue_scripts', 'tolstenko_blog_admin_metabox_order_assets' );
add_action( 'enqueue_block_editor_assets', 'tolstenko_blog_block_editor_yoast_order_assets' );

/**
 * Yoast SEO — внизу колонки «normal».
 *
 * @return string
 */
function tolstenko_yoast_metabox_prio_low() {
	return 'low';
}

/**
 * @param string $id Metabox id.
 * @return bool
 */
function tolstenko_is_yoast_metabox_id( $id ) {
	$id = strtolower( (string) $id );
	return ( strpos( $id, 'wpseo' ) !== false || strpos( $id, 'yoast' ) !== false );
}

/**
 * Переносит Yoast-метабоксы в конец comma-separated списка.
 *
 * @param string $order Comma-separated ids.
 * @return string
 */
function tolstenko_metabox_order_move_yoast_last_string( $order ) {
	if ( ! is_string( $order ) || $order === '' ) {
		return $order;
	}

	$ids = array_values( array_filter( array_map( 'trim', explode( ',', $order ) ) ) );
	if ( ! $ids ) {
		return $order;
	}

	$yoast = array();
	$rest  = array();
	foreach ( $ids as $id ) {
		if ( tolstenko_is_yoast_metabox_id( $id ) ) {
			$yoast[] = $id;
		} else {
			$rest[] = $id;
		}
	}

	if ( ! $yoast ) {
		return $order;
	}

	return implode( ',', array_merge( $rest, $yoast ) );
}

/**
 * WordPress хранит meta-box-order как массив контекстов → comma-separated ids.
 *
 * @param mixed $order Stored order.
 * @return mixed
 */
function tolstenko_metabox_order_option_move_yoast_last( $order ) {
	if ( ! is_array( $order ) ) {
		return $order;
	}

	foreach ( $order as $context => $ids ) {
		if ( is_string( $ids ) ) {
			$order[ $context ] = tolstenko_metabox_order_move_yoast_last_string( $ids );
		}
	}

	return $order;
}

/**
 * Переносит Yoast в конец очереди метабоксов (если user option ещё не сохранён).
 */
function tolstenko_reorder_blog_metaboxes() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'blog' ) {
		return;
	}

	global $wp_meta_boxes;
	if ( empty( $wp_meta_boxes['blog'] ) || ! is_array( $wp_meta_boxes['blog'] ) ) {
		return;
	}

	foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
		if ( empty( $wp_meta_boxes['blog'][ $context ] ) || ! is_array( $wp_meta_boxes['blog'][ $context ] ) ) {
			continue;
		}

		$yoast_boxes = array();
		foreach ( $wp_meta_boxes['blog'][ $context ] as $priority => $boxes ) {
			if ( ! is_array( $boxes ) ) {
				continue;
			}
			foreach ( $boxes as $id => $box ) {
				if ( tolstenko_is_yoast_metabox_id( $id ) ) {
					$yoast_boxes[ $id ] = $box;
					unset( $wp_meta_boxes['blog'][ $context ][ $priority ][ $id ] );
				}
			}
		}

		if ( ! $yoast_boxes ) {
			continue;
		}

		if ( empty( $wp_meta_boxes['blog'][ $context ]['low'] ) || ! is_array( $wp_meta_boxes['blog'][ $context ]['low'] ) ) {
			$wp_meta_boxes['blog'][ $context ]['low'] = array();
		}

		foreach ( $yoast_boxes as $id => $box ) {
			$wp_meta_boxes['blog'][ $context ]['low'][ $id ] = $box;
		}
	}
}

/**
 * JS: Yoast последним в центре, в табах сайдбара и в панели «Публикация».
 *
 * @return string
 */
function tolstenko_get_yoast_sidebar_order_script() {
	return <<<'JS'
(function(){
	function appendMatchingLast(parent, selector, matchFn) {
		if (!parent) return;
		var nodes = parent.querySelectorAll(selector);
		nodes.forEach(function(node) {
			if (matchFn && !matchFn(node)) return;
			if (node.parentElement === parent) {
				parent.appendChild(node);
			}
		});
	}

	function moveYoastCenterLast() {
		var containers = [
			document.getElementById('normal-sortables'),
			document.querySelector('.edit-post-meta-boxes-main #normal-sortables'),
			document.querySelector('.metabox-location-normal'),
			document.querySelector('.editor-post-meta-boxes-main .meta-box-sortables')
		];
		containers.forEach(function(container) {
			appendMatchingLast(container, '.postbox', function(box) {
				var id = (box.id || '').toLowerCase();
				return id.indexOf('wpseo') !== -1 || id.indexOf('yoast') !== -1;
			});
			var yoast = container && container.querySelector('#wpseo_meta');
			if (yoast) container.appendChild(yoast);
		});
	}

	function moveYoastSidebarPanelsLast() {
		var roots = [
			document.querySelector('.interface-complementary-area'),
			document.querySelector('.edit-post-sidebar'),
			document.querySelector('.editor-sidebar')
		];
		roots.forEach(function(root) {
			if (!root) return;
			appendMatchingLast(root, '.yoast-seo-sidebar-panel', null);
			appendMatchingLast(root, '.components-panel', function(panel) {
				var cls = (panel.className || '').toLowerCase();
				var id = (panel.id || '').toLowerCase();
				return cls.indexOf('yoast') !== -1 || id.indexOf('yoast') !== -1 || id.indexOf('wpseo') !== -1;
			});
		});

		var area = document.querySelector('.interface-complementary-area');
		if (area) {
			var tabList = area.querySelector('[role="tablist"]');
			if (tabList) {
				appendMatchingLast(tabList, '[role="tab"]', function(tab) {
					var label = (tab.getAttribute('aria-label') || tab.textContent || '').toLowerCase();
					var controls = (tab.getAttribute('aria-controls') || '').toLowerCase();
					var id = (tab.id || '').toLowerCase();
					return label.indexOf('yoast') !== -1 || controls.indexOf('seo-sidebar') !== -1 || id.indexOf('yoast') !== -1;
				});
			}
		}
	}

	function moveYoastPublishBoxLast() {
		var publishBox = document.getElementById('submitdiv');
		var yoastPublish = document.getElementById('yoast-seo-publishbox-section');
		if (publishBox && yoastPublish) {
			publishBox.appendChild(yoastPublish);
		}

		var sideSortables = document.getElementById('side-sortables');
		if (sideSortables) {
			appendMatchingLast(sideSortables, '.postbox', function(box) {
				var id = (box.id || '').toLowerCase();
				return id.indexOf('wpseo') !== -1 || id.indexOf('yoast') !== -1;
			});
		}
	}

	function moveYoastSidebarLast() {
		moveYoastCenterLast();
		moveYoastSidebarPanelsLast();
		moveYoastPublishBoxLast();
	}

	function boot() {
		moveYoastSidebarLast();
		var targets = [
			document.querySelector('.interface-interface-skeleton__sidebar'),
			document.querySelector('.interface-navigable-region'),
			document.getElementById('postbox-container-1'),
			document.getElementById('postbox-container-2'),
			document.querySelector('.edit-post-meta-boxes-main')
		].filter(Boolean);
		if (!targets.length || typeof MutationObserver === 'undefined') return;
		var timer = null;
		var observer = new MutationObserver(function() {
			if (timer) window.clearTimeout(timer);
			timer = window.setTimeout(moveYoastSidebarLast, 120);
		});
		targets.forEach(function(node) {
			observer.observe(node, { childList: true, subtree: true });
		});
	}

	if (window.wp && wp.domReady) {
		wp.domReady(boot);
	} else if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})();
JS;
}

/**
 * @param string $hook Hook.
 */
function tolstenko_blog_admin_metabox_order_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'blog' ) {
		return;
	}

	wp_register_script( 'tolstenko-admin-metabox-order', '', array(), null, true );
	wp_enqueue_script( 'tolstenko-admin-metabox-order' );
	wp_add_inline_script( 'tolstenko-admin-metabox-order', tolstenko_get_yoast_sidebar_order_script() );
}

/**
 * Block editor: тот же скрипт после загрузки Gutenberg.
 */
function tolstenko_blog_block_editor_yoast_order_assets() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'blog' ) {
		return;
	}

	wp_add_inline_script( 'wp-edit-post', tolstenko_get_yoast_sidebar_order_script() );
}
