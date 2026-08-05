<?php
/**
 * Универсальный REST-фильтр записей по таксономии.
 * GET /wp-json/tolstenko/v1/filter-posts
 *
 * Параметры:
 * - post_type (string)
 * - taxonomy (string)
 * - term (slug, пусто = все)
 * - posts_per_page (int, -1 = все)
 * - card (ключ рендерера карточки, напр. vacancy)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Зарегистрированные рендереры карточек для фильтра.
 *
 * @return array<string, callable>
 */
function tolstenko_get_posts_filter_card_renderers() {
	$renderers = array(
		'vacancy'      => 'tolstenko_render_vacancy_filter_card',
		'case'         => 'tolstenko_render_case_filter_card',
		'service'      => 'tolstenko_render_service_filter_card',
		'service_tile' => 'tolstenko_render_service_tile_filter_card',
		'blog'         => 'tolstenko_render_blog_filter_card',
		'blog_slider'  => 'tolstenko_render_blog_slider_filter_card',
		'blog_tile'    => 'tolstenko_render_blog_tile_filter_card',
	);

	/**
	 * Добавить/заменить рендереры карточек фильтра.
	 *
	 * @param array<string, callable> $renderers
	 */
	return apply_filters( 'tolstenko_posts_filter_card_renderers', $renderers );
}

/**
 * Карточка вакансии для фильтра (через vacancy-card.php).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_vacancy_filter_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_vacancy_post', $post );
	set_query_var( 'tolstenko_vacancy_card_class', 'vacancies-section__item br-30 fade-in-element' );
	get_template_part( 'template-parts/blocks/vacancy-card' );
}

/**
 * Карточка кейса для фильтра (через case-card.php).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_case_filter_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_case_post', $post );
	set_query_var( 'tolstenko_case_card_class', 'case-section__item case-card fade-in-element splide__slide swiper-slide' );
	get_template_part( 'template-parts/blocks/case-card' );
}

/**
 * Карточка услуги для фильтра (через service-card.php).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_service_filter_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_service_post', $post );
	set_query_var( 'tolstenko_service_card_class', 'service-section__item service-card swiper-slide' );
	get_template_part( 'template-parts/blocks/service-card' );
}

/**
 * Карточка услуги для плитки (без swiper-slide).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_service_tile_filter_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_service_post', $post );
	set_query_var( 'tolstenko_service_card_class', 'service-section__item service-card fade-in-element' );
	get_template_part( 'template-parts/blocks/service-card' );
}

/**
 * Карточка статьи для слайдера «Похожие» (через blog-card.php).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_blog_filter_card( $post ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_blog_post', $post );
	set_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card blog-card--same swiper-slide' );
	set_query_var( 'tolstenko_blog_card_same', true );
	get_template_part( 'template-parts/blocks/blog-card' );
}

/**
 * Карточка статьи для слайдера «Статьи» (с датой).
 *
 * @param WP_Post $post Post.
 */
function tolstenko_render_blog_slider_filter_card( $post, $show_date = false ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_blog_post', $post );
	set_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card fade-in-element splide__slide' );
	set_query_var( 'tolstenko_blog_card_same', false );
	set_query_var( 'tolstenko_blog_card_show_date', (bool) $show_date );
	set_query_var( 'tolstenko_blog_card_show_stats', true );
	get_template_part( 'template-parts/blocks/blog-card' );
}

/**
 * Кнопка «Все статьи» для блока «Статьи» (дефолты blog_section_filters).
 *
 * @return array{text:string,url:string}|null
 */
function tolstenko_get_blog_section_filters_button() {
	$defaults = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'blog_section_filters' )
		: array();

	$text = trim( (string) ( $defaults['btn_text'] ?? '' ) );
	$url  = trim( (string) ( $defaults['btn_url'] ?? '' ) );

	if ( $url === '' && post_type_exists( 'blog' ) ) {
		$url = (string) get_post_type_archive_link( 'blog' );
	}

	if ( $text === '' || $url === '' ) {
		return null;
	}

	return array(
		'text' => $text,
		'url'  => $url,
	);
}

/**
 * HTML кнопки под списком статей в блоке «Статьи».
 */
