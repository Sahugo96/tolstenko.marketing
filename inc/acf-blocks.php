<?php
/**
 * Блоки темы для Gutenberg (без ACF Pro — через ядро WordPress).
 * В редакторе: категория «Блоки темы», добавляй блоки и меняй порядок.
 * Контент блоков: из настроек темы (Дефолты блоков) или атрибутов Gutenberg.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'tolstenko_register_theme_blocks', 20 );

add_action( 'enqueue_block_editor_assets', 'tolstenko_enqueue_editor_blocks' );
add_filter( 'block_editor_settings_all', 'tolstenko_inject_editor_blocks_styles', 20 );

/**
 * Стили RichText в iframe холста Gutenberg.
 *
 * @param array $settings Editor settings.
 * @return array
 */
function tolstenko_inject_editor_blocks_styles( $settings ) {
	$path = get_template_directory() . '/assets/css/editor-blocks.css';
	if ( ! is_readable( $path ) ) {
		return $settings;
	}
	$css = file_get_contents( $path );
	if ( ! is_string( $css ) || $css === '' || ! is_array( $settings ) ) {
		return $settings;
	}
	if ( empty( $settings['styles'] ) || ! is_array( $settings['styles'] ) ) {
		$settings['styles'] = array();
	}
	$settings['styles'][] = array(
		'css' => $css,
	);
	return $settings;
}

/**
 * Тип записи текущего редактора Gutenberg.
 *
 * @param mixed $editor_context Контекст редактора.
 * @return string|null
 */
function tolstenko_get_editor_post_type( $editor_context = null ) {
    if ( $editor_context && isset( $editor_context->post->post_type ) ) {
        return $editor_context->post->post_type;
    }
    if ( ! empty( $_GET['post'] ) && is_numeric( $_GET['post'] ) ) {
        return get_post_type( (int) $_GET['post'] );
    }
    if ( ! empty( $_GET['post_type'] ) ) {
        return sanitize_key( $_GET['post_type'] );
    }
    return null;
}

/**
 * Блоки шаблона вакансии (баннер / контент / похожие).
 *
 * @return string[]
 */
function tolstenko_get_vacancy_only_blocks() {
    return array(
        'tolstenko/hero-vacancy',
        'tolstenko/vacancy-content',
        'tolstenko/same-vacancy',
    );
}

/**
 * Разрешаем блоки темы в редакторе записей «Услуга».
 * Блоки шаблона вакансии скрываем на остальных типах (кроме vacancy).
 */
add_filter( 'allowed_block_types_all', 'tolstenko_filter_allowed_blocks_by_post_type', 10, 2 );
function tolstenko_filter_allowed_blocks_by_post_type( $allowed, $editor_context ) {
    $post_type      = tolstenko_get_editor_post_type( $editor_context );
    $vacancy_blocks = tolstenko_get_vacancy_only_blocks();

    // В записях «Вакансия» — блоки шаблона вакансии + бесплатная консультация.
    if ( $post_type === 'vacancy' ) {
        return array_merge( $vacancy_blocks, array( 'tolstenko/consultation-free' ) );
    }

    // Статья / Акция: текст Gutenberg + блоки гибкого содержимого тела.
    $body_types = function_exists( 'tolstenko_get_content_body_post_types' )
        ? tolstenko_get_content_body_post_types()
        : array( 'blog', 'actions' );
    if ( in_array( $post_type, $body_types, true ) && function_exists( 'tolstenko_get_blog_editor_allowed_blocks' ) ) {
        return tolstenko_get_blog_editor_allowed_blocks();
    }

    $content_only = function_exists( 'tolstenko_get_blog_content_only_block_names' )
        ? tolstenko_get_blog_content_only_block_names()
        : array();

    $tolstenko_service = array(
        'tolstenko/main-hero',
        'tolstenko/contacts-page', 'tolstenko/contacts-details', 'tolstenko/contacts-maps', 'tolstenko/reviews',
        'tolstenko/certificates',
        'tolstenko/actions',
        'tolstenko/actions-section',
        'tolstenko/city',
        'tolstenko/vacancies-banner',
        'tolstenko/vacancies-section',
        'tolstenko/case-section',
        'tolstenko/service-section',
        'tolstenko/service-section-simple',
        'tolstenko/service-section-tile',
        'tolstenko/blog-section-simple',
        'tolstenko/blog-section',
        'tolstenko/blog-section-tile',
        'tolstenko/consultation-whatsapp', 'tolstenko/consultation-tg',
        'tolstenko/consultation-tel', 'tolstenko/consultation-free',
        'tolstenko/free-audit', 'tolstenko/solution', 'tolstenko/one-team', 'tolstenko/author', 'tolstenko/different-experiences', 'tolstenko/partners',
        'tolstenko/strategy', 'tolstenko/team-cards', 'tolstenko/tg-channel',
        'tolstenko/three-steps', 'tolstenko/faq', 'tolstenko/seo-section', 'tolstenko/hidden-seo',
    );

    if ( $post_type === 'service' ) {
        if ( $allowed === true ) {
            $registry = WP_Block_Type_Registry::get_instance()->get_all_registered();
            return array_values( array_diff( array_keys( $registry ), array_merge( $vacancy_blocks, $content_only ) ) );
        }
        if ( is_array( $allowed ) ) {
            return array_values( array_diff( array_merge( $allowed, $tolstenko_service ), array_merge( $vacancy_blocks, $content_only ) ) );
        }
        return $tolstenko_service;
    }

    if ( $allowed === true ) {
        $registry = WP_Block_Type_Registry::get_instance()->get_all_registered();
        return array_values( array_diff( array_keys( $registry ), array_merge( $vacancy_blocks, $content_only ) ) );
    }
    if ( is_array( $allowed ) ) {
        return array_values( array_diff( $allowed, array_merge( $vacancy_blocks, $content_only ) ) );
    }
    return $allowed;
}

