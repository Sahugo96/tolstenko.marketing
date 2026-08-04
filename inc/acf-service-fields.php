<?php
/**
 * ACF: поля для записей типа «Услуга» (service).
 * Карточка слайдера редактируется в сайдбаре Gutenberg («Данные услуги»).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', 'tolstenko_register_acf_service_fields' );

function tolstenko_register_acf_service_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'    => 'group_tolstenko_service',
			'title'  => __( 'Услуга', 'tolstenko-theme' ),
			'fields' => array(
				array(
					'key'          => 'field_service_order',
					'label'        => __( 'Порядок сортировки', 'tolstenko-theme' ),
					'name'         => 'service_order',
					'type'         => 'number',
					'instructions' => __( 'Чем меньше число, тем выше услуга в списках. Пусто — по названию.', 'tolstenko-theme' ),
					'wrapper'      => array( 'width' => 25 ),
					'placeholder'  => 10,
					'min'          => 0,
					'step'         => 1,
				),
				array(
					'key'       => 'field_service_card_hint',
					'label'     => __( 'Карточка слайдера', 'tolstenko-theme' ),
					'name'      => '',
					'type'      => 'message',
					'message'   => __( 'Описание, цены и бейдж «хит» — в панели «Данные услуги (карточка)» справа в редакторе. Изображение — миниатюра записи.', 'tolstenko-theme' ),
					'new_lines' => 'wpautop',
					'esc_html'  => 0,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'service',
					),
				),
			),
		)
	);
}