function tolstenko_render_blog_section_filters_button() {
	$btn = tolstenko_get_blog_section_filters_button();
	if ( null === $btn ) {
		return;
	}
	?>
	<a class="blog-section__btn default-btn" href="<?php echo esc_url( $btn['url'] ); ?>">
		<?php echo esc_html( $btn['text'] ); ?>
		<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
			<path d="M5.87524 14.1246L13.5356 6.46426M7.05376 5.875H13.2915C13.7517 5.875 14.1248 6.2481 14.1248 6.70833V12.9461" stroke="black" stroke-width="2" stroke-linecap="round" />
		</svg>
	</a>
	<?php
}

/**
 * Разметка блока «Статьи»: 1-я карточка слева + до 3 справа + кнопка (как marketing blog-section).
 *
 * @param array $args См. tolstenko_render_filtered_posts_html().
 * @return array{html:string,pagination:string,max_pages:int,page:int}
 */
function tolstenko_render_blog_slider_layout_payload( $args ) {
	$empty = array(
		'html'       => '',
		'pagination' => '',
		'max_pages'  => 0,
		'page'       => 1,
	);

	$post_type      = sanitize_key( $args['post_type'] ?? 'blog' );
	$taxonomy       = sanitize_key( $args['taxonomy'] ?? 'blog_cat' );
	$term           = sanitize_title( $args['term'] ?? '' );
	$posts_per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 4;
	if ( $posts_per_page < 1 ) {
		$posts_per_page = 4;
	}
	$posts_per_page = min( 4, $posts_per_page );

	$post_ids = array();
	if ( isset( $args['post_ids'] ) ) {
		$raw_ids = $args['post_ids'];
		if ( is_string( $raw_ids ) ) {
			$raw_ids = preg_split( '/[\s,]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		if ( is_array( $raw_ids ) ) {
			foreach ( $raw_ids as $id ) {
				$id = (int) $id;
				if ( $id > 0 ) {
					$post_ids[] = $id;
				}
			}
			$post_ids = array_values( array_unique( $post_ids ) );
			if ( count( $post_ids ) > 4 ) {
				$post_ids = array_slice( $post_ids, 0, 4 );
			}
		}
	}

	if ( $post_type === '' || ! post_type_exists( $post_type ) ) {
		return $empty;
	}

	$query_args = array(
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	if ( ! empty( $post_ids ) ) {
		$query_args['post__in'] = $post_ids;
		$query_args['orderby']  = 'post__in';
	}

	if ( $term !== '' && $taxonomy !== '' && taxonomy_exists( $taxonomy ) ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
		set_query_var( 'tolstenko_service_card_selected_category', $term );
	} else {
		set_query_var( 'tolstenko_service_card_selected_category', '' );
	}

	$query = new WP_Query( $query_args );
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return $empty;
	}

	ob_start();
	$index = 0;
	while ( $query->have_posts() ) {
		$query->the_post();
		$post = get_post();
		if ( ! ( $post instanceof WP_Post ) ) {
			continue;
		}

		if ( 0 === $index ) {
			tolstenko_render_blog_slider_filter_card( $post, true );
		} else {
			if ( 1 === $index ) {
				echo '<div class="splide__track"><div class="blog-section__splide-list splide__list">';
			}
			tolstenko_render_blog_slider_filter_card( $post, false );
		}
		++$index;
	}

	if ( $index > 1 ) {
		echo '</div>';
		echo '<div class="splide__pagination"></div>';
		tolstenko_render_blog_section_filters_button();
		echo '</div>';
	}

	wp_reset_postdata();
	$html = (string) ob_get_clean();

	return array(
		'html'       => $html,
		'pagination' => '',
		'max_pages'  => 0,
		'page'       => 1,
	);
}

/**
 * Карточка статьи для плитки (в сетке __items).
 *
 * @param WP_Post $post Post.
 * @param bool    $featured Первая крупная карточка (с датой).
 */
function tolstenko_render_blog_tile_filter_card( $post, $featured = false ) {
	if ( ! $post instanceof WP_Post ) {
		return;
	}
	set_query_var( 'tolstenko_blog_post', $post );
	set_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card blog-card--blog fade-in-element' );
	set_query_var( 'tolstenko_blog_card_same', false );
	set_query_var( 'tolstenko_blog_card_show_date', (bool) $featured );
	set_query_var( 'tolstenko_blog_card_show_stats', true );
	get_template_part( 'template-parts/blocks/blog-card' );
}

/**
 * Данные сайдбара для архивной плитки статей (как director в marketing).
 *
 * @return array{photo_url:string,photo_alt:string,name:string,text:string,btn:string,btn_url:string,socials:array}
 */
function tolstenko_get_blog_archive_sidebar_data() {
	$tile = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'blog_section_tile' )
		: array();
	$vac_person = function_exists( 'tolstenko_get_vacancy_sidebar_person' )
		? tolstenko_get_vacancy_sidebar_person()
		: array(
			'photo_id' => 0,
			'name'     => '',
			'text'     => '',
		);

	$photo_id = (int) ( $tile['sidebar_photo'] ?? 0 );
	if ( ! $photo_id ) {
		$photo_id = (int) ( $vac_person['photo_id'] ?? 0 );
	}
	$name = trim( (string) ( $tile['sidebar_name'] ?? '' ) );
	if ( $name === '' ) {
		$name = trim( (string) ( $vac_person['name'] ?? '' ) );
	}
	$text = (string) ( $tile['sidebar_text'] ?? '' );
	if ( trim( wp_strip_all_tags( $text ) ) === '' ) {
		$text = (string) ( $vac_person['text'] ?? '' );
	}
	$vac_defaults = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'vacancy_content' )
		: array();
	$btn = trim( (string) ( $tile['sidebar_btn'] ?? '' ) );
	if ( $btn === '' ) {
		$btn = trim( (string) ( $vac_defaults['sidebar_btn'] ?? '' ) );
	}
	if ( $btn === '' ) {
		$btn = __( 'Бесплатный аудит', 'tolstenko-theme' );
	}
	$btn_url = (string) ( $tile['sidebar_btn_url'] ?? '' );
	if ( $btn_url === '' ) {
		$btn_url = (string) ( $vac_defaults['sidebar_btn_url'] ?? '' );
	}
	$btn_url = function_exists( 'tolstenko_url_or_modal' )
		? tolstenko_url_or_modal( $btn_url )
		: ( $btn_url !== '' ? $btn_url : '#modal' );

	$photo_url = $photo_id ? (string) wp_get_attachment_image_url( $photo_id, 'medium' ) : '';
	$photo_alt = $photo_id ? (string) get_post_meta( $photo_id, '_wp_attachment_image_alt', true ) : '';
	if ( $photo_alt === '' ) {
		$photo_alt = $name;
	}

	$has_socials = false;
	if ( function_exists( 'tolstenko_get_contact_data' ) ) {
		$cd = tolstenko_get_contact_data( true );
		$has_socials = ! empty( $cd['socials_rgb'] ) && is_array( $cd['socials_rgb'] );
	}

	return array(
		'photo_url'    => $photo_url,
		'photo_alt'    => $photo_alt,
		'name'         => $name,
		'text'         => $text,
		'btn'          => $btn,
		'btn_url'      => $btn_url,
		'has_socials'  => $has_socials,
	);
}

