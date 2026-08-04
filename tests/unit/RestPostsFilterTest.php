<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_Test_State;

/**
 * @covers ::tolstenko_rest_filter_posts
 * @covers ::tolstenko_register_posts_filter_rest_route
 * @covers ::tolstenko_get_posts_filter_card_renderers
 * @covers ::tolstenko_render_filter_pagination_html
 * @covers ::tolstenko_render_filtered_posts_html
 * @covers ::tolstenko_render_filtered_posts_payload
 * @covers ::tolstenko_render_blog_archive_layout_payload
 * @covers ::tolstenko_get_blog_archive_sidebar_data
 */
final class RestPostsFilterTest extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		WP_Test_State::$post_types = array( 'vacancy', 'case', 'service', 'blog' );
		WP_Test_State::$taxonomies = array( 'blog_cat', 'service_category' );
	}

	public function test_card_renderers_are_callable_and_filterable(): void {
		$renderers = tolstenko_get_posts_filter_card_renderers();

		$this->assertArrayHasKey( 'vacancy', $renderers );
		$this->assertArrayHasKey( 'blog_tile', $renderers );
		foreach ( $renderers as $key => $callback ) {
			$this->assertIsCallable( $callback, "Рендерер {$key} должен быть вызываемым." );
		}

		add_filter(
			'tolstenko_posts_filter_card_renderers',
			static function ( array $list ): array {
				$list['custom'] = '__return_empty_string';
				return $list;
			}
		);
		$this->assertArrayHasKey( 'custom', tolstenko_get_posts_filter_card_renderers() );
	}

	public function test_pagination_is_empty_for_single_page(): void {
		$this->assertSame( '', tolstenko_render_filter_pagination_html( 0 ) );
		$this->assertSame( '', tolstenko_render_filter_pagination_html( 1, 1 ) );
	}

	public function test_pagination_first_page_has_next_but_no_prev(): void {
		$html = tolstenko_render_filter_pagination_html( 3, 1 );

		$this->assertStringNotContainsString( 'prev page-numbers', $html );
		$this->assertStringContainsString( 'next page-numbers', $html );
		$this->assertStringContainsString( '<span class="page-numbers current" aria-current="page">1</span>', $html );
		$this->assertStringContainsString( 'data-tolstenko-page="2"', $html );
	}

	public function test_pagination_last_page_has_prev_but_no_next(): void {
		$html = tolstenko_render_filter_pagination_html( 3, 3 );

		$this->assertStringContainsString( 'prev page-numbers', $html );
		$this->assertStringNotContainsString( 'next page-numbers', $html );
		$this->assertStringContainsString( '<span class="page-numbers current" aria-current="page">3</span>', $html );
	}

	public function test_pagination_uses_dots_around_current_range(): void {
		$html = tolstenko_render_filter_pagination_html( 10, 5 );

		$this->assertSame( 2, substr_count( $html, 'page-numbers dots' ) );
		foreach ( array( 1, 4, 5, 6, 10 ) as $page ) {
			$this->assertStringContainsString( '>' . $page . '<', $html );
		}
		$this->assertStringNotContainsString( '>7<', $html );
	}

	public function test_pagination_clamps_current_page(): void {
		$html = tolstenko_render_filter_pagination_html( 2, 0 );

		$this->assertStringContainsString( '<span class="page-numbers current" aria-current="page">1</span>', $html );
		$this->assertStringNotContainsString( 'prev page-numbers', $html );
	}

	public function test_payload_is_empty_for_unknown_post_type(): void {
		$this->assertSame(
			array( 'html' => '', 'pagination' => '', 'max_pages' => 0, 'page' => 1 ),
			tolstenko_render_filtered_posts_payload( array( 'post_type' => 'nope', 'card' => 'vacancy' ) )
		);
		$this->assertSame(
			'',
			tolstenko_render_filtered_posts_html( array( 'card' => 'vacancy' ) )
		);
	}

	public function test_payload_is_empty_for_unknown_card(): void {
		$this->makePost( 1, 'vacancy' );

		$this->assertSame(
			'',
			tolstenko_render_filtered_posts_html( array( 'post_type' => 'vacancy', 'card' => 'nope' ) )
		);
		$this->assertSame(
			'',
			tolstenko_render_filtered_posts_html( array( 'post_type' => 'vacancy' ) )
		);
	}

	public function test_payload_renders_one_card_per_post(): void {
		$this->makePost( 1, 'vacancy' );
		$this->makePost( 2, 'vacancy' );
		$this->makePost( 3, 'case' );

		$payload = tolstenko_render_filtered_posts_payload(
			array( 'post_type' => 'vacancy', 'card' => 'vacancy' )
		);

		$this->assertSame( 2, substr_count( $payload['html'], 'template-parts/blocks/vacancy-card' ) );
		$this->assertSame( 0, $payload['max_pages'], 'Без paginate пагинации нет.' );
		$this->assertSame( '', $payload['pagination'] );
		$this->assertSame( 1, $payload['page'] );
		$this->assertSame(
			'vacancies-section__item br-30 fade-in-element',
			WP_Test_State::$query_vars['tolstenko_vacancy_card_class']
		);
	}

	public function test_payload_keeps_post_ids_order_and_ignores_garbage_ids(): void {
		foreach ( array( 1, 2, 3 ) as $id ) {
			$this->makePost( $id, 'service' );
		}

		$payload = tolstenko_render_filtered_posts_payload(
			array( 'post_type' => 'service', 'card' => 'service', 'post_ids' => '3, 1, 3, 0, x' )
		);

		$this->assertSame( 2, substr_count( $payload['html'], 'service-card' ) );
	}

	public function test_payload_excludes_ids(): void {
		foreach ( array( 1, 2, 3 ) as $id ) {
			$this->makePost( $id, 'case' );
		}

		$payload = tolstenko_render_filtered_posts_payload(
			array( 'post_type' => 'case', 'card' => 'case', 'exclude' => array( '2', 3 ) )
		);

		$this->assertSame( 1, substr_count( $payload['html'], 'case-card' ) );
	}

	public function test_payload_with_pagination_reports_max_pages(): void {
		foreach ( range( 1, 5 ) as $id ) {
			$this->makePost( $id, 'blog' );
		}

		$payload = tolstenko_render_filtered_posts_payload(
			array(
				'post_type'      => 'blog',
				'card'           => 'blog_slider',
				'posts_per_page' => 2,
				'paginate'       => true,
				'paged'          => 2,
			)
		);

		$this->assertSame( 3, $payload['max_pages'] );
		$this->assertSame( 2, $payload['page'] );
		$this->assertSame( 2, substr_count( $payload['html'], 'blog-card' ) );
		$this->assertStringContainsString( 'prev page-numbers', $payload['pagination'] );
	}

	public function test_payload_sets_selected_category_query_var(): void {
		$this->makePost( 1, 'service' );

		tolstenko_render_filtered_posts_payload(
			array(
				'post_type' => 'service',
				'card'      => 'service_tile',
				'taxonomy'  => 'service_category',
				'term'      => 'SEO',
			)
		);
		$this->assertSame( 'seo', WP_Test_State::$query_vars['tolstenko_service_card_selected_category'] );

		tolstenko_render_filtered_posts_payload(
			array( 'post_type' => 'service', 'card' => 'service_tile' )
		);
		$this->assertSame( '', WP_Test_State::$query_vars['tolstenko_service_card_selected_category'] );
	}

	public function test_blog_tile_card_marks_first_card_with_date(): void {
		foreach ( range( 1, 3 ) as $id ) {
			$this->makePost( $id, 'blog' );
		}

		$payload = tolstenko_render_filtered_posts_payload(
			array( 'post_type' => 'blog', 'card' => 'blog_tile', 'posts_per_page' => 2, 'paged' => 1 )
		);

		$this->assertStringContainsString( '<div class="blog-section__items">', $payload['html'] );
		$this->assertStringEndsWith( '</div>', $payload['html'] );
		$this->assertSame( 2, substr_count( $payload['html'], 'template-parts/blocks/blog-card' ) );
		$this->assertFalse(
			WP_Test_State::$query_vars['tolstenko_blog_card_show_date'],
			'Дату показываем только у первой карточки.'
		);
		$this->assertSame( 2, $payload['max_pages'] );
	}

	public function test_blog_tile_layout_is_empty_without_posts(): void {
		$this->assertSame(
			array( 'html' => '', 'pagination' => '', 'max_pages' => 0, 'page' => 1 ),
			tolstenko_render_blog_archive_layout_payload( array( 'post_type' => 'blog' ) )
		);
		$this->assertSame(
			array( 'html' => '', 'pagination' => '', 'max_pages' => 0, 'page' => 1 ),
			tolstenko_render_blog_archive_layout_payload( array( 'post_type' => 'nope' ) )
		);
	}

	public function test_blog_tile_layout_renders_sidebar_from_block_defaults(): void {
		$this->makePost( 1, 'blog' );
		$this->makeAttachment( 12, 'https://example.test/director.jpg', 'Фото директора' );
		WP_Test_State::$options['tolstenko_block_defaults'] = array(
			'blog_section_tile' => array( 'sidebar_photo' => 12, 'sidebar_name' => 'Иван Иванов' ),
		);

		$payload = tolstenko_render_blog_archive_layout_payload( array( 'post_type' => 'blog' ) );

		$this->assertStringContainsString( 'blog-section__right-wrapper', $payload['html'] );
		$this->assertStringContainsString( 'https://example.test/director.jpg', $payload['html'] );
		$this->assertStringContainsString( 'Иван Иванов', $payload['html'] );
	}

	public function test_sidebar_data_falls_back_to_vacancy_defaults(): void {
		$this->makeAttachment( 21, 'https://example.test/vac.jpg' );
		WP_Test_State::$options['tolstenko_block_defaults'] = array(
			'vacancy_content' => array(
				'sidebar_photo'   => 21,
				'sidebar_name'    => ' Пётр ',
				'sidebar_text'    => ' <p>Текст вакансии</p> ',
				'sidebar_btn_url' => '',
			),
		);

		$data = tolstenko_get_blog_archive_sidebar_data();

		$this->assertSame( 'https://example.test/vac.jpg', $data['photo_url'] );
		$this->assertSame( 'Пётр', $data['name'] );
		$this->assertSame( 'Пётр', $data['photo_alt'], 'Без alt используем имя.' );
		$this->assertSame( ' <p>Текст вакансии</p> ', $data['text'] );
		$this->assertSame( '#modal', $data['btn_url'], 'Пустой URL кнопки ведёт в модалку.' );
		$this->assertFalse( $data['has_socials'] );
	}

	public function test_sidebar_data_defaults_to_audit_button(): void {
		$data = tolstenko_get_blog_archive_sidebar_data();

		$this->assertSame( 'Бесплатный аудит', $data['btn'] );
		$this->assertSame( '', $data['photo_url'] );
		$this->assertSame( '', $data['name'] );
	}

	public function test_rest_route_declares_required_params(): void {
		tolstenko_register_posts_filter_rest_route();

		$route = WP_Test_State::$rest_routes['tolstenko/v1/filter-posts'];

		$this->assertSame( 'GET', $route['methods'] );
		$this->assertSame( 'tolstenko_rest_filter_posts', $route['callback'] );
		$this->assertTrue( $route['args']['post_type']['required'] );
		$this->assertTrue( $route['args']['card']['required'] );
		$this->assertSame( 'seo', call_user_func( $route['args']['term']['sanitize_callback'], 'SEO' ) );
		$this->assertSame( 1, call_user_func( $route['args']['paged']['sanitize_callback'], -5 ) );
	}

	public function test_rest_callback_rejects_post_type_outside_allowlist(): void {
		$response = tolstenko_rest_filter_posts(
			new WP_REST_Request( array( 'post_type' => 'page', 'card' => 'vacancy' ) )
		);

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertSame( 'tolstenko_filter_forbidden_type', $response->get_error_code() );
		$this->assertSame( 400, $response->data['status'] );
	}

	public function test_rest_callback_respects_allowed_post_types_filter(): void {
		WP_Test_State::$post_types[] = 'page';
		$this->makePost( 1, 'page' );
		add_filter(
			'tolstenko_posts_filter_allowed_post_types',
			static function ( array $types ): array {
				$types[] = 'page';
				return $types;
			}
		);

		$response = tolstenko_rest_filter_posts(
			new WP_REST_Request( array( 'post_type' => 'page', 'card' => 'blog_slider' ) )
		);

		$this->assertInstanceOf( WP_REST_Response::class, $response );
	}

	public function test_rest_callback_rejects_unknown_card(): void {
		$response = tolstenko_rest_filter_posts(
			new WP_REST_Request( array( 'post_type' => 'blog', 'card' => 'nope' ) )
		);

		$this->assertInstanceOf( WP_Error::class, $response );
		$this->assertSame( 'tolstenko_filter_unknown_card', $response->get_error_code() );
	}

	public function test_rest_callback_returns_html_and_pagination(): void {
		foreach ( range( 1, 3 ) as $id ) {
			$this->makePost( $id, 'blog' );
		}

		$response = tolstenko_rest_filter_posts(
			new WP_REST_Request(
				array(
					'post_type'      => 'blog',
					'card'           => 'blog_slider',
					'posts_per_page' => 2,
					'paged'          => 1,
					'paginate'       => true,
				)
			)
		);

		$this->assertInstanceOf( WP_REST_Response::class, $response );
		$data = $response->get_data();
		$this->assertSame( array( 'html', 'pagination', 'max_pages', 'page' ), array_keys( $data ) );
		$this->assertSame( 2, $data['max_pages'] );
		$this->assertSame( 1, $data['page'] );
		$this->assertSame( 2, substr_count( $data['html'], 'blog-card' ) );
		$this->assertStringContainsString( 'next page-numbers', $data['pagination'] );
	}
}
