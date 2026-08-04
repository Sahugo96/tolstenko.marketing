<?php
/**
 * ACF: поля для записи «Блок» (CPT block).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'acf/init', 'tolstenko_register_acf_block_fields' );

function tolstenko_register_acf_block_fields() {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = array(
        array(
            'key'           => 'field_block_type',
            'label'         => __( 'Шаблон', 'tolstenko-theme' ),
            'name'          => 'block_type',
            'type'          => 'select',
            'choices'       => array(
                ''               => '— Выберите шаблон —',
                'callback-modal' => __( 'Модалка заявки', 'tolstenko-theme' ),
                'header-footer'  => __( 'Шапка и подвал сайта', 'tolstenko-theme' ),
                'reviews'        => __( 'Отзывы', 'tolstenko-theme' ),
            ),
            'instructions'  => __( 'Тип блока. Данные подставляются в шаблон из template-parts/blocks/.', 'tolstenko-theme' ),
        ),
        // Модалка заявки (callback-modal): только выбор формы CF7
        array(
            'key'               => 'field_block_callback_modal_cf7_id',
            'label'             => __( 'Форма Contact Form 7', 'tolstenko-theme' ),
            'name'              => 'block_callback_modal_cf7_id',
            'type'              => 'select',
            'choices'           => array(),
            'allow_null'        => 1,
            'instructions'      => __( 'Форма, которая открывается в модалке по кнопкам «Оставить заявку», «Консультация» и т.п.', 'tolstenko-theme' ),
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'callback-modal' ) ) ),
        ),
        // Шапка и подвал (header-footer): телефон, соцсети, текст футера
        array(
            'key'   => 'field_site_tab_phone',
            'label' => __( 'Телефон', 'tolstenko-theme' ),
            'name'  => '',
            'type'  => 'tab',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'               => 'field_site_phone',
            'label'             => __( 'Телефон (шапка и подвал)', 'tolstenko-theme' ),
            'name'              => 'site_phone',
            'type'              => 'text',
            'placeholder'       => '+7 (800) 500-71-48',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'   => 'field_site_tab_socials',
            'label' => __( 'Соцсети', 'tolstenko-theme' ),
            'name'  => '',
            'type'  => 'tab',
            'instructions' => __( 'Соцсети настраиваются в отдельном метабоксе «Соцсети (репитер)» ниже.', 'tolstenko-theme' ),
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'               => 'field_site_email',
            'label'             => __( 'Email (в подвале)', 'tolstenko-theme' ),
            'name'              => 'site_email',
            'type'              => 'email',
            'placeholder'       => 'sale@example.ru',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'   => 'field_site_tab_footer',
            'label' => __( 'Текст в футере', 'tolstenko-theme' ),
            'name'  => '',
            'type'  => 'tab',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'               => 'field_site_footer_html',
            'label'             => __( 'HTML-блок футера', 'tolstenko-theme' ),
            'name'              => 'site_footer_html',
            'type'              => 'wysiwyg',
            'tabs'              => 'all',
            'toolbar'            => 'full',
            'media_upload'       => 0,
            'instructions'      => __( 'Устарело: реквизиты и низ футера заполняются в «Настройки сайта → Контактные данные».', 'tolstenko-theme' ),
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'   => 'field_site_tab_promo_notice',
            'label' => __( 'Промо-плашка в шапке', 'tolstenko-theme' ),
            'name'  => '',
            'type'  => 'tab',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'               => 'field_site_promo_notice_html',
            'label'             => __( 'Текст плашки (HTML)', 'tolstenko-theme' ),
            'name'              => 'site_promo_notice_html',
            'type'              => 'wysiwyg',
            'tabs'              => 'all',
            'toolbar'           => 'full',
            'media_upload'      => 0,
            'instructions'      => __( 'Одна HTML-строка в верхней плашке шапки. Контент полностью задаётся вручную менеджером.', 'tolstenko-theme' ),
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'header-footer' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_block_title',
            'label'             => __( 'Заголовок блока', 'tolstenko-theme' ),
            'name'              => 'reviews_block_title',
            'type'              => 'text',
            'default_value'     => 'Отзывы',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_1_title',
            'label'             => __( 'Карточка 1 — название', 'tolstenko-theme' ),
            'name'              => 'reviews_card_1_title',
            'type'              => 'text',
            'default_value'     => 'Яндекс',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_1_rating',
            'label'             => __( 'Карточка 1 — оценка (1–5)', 'tolstenko-theme' ),
            'name'              => 'reviews_card_1_rating',
            'type'              => 'number',
            'min'               => 1,
            'max'               => 5,
            'default_value'     => 5,
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_2_title',
            'label'             => __( 'Карточка 2 — название', 'tolstenko-theme' ),
            'name'              => 'reviews_card_2_title',
            'type'              => 'text',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_2_rating',
            'label'             => __( 'Карточка 2 — оценка (1–5)', 'tolstenko-theme' ),
            'name'              => 'reviews_card_2_rating',
            'type'              => 'number',
            'min'               => 1,
            'max'               => 5,
            'default_value'     => 5,
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_3_title',
            'label'             => __( 'Карточка 3 — название', 'tolstenko-theme' ),
            'name'              => 'reviews_card_3_title',
            'type'              => 'text',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_3_rating',
            'label'             => __( 'Карточка 3 — оценка (1–5)', 'tolstenko-theme' ),
            'name'              => 'reviews_card_3_rating',
            'type'              => 'number',
            'min'               => 1,
            'max'               => 5,
            'default_value'     => 5,
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_4_title',
            'label'             => __( 'Карточка 4 — название', 'tolstenko-theme' ),
            'name'              => 'reviews_card_4_title',
            'type'              => 'text',
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
        array(
            'key'               => 'field_block_reviews_card_4_rating',
            'label'             => __( 'Карточка 4 — оценка (1–5)', 'tolstenko-theme' ),
            'name'              => 'reviews_card_4_rating',
            'type'              => 'number',
            'min'               => 1,
            'max'               => 5,
            'default_value'     => 5,
            'conditional_logic' => array( array( array( 'field' => 'field_block_type', 'operator' => '==', 'value' => 'reviews' ) ) ),
        ),
    );

    acf_add_local_field_group(
        array(
            'key'      => 'group_tolstenko_block',
            'title'    => __( 'Настройки блока', 'tolstenko-theme' ),
            'fields'   => $fields,
            'location' => array(
                array(
                    array( 'param' => 'post_type', 'operator' => '==', 'value' => 'block' ),
                ),
            ),
        )
    );
}