/**
 * Сайдбар «директор» внутри плитки архива.
 */
function tolstenko_render_blog_archive_sidebar() {
	$sb = tolstenko_get_blog_archive_sidebar_data();
	$has = (
		$sb['photo_url'] !== ''
		|| $sb['name'] !== ''
		|| trim( wp_strip_all_tags( $sb['text'] ) ) !== ''
		|| ! empty( $sb['has_socials'] )
	);
	if ( ! $has ) {
		return;
	}
	?>
	<div class="blog-section__right-wrapper">
		<?php if ( $sb['photo_url'] !== '' ) : ?>
			<img class="blog-section__right-photo" src="<?php echo esc_url( $sb['photo_url'] ); ?>" alt="<?php echo esc_attr( $sb['photo_alt'] ); ?>" loading="lazy" decoding="async">
		<?php endif; ?>
		<?php if ( $sb['name'] !== '' ) : ?>
			<div class="blog-section__right-name line-caps-bold-13-15"><?php echo esc_html( $sb['name'] ); ?></div>
		<?php endif; ?>
		<?php if ( trim( wp_strip_all_tags( $sb['text'] ) ) !== '' ) : ?>
			<div class="blog-section__right-text paragraph-15-25"><?php echo tolstenko_kses_html( $sb['text'] ); ?></div>
		<?php endif; ?>
		<?php get_template_part( 'modules/socials/socials-rgb' ); ?>
		<a class="free-audit__btn default-btn default-btn--red" href="<?php echo esc_url( $sb['btn_url'] ); ?>"><?php echo esc_html( $sb['btn'] ); ?></a>
	</div>
	<?php
}

/**
 * Разметка архивной плитки: 1-я карточка + сайдбар + сетка остальных.
 *
 * @param array $args Аргументы запроса (как у filter payload).
 * @return array{html:string,pagination:string,max_pages:int,page:int}
 */
