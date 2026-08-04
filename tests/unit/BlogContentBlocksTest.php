<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Test_State;

/**
 * @covers ::tolstenko_get_blog_content_block_slugs
 * @covers ::tolstenko_get_blog_content_block_names
 * @covers ::tolstenko_blog_video_iframe_allowed_html
 * @covers ::tolstenko_get_content_body_post_types
 * @covers ::tolstenko_is_content_body_post_type
 * @covers ::tolstenko_is_content_body_singular
 * @covers ::tolstenko_get_single_content_bem
 * @covers ::tolstenko_adapt_single_content_classes
 * @covers ::tolstenko_get_blog_content_only_block_names
 * @covers ::tolstenko_get_blog_editor_allowed_blocks
 * @covers ::tolstenko_get_blog_theme_blocks_catalog
 * @covers ::tolstenko_get_blog_core_writing_blocks
 * @covers ::tolstenko_get_blog_content_defaults
 */
final class BlogContentBlocksTest extends TestCase {

	public function test_block_names_are_slugs_in_theme_namespace(): void {
		$slugs = tolstenko_get_blog_content_block_slugs();
		$names = tolstenko_get_blog_content_block_names();

		$this->assertNotEmpty( $slugs );
		$this->assertCount( count( $slugs ), $names );
		$this->assertSame(
			array_map(
				static function ( string $slug ): string {
					return 'tolstenko/' . $slug;
				},
				$slugs
			),
			$names
		);
	}

	public function test_video_iframe_allowed_html_permits_iframe_src(): void {
		$allowed = tolstenko_blog_video_iframe_allowed_html();

		$this->assertArrayHasKey( 'iframe', $allowed );
		$this->assertTrue( $allowed['iframe']['src'] );
		$this->assertTrue( $allowed['iframe']['allowfullscreen'] );
		$this->assertArrayNotHasKey( 'script', $allowed );
	}

	public function test_content_body_post_types(): void {
		$this->assertSame( array( 'blog', 'actions' ), tolstenko_get_content_body_post_types() );
		$this->assertTrue( tolstenko_is_content_body_post_type( 'blog' ) );
		$this->assertTrue( tolstenko_is_content_body_post_type( 'actions' ) );
		$this->assertFalse( tolstenko_is_content_body_post_type( 'page' ) );
	}

	public function test_content_body_post_type_uses_current_post_when_null(): void {
		$this->makePost( 10, 'actions' );
		WP_Test_State::$current_post_id = 10;
		$this->assertTrue( tolstenko_is_content_body_post_type() );

		$this->makePost( 11, 'page' );
		WP_Test_State::$current_post_id = 11;
		$this->assertFalse( tolstenko_is_content_body_post_type() );
	}

	public function test_content_body_singular_and_bem_prefix(): void {
		$this->assertFalse( tolstenko_is_content_body_singular() );
		$this->assertSame( 'single-blog', tolstenko_get_single_content_bem() );

		WP_Test_State::$singular = array( 'actions' );
		$this->assertTrue( tolstenko_is_content_body_singular() );
		$this->assertSame( 'single-actions', tolstenko_get_single_content_bem() );

		WP_Test_State::$singular = array( 'blog' );
		$this->assertTrue( tolstenko_is_content_body_singular() );
		$this->assertSame( 'single-blog', tolstenko_get_single_content_bem() );
	}

	public function test_adapt_single_content_classes_only_on_actions_singular(): void {
		$html = '<div class="single-blog__text single-blog__text--big">x</div>';

		$this->assertSame( $html, tolstenko_adapt_single_content_classes( $html ) );

		WP_Test_State::$singular = array( 'actions' );
		$this->assertSame(
			'<div class="single-actions__text single-actions__text--big">x</div>',
			tolstenko_adapt_single_content_classes( $html )
		);
		$this->assertSame( '', tolstenko_adapt_single_content_classes( '' ) );
	}

	public function test_content_only_block_names_include_koritan_aliases(): void {
		$names = tolstenko_get_blog_content_only_block_names();

		$this->assertContains( 'tolstenko/blog-video', $names );
		$this->assertContains( 'koritan/blog-video', $names );
		$this->assertNotContains( 'tolstenko/consultation-tg', $names, 'consultation-* доступны и вне блога.' );
		$this->assertSame( array_unique( $names ), $names );
	}

	public function test_editor_allowed_blocks_merge_core_theme_and_aliases(): void {
		$allowed = tolstenko_get_blog_editor_allowed_blocks();

		$this->assertContains( 'core/paragraph', $allowed );
		$this->assertContains( 'tolstenko/blog-table', $allowed );
		$this->assertContains( 'koritan/blog-table', $allowed );
		$this->assertNotContains( 'core/post-title', $allowed );
		$this->assertSame( array_unique( $allowed ), $allowed );
	}

	public function test_editor_allowed_blocks_respect_core_blocks_filter(): void {
		add_filter(
			'tolstenko_blog_core_writing_blocks',
			static function ( array $blocks ): array {
				return array( 'core/paragraph' );
			}
		);

		$allowed = tolstenko_get_blog_editor_allowed_blocks();

		$this->assertContains( 'core/paragraph', $allowed );
		$this->assertNotContains( 'core/heading', $allowed );
	}

	public function test_theme_blocks_catalog_is_filterable(): void {
		add_filter(
			'tolstenko_blog_theme_blocks_catalog',
			static function ( array $catalog ): array {
				$catalog['tolstenko/custom'] = 'Custom';
				return $catalog;
			}
		);

		$catalog = tolstenko_get_blog_theme_blocks_catalog();

		$this->assertSame( 'Custom', $catalog['tolstenko/custom'] );
		$this->assertArrayHasKey( 'tolstenko/blog-seo', $catalog );
		$this->assertContains( 'koritan/custom', tolstenko_get_blog_editor_allowed_blocks() );
	}

	public function test_content_defaults_return_schema_defaults_without_saved_values(): void {
		$this->assertSame(
			array( 'title' => 'Нужна помощь с продвижением?', 'btn' => 'Получить консультацию', 'btn_url' => '' ),
			tolstenko_get_blog_content_defaults( 'blog_seo' )
		);
		$this->assertSame( array(), tolstenko_get_blog_content_defaults( 'unknown_block' ) );
	}

	public function test_content_defaults_merge_saved_over_schema(): void {
		WP_Test_State::$options['tolstenko_block_defaults'] = array(
			'blog_seo' => array( 'btn_url' => 'https://example.test/audit' ),
		);

		$defaults = tolstenko_get_blog_content_defaults( 'blog_seo' );

		$this->assertSame( 'https://example.test/audit', $defaults['btn_url'] );
		$this->assertSame( 'Нужна помощь с продвижением?', $defaults['title'] );
	}
}
