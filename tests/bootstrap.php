<?php
/**
 * Bootstrap юнит-тестов темы: заглушки WP + подключение тестируемых файлов.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/wp-stubs.php';

$tolstenko_theme_dir = dirname( __DIR__ );

require_once $tolstenko_theme_dir . '/inc/block-defaults-admin.php';
require_once $tolstenko_theme_dir . '/inc/blog/authors-admin.php';
require_once $tolstenko_theme_dir . '/inc/blog/content-blocks.php';
require_once $tolstenko_theme_dir . '/inc/blog/allowed-blocks.php';
require_once $tolstenko_theme_dir . '/inc/blog/helpers.php';
require_once $tolstenko_theme_dir . '/inc/blog/plugins-display.php';
require_once $tolstenko_theme_dir . '/inc/blog/reading-time.php';
require_once $tolstenko_theme_dir . '/inc/compat-koritan-rename.php';
require_once $tolstenko_theme_dir . '/inc/reviews-helpers.php';
require_once $tolstenko_theme_dir . '/inc/rest-posts-filter.php';
