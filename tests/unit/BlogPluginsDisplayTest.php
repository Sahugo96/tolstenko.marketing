<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Test_State;

/**
 * @covers ::tolstenko_get_post_views_count
 * @covers ::tolstenko_pvc_option_add_blog_type
 * @covers ::tolstenko_hide_blog_post_views_auto_display
 * @covers ::tolstenko_get_reading_time_text
 */
final class BlogPluginsDisplayTest extends TestCase {

	public function test_views_count_is_zero_without_plugin(): void {
		$this->assertSame( 0, tolstenko_get_post_views_count( 5 ) );
	}

	public function test_views_count_is_zero_outside_post_context(): void {
		$this->assertSame( 0, tolstenko_get_post_views_count() );
	}

	public function test_pvc_option_adds_blog_post_type_once(): void {
		$opts = tolstenko_pvc_option_add_blog_type( array( 'post_types_count' => array( 'post' ) ) );
		$this->assertSame( array( 'post', 'blog' ), $opts['post_types_count'] );

		$this->assertSame( $opts, tolstenko_pvc_option_add_blog_type( $opts ) );
	}

	public function test_pvc_option_creates_missing_or_invalid_type_list(): void {
		$this->assertSame(
			array( 'other' => 1, 'post_types_count' => array( 'blog' ) ),
			tolstenko_pvc_option_add_blog_type( array( 'other' => 1 ) )
		);
		$this->assertSame(
			array( 'post_types_count' => array( 'blog' ) ),
			tolstenko_pvc_option_add_blog_type( array( 'post_types_count' => 'broken' ) )
		);
	}

	public function test_pvc_option_passes_through_non_arrays(): void {
		$this->assertFalse( tolstenko_pvc_option_add_blog_type( false ) );
		$this->assertSame( 'x', tolstenko_pvc_option_add_blog_type( 'x' ) );
	}

	public function test_auto_views_display_hidden_on_content_body_singular(): void {
		$this->assertTrue( tolstenko_hide_blog_post_views_auto_display( true ) );

		WP_Test_State::$singular = array( 'blog' );
		$this->assertFalse( tolstenko_hide_blog_post_views_auto_display( true ) );

		WP_Test_State::$singular = array( 'actions' );
		$this->assertFalse( tolstenko_hide_blog_post_views_auto_display( true ) );
	}

	public function test_reading_time_text_is_empty_without_plugin_shortcode(): void {
		$this->assertSame( '', tolstenko_get_reading_time_text( 7 ) );
	}

	public function test_reading_time_text_is_empty_outside_post_context(): void {
		WP_Test_State::$shortcodes['rt_reading_time'] = '5 мин';

		$this->assertSame( '', tolstenko_get_reading_time_text() );
	}

	public function test_reading_time_text_is_normalized(): void {
		WP_Test_State::$shortcodes['rt_reading_time'] = "<span> 5 &nbsp;\n мин </span>";

		$this->assertSame( '5 мин', tolstenko_get_reading_time_text( 7 ) );
	}

	public function test_reading_time_text_uses_current_post(): void {
		WP_Test_State::$shortcodes['rt_reading_time'] = '3 мин';
		WP_Test_State::$current_post_id               = 9;

		$this->assertSame( '3 мин', tolstenko_get_reading_time_text() );
	}
}
