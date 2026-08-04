<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Test_State;

/**
 * @covers ::tolstenko_get_image_attrs
 * @covers ::tolstenko_get_curated_blog_comments_count
 * @covers ::tolstenko_get_blog_comments_count
 * @covers ::tolstenko_get_single_blog_director
 * @covers ::tolstenko_prepare_blog_toc
 */
final class BlogHelpersTest extends TestCase {

	public function test_image_attrs_from_attachment_id(): void {
		$this->makeAttachment( 12, 'https://example.test/pic.jpg', 'Альт' );

		$attrs = tolstenko_get_image_attrs( 12 );

		$this->assertSame(
			array(
				'id'     => 12,
				'url'    => 'https://example.test/pic.jpg',
				'srcset' => 'https://example.test/pic.jpg 1x',
				'sizes'  => '(max-width: 100vw) 100vw',
				'alt'    => 'Альт',
			),
			$attrs
		);
	}

	public function test_image_attrs_from_acf_array_falls_back_to_array_alt(): void {
		$this->makeAttachment( 7, 'https://example.test/acf.jpg' );

		$attrs = tolstenko_get_image_attrs( array( 'ID' => '7', 'alt' => 'ACF alt' ) );

		$this->assertSame( 7, $attrs['id'] );
		$this->assertSame( 'ACF alt', $attrs['alt'] );
	}

	public function test_image_attrs_meta_alt_wins_over_acf_alt(): void {
		$this->makeAttachment( 7, 'https://example.test/acf.jpg', 'Meta alt' );

		$attrs = tolstenko_get_image_attrs( array( 'ID' => 7, 'alt' => 'ACF alt' ) );

		$this->assertSame( 'Meta alt', $attrs['alt'] );
	}

	/**
	 * @dataProvider provideEmptyImages
	 *
	 * @param mixed $image Image value.
	 */
	public function test_image_attrs_returns_null_without_resolvable_id( $image ): void {
		$this->assertNull( tolstenko_get_image_attrs( $image ) );
	}

	public static function provideEmptyImages(): array {
		return array(
			'null'          => array( null ),
			'empty string'  => array( '' ),
			'zero'          => array( 0 ),
			'array no ID'   => array( array( 'url' => 'https://example.test/x.jpg' ) ),
			'unknown id'    => array( 999 ),
		);
	}

	public function test_curated_comments_count_includes_replies(): void {
		$this->setMeta(
			5,
			'blog_comments',
			array(
				array( 'text' => 'a', 'replies' => array( array( 'text' => 'r1' ), array( 'text' => 'r2' ) ) ),
				array( 'text' => 'b' ),
				array( 'text' => 'c', 'replies' => 'not-an-array' ),
			)
		);

		$this->assertSame( 5, tolstenko_get_curated_blog_comments_count( 5 ) );
	}

	public function test_curated_comments_count_is_zero_without_meta(): void {
		$this->assertSame( 0, tolstenko_get_curated_blog_comments_count( 5 ) );
		$this->setMeta( 6, 'blog_comments', 'garbage' );
		$this->assertSame( 0, tolstenko_get_curated_blog_comments_count( 6 ) );
	}

	public function test_blog_comments_count_uses_current_post_when_no_id(): void {
		WP_Test_State::$current_post_id = 42;
		$this->setMeta( 42, 'blog_comments', array( array( 'text' => 'a' ) ) );

		$this->assertSame( 1, tolstenko_get_blog_comments_count() );
	}

	public function test_blog_comments_count_returns_zero_outside_post_context(): void {
		$this->assertSame( 0, tolstenko_get_blog_comments_count() );
	}

	public function test_single_blog_director_prefers_job_title_from_authors_list(): void {
		$this->setMeta( 3, 'blog_author', '1' );
		$this->setMeta( 3, 'single-blog_quest', '0' );
		WP_Test_State::$options['tolstenko_blog_authors'] = array(
			array( 'name' => 'Первый' ),
			array(
				'photo'       => '55',
				'name'        => '  Иван  ',
				'job_title'   => ' CEO ',
				'position'    => 'Директор',
				'description' => ' Описание ',
			),
		);

		$director = tolstenko_get_single_blog_director( 3 );

		$this->assertSame(
			array(
				'photo'       => 55,
				'name'        => 'Иван',
				'title'       => 'CEO',
				'position'    => 'Директор',
				'description' => 'Описание',
				'show_quest'  => false,
			),
			$director
		);
	}

