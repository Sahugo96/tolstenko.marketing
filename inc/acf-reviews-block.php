<?php
/**
 * ACF: настройки блока «Отзывы» (заголовок + 4 карточки рейтинга).
 * Показывается на странице настроек (nastroyki-sayta).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'acf/init', 'tolstenko_register_acf_reviews_block' );

function tolstenko_register_acf_reviews_block() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }
    $page_id = function_exists( 'tolstenko_get_settings_page_id' ) ? tolstenko_get_settings_page_id() : 0;
    if ( ! $page_id ) {
        return;
    }

    $fields = array(
        array(
            'key'           => 'field_reviews_block_title',
            'label'         => __( 'Заголовок блока', 'tolstenko-theme' ),
            'name'          => 'reviews_block_title',
            'type'          => 'text',
            'default_value' => 'Отзывы',
        ),
    );
    for ( $i = 1; $i <= 4; $i++ ) {
        $fields[] = array(
            'key'           => 'field_reviews_card_' . $i . '_title',
            'label'         => sprintf( __( 'Карточка %d — название', 'tolstenko-theme' ), $i ),
            'name'          => 'reviews_card_' . $i . '_title',
            'type'          => 'text',
            'default_value' => $i === 1 ? 'Яндекс' : '',
        );
        $fields[] = array(
            'key'           => 'field_reviews_card_' . $i . '_rating',
            'label'         => sprintf( __( 'Карточка %d — оценка (1–5)', 'tolstenko-theme' ), $i ),
            'name'          => 'reviews_card_' . $i . '_rating',
            'type'          => 'number',
            'min'           => 1,
            'max'           => 5,
            'default_value' => 5,
        );
    }

    acf_add_local_field_group(
        array(
            'key'      => 'group_tolstenko_reviews_block',
            'title'    => __( 'Блок «Отзывы» (карточки рейтинга)', 'tolstenko-theme' ),
            'fields'   => $fields,
            'location' => array(
                array(
                    array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ),
                    array( 'param' => 'page', 'operator' => '==', 'value' => (string) $page_id ),
                ),
            ),
        )
    );
}
