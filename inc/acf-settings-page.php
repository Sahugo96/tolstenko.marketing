<?php
/**
 * Общая страница настроек для блоков (одна на все блоки).
 * Создай страницу с ярлыком nastroyki-sayta — там будут группы полей всех таких блоков (каждый блок — своя группа).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TOLSTENKO_SETTINGS_PAGE_SLUG', 'nastroyki-sayta' );

/**
 * ID страницы настроек. 0 если страницы нет.
 *
 * @return int
 */
function tolstenko_get_settings_page_id() {
    $page = get_page_by_path( TOLSTENKO_SETTINGS_PAGE_SLUG );
    return $page ? (int) $page->ID : 0;
}
