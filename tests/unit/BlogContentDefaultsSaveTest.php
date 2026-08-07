<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Test_State;

/**
 * @covers ::tolstenko_save_blog_content_defaults_from_request
 * @covers ::tolstenko_blog_content_defaults_schema
 */
final class BlogContentDefaultsSaveTest extends TestCase {

	public function test_schema_covers_every_content_block_slug(): void {
		$schema = tolstenko_blog_content_defaults_schema();

		foreach ( tolstenko_get_blog_content_block_slugs() as $slug ) {
			$this->assertArrayHasKey( str_replace( '-', '_', $slug ), $schema );
		}
	}

	public function test_empty_request_writes_normalized_defaults(): void {
		tolstenko_save_blog_content_defaults_from_request();

		$saved = WP_Test_State::$options['tolstenko_block_defaults'];

		$this->assertSame( array( 'image' => 0 ), $saved['blog_large_img'] );
		$this->assertSame( array( 'left' => 0, 'right' => 0 ), $saved['blog_imgs'] );
		$this->assertSame( array( 'items' => array() ), $saved['blog_number_list'] );
		$this->assertSame( array( 'items' => array() ), $saved['blog_warning'] );
		$this->assertSame(
			array( 'use_header' => false, 'header' => array(), 'rows' => array() ),
			$saved['blog_table']
		);
	}

	public function test_unrelated_saved_keys_are_preserved(): void {
		WP_Test_State::$options['tolstenko_block_defaults'] = array( 'vacancy_content' => array( 'sidebar_name' => 'Иван' ) );

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertSame(
			array( 'sidebar_name' => 'Иван' ),
			WP_Test_State::$options['tolstenko_block_defaults']['vacancy_content']
		);
	}

	public function test_broken_saved_option_is_replaced_by_array(): void {
		WP_Test_State::$options['tolstenko_block_defaults'] = 'not-an-array';

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertIsArray( WP_Test_State::$options['tolstenko_block_defaults'] );
	}

	public function test_attachment_ids_are_cast_to_int(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_large_img' => array( 'image' => '15' ),
			'blog_imgs'      => array( 'left' => '3abc', 'right' => '' ),
			'blog_video'     => array( 'preview' => '9' ),
		);

		tolstenko_save_blog_content_defaults_from_request();
		$saved = WP_Test_State::$options['tolstenko_block_defaults'];