function tolstenko_enqueue_editor_blocks() {
    wp_enqueue_media();
    wp_enqueue_editor();
    $uri = get_template_directory_uri();
    $path = get_template_directory() . '/assets/js/editor-blocks.js';
    $ver = file_exists( $path ) ? (string) filemtime( $path ) : '1.0';
    wp_enqueue_script(
        'tolstenko-editor-blocks',
        $uri . '/assets/js/editor-blocks.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-edit-post', 'wp-editor', 'wp-data', 'wp-core-data', 'wp-plugins' ),
        $ver,
        true
    );
	$editor_css = get_template_directory() . '/assets/css/editor-blocks.css';
	if ( is_readable( $editor_css ) ) {
		wp_enqueue_style(
			'tolstenko-editor-blocks',
			$uri . '/assets/css/editor-blocks.css',
			array(),
			(string) filemtime( $editor_css )
		);
	}
    if ( function_exists( 'tolstenko_get_block_defaults' ) ) {
        $vacancy_posts_for_editor = array();
        if ( post_type_exists( 'vacancy' ) ) {
            $vacancy_list = get_posts(
                array(
                    'post_type'      => 'vacancy',
                    'posts_per_page' => 100,
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                )
            );
            foreach ( $vacancy_list as $vp ) {
                $vacancy_posts_for_editor[] = array(
                    'id'    => (int) $vp->ID,
                    'title' => get_the_title( $vp ),
                );
            }
        }
        $action_posts_for_editor = array();
        if ( post_type_exists( 'actions' ) ) {
            $action_list = get_posts(
                array(
                    'post_type'      => 'actions',
                    'posts_per_page' => 100,
                    'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
                    'orderby'        => 'title',
                    'order'          => 'ASC',
                )
            );
            foreach ( $action_list as $ap ) {
                $action_posts_for_editor[] = array(
                    'id'    => (int) $ap->ID,
                    'title' => get_the_title( $ap ),
                );
            }
        }
        $blog_authors_for_editor = array();
        if ( function_exists( 'tolstenko_get_blog_authors_list' ) ) {
            foreach ( tolstenko_get_blog_authors_list() as $index => $author_row ) {
                $blog_authors_for_editor[] = array(
                    'index' => (string) $index,
                    'label' => tolstenko_get_blog_author_option_label( $author_row, (int) $index ),
                );
            }
        }
        wp_localize_script(
            'tolstenko-editor-blocks',
            'tolstenkoBlockDefaults',
            array(
				'contacts_page'    => tolstenko_get_block_defaults( 'contacts_page' ),
				'contacts_details' => tolstenko_get_block_defaults( 'contacts_details' ),
				'contacts_maps'    => tolstenko_get_block_defaults( 'contacts_maps' ),
                'main_hero'      => tolstenko_get_block_defaults( 'main_hero' ),
                'certificates'   => tolstenko_get_block_defaults( 'certificates' ),
                'actions'          => tolstenko_get_block_defaults( 'actions' ),
                'actions_section' => tolstenko_get_block_defaults( 'actions_section' ),
                'reviews'           => tolstenko_get_block_defaults( 'reviews' ),
                'vacancies_banner'  => tolstenko_get_block_defaults( 'vacancies_banner' ),
                'vacancies_section' => tolstenko_get_block_defaults( 'vacancies_section' ),
                'case_section'      => tolstenko_get_block_defaults( 'case_section' ),
                'service_section'   => tolstenko_get_block_defaults( 'service_section' ),
                'service_section_filters' => tolstenko_get_block_defaults( 'service_section_filters' ),
                'service_section_tile' => tolstenko_get_block_defaults( 'service_section_tile' ),
                'blog_section'          => tolstenko_get_block_defaults( 'blog_section' ),
                'blog_section_filters'  => tolstenko_get_block_defaults( 'blog_section_filters' ),
                'blog_section_tile'     => tolstenko_get_block_defaults( 'blog_section_tile' ),
                'blog_large_img'    => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_large_img' ) : tolstenko_get_block_defaults( 'blog_large_img' ),
                'blog_video'        => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_video' ) : tolstenko_get_block_defaults( 'blog_video' ),
                'blog_blockquote'   => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_blockquote' ) : tolstenko_get_block_defaults( 'blog_blockquote' ),
                'blog_number_list'  => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_number_list' ) : tolstenko_get_block_defaults( 'blog_number_list' ),
                'blog_warning'      => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_warning' ) : tolstenko_get_block_defaults( 'blog_warning' ),
                'blog_seo'          => function_exists( 'tolstenko_get_blog_content_defaults' ) ? tolstenko_get_blog_content_defaults( 'blog_seo' ) : tolstenko_get_block_defaults( 'blog_seo' ),
                'consultation_whatsapp' => tolstenko_get_block_defaults( 'consultation_whatsapp' ),
                'consultation_tg'       => tolstenko_get_block_defaults( 'consultation_tg' ),
                'consultation_tel'      => tolstenko_get_block_defaults( 'consultation_tel' ),
                'consultation_free'     => tolstenko_get_block_defaults( 'consultation_free' ),
                'free_audit'            => tolstenko_get_block_defaults( 'free_audit' ),
                'solution'              => tolstenko_get_block_defaults( 'solution' ),
                'one_team'              => tolstenko_get_block_defaults( 'one_team' ),
                'author'                => tolstenko_get_block_defaults( 'author' ),
                'different_experiences' => tolstenko_get_block_defaults( 'different_experiences' ),
                'partners'              => tolstenko_get_block_defaults( 'partners' ),
                'strategy'              => tolstenko_get_block_defaults( 'strategy' ),
                'team_cards'            => tolstenko_get_block_defaults( 'team_cards' ),
                'tg_channel'            => tolstenko_get_block_defaults( 'tg_channel' ),
                'three_steps'           => tolstenko_get_block_defaults( 'three_steps' ),
                'faq'                   => tolstenko_get_block_defaults( 'faq' ),
                'seo_section'           => tolstenko_get_block_defaults( 'seo_section' ),
                'we_can'                => tolstenko_get_block_defaults( 'we_can' ),
                'recomendation'         => tolstenko_get_block_defaults( 'recomendation' ),
                'referal'               => tolstenko_get_block_defaults( 'referal' ),
                'commission'            => tolstenko_get_block_defaults( 'commission' ),
                'benefits_cooperation'  => tolstenko_get_block_defaults( 'benefits_cooperation' ),
                'aducation'             => tolstenko_get_block_defaults( 'aducation' ),
                'clients'               => tolstenko_get_block_defaults( 'clients' ),
                'themes'                => tolstenko_get_block_defaults( 'themes' ),
                'collaboration'         => tolstenko_get_block_defaults( 'collaboration' ),
                'hero_vacancy'          => tolstenko_get_block_defaults( 'hero_vacancy' ),
                'vacancy_content'       => tolstenko_get_block_defaults( 'vacancy_content' ),
                'same_vacancy'          => tolstenko_get_block_defaults( 'same_vacancy' ),
                'vacancyPosts'          => $vacancy_posts_for_editor,
                'actionPosts'           => $action_posts_for_editor,
                'blogAuthors'           => $blog_authors_for_editor,
            )
        );
    }
}