function tolstenko_render_blog_archive_layout_payload( $args ) {
	$empty = array(
		'html'       => '',
		'pagination' => '',
		'max_pages'  => 0,
		'page'       => 1,
	);

	$args['card']     = 'blog_tile';
	$args['paginate'] = true;
	// Сначала обычный query через payload без рендера карточек — переиспользуем логику запроса.
	$post_type      = sanitize_key( $args['post_type'] ?? 'blog' );
	$taxonomy       = sanitize_key( $args['taxonomy'] ?? 'blog_cat' );
	$term           = sanitize_title( $args['term'] ?? '' );
	$posts_per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : 9;
	$paged          = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
	if ( $posts_per_page === 0 ) {
		$posts_per_page = 9;
	}

	$post_ids = array();
	if ( isset( $args['post_ids'] ) ) {
		$raw_ids = $args['post_ids'];
		if ( is_string( $raw_ids ) ) {
			$raw_ids = preg_split( '/[\s,]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		if ( is_array( $raw_ids ) ) {
			foreach ( $raw_ids as $id ) {
				$id = (int) $id;
				if ( $id > 0 ) {
					$post_ids[] = $id;
				}
			}
			$post_ids = array_values( array_unique( $post_ids ) );
		}
	}

	if ( $post_type === '' || ! post_type_exists( $post_type ) ) {
		return $empty;
	}

	$query_args = array(
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page,
		'paged'                  => $paged,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => false,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);
	if ( ! empty( $post_ids ) ) {
		$query_args['post__in'] = $post_ids;
		$query_args['orderby']  = 'post__in';
	}
	if ( $term !== '' && $taxonomy !== '' && taxonomy_exists( $taxonomy ) ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
	}

	$query = new WP_Query( $query_args );
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return $empty;
	}

	ob_start();
	$is_first = true;
	while ( $query->have_posts() ) {
		$query->the_post();
		$post = get_post();
		if ( $is_first ) {
			tolstenko_render_blog_tile_filter_card( $post, true );
			tolstenko_render_blog_archive_sidebar();
			echo '<div class="blog-section__items">';
			$is_first = false;
			continue;
		}
		tolstenko_render_blog_tile_filter_card( $post, false );
	}
	if ( ! $is_first ) {
		echo '</div>';
	}
	wp_reset_postdata();
	$html = (string) ob_get_clean();

	$max_pages = (int) $query->max_num_pages;
	return array(
		'html'       => $html,
		'pagination' => tolstenko_render_filter_pagination_html( $max_pages, $paged ),
		'max_pages'  => $max_pages,
		'page'       => $paged,
	);
}

/**
 * HTML постраничной навигации для AJAX-плитки.
 *
 * @param int $max_pages Макс. страниц.
 * @param int $current   Текущая страница.
 * @return string
 */
function tolstenko_render_filter_pagination_html( $max_pages, $current = 1 ) {
	$max_pages = (int) $max_pages;
	$current   = max( 1, (int) $current );
	if ( $max_pages <= 1 ) {
		return '';
	}

	ob_start();
	echo '<div class="blog-section__pagination pagination-page"><div class="pagination">';

	if ( $current > 1 ) {
		printf(
			'<a class="prev page-numbers" href="#" data-tolstenko-page="%d" aria-label="%s">&lsaquo;</a>',
			(int) ( $current - 1 ),
			esc_attr__( 'Назад', 'tolstenko-theme' )
		);
	}

	$range = 1;
	for ( $i = 1; $i <= $max_pages; $i++ ) {
		if ( $i === 1 || $i === $max_pages || ( $i >= $current - $range && $i <= $current + $range ) ) {
			if ( $i === $current ) {
				printf( '<span class="page-numbers current" aria-current="page">%d</span>', (int) $i );
			} else {
				printf(
					'<a class="page-numbers" href="#" data-tolstenko-page="%d">%d</a>',
					(int) $i,
					(int) $i
				);
			}
		} elseif (
			( $i === $current - $range - 1 && $i > 1 )
			|| ( $i === $current + $range + 1 && $i < $max_pages )
		) {
			echo '<span class="page-numbers dots">&hellip;</span>';
		}
	}

	if ( $current < $max_pages ) {
		printf(
			'<a class="next page-numbers" href="#" data-tolstenko-page="%d" aria-label="%s">&rsaquo;</a>',
			(int) ( $current + 1 ),
			esc_attr__( 'Вперёд', 'tolstenko-theme' )
		);
	}

	echo '</div></div>';
	return (string) ob_get_clean();
}

