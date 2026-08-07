<?php

// Тема Tolstenko: базовая настройка и подключение статики из tolstenko-v

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme setup
 */
function tolstenko_theme_setup() {
    // Управление <title> через WP/Yoast
    add_theme_support( 'title-tag' );

    // Миниатюры записей (обязательно для featured image у CPT, в т.ч. «Услуга»).
    add_theme_support( 'post-thumbnails' );

    // Минимальные HTML5-шаблоны
    add_theme_support(
        'html5',
        array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        )
    );

    // Регистрация меню (топ, основное, футер, при необходимости мобильное)
    register_nav_menus(
        array(
            'header_top'    => __( 'Header Top Menu', 'tolstenko-theme' ),
            'header_main'   => __( 'Header Main Menu', 'tolstenko-theme' ),
            'footer_menu_1' => __( 'Футер — Услуги', 'tolstenko-theme' ),
            'footer_menu_2' => __( 'Футер — О нас', 'tolstenko-theme' ),
            'mobile_main'   => __( 'Mobile Main Menu', 'tolstenko-theme' ),
            'mobile_services'   => __( 'Mobile Services Menu', 'tolstenko-theme' ),
            'header_services'   => __( 'Услуги — мегаменю в шапке', 'tolstenko-theme' ),
        )
    );
}
add_action( 'after_setup_theme', 'tolstenko_theme_setup' );

/**
 * Фавикон темы (favicon.ico в корне темы).
 */
function tolstenko_output_favicon() {
	$href = get_template_directory_uri() . '/favicon.ico';
	printf(
		'<link rel="icon" href="%1$s" type="image/x-icon">' . "\n" .
		'<link rel="shortcut icon" href="%1$s" type="image/x-icon">' . "\n",
		esc_url( $href )
	);
}
add_action( 'wp_head', 'tolstenko_output_favicon', 1 );

/**
 * Разрешаем загрузку SVG в медиатеку.
 */
function tolstenko_allow_svg_upload( $mimes ) {
    $mimes['svg']  = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'tolstenko_allow_svg_upload' );

/**
 * Корректно определяем mime/type для SVG в момент проверки файла.
 */