	public function test_single_blog_director_falls_back_to_position_as_title(): void {
		$this->setMeta( 3, 'blog_author', 0 );
		WP_Test_State::$options['tolstenko_blog_authors'] = array(
			array( 'name' => 'Иван', 'position' => 'Директор' ),
		);

		$director = tolstenko_get_single_blog_director( 3 );

		$this->assertSame( 'Директор', $director['title'] );
		$this->assertNull( $director['photo'] );
		$this->assertTrue( $director['show_quest'], 'Пустой single-blog_quest = показывать блок.' );
	}

	public function test_single_blog_director_falls_back_to_block_defaults(): void {
		$this->setMeta( 3, 'blog_author', '' );
		WP_Test_State::$options['tolstenko_block_defaults'] = array(
			'vacancy_content' => array(
				'sidebar_photo' => '77',
				'sidebar_name'  => ' Сайдбар ',
				'sidebar_text'  => ' Текст ',
			),
		);

		$director = tolstenko_get_single_blog_director( 3 );

		$this->assertSame(
			array(
				'photo'       => 77,
				'name'        => 'Сайдбар',
				'title'       => '',
				'position'    => '',
				'description' => 'Текст',
				'show_quest'  => true,
			),
			$director
		);
	}

	public function test_toc_uses_h2_and_adds_ids(): void {
		$html = '<h2>First section</h2><p>текст</p><h3>Skipped</h3><h2><span>Second</span> section</h2>';

		$result = tolstenko_prepare_blog_toc( $html );

		$this->assertSame(
			array(
				array( 'id' => 'first-section', 'text' => 'First section', 'level' => 2 ),
				array( 'id' => 'second-section', 'text' => 'Second section', 'level' => 2 ),
			),
			$result['items']
		);
		$this->assertStringContainsString( '<h2 id="first-section">First section</h2>', $result['html'] );
		$this->assertStringContainsString( '<h3>Skipped</h3>', $result['html'], 'h3 не трогаем, если есть h2.' );
	}

	public function test_toc_falls_back_to_h3_when_no_h2(): void {
		$result = tolstenko_prepare_blog_toc( '<h3>Only h3</h3>' );

		$this->assertCount( 1, $result['items'] );
		$this->assertSame( 3, $result['items'][0]['level'] );
		$this->assertStringContainsString( 'id="only-h3"', $result['html'] );
	}

	public function test_toc_makes_duplicate_ids_unique(): void {
		$result = tolstenko_prepare_blog_toc( '<h2>Section</h2><h2>Section</h2><h2>Section</h2>' );

		$this->assertSame(
			array( 'section', 'section-2', 'section-3' ),
			array_column( $result['items'], 'id' )
		);
	}

	public function test_toc_keeps_existing_heading_id(): void {
		$result = tolstenko_prepare_blog_toc( '<h2 id="custom" class="x">Section</h2>' );

		$this->assertSame( 'section', $result['items'][0]['id'] );
		$this->assertSame( '<h2 id="custom" class="x">Section</h2>', $result['html'] );
	}

	public function test_toc_skips_empty_headings_and_untitled_markup(): void {
		$result = tolstenko_prepare_blog_toc( '<h2>   </h2><h2>!!!</h2>' );

		$this->assertSame( array( 'section' ), array_column( $result['items'], 'id' ) );
		$this->assertStringContainsString( '<h2>   </h2>', $result['html'] );
	}

	public function test_toc_without_headings_returns_html_unchanged(): void {
		$this->assertSame(
			array( 'html' => '<p>Просто текст</p>', 'items' => array() ),
			tolstenko_prepare_blog_toc( '<p>Просто текст</p>' )
		);
		$this->assertSame( array( 'html' => '', 'items' => array() ), tolstenko_prepare_blog_toc( '' ) );
	}
}