/**
 * Рендер списка HTML по параметрам фильтра.
 *
 * @param array $args {
 *     @type string     $post_type
 *     @type string     $taxonomy
 *     @type string     $term
 *     @type int        $posts_per_page
 *     @type string     $card
 *     @type int[]|string $post_ids  Если не пусто — только эти ID (порядок сохраняется).
 *     @type int[]|string $exclude   ID для post__not_in.
 *     @type int        $paged      Номер страницы (0/1 = без пагинации / первая).
 *     @type bool       $paginate   Включить found_rows и постраничность.
 * }
 * @return string
 */
function tolstenko_render_filtered_posts_html( $args ) {
	$result = tolstenko_render_filtered_posts_payload( $args );
	return isset( $result['html'] ) ? (string) $result['html'] : '';
}

/**
 * Рендер карточек + мета пагинации.
 *
 * @param array $args См. tolstenko_render_filtered_posts_html().
 * @return array{html:string,pagination:string,max_pages:int,page:int}
 */
function tolstenko_render_filtered_posts_payload( $args ) {
	$empty = array(
		'html'       => '',
		'pagination' => '',
		'max_pages'  => 0,
		'page'       => 1,
	);

	$post_type      = sanitize_key( $args['post_type'] ?? '' );
	$taxonomy       = sanitize_key( $args['taxonomy'] ?? '' );
	$term           = sanitize_title( $args['term'] ?? '' );
	$posts_per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : -1;
	$card           = sanitize_key( $args['card'] ?? '' );
	$paginate       = ! empty( $args['paginate'] );
	$paged          = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
	$post_ids       = array();
	$exclude        = array();

	if ( isset( $args['post_ids'] ) ) {
		$raw_ids = $args['post_ids'];
		if ( is_string( $raw_ids ) ) {
			$raw_ids = preg_split( '/[\s,]+/', $raw_ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		if ( is_array( $raw_ids ) ) {
			foreach ( $raw_ids as $id ) {
				$id = (int) $id;
				if ( $id > 0 ) {
					$post_ids[] = $id;
				}
			}
			$post_ids = array_values( array_unique( $post_ids ) );
		}
	}

	if ( isset( $args['exclude'] ) ) {
		$raw_exclude = $args['exclude'];
		if ( is_string( $raw_exclude ) ) {
			$raw_exclude = preg_split( '/[\s,]+/', $raw_exclude, -1, PREG_SPLIT_NO_EMPTY );
		}
		if ( is_array( $raw_exclude ) ) {
			foreach ( $raw_exclude as $id ) {
				$id = (int) $id;
				if ( $id > 0 ) {
					$exclude[] = $id;
				}
			}
			$exclude = array_values( array_unique( $exclude ) );
		}
	}

	if ( $post_type === '' || ! post_type_exists( $post_type ) ) {
		return $empty;
	}

	$renderers = tolstenko_get_posts_filter_card_renderers();
	if ( $card === '' || empty( $renderers[ $card ] ) || ! is_callable( $renderers[ $card ] ) ) {
		return $empty;
	}

	// Блок «Статьи»: крупная карточка слева + колонка справа + кнопка.
	if ( $card === 'blog_slider' ) {
		return tolstenko_render_blog_slider_layout_payload( $args );
	}

	// Архивная плитка: 1-я карточка + сайдбар + сетка (как blog-archive в marketing).
	if ( $card === 'blog_tile' ) {
		return tolstenko_render_blog_archive_layout_payload(
			array(
				'post_type'      => $post_type,
				'taxonomy'       => $taxonomy,
				'term'           => $term,
				'posts_per_page' => $posts_per_page,
				'post_ids'       => $post_ids,
				'paged'          => $paged,
				'paginate'       => true,
			)
		);
	}

	$query_args = array(
		'post_type'              => $post_type,
		'post_status'            => 'publish',
		'posts_per_page'         => $posts_per_page,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => ! $paginate,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
	);

	if ( ! empty( $post_ids ) ) {
		$query_args['post__in'] = $post_ids;
		$query_args['orderby']  = 'post__in';
		if ( ! $paginate ) {
			$query_args['posts_per_page'] = count( $post_ids );
		}
	}

	if ( $paginate ) {
		$query_args['paged'] = $paged;
		if ( $posts_per_page === 0 ) {
			$query_args['posts_per_page'] = 9;
		}
	}

	if ( ! empty( $exclude ) ) {
		$query_args['post__not_in'] = $exclude;
	}

	if ( $term !== '' && $taxonomy !== '' && taxonomy_exists( $taxonomy ) ) {
		$query_args['tax_query'] = array(
			array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term,
			),
		);
		set_query_var( 'tolstenko_service_card_selected_category', $term );
	} else {
		set_query_var( 'tolstenko_service_card_selected_category', '' );
	}

	$query = new WP_Query( $query_args );
	ob_start();
	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			call_user_func( $renderers[ $card ], get_post() );
		}
		wp_reset_postdata();
	}
	$html = (string) ob_get_clean();

	$max_pages = $paginate ? (int) $query->max_num_pages : 0;
	$page      = $paginate ? $paged : 1;

	return array(
		'html'       => $html,
		'pagination' => $paginate ? tolstenko_render_filter_pagination_html( $max_pages, $page ) : '',
		'max_pages'  => $max_pages,
		'page'       => $page,
	);
}

