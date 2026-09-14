<?php
/**
 * Шаблон результатов поиска.
 * Форма в шапке: method="get", action="home_url()", поле name="s" — запрос уходит в ?s=...
 *
 * Результаты фильтруются по типу записи фильтром-пилюлями (как в «Слайдере услуг»)
 * и выводятся карточками записей.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$query = get_search_query();

// Группируем результаты по типу записи (исключаем «Города», «Вакансии», «Акции» и сервисные страницы).
$groups = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$loop_post = get_post();
		// Пропускаем записи без заголовка или постоянной ссылки —
		// иначе в разметке появляются пустые <li>.
		if ( ! $loop_post instanceof WP_Post || '' === get_the_title( $loop_post ) || '' === (string) get_permalink( $loop_post ) ) {
			continue;
		}
		$pt = get_post_type();
		// Исключаем из выдачи: города, вакансии, акции и сервисные страницы (page).
		if ( in_array( $pt, array( 'city', 'vacancy', 'actions', 'page' ), true ) ) {
			continue;
		}
		$groups[ $pt ][] = $loop_post;
	}
	wp_reset_postdata();
}

// Собираем фильтры: типы записей (без городов, вакансий, акций и сервисных страниц).
$tabs = array();
foreach ( $groups as $pt => $posts ) {
	$pto = get_post_type_object( $pt );
	if ( ! $pto ) {
		continue;
	}
	$tabs[] = array(
		'key'   => $pt,
		'label' => $pto->labels->name,
		'posts' => $posts,
	);
}

$has_results = ! empty( $tabs );
$section_id  = 'search_results_' . wp_unique_id();
$first_key   = ! empty( $tabs ) ? $tabs[0]['key'] : '';
?>
<main class="main main-content search-results">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}
	?>
	<div class="container">
		<h1 class="search-results-title">
			<?php
			if ( $query ) {
				printf(
					/* translators: %s: search query */
					esc_html__( 'Результаты поиска: %s', 'tolstenko-theme' ),
					'<span class="search-results-query">' . esc_html( $query ) . '</span>'
				);
			} else {
				esc_html_e( 'Поиск', 'tolstenko-theme' );
			}
			?>
		</h1>

		<?php if ( $has_results ) : ?>
			<div class="search-results__filter filter">
				<div class="filter__form">
					<?php foreach ( $tabs as $i => $tab ) : ?>
						<label class="filter__radio">
							<input
								type="radio"
								name="<?php echo esc_attr( $section_id ); ?>_type"
								value="<?php echo esc_attr( $tab['key'] ); ?>"
								class="search-results-filter-radio"
								<?php checked( 0 === $i ); ?>
							>
							<span class="filter__label"><?php echo esc_html( $tab['label'] ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="search-results__content">
				<?php foreach ( $tabs as $i => $tab ) : ?>
					<div class="search-results__group<?php echo 0 === $i ? ' is-active' : ''; ?>" data-group="<?php echo esc_attr( $tab['key'] ); ?>">
						<?php
						switch ( $tab['key'] ) {
							case 'service':
								?>
								<div class="search-results-grid search-results-grid--cards">
									<?php foreach ( $tab['posts'] as $card_post ) : ?>
										<?php
										set_query_var( 'tolstenko_service_post', $card_post );
										set_query_var( 'tolstenko_service_card_class', 'service-section__item service-card fade-in-element search-results-card' );
										get_template_part( 'template-parts/blocks/service-card' );
										?>
									<?php endforeach; ?>
								</div>
								<?php
								break;

							case 'blog':
								// Карточка как в «Похожие статьи» (blog-card--same).
								?>
								<div class="search-results-grid search-results-grid--cards">
									<?php foreach ( $tab['posts'] as $card_post ) : ?>
										<?php
										set_query_var( 'tolstenko_blog_post', $card_post );
										set_query_var( 'tolstenko_blog_card_class', 'blog-section__item blog-card blog-card--same fade-in-element search-results-card' );
										set_query_var( 'tolstenko_blog_card_same', true );
										get_template_part( 'template-parts/blocks/blog-card' );
										?>
									<?php endforeach; ?>
								</div>
								<?php
								break;

							case 'case':
								?>
								<div class="search-results-grid search-results-grid--actions">
									<?php foreach ( $tab['posts'] as $card_post ) : ?>
										<?php
										set_query_var( 'tolstenko_search_case_post', $card_post );
										get_template_part( 'template-parts/blocks/search-case-card' );
										?>
									<?php endforeach; ?>
								</div>
								<?php
								break;

							default:
								// Любые другие типы — простым списком ссылок.
								?>
								<ul class="search-results-list">
									<?php foreach ( $tab['posts'] as $card_post ) : ?>
										<li class="search-results-item">
											<a href="<?php echo esc_url( get_permalink( $card_post ) ); ?>" class="search-results-link">
												<?php echo esc_html( get_the_title( $card_post ) ); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php
								break;
						}
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="search-results-empty">
				<?php esc_html_e( 'По вашему запросу ничего не найдено.', 'tolstenko-theme' ); ?>
			</p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();