		$this->assertSame( 15, $saved['blog_large_img']['image'] );
		$this->assertSame( array( 'left' => 3, 'right' => 0 ), $saved['blog_imgs'] );
		$this->assertSame( 9, $saved['blog_video']['preview'] );
	}

	public function test_video_iframe_keeps_iframe_and_drops_scripts(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_video' => array(
				'url'    => 'https://rutube.ru/video/abc/',
				'iframe' => '<iframe src="https://rutube.ru/play/embed/abc/"></iframe><script>alert(1)</script>',
			),
		);

		tolstenko_save_blog_content_defaults_from_request();
		$video = WP_Test_State::$options['tolstenko_block_defaults']['blog_video'];

		$this->assertSame( 'https://rutube.ru/video/abc/', $video['url'] );
		$this->assertStringContainsString( '<iframe', $video['iframe'] );
		$this->assertStringNotContainsString( '<script', $video['iframe'] );
	}

	public function test_blockquote_fields_are_sanitized(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_blockquote' => array(
				'text'         => '<strong>Цитата</strong><script>x</script>',
				'link'         => 'javascript:alert(1)',
				'show_author'  => '1',
				'image'        => '4',
				'author'       => "Иван\nИванов",
				'author_under' => ' СЕО ',
				'btn_text'     => '<b>Кнопка</b>',
				'btn_url'      => 'https://example.test/x',
			),
		);

		tolstenko_save_blog_content_defaults_from_request();
		$bq = WP_Test_State::$options['tolstenko_block_defaults']['blog_blockquote'];

		$this->assertStringContainsString( 'Цитата', $bq['text'] );
		$this->assertStringNotContainsString( '<script', $bq['text'] );
		$this->assertSame( '', $bq['link'], 'javascript: ссылки не сохраняем.' );
		$this->assertTrue( $bq['show_author'] );
		$this->assertSame( 4, $bq['image'] );
		$this->assertSame( 'Иван Иванов', $bq['author'] );
		$this->assertSame( 'СЕО', $bq['author_under'] );
		$this->assertSame( 'Кнопка', $bq['btn_text'] );
		$this->assertSame( 'https://example.test/x', $bq['btn_url'] );
	}

	public function test_blockquote_show_author_defaults_to_false(): void {
		$_POST['tolstenko_block_defaults'] = array( 'blog_blockquote' => array( 'show_author' => '0' ) );

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertFalse( WP_Test_State::$options['tolstenko_block_defaults']['blog_blockquote']['show_author'] );
	}

	public function test_number_list_drops_empty_rows_and_accepts_plain_strings(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_number_list' => array(
				'items' => array(
					array( 'text' => '  Первый  ' ),
					array( 'text' => '   ' ),
					array( 'other' => 'нет текста' ),
					'Строкой',
					'',
				),
			),
		);

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertSame(
			array( array( 'text' => 'Первый' ), array( 'text' => 'Строкой' ) ),
			WP_Test_State::$options['tolstenko_block_defaults']['blog_number_list']['items']
		);
	}

	public function test_warning_items_validate_type_and_skip_invalid_rows(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_warning' => array(
				'items' => array(
					array( 'type' => 'pin', 'text' => 'Закреплено', 'icon' => '8' ),
					array( 'type' => 'weird', 'text' => 'Неизвестный тип' ),
					array( 'text' => 'Без типа' ),
					array( 'type' => 'ide', 'text' => '   ' ),
					'not-an-array',
				),
			),
		);

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertSame(
			array(
				array( 'type' => 'pin', 'text' => 'Закреплено', 'icon' => 8 ),
				array( 'type' => 'warn', 'text' => 'Неизвестный тип', 'icon' => 0 ),
				array( 'type' => 'warn', 'text' => 'Без типа', 'icon' => 0 ),
			),
			WP_Test_State::$options['tolstenko_block_defaults']['blog_warning']['items']
		);
	}

	public function test_table_is_parsed_from_pipe_separated_raw_input(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_table' => array(
				'use_header' => '1',
				'header_raw' => ' Ключ | Значение ',
				'rows_raw'   => "a | b\r\n\n  \nc|d\n",
			),
		);

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertSame(
			array(
				'use_header' => true,
				'header'     => array( 'Ключ', 'Значение' ),
				'rows'       => array(
					array( 'cells' => array( 'a', 'b' ) ),
					array( 'cells' => array( 'c', 'd' ) ),
				),
			),
			WP_Test_State::$options['tolstenko_block_defaults']['blog_table']
		);
	}

	public function test_seo_fields_are_sanitized(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_seo' => array(
				'title'   => '<span class="accent">Аудит</span>',
				'btn'     => "<b>Получить</b>\nконсультацию",
				'btn_url' => 'https://example.test/audit',
			),
		);

		tolstenko_save_blog_content_defaults_from_request();
		$seo = WP_Test_State::$options['tolstenko_block_defaults']['blog_seo'];

		$this->assertStringContainsString( 'Аудит', $seo['title'] );
		$this->assertSame( 'Получить консультацию', $seo['btn'] );
		$this->assertSame( 'https://example.test/audit', $seo['btn_url'] );
	}

	public function test_slashes_from_request_are_removed(): void {
		$_POST['tolstenko_block_defaults'] = array(
			'blog_blockquote' => array( 'author' => "Иван \\\"Ivan\\\" Иванов" ),
		);

		tolstenko_save_blog_content_defaults_from_request();

		$this->assertSame(
			'Иван "Ivan" Иванов',
			WP_Test_State::$options['tolstenko_block_defaults']['blog_blockquote']['author']
		);
	}
}