add_action( 'rest_api_init', 'tolstenko_register_posts_filter_rest_route' );

function tolstenko_register_posts_filter_rest_route() {
	register_rest_route(
		'tolstenko/v1',
		'/filter-posts',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'tolstenko_rest_filter_posts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'post_type'      => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => static function ( $value ) {
						return sanitize_key( (string) $value );
					},
				),
				'taxonomy'       => array(
					'type'              => 'string',
					'required'          => false,
					'default'           => '',
					'sanitize_callback' => static function ( $value ) {
						return sanitize_key( (string) $value );
					},
				),
				'term'           => array(
					'type'              => 'string',
					'required'          => false,
					'default'           => '',
					'sanitize_callback' => static function ( $value ) {
						return sanitize_title( (string) $value );
					},
				),
				'posts_per_page' => array(
					'type'              => 'integer',
					'required'          => false,
					'default'           => -1,
					'sanitize_callback' => static function ( $value ) {
						return (int) $value;
					},
				),
				'card'           => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => static function ( $value ) {
						return sanitize_key( (string) $value );
					},
				),
				'post_ids'       => array(
					'type'              => 'string',
					'required'          => false,
					'default'           => '',
					'sanitize_callback' => static function ( $value ) {
						return sanitize_text_field( (string) $value );
					},
				),
				'paged'          => array(
					'type'              => 'integer',
					'required'          => false,
					'default'           => 1,
					'sanitize_callback' => static function ( $value ) {
						return max( 1, (int) $value );
					},
				),
				'paginate'       => array(
					'type'              => 'boolean',
					'required'          => false,
					'default'           => false,
				),
			),
		)
	);
}

/**
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function tolstenko_rest_filter_posts( $request ) {
	$post_type = (string) $request->get_param( 'post_type' );
	$card      = (string) $request->get_param( 'card' );

	$allowed_types = apply_filters(
		'tolstenko_posts_filter_allowed_post_types',
		array( 'vacancy', 'case', 'service', 'blog' )
	);
	if ( ! in_array( $post_type, (array) $allowed_types, true ) ) {
		return new WP_Error( 'tolstenko_filter_forbidden_type', __( 'Недопустимый тип записи.', 'tolstenko-theme' ), array( 'status' => 400 ) );
	}

	$renderers = tolstenko_get_posts_filter_card_renderers();
	if ( empty( $renderers[ $card ] ) ) {
		return new WP_Error( 'tolstenko_filter_unknown_card', __( 'Неизвестный тип карточки.', 'tolstenko-theme' ), array( 'status' => 400 ) );
	}

	$paginate = (bool) $request->get_param( 'paginate' );
	$result   = tolstenko_render_filtered_posts_payload(
		array(
			'post_type'      => $post_type,
			'taxonomy'       => (string) $request->get_param( 'taxonomy' ),
			'term'           => (string) $request->get_param( 'term' ),
			'posts_per_page' => (int) $request->get_param( 'posts_per_page' ),
			'card'           => $card,
			'post_ids'       => (string) $request->get_param( 'post_ids' ),
			'paged'          => (int) $request->get_param( 'paged' ),
			'paginate'       => $paginate,
		)
	);

	return rest_ensure_response(
		array(
			'html'       => $result['html'],
			'pagination' => $result['pagination'],
			'max_pages'  => $result['max_pages'],
			'page'       => $result['page'],
		)
	);
}