function tolstenko_fix_svg_filetype( $data, $file, $filename, $mimes ) {
    $ext = isset( $data['ext'] ) ? strtolower( (string) $data['ext'] ) : '';
    if ( $ext === 'svg' || $ext === 'svgz' ) {
        $data['ext']  = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'tolstenko_fix_svg_filetype', 10, 4 );

/**
 * Подключение стилей и скриптов из папки темы
 *
 * Стили темы — assets/css/style.min.css (после CSS библиотек).
 */

function tolstenko_theme_scripts() {
    $theme_uri  = get_template_directory_uri();
    $theme_dir  = get_template_directory();
    $style_path = $theme_dir . '/assets/css/style.min.css';
    $style_ver  = file_exists( $style_path ) ? (string) filemtime( $style_path ) : null;

    // Библиотеки
    wp_enqueue_style(
        'tolstenko-fancybox',
        $theme_uri . '/assets/libs/fancybox/fancybox.css',
        array(),
        null
    );

    wp_enqueue_style(
        'tolstenko-swiper',
        $theme_uri . '/assets/libs/swiper/swiper-bundle.min.css',
        array(),
        null
    );

    // Стили темы — после библиотек
    wp_enqueue_style(
        'tolstenko-style',
        $theme_uri . '/assets/css/style.min.css',
        array( 'tolstenko-fancybox', 'tolstenko-swiper' ),
        $style_ver
    );

    $swiper_fix_path = $theme_dir . '/assets/css/tolstenko-swiper-splide.css';
    wp_enqueue_style(
        'tolstenko-swiper-splide',
        $theme_uri . '/assets/css/tolstenko-swiper-splide.css',
        array( 'tolstenko-style', 'tolstenko-swiper' ),
        file_exists( $swiper_fix_path ) ? (string) filemtime( $swiper_fix_path ) : null
    );

    // JS библиотеки
    wp_enqueue_script(
        'tolstenko-fancybox',
        $theme_uri . '/assets/libs/fancybox/fancybox.umd.js',
        array(),
        null,
        true
    );

    wp_enqueue_script(
        'tolstenko-swiper',
        $theme_uri . '/assets/libs/swiper/swiper-bundle.min.js',
        array(),
        null,
        true
    );

    $service_swiper_js = $theme_dir . '/assets/js/service-section-swiper.js';
    wp_enqueue_script(
        'tolstenko-service-section-swiper',
        $theme_uri . '/assets/js/service-section-swiper.js',
        array( 'tolstenko-swiper' ),
        file_exists( $service_swiper_js ) ? (string) filemtime( $service_swiper_js ) : null,
        true
    );

    // Основной скрипт из статики
    wp_enqueue_script(
        'tolstenko-main',
        $theme_uri . '/assets/js/script.js',
        array( 'tolstenko-fancybox', 'tolstenko-swiper', 'tolstenko-service-section-swiper' ),
        file_exists( $theme_dir . '/assets/js/script.js' ) ? (string) filemtime( $theme_dir . '/assets/js/script.js' ) : null,
        true
    );

    wp_localize_script(
        'tolstenko-main',
        'tolstenkoFilter',
        array(
            // Относительный URL — без http/https mismatch на локалке.
            'restUrl' => esc_url_raw( wp_make_link_relative( rest_url( 'tolstenko/v1/filter-posts' ) ) ),
            'nonce'   => wp_create_nonce( 'wp_rest' ),
        )
    );

    wp_localize_script(
        'tolstenko-main',
        'tolstenkoAjax',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        )
    );

    // Маска телефона для полей type="tel" и полей CF7 (без CDN)
    wp_enqueue_script(
        'tolstenko-phone-mask',
        $theme_uri . '/assets/js/phone-mask.js',
        array( 'tolstenko-main' ),
        null,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'tolstenko_theme_scripts' );

/**
 * Разрешённые HTML-теги для контента блока «Полный блок статьи» (article-full).
 * Расширяет стандартный набор post: section, article, figure, figcaption, iframe (для встраивания) и т.д.
 *
 * @return array Массив тегов и атрибутов для wp_kses().
 */
function tolstenko_kses_article_full_html() {
	$allowed = wp_kses_allowed_html( 'post' );
	// Дополнительные теги для статей
	$allowed['section']  = array( 'class' => true, 'id' => true );
	$allowed['article']  = array( 'class' => true, 'id' => true );
	$allowed['figure']   = array( 'class' => true, 'id' => true );
	$allowed['figcaption'] = array( 'class' => true, 'id' => true );
	$allowed['video']    = array( 'class' => true, 'id' => true, 'src' => true, 'controls' => true, 'width' => true, 'height' => true );
	$allowed['source']   = array( 'src' => true, 'type' => true );
	$allowed['iframe']   = array(
		'src'             => true,
		'width'           => true,
		'height'          => true,
		'frameborder'     => true,
		'allow'           => true,
		'allowfullscreen' => true,
		'class'           => true,
		'id'              => true,
	);
	if ( isset( $allowed['div'] ) && is_array( $allowed['div'] ) ) {
		$allowed['div']['data-*'] = true;
	}
	if ( isset( $allowed['span'] ) && is_array( $allowed['span'] ) ) {
		$allowed['span']['data-*'] = true;
	}
	return $allowed;
}

/**
 * Безопасный HTML для заголовков и текстов блоков (span.class, br, strong и т.п.).
 *
 * @param mixed $html Raw HTML.
 * @return string
 */
function tolstenko_kses_html( $html ) {
	$html = (string) $html;
	if ( $html === '' ) {
		return '';
	}

	$allowed = wp_kses_allowed_html( 'post' );
	$allowed['span'] = array(
		'class' => true,
		'style' => true,
		'id'    => true,
	);
	$allowed['br'] = array(
		'class' => true,
	);
	$allowed['mark'] = array(
		'class' => true,
		'style' => true,
	);
	if ( isset( $allowed['strong'] ) && is_array( $allowed['strong'] ) ) {
		$allowed['strong']['class'] = true;
	}
	if ( isset( $allowed['em'] ) && is_array( $allowed['em'] ) ) {
		$allowed['em']['class'] = true;
	}
	if ( isset( $allowed['b'] ) && is_array( $allowed['b'] ) ) {
		$allowed['b']['class'] = true;
	}
	if ( isset( $allowed['i'] ) && is_array( $allowed['i'] ) ) {
		$allowed['i']['class'] = true;
	}

	return wp_kses( $html, $allowed );
}

/**
 * HTML редактора (.redactor): гарантируем <p>, если редактор их не положил
 * (Gutenberg RichText с tagName=div даёт текст + <strong>/<ul>, без абзацев).
 *
 * @param string $html Сырой HTML.
 * @return string
 */
function tolstenko_kses_redactor( $html ) {
	$html = trim( (string) $html );
	if ( $html === '' ) {
		return '';
	}

	if ( ! preg_match( '/<p(\s|>)/i', $html ) ) {
		$html = preg_replace( '/<br\s*\/?>/i', "\n\n", $html );
		$html = wpautop( $html );
	}

	$html = tolstenko_kses_html( $html );
	$html = preg_replace( '/<p>(?:\s|&nbsp;|&#0*160;|<br\s*\/?>)*<\/p>/iu', '', $html );

	return trim( (string) $html );
}

/**
 * Пустая ссылка → модалка заявки.
 *
 * @param string $url URL.
 * @return string
 */
function tolstenko_url_or_modal( $url ) {
	$url = trim( (string) $url );
	return $url === '' ? '#modal' : $url;
}

/**
 * Normalize heading tag name from block attrs.
 *
 * @param string $tag
 * @param string $default
 * @return string
 */
function tolstenko_normalize_heading_tag( $tag, $default = 'h2' ) {
	$allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
	$tag = strtolower( trim( (string) $tag ) );
	$default = strtolower( trim( (string) $default ) );
	if ( ! in_array( $default, $allowed, true ) ) {
		$default = 'h2';
	}
	return in_array( $tag, $allowed, true ) ? $tag : $default;
}

/**
 * Текст отзыва: один проход автопараграфов и без пустых <p>.
 *
 * @param string $content Сырой текст или HTML из редактора.
 * @return string
 */
function tolstenko_format_review_text_content( $content ) {
	$content = trim( (string) $content );
	if ( $content === '' ) {
		return '';
	}

	if ( preg_match( '/<(p|h[1-6]|ul|ol|blockquote|figure|div)\b/i', $content ) || false !== strpos( $content, '<!-- wp:' ) ) {
		$html = apply_filters( 'the_content', $content );
	} else {
		$html = wpautop( $content );
	}

	$html = preg_replace( '/<p>(?:\s|&nbsp;|&#0*160;|<br\s*\/?>)*<\/p>/iu', '', $html );

	return trim( $html );
}

/**
 * Класс обертки страницы (contacts-page / services-page / service-page и т.п.)
 * Сейчас используем для контактов, дальше расширим под другие шаблоны.
 */
function tolstenko_get_root_page_class() {
    // Главная (index.html)
    if ( is_front_page() ) {
        return 'index-page';
    }

    // Контакты (contacts.html)
    if ( is_page( 'contacts' ) ) {
        return 'contacts-page';
    }

    // Список услуг (services.html)
    if ( is_page( 'services' ) ) {
        return 'services-page';
    }

    // Архив подкатегории услуг (service-category/slug/)
    if ( is_tax( 'service_category' ) ) {
        return 'services-page';
    }

    // Карточка услуги (service.html) — CPT service
    if ( is_singular( 'service' ) ) {
        return 'service-page';
    }

    // О нас (about-us.html)
    if ( is_page( array( 'about-us', 'about' ) ) ) {
        return 'about-us-page';
    }

    // Отзывы (reviews.html)
    if ( is_page( 'reviews' ) ) {
        return 'reviews-page';
    }

    // Архив категории статей
    if ( is_tax( 'blog_cat' ) ) {
        return 'article-page';
    }

    // Статья / акция (hybrid single)
    if ( function_exists( 'tolstenko_is_content_body_singular' ) && tolstenko_is_content_body_singular() ) {
        return 'article-page';
    }

    // Страница с slug article (если нужна отдельная страница «Статья»)
    if ( is_page( 'article' ) ) {
        return 'article-page';
    }

    // Страница спасибо (thanks.html)
    if ( is_page( array( 'thanks', 'ok-thanks' ) ) ) {
        return 'thanks-page';
    }

    // 404 — тот же layout, что и «Спасибо» (контент по центру, футер внизу)
    if ( is_404() ) {
        return 'thanks-page';
    }

    return 'index-page';
}

/**
 * Страница одной услуги (CPT service): обёртки блоков в tolstenko_render_theme_block().
 */
function tolstenko_is_service_single_page() {
    if ( ! empty( $GLOBALS['tolstenko_service_single_render'] ) ) {
        return true;
    }
    return is_singular( 'service' );
}

/**
 * Архив подкатегории услуг (taxonomy service_category): отступ между «Статья» и «Контакты».
 */
function tolstenko_is_service_category_page() {
    if ( ! empty( $GLOBALS['tolstenko_service_category_render'] ) ) {
        return true;
    }
    return is_tax( 'service_category' );
}

/**
 * Добавляем классы к ссылкам меню, чтобы совпасть с версткой
 * (footer-menu-item, header-top-menu-item, header-bottom-menu-item, header-mobile-menu-item)
 */
function tolstenko_menu_link_classes( $atts, $item, $args, $depth = 0 ) {
    $class = isset( $atts['class'] ) ? $atts['class'] . ' ' : '';

    switch ( $args->theme_location ?? '' ) {
        case 'footer_menu_1':
        case 'footer_menu_2':
            $class .= 'footer-menu-item';
            break;

        case 'header_top':
            $class .= 'header-top-menu-item';
            break;

        case 'header_main':
            $class .= 'header-bottom-menu-item';
            break;

        case 'mobile_main':
        case 'mobile_services':
            $class .= 'header-mobile-menu-item';
            break;

        case 'header_services':
            if ( ! empty( $args->tolstenko_services_column_title ) ) {
                $class .= 'header-services-subcategory-title';
            } elseif ( ! empty( $args->tolstenko_services_service_link ) ) {
                $class .= 'header-services-subcategory-link';
            }
            break;

        default:
            break;
    }

    if ( $class ) {
        $atts['class'] = trim( $class );
    }

    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'tolstenko_menu_link_classes', 10, 4 );

/**
 * Walker для меню без ul/li — только ссылки (например header-top-menu).
 */
class Tolstenko_Walker_Flat_Menu extends Walker_Nav_Menu {

	public function start_lvl( &$output, $depth = 0, $args = null ) {
		// Не выводим обёртку списка.
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		// Не выводим.
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		if ( ! $args ) {
			return;
		}
		$atts = array(
			'title'  => ! empty( $item->attr_title ) ? $item->attr_title : '',
			'target' => ! empty( $item->target ) ? $item->target : '',
			'rel'    => ! empty( $item->xfn ) ? $item->xfn : '',
			'href'   => ! empty( $item->url ) ? $item->url : '',
		);
		$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( $value !== '' && $value !== false ) {
				$attributes .= ' ' . $attr . '="' . esc_attr( $value ) . '"';
			}
		}
		$title = apply_filters( 'the_title', $item->title, $item->ID );
		$title = apply_filters( 'nav_menu_item_title', $title, $item, $args, $depth );
		$item_output  = $args->before ?? '';
		$item_output .= '<a' . $attributes . '>';
		$item_output .= ( $args->link_before ?? '' ) . $title . ( $args->link_after ?? '' );
		$item_output .= '</a>';
		$item_output .= $args->after ?? '';
		$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		// Не выводим закрывающий тег — только ссылка.
	}
}

/**
 * Регистрация кастомных типов записей:
 * - service, review, vacancy, actions, city, case, blog
 */