function tolstenko_get_theme_block_attributes() {
    return array(
        'main-hero' => array(
            'block_main_hero_title'          => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_title_tag'      => array( 'type' => 'string', 'default' => 'h1' ),
            'block_main_hero_text'           => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_items'          => array( 'type' => 'array', 'default' => array() ),
            'block_main_hero_btn_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_show_promo'     => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_promo_text'     => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_present_image'  => array( 'type' => 'integer', 'default' => 0 ),
            'block_main_hero_person_name'    => array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_person_position'=> array( 'type' => 'string', 'default' => '' ),
            'block_main_hero_image'          => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'contacts-page' => array(
            'block_contacts_page_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_contacts_page_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_contacts_page_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_contacts_page_addresses' => array( 'type' => 'array', 'default' => array() ),
        ),
        'contacts-details' => array(
            'block_contacts_details_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_contacts_details_title_tag'  => array( 'type' => 'string', 'default' => 'h2' ),
            'block_contacts_details_items'      => array( 'type' => 'array', 'default' => array() ),
            'block_contacts_details_form_title' => array( 'type' => 'string', 'default' => '' ),
            'block_contacts_details_form_text'  => array( 'type' => 'string', 'default' => '' ),
        ),
        'contacts-maps' => array(
            'block_contacts_maps_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_contacts_maps_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_contacts_maps_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'thanks' => array(
            'block_thanks_title'       => array( 'type' => 'string', 'default' => '' ),
            'block_thanks_title_tag'   => array( 'type' => 'string', 'default' => 'h2' ),
            'block_thanks_description' => array( 'type' => 'string', 'default' => '' ),
        ),
        'reviews' => array(
            'block_reviews_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_reviews_title_tag'  => array( 'type' => 'string', 'default' => 'h2' ),
            'block_reviews_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_reviews_show_items' => array( 'type' => 'boolean', 'default' => true ),
            'block_reviews_ids'        => array(
                'type'    => 'array',
                'default' => array(),
                'items'   => array( 'type' => 'number' ),
            ),
        ),
        'certificates' => array(
            'block_certificates_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_certificates_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_certificates_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_certificates_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'actions' => array(
            'block_actions_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_actions_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_actions_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'actions-section' => array(
            'block_actions_section_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_actions_section_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_actions_section_text'      => array( 'type' => 'string', 'default' => '' ),
        ),
        'city' => array(
            'block_city_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_city_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_city_text'      => array( 'type' => 'string', 'default' => '' ),
        ),
        'vacancies-banner' => array(
            'block_vacancies_banner_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_vacancies_banner_title_tag' => array( 'type' => 'string', 'default' => 'h1' ),
            'block_vacancies_banner_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_vacancies_banner_image'     => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'vacancies-section' => array(
            'block_vacancies_section_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_vacancies_section_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_vacancies_section_text'      => array( 'type' => 'string', 'default' => '' ),
        ),
        'case-section' => array(
            'block_case_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_case_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_case_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_case_section_posts_per_page'  => array( 'type' => 'number', 'default' => 4 ),
        ),
        'service-section' => array(
            'block_service_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_service_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_service_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_service_section_posts_per_page'  => array( 'type' => 'number', 'default' => 6 ),
            'block_service_section_ids'             => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
        ),
        'service-section-simple' => array(
            'block_service_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_service_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_service_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_service_section_posts_per_page'  => array( 'type' => 'number', 'default' => 6 ),
            'block_service_section_ids'             => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
        ),
        'blog-section-simple' => array(
            'block_blog_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_blog_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_posts_per_page'  => array( 'type' => 'number', 'default' => 6 ),
            'block_blog_section_ids'             => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
            'block_blog_section_exclude'         => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
        ),
        'blog-section' => array(
            'block_blog_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_blog_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_posts_per_page'  => array( 'type' => 'number', 'default' => 6 ),
            'block_blog_section_ids'             => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
        ),
        'blog-section-tile' => array(
            'block_blog_section_title'           => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_title_tag'       => array( 'type' => 'string', 'default' => 'h2' ),
            'block_blog_section_text'            => array( 'type' => 'string', 'default' => '' ),
            'block_blog_section_posts_per_page'  => array( 'type' => 'number', 'default' => 9 ),
            'block_blog_section_ids'             => array(
                'type'    => 'array',
                'items'   => array( 'type' => 'number' ),
                'default' => array(),
            ),
        ),
        'service-section-tile' => array(
            'block_service_section_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_service_section_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_service_section_text'      => array( 'type' => 'string', 'default' => '' ),
        ),
        'consultation-whatsapp' => array(
            'block_consultation_whatsapp_title'       => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_whatsapp_title_tag'   => array( 'type' => 'string', 'default' => 'h2' ),
            'block_consultation_whatsapp_text'        => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_whatsapp_btn_text'    => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_whatsapp_btn_url'     => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_whatsapp_color'       => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_whatsapp_color_hover' => array( 'type' => 'string', 'default' => '' ),
        ),
        'consultation-tg' => array(
            'block_consultation_tg_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tg_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_consultation_tg_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tg_btn_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tg_btn_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tg_text_btn'  => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tg_image'     => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'consultation-tel' => array(
            'block_consultation_tel_title'              => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_title_tag'          => array( 'type' => 'string', 'default' => 'h2' ),
            'block_consultation_tel_message'            => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_position'           => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_phone'              => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_btn_tel_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_btn_messenger_text' => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_btn_messenger_url'  => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_color'              => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_color_hover'        => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_tel_image'              => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'consultation-free' => array(
            'block_consultation_free_title'          => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_title_tag'      => array( 'type' => 'string', 'default' => 'h2' ),
            'block_consultation_free_text'           => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_subtitle'       => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_contacts_label' => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_phone'          => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_telegram_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_whatsapp_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_vk_url'         => array( 'type' => 'string', 'default' => '' ),
            'block_consultation_free_image'          => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'free-audit' => array(
            'block_free_audit_items'    => array( 'type' => 'array', 'default' => array() ),
            'block_free_audit_btn_text' => array( 'type' => 'string', 'default' => '' ),
            'block_free_audit_btn_url'  => array( 'type' => 'string', 'default' => '' ),
        ),
        'solution' => array(
            'block_solution_title'         => array( 'type' => 'string', 'default' => '' ),
            'block_solution_title_tag'     => array( 'type' => 'string', 'default' => 'h2' ),
            'block_solution_text'          => array( 'type' => 'string', 'default' => '' ),
            'block_solution_items'         => array( 'type' => 'array', 'default' => array() ),
            'block_solution_items_second'  => array( 'type' => 'array', 'default' => array() ),
        ),
        'one-team' => array(
            'block_one_team_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_one_team_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_one_team_btn_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_one_team_btn_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_one_team_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'author' => array(
            'block_author_name'             => array( 'type' => 'string', 'default' => '' ),
            'block_author_name_tag'         => array( 'type' => 'string', 'default' => 'h2' ),
            'block_author_photo'            => array( 'type' => 'integer', 'default' => 0 ),
            'block_author_btn_text'         => array( 'type' => 'string', 'default' => '' ),
            'block_author_btn_url'          => array( 'type' => 'string', 'default' => '' ),
            'block_author_list'             => array( 'type' => 'array', 'default' => array() ),
            'block_author_items'            => array( 'type' => 'array', 'default' => array() ),
            'block_author_links_label'      => array( 'type' => 'string', 'default' => '' ),
            'block_author_links'            => array( 'type' => 'array', 'default' => array() ),
            'block_author_show_bottom'      => array( 'type' => 'boolean', 'default' => true ),
            'block_author_subtitle'         => array( 'type' => 'string', 'default' => '' ),
            'block_author_text'             => array( 'type' => 'string', 'default' => '' ),
            'block_author_sublist'          => array( 'type' => 'array', 'default' => array() ),
            'block_author_btn_more_text'    => array( 'type' => 'string', 'default' => '' ),
            'block_author_btn_more_url'     => array( 'type' => 'string', 'default' => '' ),
            'block_author_award'            => array( 'type' => 'string', 'default' => '' ),
            'block_author_award_image'      => array( 'type' => 'integer', 'default' => 0 ),
            'block_author_right_image'      => array( 'type' => 'integer', 'default' => 0 ),
            'block_author_speeches'         => array( 'type' => 'array', 'default' => array() ),
            'block_author_btn_invite_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_author_btn_invite_url'   => array( 'type' => 'string', 'default' => '' ),
        ),
        'different-experiences' => array(
            'block_different_experiences_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_different_experiences_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_different_experiences_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_different_experiences_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_different_experiences_tg_text'   => array( 'type' => 'string', 'default' => '' ),
            'block_different_experiences_tg_url'    => array( 'type' => 'string', 'default' => '' ),
            'block_different_experiences_modal_text'=> array( 'type' => 'string', 'default' => '' ),
            'block_different_experiences_modal_url' => array( 'type' => 'string', 'default' => '' ),
        ),
        'partners' => array(
            'block_partners_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_partners_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_partners_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_partners_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'strategy' => array(
            'block_strategy_title'          => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_title_tag'      => array( 'type' => 'string', 'default' => 'h2' ),
            'block_strategy_subtitle'       => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_text'           => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_items'          => array( 'type' => 'array', 'default' => array() ),
            'block_strategy_btn_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_btn_url'        => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_file_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_file_url'       => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_contacts_label' => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_phone'          => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_telegram_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_telegram_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_strategy_image'          => array( 'type' => 'integer', 'default' => 0 ),
            'block_strategy_image_mob'      => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'team-cards' => array(
            'block_team_cards_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_team_cards_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_team_cards_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_team_cards_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'tg-channel' => array(
            'block_tg_channel_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_tg_channel_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_tg_channel_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_tg_channel_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_tg_channel_btn_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_tg_channel_btn_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_tg_channel_image'     => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'three-steps' => array(
            'block_three_steps_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_three_steps_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_three_steps_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_three_steps_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'faq' => array(
            'block_faq_title'        => array( 'type' => 'string', 'default' => '' ),
            'block_faq_title_tag'    => array( 'type' => 'string', 'default' => 'h2' ),
            'block_faq_text'         => array( 'type' => 'string', 'default' => '' ),
            'block_faq_items'        => array( 'type' => 'array', 'default' => array() ),
            'block_faq_form_title'   => array( 'type' => 'string', 'default' => '' ),
            'block_faq_form_text'    => array( 'type' => 'string', 'default' => '' ),
            'block_faq_foto'         => array( 'type' => 'integer', 'default' => 0 ),
            'block_faq_foto_text'    => array( 'type' => 'string', 'default' => '' ),
            'block_faq_phone'        => array( 'type' => 'string', 'default' => '' ),
            'block_faq_telegram_url' => array( 'type' => 'string', 'default' => '' ),
        ),
        'seo-section' => array(
            'block_seo_section_title'         => array( 'type' => 'string', 'default' => '' ),
            'block_seo_section_title_tag'     => array( 'type' => 'string', 'default' => 'h2' ),
            'block_seo_section_subtitle'      => array( 'type' => 'string', 'default' => '' ),
            'block_seo_section_more_text'     => array( 'type' => 'string', 'default' => '' ),
            'block_seo_section_blocks'        => array( 'type' => 'array', 'default' => array() ),
        ),
        'hidden-seo'  => array(),
        'we-can' => array(
            'block_we_can_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_we_can_title_tag'  => array( 'type' => 'string', 'default' => 'h2' ),
            'block_we_can_items'      => array( 'type' => 'array', 'default' => array() ),
            'block_we_can_list_title' => array( 'type' => 'string', 'default' => '' ),
            'block_we_can_list'       => array( 'type' => 'array', 'default' => array() ),
            'block_we_can_form_title' => array( 'type' => 'string', 'default' => '' ),
            'block_we_can_form_text'  => array( 'type' => 'string', 'default' => '' ),
        ),
        'recomendation' => array(
            'block_recomendation_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_recomendation_title_tag'  => array( 'type' => 'string', 'default' => 'h2' ),
            'block_recomendation_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_recomendation_items'      => array( 'type' => 'array', 'default' => array() ),
            'block_recomendation_list_title' => array( 'type' => 'string', 'default' => '' ),
            'block_recomendation_list'       => array( 'type' => 'array', 'default' => array() ),
            'block_recomendation_btn_text'   => array( 'type' => 'string', 'default' => '' ),
            'block_recomendation_btn_url'    => array( 'type' => 'string', 'default' => '' ),
        ),
        'referal' => array(
            'block_referal_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_referal_title_tag'  => array( 'type' => 'string', 'default' => 'h2' ),
            'block_referal_items'      => array( 'type' => 'array', 'default' => array() ),
            'block_referal_list_title' => array( 'type' => 'string', 'default' => '' ),
            'block_referal_list'       => array( 'type' => 'array', 'default' => array() ),
            'block_referal_btn_text'   => array( 'type' => 'string', 'default' => '' ),
            'block_referal_btn_url'    => array( 'type' => 'string', 'default' => '' ),
        ),
        'commission' => array(
            'block_commission_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_commission_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_commission_text'      => array( 'type' => 'string', 'default' => '' ),
            'block_commission_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'benefits-cooperation' => array(
            'block_benefits_cooperation_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_benefits_cooperation_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_benefits_cooperation_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        'aducation' => array(
            'block_aducation_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_aducation_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_aducation_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_aducation_images'    => array( 'type' => 'array', 'default' => array() ),
        ),
        'clients' => array(
            'block_clients_title'         => array( 'type' => 'string', 'default' => '' ),
            'block_clients_title_tag'     => array( 'type' => 'string', 'default' => 'h2' ),
            'block_clients_text'          => array( 'type' => 'string', 'default' => '' ),
            'block_clients_items'         => array( 'type' => 'array', 'default' => array() ),
            'block_clients_show_top'      => array( 'type' => 'boolean', 'default' => true ),
            'block_clients_subtitle'      => array( 'type' => 'string', 'default' => '' ),
            'block_clients_smi'           => array( 'type' => 'array', 'default' => array() ),
            'block_clients_show_bottom'   => array( 'type' => 'boolean', 'default' => true ),
        ),
        'themes' => array(
            'block_themes_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_themes_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_themes_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_themes_more_text' => array( 'type' => 'string', 'default' => '' ),
            'block_themes_btn_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_themes_btn_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_themes_image'     => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'collaboration' => array(
            'block_collaboration_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_collaboration_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_collaboration_items'     => array( 'type' => 'array', 'default' => array() ),
            'block_collaboration_btn_text'  => array( 'type' => 'string', 'default' => '' ),
            'block_collaboration_btn_url'   => array( 'type' => 'string', 'default' => '' ),
            'block_collaboration_image'     => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'hero-vacancy' => array(
            'block_hero_vacancy_title'      => array( 'type' => 'string', 'default' => '' ),
            'block_hero_vacancy_title_tag'  => array( 'type' => 'string', 'default' => 'h1' ),
            'block_hero_vacancy_cost'       => array( 'type' => 'string', 'default' => '' ),
            'block_hero_vacancy_conditions' => array( 'type' => 'array', 'default' => array() ),
            'block_hero_vacancy_items'      => array( 'type' => 'array', 'default' => array() ),
            'block_hero_vacancy_btn_text'   => array( 'type' => 'string', 'default' => '' ),
            'block_hero_vacancy_btn_url'    => array( 'type' => 'string', 'default' => '' ),
            'block_hero_vacancy_btn_close'  => array( 'type' => 'string', 'default' => '' ),
            'block_hero_vacancy_image'      => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'vacancy-content' => array(
            'block_vacancy_content_title'            => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_title_tag'        => array( 'type' => 'string', 'default' => 'h2' ),
            'block_vacancy_content_html'             => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_apply_text'       => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_apply_url'        => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_sidebar_author'   => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_sidebar_btn'      => array( 'type' => 'string', 'default' => '' ),
            'block_vacancy_content_sidebar_btn_url'  => array( 'type' => 'string', 'default' => '' ),
        ),
        'same-vacancy' => array(
            'block_same_vacancy_title'     => array( 'type' => 'string', 'default' => '' ),
            'block_same_vacancy_title_tag' => array( 'type' => 'string', 'default' => 'h2' ),
            'block_same_vacancy_items'     => array( 'type' => 'array', 'default' => array() ),
        ),
        // Тело статьи (flexible content Tolstenko → Gutenberg).
        'blog-large-img' => array(
            'block_blog_large_img_id' => array( 'type' => 'integer', 'default' => 0 ),
        ),
        'blog-video' => array(
            'block_blog_video_preview' => array( 'type' => 'integer', 'default' => 0 ),
            'block_blog_video_url'     => array( 'type' => 'string', 'default' => '' ),
            'block_blog_video_iframe'  => array( 'type' => 'string', 'default' => '' ),
        ),
        'blog-blockquote' => array(
            'block_blog_blockquote_text'         => array( 'type' => 'string', 'default' => '' ),
            'block_blog_blockquote_link'         => array( 'type' => 'string', 'default' => '' ),
            'block_blog_blockquote_show_author'  => array( 'type' => 'boolean', 'default' => false ),
            'block_blog_blockquote_image'        => array( 'type' => 'integer', 'default' => 0 ),
            'block_blog_blockquote_author'       => array( 'type' => 'string', 'default' => '' ),
            'block_blog_blockquote_author_under' => array( 'type' => 'string', 'default' => '' ),
            'block_blog_blockquote_btn_text'     => array( 'type' => 'string', 'default' => '' ),
            'block_blog_blockquote_btn_url'      => array( 'type' => 'string', 'default' => '' ),
        ),
        'blog-number-list' => array(
            'block_blog_number_list_items' => array(
                'type'    => 'array',
                'default' => array(),
            ),
        ),
        'blog-warning' => array(
            'block_blog_warning_items' => array(
                'type'    => 'array',
                'default' => array(),
            ),
        ),
        'blog-seo' => array(
            'block_blog_seo_title'   => array( 'type' => 'string', 'default' => '' ),
            'block_blog_seo_btn'     => array( 'type' => 'string', 'default' => '' ),
            'block_blog_seo_btn_url' => array( 'type' => 'string', 'default' => '' ),
        ),
    );
}

function tolstenko_register_theme_blocks() {
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

	$cat_new      = 'tolstenko-blocks-new';
	$cat_partner  = 'tolstenko-blocks-partner';
	$cat_press    = 'tolstenko-blocks-press';
	$cat_contacts = 'tolstenko-blocks-contacts';

    $blocks = array(
        array( 'name' => 'main-hero', 'title' => __( 'Главный баннер', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'contacts-page', 'title' => __( 'Контакты', 'tolstenko-theme' ), 'category' => $cat_contacts ),
        array( 'name' => 'contacts-details', 'title' => __( 'Реквизиты', 'tolstenko-theme' ), 'category' => $cat_contacts ),
        array( 'name' => 'contacts-maps', 'title' => __( 'Карты', 'tolstenko-theme' ), 'category' => $cat_contacts ),
        array( 'name' => 'reviews', 'title' => __( 'Отзывы', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'certificates', 'title' => __( 'Сертификаты', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'actions', 'title' => __( 'Акции, бонусы, подарки', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'actions-section', 'title' => __( 'Плитка акций', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'city', 'title' => __( 'Города', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'vacancies-banner', 'title' => __( 'Баннер вакансий', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'vacancies-section', 'title' => __( 'Секция вакансий', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'case-section', 'title' => __( 'Кейсы', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'service-section-simple', 'title' => __( 'Слайдер услуг', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'service-section', 'title' => __( 'Слайдер услуг (фильтры)', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'service-section-tile', 'title' => __( 'Услуги (плитка)', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-section-simple', 'title' => __( 'Слайдер статей', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-section', 'title' => __( 'Статьи', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-section-tile', 'title' => __( 'Статьи плитка', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-large-img', 'title' => __( 'Статья: крупное фото', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-video', 'title' => __( 'Статья: видео', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-blockquote', 'title' => __( 'Статья: цитата', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-number-list', 'title' => __( 'Статья: нумерованный список', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-warning', 'title' => __( 'Статья: предупреждения', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'blog-seo', 'title' => __( 'Статья: SEO / CTA', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'consultation-whatsapp', 'title' => __( 'Забронируйте место', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'consultation-tg', 'title' => __( 'Консультация Telegram', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'consultation-tel', 'title' => __( 'Консультация телефон', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'consultation-free', 'title' => __( 'Бесплатная консультация', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'free-audit', 'title' => __( 'Бесплатный аудит', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'solution', 'title' => __( 'Решение', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'one-team', 'title' => __( 'Одна команда', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'author', 'title' => __( 'Автор', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'different-experiences', 'title' => __( 'Разный опыт', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'partners', 'title' => __( 'Партнёры', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'strategy', 'title' => __( 'Стратегия', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'team-cards', 'title' => __( 'Команда', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'tg-channel', 'title' => __( 'Telegram-канал', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'three-steps', 'title' => __( 'Три шага', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'faq', 'title' => __( 'FAQ', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'seo-section', 'title' => __( 'SEO продвижение', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'hidden-seo', 'title' => __( 'Скрытый seo', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'we-can', 'title' => __( 'Мы можем', 'tolstenko-theme' ), 'category' => $cat_partner ),
        array( 'name' => 'recomendation', 'title' => __( 'Рекомендации', 'tolstenko-theme' ), 'category' => $cat_partner ),
        array( 'name' => 'referal', 'title' => __( 'Рефералка', 'tolstenko-theme' ), 'category' => $cat_partner ),
        array( 'name' => 'commission', 'title' => __( 'Вознаграждение', 'tolstenko-theme' ), 'category' => $cat_partner ),
        array( 'name' => 'benefits-cooperation', 'title' => __( 'Преимущества', 'tolstenko-theme' ), 'category' => $cat_partner ),
        array( 'name' => 'aducation', 'title' => __( 'Образование', 'tolstenko-theme' ), 'category' => $cat_press ),
        array( 'name' => 'clients', 'title' => __( 'Клиенты', 'tolstenko-theme' ), 'category' => $cat_press ),
        array( 'name' => 'themes', 'title' => __( 'Темы обучений и выступлений', 'tolstenko-theme' ), 'category' => $cat_press ),
        array( 'name' => 'collaboration', 'title' => __( 'Форматы сотрудничества', 'tolstenko-theme' ), 'category' => $cat_press ),
        array( 'name' => 'hero-vacancy', 'title' => __( 'Баннер вакансии', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'vacancy-content', 'title' => __( 'Контент вакансии', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'same-vacancy', 'title' => __( 'Похожие вакансии', 'tolstenko-theme' ), 'category' => $cat_new ),
        array( 'name' => 'thanks', 'title' => __( 'Страница «Спасибо»', 'tolstenko-theme' ) ),
    );

    $block_attrs = tolstenko_get_theme_block_attributes();

    foreach ( $blocks as $b ) {
        $attrs = isset( $block_attrs[ $b['name'] ] ) ? $block_attrs[ $b['name'] ] : array();
        register_block_type(
            'tolstenko/' . $b['name'],
            array(
                'api_version'      => 3,
                'title'            => $b['title'],
                'category'         => ! empty( $b['category'] ) ? $b['category'] : 'tolstenko-blocks',
                'icon'             => 'layout',
                'description'      => sprintf(
                    /* translators: %s: block title */
                    __( 'Блок темы: %s.', 'tolstenko-theme' ),
                    $b['title']
                ),
                'render_callback'  => 'tolstenko_render_theme_block',
                'editor_script'    => 'tolstenko-editor-blocks',
                'attributes'       => $attrs,
                'supports'         => array(
                    'align' => array( 'wide', 'full' ),
                ),
            )
        );
    }
}

/**
 * Рендер блока темы: подключает шаблон из template-parts/blocks/.
 * Контент — дефолты из «Настройки сайта → Дефолты блоков» или атрибуты Gutenberg.
 *
 * @param array    $attributes Атрибуты блока.
 * @param string   $content    Внутренний контент (пусто у динамических блоков).
 * @param WP_Block $block      Объект блока.
 * @return string
 */
function tolstenko_render_theme_block( $attributes, $content, $block ) {
    $block_name = isset( $block->block_type->name ) ? $block->block_type->name : '';
    $slug       = str_replace( array( 'tolstenko/', 'koritan/' ), '', $block_name );
    if ( empty( $slug ) ) {
        return '';
    }
    $path = get_template_directory() . '/template-parts/blocks/' . $slug . '.php';
    if ( ! file_exists( $path ) ) {
        return '';
    }

    // В теле статьи/акции — компактная вёрстка без section/container.
    $blog_inline = function_exists( 'tolstenko_is_content_body_singular' )
        ? tolstenko_is_content_body_singular()
        : is_singular( array( 'blog', 'actions' ) );
    $bem         = function_exists( 'tolstenko_get_single_content_bem' )
        ? tolstenko_get_single_content_bem()
        : 'single-blog';
    set_query_var( 'tolstenko_block_attributes', is_array( $attributes ) ? $attributes : array() );
    set_query_var( 'tolstenko_block_inner_content', $content );
    set_query_var( 'tolstenko_block_blog_inline', $blog_inline );
    ob_start();
    get_template_part( 'template-parts/blocks/' . $slug );
    $html = ob_get_clean();
    set_query_var( 'tolstenko_block_blog_inline', false );

    if ( 'hidden-seo' === $slug ) {
        return $html;
    }

    if ( $blog_inline && $html !== '' ) {
        if ( function_exists( 'tolstenko_adapt_single_content_classes' ) ) {
            $html = tolstenko_adapt_single_content_classes( $html );
        }
        // Блоки тела и WA/TG отдают свою разметку — не оборачивать повторно.
        $self_wrapped = ( strpos( $slug, 'blog-' ) === 0 )
            || in_array( $slug, array( 'consultation-whatsapp', 'consultation-tg' ), true );
        if ( $self_wrapped ) {
            return $html;
        }
        return '<div class="' . esc_attr( $bem ) . '__content-block ' . esc_attr( $bem ) . '__content-block--' . esc_attr( sanitize_html_class( $slug ) ) . '">' . $html . '</div>';
    }
    if ( function_exists( 'tolstenko_is_service_single_page' ) && tolstenko_is_service_single_page() ) {
        $classes = array(
            'tolstenko-service-block',
            'tolstenko-service-block--' . sanitize_html_class( $slug ),
        );
        return '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">' . $html . '</div>';
    }
    return $html;
}

add_filter( 'block_categories_all', 'tolstenko_block_category', 10, 2 );
// До WP 5.8 фильтр назывался block_categories
add_filter( 'block_categories', 'tolstenko_block_category', 10, 2 );

function tolstenko_block_category( $categories, $editor_context = null ) {
    $tolstenko = array(
        'slug'  => 'tolstenko-blocks',
        'title' => __( 'Блоки темы', 'tolstenko-theme' ),
        'icon'  => 'layout',
    );
    $tolstenko_new = array(
        'slug'  => 'tolstenko-blocks-new',
        'title' => __( 'Новые блоки темы', 'tolstenko-theme' ),
        'icon'  => 'screenoptions',
    );
    $tolstenko_partner = array(
        'slug'  => 'tolstenko-blocks-partner',
        'title' => __( 'Партнёры блоки', 'tolstenko-theme' ),
        'icon'  => 'groups',
    );
    $tolstenko_press = array(
        'slug'  => 'tolstenko-blocks-press',
        'title' => __( 'Пресс-портрет', 'tolstenko-theme' ),
        'icon'  => 'id-alt',
    );
    $tolstenko_contacts = array(
        'slug'  => 'tolstenko-blocks-contacts',
        'title' => __( 'Блоки контактов', 'tolstenko-theme' ),
        'icon'  => 'phone',
    );
    $post_type = null;
    if ( $editor_context && isset( $editor_context->post->post_type ) ) {
        $post_type = $editor_context->post->post_type;
    }
    if ( $post_type === 'service' || $post_type === 'vacancy' ) {
        return array_merge( array( $tolstenko, $tolstenko_new, $tolstenko_partner, $tolstenko_press, $tolstenko_contacts ), $categories );
    }
    $categories[] = $tolstenko;
    $categories[] = $tolstenko_new;
    $categories[] = $tolstenko_partner;
    $categories[] = $tolstenko_press;
    $categories[] = $tolstenko_contacts;
    return $categories;
}