function tolstenko_register_cpts() {
    // Услуги
    register_post_type(
        'service',
        array(
            'labels'            => array(
                'name'          => __( 'Услуги', 'tolstenko-theme' ),
                'singular_name' => __( 'Услуга', 'tolstenko-theme' ),
                'add_new'       => __( 'Добавить услугу', 'tolstenko-theme' ),
                'add_new_item'  => __( 'Добавить услугу', 'tolstenko-theme' ),
                'edit_item'     => __( 'Редактировать услугу', 'tolstenko-theme' ),
                'view_item'     => __( 'Смотреть услугу', 'tolstenko-theme' ),
                'search_items'  => __( 'Искать услуги', 'tolstenko-theme' ),
                'not_found'     => __( 'Услуг не найдено', 'tolstenko-theme' ),
            ),
            'label'             => __( 'Услуги', 'tolstenko-theme' ),
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
            'has_archive'       => false,
            'rewrite'           => array(
                'slug'       => 'service',
                'with_front' => false,
            ),
            'show_in_rest'      => true,
            'menu_position'     => 20,
            'menu_icon'         => 'dashicons-hammer',
        )
    );


    // Отзывы — только данные для блока, без публичных URL /archive/single.
    register_post_type(
        'review',
        array(
            'label'               => __( 'Отзывы', 'tolstenko-theme' ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'supports'            => array( 'title' ),
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'show_in_rest'        => true,
            'menu_position'       => 22,
            'menu_icon'           => 'dashicons-format-status',
        )
    );

    // Вакансии (фильтр по категориям vacancy_cat)
    register_post_type(
        'vacancy',
        array(
            'labels' => array(
                'name'               => __( 'Вакансии', 'tolstenko-theme' ),
                'singular_name'      => __( 'Вакансия', 'tolstenko-theme' ),
                'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
                'add_new_item'       => __( 'Добавить вакансию', 'tolstenko-theme' ),
                'edit_item'          => __( 'Редактировать вакансию', 'tolstenko-theme' ),
                'new_item'           => __( 'Новая вакансия', 'tolstenko-theme' ),
                'view_item'          => __( 'Смотреть вакансию', 'tolstenko-theme' ),
                'search_items'       => __( 'Искать вакансии', 'tolstenko-theme' ),
                'not_found'          => __( 'Вакансий не найдено', 'tolstenko-theme' ),
                'not_found_in_trash' => __( 'В корзине вакансий нет', 'tolstenko-theme' ),
                'all_items'          => __( 'Все вакансии', 'tolstenko-theme' ),
            ),
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'menu_position'     => 24,
            'menu_icon'         => 'dashicons-id-alt',
            'supports'          => array( 'title', 'editor' ),
            'has_archive'       => false,
            'rewrite'           => array(
                'slug'       => 'vacancies',
                'with_front' => false,
            ),
            'show_in_rest'      => true,
            'template'          => array(
                array( 'tolstenko/hero-vacancy' ),
                array( 'tolstenko/vacancy-content' ),
                array( 'tolstenko/consultation-free' ),
                array( 'tolstenko/same-vacancy' ),
            ),
            'template_lock'     => false,
        )
    );

    // Акции (без таксономии)
    register_post_type(
        'actions',
        array(
            'labels' => array(
                'name'               => __( 'Акции', 'tolstenko-theme' ),
                'singular_name'      => __( 'Акция', 'tolstenko-theme' ),
                'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
                'add_new_item'       => __( 'Добавить акцию', 'tolstenko-theme' ),
                'edit_item'          => __( 'Редактировать акцию', 'tolstenko-theme' ),
                'new_item'           => __( 'Новая акция', 'tolstenko-theme' ),
                'view_item'          => __( 'Смотреть акцию', 'tolstenko-theme' ),
                'search_items'       => __( 'Искать акции', 'tolstenko-theme' ),
                'not_found'          => __( 'Акций не найдено', 'tolstenko-theme' ),
                'not_found_in_trash' => __( 'В корзине акций нет', 'tolstenko-theme' ),
                'all_items'          => __( 'Все акции', 'tolstenko-theme' ),
            ),
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'menu_position'     => 25,
            'menu_icon'         => 'dashicons-megaphone',
            // Как у статьи: редактор + комментарии; без таксономий; уникальные meta action_*.
            'supports'          => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields' ),
            'has_archive'       => false,
            'rewrite'           => array(
                'slug'       => 'actions',
                'with_front' => false,
            ),
            'show_in_rest'      => true,
        )
    );

    // Города (без архива и таксономии).
    register_post_type(
        'city',
        array(
            'labels' => array(
                'name'               => __( 'Города', 'tolstenko-theme' ),
                'singular_name'      => __( 'Город', 'tolstenko-theme' ),
                'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
                'add_new_item'       => __( 'Добавить город', 'tolstenko-theme' ),
                'edit_item'          => __( 'Редактировать город', 'tolstenko-theme' ),
                'new_item'           => __( 'Новый город', 'tolstenko-theme' ),
                'view_item'          => __( 'Смотреть город', 'tolstenko-theme' ),
                'search_items'       => __( 'Искать города', 'tolstenko-theme' ),
                'not_found'          => __( 'Городов не найдено', 'tolstenko-theme' ),
                'not_found_in_trash' => __( 'В корзине городов нет', 'tolstenko-theme' ),
                'all_items'          => __( 'Все города', 'tolstenko-theme' ),
                'menu_name'          => __( 'Города', 'tolstenko-theme' ),
            ),
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_nav_menus' => true,
            'menu_position'     => 26,
            'menu_icon'         => 'dashicons-location-alt',
            'supports'          => array( 'title', 'editor', 'thumbnail' ),
            'has_archive'       => false,
            'rewrite'           => array(
                'slug'       => 'city',
                'with_front' => false,
            ),
            'show_in_rest'      => true,
        )
    );

	// Кейсы — данные для блока (как отзывы), без публичных URL /single.
	register_post_type(
		'case',
		array(
			'labels' => array(
				'name'               => __( 'Кейсы', 'tolstenko-theme' ),
				'singular_name'      => __( 'Кейс', 'tolstenko-theme' ),
				'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
				'add_new_item'       => __( 'Добавить кейс', 'tolstenko-theme' ),
				'edit_item'          => __( 'Редактировать кейс', 'tolstenko-theme' ),
				'new_item'           => __( 'Новый кейс', 'tolstenko-theme' ),
				'view_item'          => __( 'Смотреть кейс', 'tolstenko-theme' ),
				'search_items'       => __( 'Искать кейсы', 'tolstenko-theme' ),
				'not_found'          => __( 'Кейсов не найдено', 'tolstenko-theme' ),
				'not_found_in_trash' => __( 'В корзине кейсов нет', 'tolstenko-theme' ),
				'all_items'          => __( 'Все кейсы', 'tolstenko-theme' ),
				'menu_name'          => __( 'Кейсы', 'tolstenko-theme' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_in_nav_menus'   => false,
			'menu_position'       => 27,
			'menu_icon'           => 'dashicons-awards',
			'supports'            => array( 'title', 'thumbnail' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'show_in_rest'        => true,
		)
	);

	// Статьи (URL через таксономию blog_cat: /blog/{cat}/{slug}/, без категории — /blog/{slug}/).
	register_post_type(
		'blog',
		array(
			'labels' => array(
				'name'               => __( 'Статьи', 'tolstenko-theme' ),
				'singular_name'      => __( 'Статья', 'tolstenko-theme' ),
				'add_new'            => __( 'Добавить', 'tolstenko-theme' ),
				'add_new_item'       => __( 'Добавить статью', 'tolstenko-theme' ),
				'edit_item'          => __( 'Редактировать статью', 'tolstenko-theme' ),
				'new_item'           => __( 'Новая статья', 'tolstenko-theme' ),
				'view_item'          => __( 'Смотреть статью', 'tolstenko-theme' ),
				'search_items'       => __( 'Искать статьи', 'tolstenko-theme' ),
				'not_found'          => __( 'Статей не найдено', 'tolstenko-theme' ),
				'not_found_in_trash' => __( 'В корзине статей нет', 'tolstenko-theme' ),
				'all_items'          => __( 'Все статьи', 'tolstenko-theme' ),
				'menu_name'          => __( 'Статьи', 'tolstenko-theme' ),
			),
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_nav_menus' => true,
			'menu_position'     => 28,
			'menu_icon'         => 'dashicons-welcome-write-blog',
			'supports'          => array( 'title', 'editor', 'thumbnail', 'comments', 'custom-fields' ),
			'has_archive'       => false,
			// rewrite нужен, иначе в редакторе пропадает смена URL (slug).
			// Красивые URL с категорией собирает post_type_link + правило ниже.
			'rewrite'           => array(
				'slug'       => 'blog',
				'with_front' => false,
			),
			'show_in_rest'      => true,
			'taxonomies'        => array( 'blog_cat' ),
		)
	);
}
add_action( 'init', 'tolstenko_register_cpts' );

/**
 * Сбросить permalinks после изменения rewrite CPT (actions / vacancy / city / case / blog).
 */
function tolstenko_maybe_flush_actions_rewrite() {
	if ( get_option( 'tolstenko_actions_rewrite_flushed' ) === '10' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'tolstenko_actions_rewrite_flushed', '10', false );
}
add_action( 'init', 'tolstenko_maybe_flush_actions_rewrite', 99 );

/**
 * Сброс rewrite после отключения публичных URL CPT review.
 */
function tolstenko_maybe_flush_review_rewrite() {
	if ( get_option( 'tolstenko_review_rewrite_flushed' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'tolstenko_review_rewrite_flushed', '1', false );
}
add_action( 'init', 'tolstenko_maybe_flush_review_rewrite', 99 );

/**
 * Сброс rewrite после отключения публичных URL CPT case.
 */
function tolstenko_maybe_flush_case_rewrite() {
	if ( get_option( 'tolstenko_case_rewrite_flushed' ) === '1' ) {
		return;
	}
	flush_rewrite_rules( false );
	update_option( 'tolstenko_case_rewrite_flushed', '1', false );
}
add_action( 'init', 'tolstenko_maybe_flush_case_rewrite', 99 );

/**
 * Мета-поля акции для плитки (в правой панели Gutenberg).
 */
function tolstenko_register_actions_post_meta() {
	$common = array(
		'show_in_rest'      => true,
		'single'            => true,
		'type'              => 'string',
		'default'           => '',
		'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
			return current_user_can( 'edit_post', (int) $post_id );
		},
	);

	register_post_meta(
		'actions',
		'action_description',
		array_merge(
			$common,
			array( 'sanitize_callback' => 'sanitize_textarea_field' )
		)
	);
	register_post_meta(
		'actions',
		'action_same_cost',
		array_merge(
			$common,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		)
	);
	register_post_meta(
		'actions',
		'action_cost',
		array_merge(
			$common,
			array( 'sanitize_callback' => 'sanitize_text_field' )
		)
	);
}
add_action( 'init', 'tolstenko_register_actions_post_meta' );

/**
 * Мета-поля кейса для карточки в секции «Кейсы».
 */
function tolstenko_register_case_post_meta() {
	$auth = static function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', (int) $post_id );
	};

	$common_string = array(
		'show_in_rest'      => true,
		'single'            => true,
		'type'              => 'string',
		'default'           => '',
		'auth_callback'     => $auth,
		'sanitize_callback' => 'sanitize_text_field',
	);

	register_post_meta( 'case', 'case_title', $common_string );
	register_post_meta(
		'case',
		'case_text',
		array_merge(
			$common_string,
			array( 'sanitize_callback' => 'sanitize_textarea_field' )
		)
	);
	register_post_meta(
		'case',
		'case_link',
		array_merge(
			$common_string,
			array( 'sanitize_callback' => 'esc_url_raw' )
		)
	);
	register_post_meta(
		'case',
		'case_service',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'integer',
			'default'           => 0,
			'auth_callback'     => $auth,
			'sanitize_callback' => 'absint',
		)
	);
	register_post_meta(
		'case',
		'case_items',
		array(
			'show_in_rest'  => array(
				'schema' => array(
					'type'  => 'array',
					'items' => array(
						'type'       => 'object',
						'properties' => array(
							'value' => array( 'type' => 'string' ),
							'text'  => array( 'type' => 'string' ),
						),
					),
				),
			),
			'single'        => true,
			'type'          => 'array',
			'default'       => array(),
			'auth_callback' => $auth,
			'sanitize_callback' => static function ( $value ) {
				$out = array();
				if ( ! is_array( $value ) ) {
					return $out;
				}
				foreach ( $value as $row ) {
					if ( ! is_array( $row ) ) {
						continue;
					}
					$v = sanitize_text_field( (string) ( $row['value'] ?? '' ) );
					$t = sanitize_text_field( (string) ( $row['text'] ?? '' ) );
					if ( $v === '' && $t === '' ) {
						continue;
					}
					$out[] = array(
						'value' => $v,
						'text'  => $t,
					);
				}
				return $out;
			},
		)
	);
}
add_action( 'init', 'tolstenko_register_case_post_meta' );

/**
 * Данные карточки кейса.
 *
 * @param int $post_id Post ID.
 * @return array{title:string,text:string,link:string,items:array<int,array{value:string,text:string}>,service_id:int,service_url:string,image_id:int,image_url:string,image_alt:string}
 */
function tolstenko_get_case_card_data( $post_id ) {
	$post_id = (int) $post_id;
	$title   = (string) get_post_meta( $post_id, 'case_title', true );
	if ( $title === '' ) {
		$title = (string) get_the_title( $post_id );
	}
	$text       = (string) get_post_meta( $post_id, 'case_text', true );
	$link       = (string) get_post_meta( $post_id, 'case_link', true );
	$service_id = (int) get_post_meta( $post_id, 'case_service', true );
	$items_raw  = get_post_meta( $post_id, 'case_items', true );
	$items      = array();
	if ( is_array( $items_raw ) ) {
		foreach ( $items_raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$v = (string) ( $row['value'] ?? '' );
			$t = (string) ( $row['text'] ?? '' );
			if ( $v === '' && $t === '' ) {
				continue;
			}
			$items[] = array(
				'value' => $v,
				'text'  => $t,
			);
		}
	}

	$image_id  = (int) get_post_thumbnail_id( $post_id );
	$image_url = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$image_alt = $image_id ? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';
	if ( $image_alt === '' ) {
		$image_alt = $title;
	}

	$service_url = '';
	if ( $service_id > 0 && get_post_status( $service_id ) ) {
		$service_url = (string) get_permalink( $service_id );
	}

	return array(
		'title'       => $title,
		'text'        => $text,
		'link'        => $link,
		'items'       => $items,
		'service_id'  => $service_id,
		'service_url' => $service_url,
		'image_id'    => $image_id,
		'image_url'   => $image_url,
		'image_alt'   => $image_alt,
	);
}

/**
 * Мета-поля услуги для карточки в секции «Слайдер услуг».
 */
function tolstenko_register_service_post_meta() {
	$auth = static function ( $allowed, $meta_key, $post_id ) {
		return current_user_can( 'edit_post', (int) $post_id );
	};

	$common_string = array(
		'show_in_rest'      => true,
		'single'            => true,
		'type'              => 'string',
		'default'           => '',
		'auth_callback'     => $auth,
		'sanitize_callback' => 'sanitize_text_field',
	);

	register_post_meta(
		'service',
		'service_description',
		array_merge(
			$common_string,
			array( 'sanitize_callback' => 'sanitize_textarea_field' )
		)
	);
	register_post_meta( 'service', 'service_price_from', $common_string );
	register_post_meta( 'service', 'service_price_old', $common_string );
	register_post_meta( 'service', 'service_discount', $common_string );
	register_post_meta(
		'service',
		'service_is_hit',
		array(
			'show_in_rest'      => true,
			'single'            => true,
			'type'              => 'boolean',
			'default'           => false,
			'auth_callback'     => $auth,
			'sanitize_callback' => static function ( $value ) {
				return (bool) $value;
			},
		)
	);
}
add_action( 'init', 'tolstenko_register_service_post_meta' );

/**
 * Данные карточки услуги.
 *
 * @param int $post_id Post ID.
 * @return array{title:string,description:string,price_from:string,price_old:string,is_hit:bool,discount:string,tag_name:string,image_id:int,image_url:string,image_alt:string}
 */
function tolstenko_get_service_card_data( $post_id ) {
	$post_id = (int) $post_id;
	$title   = (string) get_the_title( $post_id );

	$description = (string) get_post_meta( $post_id, 'service_description', true );
	$price_from  = (string) get_post_meta( $post_id, 'service_price_from', true );
	$price_old   = (string) get_post_meta( $post_id, 'service_price_old', true );
	$discount    = (string) get_post_meta( $post_id, 'service_discount', true );
	$is_hit      = (bool) get_post_meta( $post_id, 'service_is_hit', true );

	// Legacy ACF / tolstenko field names.
	if ( $description === '' && function_exists( 'get_field' ) ) {
		$legacy = get_field( 'single_service_description', $post_id );
		if ( is_string( $legacy ) && $legacy !== '' ) {
			$description = $legacy;
		}
	}
	if ( $price_from === '' && function_exists( 'get_field' ) ) {
		$legacy = get_field( 'single_service_same_cost', $post_id );
		if ( is_string( $legacy ) && $legacy !== '' ) {
			$price_from = $legacy;
		}
	}
	if ( $price_old === '' && function_exists( 'get_field' ) ) {
		$legacy = get_field( 'single_service_cost', $post_id );
		if ( is_string( $legacy ) && $legacy !== '' ) {
			$price_old = $legacy;
		}
	}
	if ( ! $is_hit && function_exists( 'get_field' ) ) {
		$is_hit = (bool) get_field( 'single_service_hit', $post_id );
	}
	if ( $discount === '' && function_exists( 'get_field' ) ) {
		$legacy = get_field( 'single_service_action', $post_id );
		if ( is_string( $legacy ) && $legacy !== '' ) {
			$discount = $legacy;
		}
	}

	$selected_category = (string) get_query_var( 'tolstenko_service_card_selected_category', '' );
	$tag_name          = '';
	$terms             = get_the_terms( $post_id, 'service_category' );
	if ( ! is_wp_error( $terms ) && is_array( $terms ) && ! empty( $terms ) ) {
		$ordered = $terms;
		if ( $selected_category !== '' ) {
			$selected_term = null;
			$other_terms   = array();
			foreach ( $terms as $term ) {
				if ( $term instanceof WP_Term && $term->slug === $selected_category ) {
					$selected_term = $term;
				} elseif ( $term instanceof WP_Term ) {
					$other_terms[] = $term;
				}
			}
			if ( $selected_term instanceof WP_Term ) {
				$ordered = array_merge( array( $selected_term ), $other_terms );
			}
		}
		if ( ! empty( $ordered[0] ) && $ordered[0] instanceof WP_Term ) {
			$tag_name = (string) $ordered[0]->name;
		}
	}

	$image_id  = (int) get_post_thumbnail_id( $post_id );
	$image_url = $image_id ? (string) wp_get_attachment_image_url( $image_id, 'large' ) : '';
	$image_alt = $image_id ? (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : '';
	if ( $image_alt === '' ) {
		$image_alt = $title;
	}

	return array(
		'title'       => $title,
		'description' => $description,
		'price_from'  => $price_from,
		'price_old'   => $price_old,
		'is_hit'      => $is_hit,
		'discount'    => $discount,
		'tag_name'    => $tag_name,
		'image_id'    => $image_id,
		'image_url'   => $image_url,
		'image_alt'   => $image_alt,
	);
}

/**
 * Значение мета акции (с fallback на ACF get_field, если есть).
 *
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @return string
 */
function tolstenko_get_action_field( $post_id, $key ) {
	$post_id = (int) $post_id;
	$key     = (string) $key;
	if ( function_exists( 'get_field' ) ) {
		$acf = get_field( $key, $post_id );
		if ( $acf !== null && $acf !== false && $acf !== '' ) {
			return is_scalar( $acf ) ? (string) $acf : '';
		}
	}
	return (string) get_post_meta( $post_id, $key, true );
}

/**
 * WP при первом заходе на «Меню» прячет все метабоксы, кроме Страниц / Произвольных ссылок / Рубрик.
 * CPT «Услуги» и таксономия «Категории услуг» из‑за этого не видны, пока их не включить в «Настройки экрана».
 */
function tolstenko_nav_menus_unhide_service_boxes( $hidden, $screen ) {
	if ( ! $screen || $screen->id !== 'nav-menus' || ! is_array( $hidden ) ) {
		return $hidden;
	}

	$keep_visible = array( 'add-post-type-service', 'add-service_category' );
	return array_values( array_diff( $hidden, $keep_visible ) );
}
add_filter( 'hidden_meta_boxes', 'tolstenko_nav_menus_unhide_service_boxes', 10, 2 );

/**
 * На экране «Меню» блок выбора записей «Услуги» вставляется сразу под «Категории услуг» (таксономия).
 */
function tolstenko_nav_menus_move_service_box_under_service_categories() {
	$pto = get_post_type_object( 'service' );
	if ( ! $pto instanceof WP_Post_Type || ! $pto->show_in_nav_menus ) {
		return;
	}
	?>
	<script>
	jQuery( function( $ ) {
		var serviceBox = $( '#add-post-type-service' );
		var categoryBox = $( '#add-service_category' );
		if ( serviceBox.length && categoryBox.length ) {
			serviceBox.insertAfter( categoryBox );
		}
	} );
	</script>
	<?php
}
add_action( 'admin_footer-nav-menus.php', 'tolstenko_nav_menus_move_service_box_under_service_categories' );

/**
 * Если существует обычная страница /services/, используем её как разводящую,
 * даже если по старым rewrite-правилам срабатывает архив CPT service.
 */
function tolstenko_redirect_service_archive_to_services_page() {
    if ( ! is_post_type_archive( 'service' ) || is_admin() ) {
        return;
    }

    $services_page = get_page_by_path( 'services' );
    if ( ! ( $services_page instanceof WP_Post ) ) {
        return;
    }

    $target = get_permalink( $services_page );
    if ( empty( $target ) ) {
        return;
    }

    wp_safe_redirect( $target, 301 );
    exit;
}
add_action( 'template_redirect', 'tolstenko_redirect_service_archive_to_services_page' );

/**
 * Если существует страница /vacancies/, отдаём её вместо архива CPT vacancy.
 */
function tolstenko_redirect_vacancy_archive_to_vacancies_page() {
	if ( ! is_post_type_archive( 'vacancy' ) || is_admin() ) {
		return;
	}

	$vacancies_page = get_page_by_path( 'vacancies' );
	if ( ! ( $vacancies_page instanceof WP_Post ) ) {
		return;
	}

	$target = get_permalink( $vacancies_page );
	if ( empty( $target ) ) {
		return;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'tolstenko_redirect_vacancy_archive_to_vacancies_page' );

/**
 * Канонический URL услуги: /services/{cat}/{slug}/ или /services/{slug}/.
 * Сырой CPT /service/{slug}/ и URL без рубрики редиректим на правильный permalink.
 */
function tolstenko_redirect_service_to_canonical_url() {
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	if ( ! is_singular( 'service' ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) || $post->post_name === '' ) {
		return;
	}

	$canonical = get_permalink( $post );
	if ( ! is_string( $canonical ) || $canonical === '' ) {
		return;
	}

	$canonical_path = wp_parse_url( $canonical, PHP_URL_PATH );
	$request_uri    = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$request_path   = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $canonical_path ) || ! is_string( $request_path ) ) {
		return;
	}

	$canonical_path = untrailingslashit( $canonical_path );
	$request_path   = untrailingslashit( $request_path );

	if ( $canonical_path === '' || $request_path === '' || strcasecmp( $canonical_path, $request_path ) === 0 ) {
		return;
	}

	$target = $canonical;
	$query  = wp_parse_url( $request_uri, PHP_URL_QUERY );
	if ( is_string( $query ) && $query !== '' ) {
		$target .= ( strpos( $target, '?' ) === false ? '?' : '&' ) . $query;
	}

	wp_safe_redirect( $target, 301 );
	exit;
}
add_action( 'template_redirect', 'tolstenko_redirect_service_to_canonical_url', 5 );

/**
 * /services/{slug}/ попадает в rewrite таксономии (service_category), хотя для услуги без категории
 * нужна одиночная запись service. Если термина с таким slug нет — подменяем query на CPT.
 *
 * @param array<string, string> $query_vars Query vars.
 * @return array<string, string>
 */
function tolstenko_request_single_service_under_services_prefix( $query_vars ) {
	if ( empty( $query_vars['service_category'] ) || is_array( $query_vars['service_category'] ) ) {
		return $query_vars;
	}
	if (
		! empty( $query_vars['name'] )
		&& ! empty( $query_vars['post_type'] )
		&& $query_vars['post_type'] === 'service'
	) {
		return $query_vars;
	}
	$slug = $query_vars['service_category'];
	if ( $slug === '' || strpos( $slug, '/' ) !== false ) {
		return $query_vars;
	}
	$term = get_term_by( 'slug', $slug, 'service_category' );
	if ( $term && ! is_wp_error( $term ) ) {
		return $query_vars;
	}
	$ids = get_posts(
		array(
			'post_type'              => 'service',
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
	unset( $query_vars['service_category'] );
	$query_vars['post_type'] = 'service';
	$query_vars['name']     = $slug;
	return $query_vars;
}
add_filter( 'request', 'tolstenko_request_single_service_under_services_prefix', 5 );

/**
 * /blog/{slug}/ попадает в rewrite таксономии (blog_cat). Если термина нет — одиночная статья.
 *
 * @param array<string, string> $query_vars Query vars.
 * @return array<string, string>
 */
function tolstenko_request_single_blog_under_blog_prefix( $query_vars ) {
	if ( empty( $query_vars['blog_cat'] ) || is_array( $query_vars['blog_cat'] ) ) {
		return $query_vars;
	}
	if (
		! empty( $query_vars['name'] )
		&& ! empty( $query_vars['post_type'] )
		&& $query_vars['post_type'] === 'blog'
	) {
		return $query_vars;
	}
	$slug = $query_vars['blog_cat'];
	if ( $slug === '' || strpos( $slug, '/' ) !== false ) {
		return $query_vars;
	}
	$term = get_term_by( 'slug', $slug, 'blog_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		return $query_vars;
	}
	$ids = get_posts(
		array(
			'post_type'              => 'blog',
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
	unset( $query_vars['blog_cat'] );
	$query_vars['post_type'] = 'blog';
	$query_vars['name']      = $slug;
	return $query_vars;
}
add_filter( 'request', 'tolstenko_request_single_blog_under_blog_prefix', 5 );

/**
 * Собирает строку атрибутов для тега <a> после фильтра nav_menu_link_attributes.
 *
 * @param array<string, string|false> $atts Атрибуты.
 * @return string
 */
function tolstenko_nav_menu_link_atts_string( $atts ) {
	$html = '';
	foreach ( $atts as $attr => $value ) {
		if ( $value === '' || $value === false ) {
			continue;
		}
		$html .= ' ' . esc_attr( (string) $attr ) . '="' . esc_attr( (string) $value ) . '"';
	}
	return $html;
}

/**
 * Услуги (CPT) без рубрики service_category.
 *
 * @return WP_Post[]
 */
function tolstenko_get_uncategorized_service_posts() {
	$q = new WP_Query(
		array(
			'post_type'              => 'service',
			'post_status'            => 'publish',
			'posts_per_page'         => 200,
			'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'          => true,
			'update_post_term_cache' => true,
			'tax_query'              => array(
				array(
					'taxonomy' => 'service_category',
					'operator' => 'NOT EXISTS',
				),
			),
		)
	);
	$posts = $q->posts;
	wp_reset_postdata();
	return is_array( $posts ) ? $posts : array();
}

/**
 * Список услуг для левой колонки мегаменю по данным вкладки (как в tolstenko_get_header_services_menu_data).
 *
 * @param array{term?: WP_Term|null, uncategorized?: bool} $item Элемент из tolstenko_get_header_services_menu_data().
 * @return WP_Post[]
 */
function tolstenko_get_service_posts_for_mega_menu_tab( array $item ) {
	if ( ! empty( $item['uncategorized'] ) ) {
		return tolstenko_get_uncategorized_service_posts();
	}
	$term = isset( $item['term'] ) ? $item['term'] : null;
	if ( $term instanceof WP_Term ) {
		return get_posts(
			array(
				'post_type'              => 'service',
				'post_status'            => 'publish',
				'posts_per_page'         => 200,
				'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
				'no_found_rows'          => true,
				'update_post_term_cache' => false,
				'tax_query'              => array(
					array(
						'taxonomy'         => 'service_category',
						'field'            => 'term_id',
						'terms'            => $term->term_id,
						'include_children' => true,
					),
				),
			)
		);
	}
	$merged = array();
	if ( empty( $item['subcategories'] ) || ! is_array( $item['subcategories'] ) ) {
		return $merged;
	}
	foreach ( $item['subcategories'] as $sub ) {
		if ( empty( $sub['services'] ) || ! is_array( $sub['services'] ) ) {
			continue;
		}
		foreach ( $sub['services'] as $post ) {
			if ( $post instanceof WP_Post ) {
				$merged[ $post->ID ] = $post;
			}
		}
	}
	return array_values( $merged );
}

/**
 * Услуги для вкладки мегаменю, если корень — термин service_category (меню из админки).
 *
 * @param WP_Term $term Рубрика.
 * @return WP_Post[]
 */
function tolstenko_get_service_posts_for_header_nav_term( WP_Term $term ) {
	return get_posts(
		array(
			'post_type'              => 'service',
			'post_status'            => 'publish',
			'posts_per_page'         => 200,
			'orderby'                => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy'         => 'service_category',
					'field'            => 'term_id',
					'terms'            => $term->term_id,
					'include_children' => true,
				),
			),
		)
	);
}

/**
 * Обёртка панели услуг в marketing-разметке.
 *
 * @param string $tabs_html   Tabs HTML.
 * @param string $panels_html Panels HTML.
 * @return string
 */
function tolstenko_wrap_header_services_panel_html( $tabs_html, $panels_html ) {
	$cta_title = __( 'Помочь с выбором?', 'tolstenko-theme' );
	$cta_text  = __( 'Оставьте заявку — подберём услугу под ваши задачи.', 'tolstenko-theme' );
	$cta_btn   = __( 'Связаться с нами', 'tolstenko-theme' );

	ob_start();
	?>
	<div class="header-services container" id="header-services-panel" hidden>
		<div class="header-services__inner br-30">
			<div class="header-services__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Рубрики услуг', 'tolstenko-theme' ); ?>">
				<?php echo $tabs_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="header-services__content">
				<?php echo $panels_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<div class="header-services__cta">
				<div class="header-services__cta-title line-caps-bold-16-15"><?php echo esc_html( $cta_title ); ?></div>
				<p class="header-services__cta-text paragraph-15-25"><?php echo esc_html( $cta_text ); ?></p>
				<a
					class="header-services__cta-btn default-btn line-caps-bold-13-15"
					href="#modal"
				>
					<span><?php echo esc_html( $cta_btn ); ?></span>
					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<path d="M6 14L14 6M14 6H8M14 6V12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</a>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Колонки мегаменю «Услуги» из назначенного меню (локация header_services).
 * Структура: корень — вкладки слева; уровень 1 — колонка (заголовок-ссылка); уровень 2 — ссылки услуг.
 *
 * @return string Пустая строка, если меню не назначено или без пунктов.
 */
function tolstenko_get_header_services_wp_menu_columns_html() {
	$locations = get_nav_menu_locations();
	if ( empty( $locations['header_services'] ) ) {
		return '';
	}
	$menu_id = (int) $locations['header_services'];
	$all     = wp_get_nav_menu_items( $menu_id );
	if ( empty( $all ) || ! is_array( $all ) ) {
		return '';
	}
	$by_parent = array();
	foreach ( $all as $item ) {
		if ( ! isset( $item->ID ) ) {
			continue;
		}
		$p = (int) $item->menu_item_parent;
		if ( ! isset( $by_parent[ $p ] ) ) {
			$by_parent[ $p ] = array();
		}
		$by_parent[ $p ][] = $item;
	}
	foreach ( $by_parent as &$group ) {
		usort(
			$group,
			static function ( $a, $b ) {
				return (int) $a->menu_order - (int) $b->menu_order;
			}
		);
	}
	unset( $group );
	if ( empty( $by_parent[0] ) ) {
		return '';
	}

	$nav_args_base = (object) array(
		'theme_location' => 'header_services',
	);

	$tabs   = '';
	$panels = '';
	$cat_index = 0;
	foreach ( $by_parent[0] as $root_item ) {
		$rtitle = apply_filters( 'the_title', $root_item->title, $root_item->ID );
		$rtitle = apply_filters( 'nav_menu_item_title', $rtitle, $root_item, $nav_args_base, 0 );
		$rtitle = wptexturize( $rtitle );
		$tabs  .= sprintf(
			'<button class="header-services__tab line-caps-bold-13-15%s" type="button" role="tab" aria-selected="%s" aria-controls="header-services-tabpanel-%d" data-tab-index="%d">%s</button>',
			0 === $cat_index ? ' is-active' : '',
			0 === $cat_index ? 'true' : 'false',
			$cat_index,
			$cat_index,
			esc_html( $rtitle )
		);

		$children = $by_parent[ (int) $root_item->ID ] ?? array();
		$groups_html = '';
		foreach ( $children as $sub_item ) {
			$sub_children = $by_parent[ (int) $sub_item->ID ] ?? array();
			$stitle = apply_filters( 'the_title', $sub_item->title, $sub_item->ID );
			$stitle = apply_filters( 'nav_menu_item_title', $stitle, $sub_item, $nav_args_base, 1 );
			$stitle = wptexturize( $stitle );

			$links = '';
			foreach ( $sub_children as $link_item ) {
				$lhref  = ! empty( $link_item->url ) ? $link_item->url : '#';
				$ltitle = apply_filters( 'the_title', $link_item->title, $link_item->ID );
				$ltitle = apply_filters( 'nav_menu_item_title', $ltitle, $link_item, $nav_args_base, 2 );
				$ltitle = wptexturize( $ltitle );
				$links .= '<li class="header-services__item"><a class="header-services__link line-13-15" href="' . esc_url( $lhref ) . '"><span class="header-services__service-title">' . esc_html( $ltitle ) . '</span></a></li>';
			}

			$groups_html .= '<div class="header-services__group">';
			if ( $stitle !== '' ) {
				$groups_html .= '<div class="header-services__group-title line-caps-bold-13-15">' . esc_html( $stitle ) . '</div>';
			}
			if ( $links !== '' ) {
				$groups_html .= '<ul class="header-services__links">' . $links . '</ul>';
			}
			$groups_html .= '</div>';
		}

		$panels .= sprintf(
			'<div class="header-services__panel%s" id="header-services-tabpanel-%d" role="tabpanel" data-tab-index="%d"%s><div class="header-services__columns"><div class="header-services__column">%s</div></div></div>',
			0 === $cat_index ? ' is-active' : '',
			$cat_index,
			$cat_index,
			0 === $cat_index ? '' : ' hidden',
			$groups_html
		);
		$cat_index++;
	}

	if ( $tabs === '' ) {
		return '';
	}

	return tolstenko_wrap_header_services_panel_html( $tabs, $panels );
}

/**
 * Колонки мегаменю по данным таксономии (как раньше), если в админке не задано меню header_services.
 *
 * @return string
 */
function tolstenko_get_header_services_fallback_columns_html() {
	$header_services = tolstenko_get_header_services_menu_data();
	if ( empty( $header_services ) ) {
		return '';
	}
	$services_page_url = get_post_type_archive_link( 'service' );
	if ( ! $services_page_url ) {
		$services_page     = get_page_by_path( 'services' );
		$services_page_url = $services_page ? get_permalink( $services_page ) : home_url( '/' );
	}

	$tabs   = '';
	$panels = '';
	$cat_index = 0;
	foreach ( $header_services as $item ) {
		$term = $item['term'] ?? null;
		if ( $term instanceof WP_Term ) {
			$label = $term->name;
		} else {
			$label = __( 'Услуги', 'tolstenko-theme' );
		}

		$tabs .= sprintf(
			'<button class="header-services__tab line-caps-bold-13-15%s" type="button" role="tab" aria-selected="%s" aria-controls="header-services-tabpanel-%d" data-tab-index="%d">%s</button>',
			0 === $cat_index ? ' is-active' : '',
			0 === $cat_index ? 'true' : 'false',
			$cat_index,
			$cat_index,
			esc_html( $label )
		);

		$groups_html = '';
		foreach ( ( $item['subcategories'] ?? array() ) as $sub ) {
			$sub_term = $sub['term'] ?? null;
			$services = $sub['services'] ?? array();
			if ( empty( $services ) ) {
				continue;
			}
			$title = $sub_term instanceof WP_Term ? $sub_term->name : __( 'Услуги', 'tolstenko-theme' );
			$links = '';
			foreach ( $services as $post ) {
				if ( ! $post instanceof WP_Post ) {
					continue;
				}
				$links .= '<li class="header-services__item"><a class="header-services__link line-13-15" href="' . esc_url( get_permalink( $post ) ) . '"><span class="header-services__service-title">' . esc_html( get_the_title( $post ) ) . '</span></a></li>';
			}
			$groups_html .= '<div class="header-services__group">';
			if ( $title !== '' ) {
				$groups_html .= '<div class="header-services__group-title line-caps-bold-13-15">' . esc_html( $title ) . '</div>';
			}
			if ( $links !== '' ) {
				$groups_html .= '<ul class="header-services__links">' . $links . '</ul>';
			}
			$groups_html .= '</div>';
		}

		$panels .= sprintf(
			'<div class="header-services__panel%s" id="header-services-tabpanel-%d" role="tabpanel" data-tab-index="%d"%s><div class="header-services__columns"><div class="header-services__column">%s</div></div></div>',
			0 === $cat_index ? ' is-active' : '',
			$cat_index,
			$cat_index,
			0 === $cat_index ? '' : ' hidden',
			$groups_html
		);
		$cat_index++;
	}

	if ( $tabs === '' ) {
		return '';
	}

	return tolstenko_wrap_header_services_panel_html( $tabs, $panels );
}

function tolstenko_get_header_services_columns_html() {
	$wp = tolstenko_get_header_services_wp_menu_columns_html();
	if ( $wp !== '' ) {
		return $wp;
	}
	return tolstenko_get_header_services_fallback_columns_html();
}

/**
 * Данные для выпадающего меню «Услуги» в шапке.
 * Слева — категории (родители). В середине — подкатегории (дочерние термины), под каждой заголовок подкатегории и ссылки на услуги.
 *
 * @return array<int, array{term: WP_Term|null, subcategories: array<int, array{term: WP_Term, services: WP_Post[]}>}>
 */
function tolstenko_get_header_services_menu_data() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'service_category',
			'hide_empty' => true,
			'parent'     => 0,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		$categories = array();
	}
	$out = array();
	foreach ( $categories as $cat ) {
		$children = get_terms(
			array(
				'taxonomy'   => 'service_category',
				'hide_empty' => true,
				'parent'     => $cat->term_id,
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);
		if ( is_wp_error( $children ) ) {
			$children = array();
		}
		$subcategories = array();
		if ( ! empty( $children ) ) {
			foreach ( $children as $child ) {
				$services = get_posts(
					array(
						'post_type'      => 'service',
						'posts_per_page' => 50,
						'orderby'        => 'menu_order title',
						'order'          => 'ASC',
						'tax_query'      => array(
							array(
								'taxonomy' => 'service_category',
								'field'    => 'term_id',
								'terms'    => $child->term_id,
							),
						),
					)
				);
				$subcategories[] = array( 'term' => $child, 'services' => $services );
			}
		} else {
			$services = get_posts(
				array(
					'post_type'      => 'service',
					'posts_per_page' => 50,
					'orderby'        => 'menu_order title',
					'order'          => 'ASC',
					'tax_query'      => array(
						array(
							'taxonomy' => 'service_category',
							'field'    => 'term_id',
							'terms'    => $cat->term_id,
						),
					),
				)
			);
			$subcategories[] = array( 'term' => $cat, 'services' => $services );
		}
		$out[] = array( 'term' => $cat, 'subcategories' => $subcategories );
	}
	if ( empty( $out ) ) {
		$all = get_posts(
			array(
				'post_type'      => 'service',
				'posts_per_page' => 50,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
			)
		);
		if ( ! empty( $all ) ) {
			$out[] = array(
				'term'          => null,
				'subcategories' => array( array( 'term' => null, 'services' => $all ) ),
			);
		}
	} elseif ( ! empty( $out ) ) {
		$uncat = tolstenko_get_uncategorized_service_posts();
		if ( ! empty( $uncat ) ) {
			$out[] = array(
				'term'            => null,
				'uncategorized'   => true,
				'subcategories'   => array(
					array(
						'term'     => null,
						'services' => $uncat,
					),
				),
			);
		}
	}
	return $out;
}

/**
 * Таксономии: категории услуг; вакансий; кейсов; статей.
 */
function tolstenko_register_taxonomies() {
    register_taxonomy(
        'service_category',
        array( 'service' ),
        array(
            'label'             => __( 'Категории услуг', 'tolstenko-theme' ),
            'hierarchical'      => true,
            'public'            => true,
            // rewrite отключён: /services/{cat}/ — inc/rewrite/service-rewrite.php
            'rewrite'           => false,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
        )
    );

    register_taxonomy(
        'vacancy_cat',
        array( 'vacancy' ),
        array(
            'labels' => array(
                'name'          => __( 'Категории вакансий', 'tolstenko-theme' ),
                'singular_name' => __( 'Категория вакансий', 'tolstenko-theme' ),
                'search_items'  => __( 'Искать категории', 'tolstenko-theme' ),
                'all_items'     => __( 'Все категории', 'tolstenko-theme' ),
                'edit_item'     => __( 'Редактировать категорию', 'tolstenko-theme' ),
                'update_item'   => __( 'Обновить категорию', 'tolstenko-theme' ),
                'add_new_item'  => __( 'Добавить категорию', 'tolstenko-theme' ),
                'new_item_name' => __( 'Новая категория', 'tolstenko-theme' ),
                'menu_name'     => __( 'Категории', 'tolstenko-theme' ),
            ),
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_rest'      => true,
            // rewrite отключён: /vacancies/{cat}/ — inc/rewrite/vacancy-rewrite.php
            'rewrite'           => false,
        )
    );

	register_taxonomy(
		'case_cat',
		array( 'case' ),
		array(
			'labels' => array(
				'name'          => __( 'Категории кейсов', 'tolstenko-theme' ),
				'singular_name' => __( 'Категория кейсов', 'tolstenko-theme' ),
				'search_items'  => __( 'Искать категории', 'tolstenko-theme' ),
				'all_items'     => __( 'Все категории', 'tolstenko-theme' ),
				'edit_item'     => __( 'Редактировать категорию', 'tolstenko-theme' ),
				'update_item'   => __( 'Обновить категорию', 'tolstenko-theme' ),
				'add_new_item'  => __( 'Добавить категорию', 'tolstenko-theme' ),
				'new_item_name' => __( 'Новая категория', 'tolstenko-theme' ),
				'menu_name'     => __( 'Категории', 'tolstenko-theme' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'         => 'case-category',
				'with_front'   => false,
				'hierarchical' => false,
			),
		)
	);

	// Категории статей — по образцу service_category (иерархия, /blog/{cat}/).
	register_taxonomy(
		'blog_cat',
		array( 'blog' ),
		array(
			'labels' => array(
				'name'              => __( 'Категории статей', 'tolstenko-theme' ),
				'singular_name'     => __( 'Категория статей', 'tolstenko-theme' ),
				'search_items'      => __( 'Искать категории', 'tolstenko-theme' ),
				'all_items'         => __( 'Все категории', 'tolstenko-theme' ),
				'parent_item'       => __( 'Родительская категория', 'tolstenko-theme' ),
				'parent_item_colon' => __( 'Родительская категория:', 'tolstenko-theme' ),
				'edit_item'         => __( 'Редактировать категорию', 'tolstenko-theme' ),
				'update_item'       => __( 'Обновить категорию', 'tolstenko-theme' ),
				'add_new_item'      => __( 'Добавить категорию', 'tolstenko-theme' ),
				'new_item_name'     => __( 'Новая категория', 'tolstenko-theme' ),
				'menu_name'         => __( 'Категории', 'tolstenko-theme' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_in_rest'      => true,
			// rewrite отключён: правила /blog/{cat}/ задаём вручную (inc/rewrite/blog-rewrite.php),
			// иначе CPT blog/{slug} перехватывает рубрики и даёт 404.
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'tolstenko_register_taxonomies' );

/**
 * Кастомные rewrite-правила для иерархии:
 * /services/{category}/
 * /services/{category}/{service}/
 * /blog/{category}/{article}/
 */
function tolstenko_register_services_rewrite_rules() {
    add_rewrite_rule(
        '^services/([^/]+)/([^/]+)/?$',
        'index.php?post_type=service&name=$matches[2]&service_category=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^blog/([^/]+)/([^/]+)/?$',
        'index.php?post_type=blog&name=$matches[2]&blog_cat=$matches[1]',
        'top'
    );
}
add_action( 'init', 'tolstenko_register_services_rewrite_rules', 20 );

/**
 * Ссылка на услугу в формате /services/{category}/{service}/.
 */
function tolstenko_service_post_type_link( $post_link, $post ) {
    if ( $post->post_type !== 'service' ) {
        return $post_link;
    }

    // В админке и REST (Gutenberg) — стандартный /service/{slug}/, как у city.
    // Кастомный /services/{category}/{slug}/ только на фронте.
    if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
        return $post_link;
    }

    $terms = get_the_terms( $post, 'service_category' );
    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return home_url( '/services/' . $post->post_name . '/' );
    }

    $term = array_shift( $terms );
    if ( ! ( $term instanceof WP_Term ) ) {
        return home_url( '/services/' . $post->post_name . '/' );
    }

    return home_url( '/services/' . $term->slug . '/' . $post->post_name . '/' );
}
add_filter( 'post_type_link', 'tolstenko_service_post_type_link', 10, 2 );

/**
 * Ссылка на статью: /blog/{category}/{slug}/ или /blog/{slug}/ без категории.
 *
 * @param string  $post_link Permalink.
 * @param WP_Post $post      Post.
 * @return string
 */
function tolstenko_blog_post_type_link( $post_link, $post ) {
	if ( ! ( $post instanceof WP_Post ) || $post->post_type !== 'blog' ) {
		return $post_link;
	}

	$terms = get_the_terms( $post, 'blog_cat' );
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return home_url( '/blog/' . $post->post_name . '/' );
	}

	$term = array_shift( $terms );
	if ( ! ( $term instanceof WP_Term ) ) {
		return home_url( '/blog/' . $post->post_name . '/' );
	}

	return home_url( '/blog/' . $term->slug . '/' . $post->post_name . '/' );
}
add_filter( 'post_type_link', 'tolstenko_blog_post_type_link', 10, 2 );

/**
 * Для CPT blog: пустой редактор без locked template —
 * тело статьи = Gutenberg / Mammoth .docx (hybrid single рендерит the_content).
 *
 * @param array $settings                Настройки редактора блоков.
 * @param \WP_Block_Editor_Context $ctx Контекст редактора (post, post type и т.д.).
 * @return array
 */
function tolstenko_blog_default_block_template( $settings, $ctx ) {
    if ( ! isset( $ctx->post ) ) {
        return $settings;
    }
    $pt = (string) $ctx->post->post_type;
    $body_types = function_exists( 'tolstenko_get_content_body_post_types' )
        ? tolstenko_get_content_body_post_types()
        : array( 'blog', 'actions' );
    if ( ! in_array( $pt, $body_types, true ) ) {
        return $settings;
    }
    if ( ! empty( trim( (string) $ctx->post->post_content ) ) ) {
        return $settings;
    }
    $settings['template']     = array();
    $settings['templateLock'] = false;
    return $settings;
}
add_filter( 'block_editor_settings_all', 'tolstenko_blog_default_block_template', 10, 2 );

/**
 * ACF Options Page для общих настроек (контакты, сквозные блоки и т.п.)
 * Работает, только если установлен ACF PRO или ACF с поддержкой options page.
 */
function tolstenko_acf_options_page() {
    if ( function_exists( 'acf_add_options_page' ) ) {
        acf_add_options_page(
            array(
                'page_title' => __( 'Общие настройки сайта', 'tolstenko-theme' ),
                'menu_title' => __( 'Общие настройки', 'tolstenko-theme' ),
                'menu_slug'  => 'tolstenko-general-settings',
                'capability' => 'manage_options',
                'redirect'   => false,
            )
        );
    }
}
add_action( 'init', 'tolstenko_acf_options_page' );

/**
 * Хлебные крошки: один раз перед контентом (не на главной).
 */
function tolstenko_render_breadcrumb() {
	static $rendered = false;
	if ( $rendered || is_front_page() ) {
		return;
	}
	$rendered = true;
	get_template_part( 'modules/breadcrumb/breadcrumb' );
}

/**
 * Ссылка на разводящую страницу CPT, если архив отключён.
 * service → /services/, vacancy → /vacancies/, actions → /actions/.
 *
 * @param string $post_type Post type.
 * @return array{url:string,label:string}|null
 */
function tolstenko_get_cpt_listing_breadcrumb( $post_type ) {
	$post_type = sanitize_key( (string) $post_type );
	if ( $post_type === '' ) {
		return null;
	}

	$post_type_obj = get_post_type_object( $post_type );
	$label         = $post_type_obj ? (string) $post_type_obj->labels->name : '';

	$archive_link = get_post_type_archive_link( $post_type );
	if ( $archive_link ) {
		return array(
			'url'   => $archive_link,
			'label' => $label !== '' ? $label : $post_type,
		);
	}

	$page_map = array(
		'service' => 'services',
		'vacancy' => 'vacancies',
		'actions' => 'actions',
		'city'    => 'city',
		'case'    => 'cases',
		'blog'    => 'blog',
	);

	/**
	 * Карта CPT → slug страницы-листинга (когда has_archive = false).
	 *
	 * @param array<string,string> $page_map
	 */
	$page_map = apply_filters( 'tolstenko_cpt_listing_page_slugs', $page_map );

	if ( empty( $page_map[ $post_type ] ) ) {
		return null;
	}

	$page = get_page_by_path( (string) $page_map[ $post_type ] );
	if ( ! ( $page instanceof WP_Post ) ) {
		return null;
	}

	$url = get_permalink( $page );
	if ( ! $url ) {
		return null;
	}

	$page_title = get_the_title( $page );
	return array(
		'url'   => $url,
		'label' => $page_title !== '' ? $page_title : ( $label !== '' ? $label : $post_type ),
	);
}

/**
 * Contact Form 7: плейсхолдеры ссылок и иконки в разметке форм.
 */
require_once get_template_directory() . '/inc/cf7/custom-elements.php';

/**
 * Разрешаем query var для атрибутов Gutenberg-блоков темы.
 */
function tolstenko_query_vars( $vars ) {
    $vars[] = 'tolstenko_block_attributes';
    return $vars;
}
add_filter( 'query_vars', 'tolstenko_query_vars' );

/**
 * Хелперы блока «SEO продвижение».
 */
require_once get_template_directory() . '/inc/seo-section-helpers.php';

/**
 * ACF Block Types для Gutenberg (блоки темы в редакторе)
 */
require_once get_template_directory() . '/inc/acf-blocks.php';

/**
 * ACF: поля для услуг (карточка)
 */
require_once get_template_directory() . '/inc/acf-service-fields.php';

/**
 * Блоки подкатегории (term meta): решения, баннер, о нас.
 */
require_once get_template_directory() . '/inc/service-category-extra-blocks.php';
require_once get_template_directory() . '/inc/service-category-article-sections.php';
require_once get_template_directory() . '/inc/service-category-admin-hero-fields.php';

/**
 * ACF: поля для категорий услуг (страница подкатегории — баннер и т.д.)
 */
require_once get_template_directory() . '/inc/acf-service-category-fields.php';

/**
 * CPT «Отзывы»: нативный метабокс полей (без ACF) + хелперы фронта.
 */
require_once get_template_directory() . '/inc/review-metabox.php';
require_once get_template_directory() . '/inc/reviews-helpers.php';

/**
 * CPT «Кейсы»: нативный метабокс (как у отзывов), без публичных страниц.
 */
require_once get_template_directory() . '/inc/case-metabox.php';

/**
 * Дефолты блоков темы: админка + helper для шаблонов.
 */
require_once get_template_directory() . '/inc/block-defaults-admin.php';

/**
 * Партнёры блоки / Пресс-портрет: отдельные пункты «Настройки сайта».
 */
require_once get_template_directory() . '/inc/partner-press-defaults-admin.php';

/**
 * Шаблон вакансии: Настройки сайта → Шаблон вакансии.
 */
require_once get_template_directory() . '/inc/vacancy-template-admin.php';
require_once get_template_directory() . '/inc/rest-posts-filter.php';

/**
 * Настройки сайта → Контактные данные (телефон, почта, соцсети).
 */
require_once get_template_directory() . '/inc/contact-data-admin.php';
require_once get_template_directory() . '/inc/contacts-page-admin.php';

/**
 * Данные шапки и подвала (телефон, соцсети) из «Контактных данных».
 */
require_once get_template_directory() . '/inc/site-header-footer-data.php';

/**
 * Блог (CPT blog): stats, reading time, плагины, метабоксы, авторы.
 */
require_once get_template_directory() . '/inc/compat-koritan-rename.php';

require_once get_template_directory() . '/inc/blog/helpers.php';
require_once get_template_directory() . '/inc/blog/reading-time.php';
require_once get_template_directory() . '/inc/blog/plugins-display.php';
require_once get_template_directory() . '/inc/blog/authors-admin.php';
require_once get_template_directory() . '/inc/blog/metabox.php';
require_once get_template_directory() . '/inc/blog/faq-metabox.php';
require_once get_template_directory() . '/inc/blog/sliders-metabox.php';
require_once get_template_directory() . '/inc/blog/allowed-blocks.php';
require_once get_template_directory() . '/inc/blog/content-blocks.php';
require_once get_template_directory() . '/inc/rewrite/blog-rewrite.php';
require_once get_template_directory() . '/inc/rewrite/service-rewrite.php';
require_once get_template_directory() . '/inc/rewrite/vacancy-rewrite.php';

