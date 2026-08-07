<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Block_Type_Registry;
use WP_Test_State;
use wpdb;

/**
 * @covers ::tolstenko_remap_koritan_namespace
 * @covers ::tolstenko_remap_parsed_block_name
 * @covers ::tolstenko_run_koritan_rename_migration
 * @covers ::tolstenko_register_koritan_block_aliases
 */
final class KoritanCompatTest extends TestCase {

	/**
	 * @dataProvider provideStrings
	 */
	public function test_remap_strings( string $input, string $expected ): void {
		$this->assertSame( $expected, tolstenko_remap_koritan_namespace( $input ) );
	}

	public static function provideStrings(): array {
		return array(
			'block comment'   => array( '<!-- wp:koritan/blog-seo {"a":1} -->', '<!-- wp:tolstenko/blog-seo {"a":1} -->' ),
			'block name'      => array( 'koritan/blog-video', 'tolstenko/blog-video' ),
			'protected meta'  => array( '_koritan_block_id', '_tolstenko_block_id' ),
			'option prefix'   => array( 'koritan_block_defaults', 'tolstenko_block_defaults' ),
			'several matches' => array( 'koritan/a koritan_b', 'tolstenko/a tolstenko_b' ),
			'untouched'       => array( 'nothing to remap', 'nothing to remap' ),
		);
	}

	public function test_remap_does_not_double_prefix_protected_meta_keys(): void {
		$this->assertSame( '_tolstenko_x', tolstenko_remap_koritan_namespace( '_koritan_x' ) );
	}

	public function test_remap_walks_nested_arrays_including_keys(): void {
		$value = array(
			'koritan_block' => array(
				'name'            => 'koritan/blog-imgs',
				'_koritan_nested' => array( 'koritan_deep' => 'wp:koritan/blog-table' ),
			),
			5               => 'koritan/keep-numeric-key',
		);

		$this->assertSame(
			array(
				'tolstenko_block' => array(
					'name'              => 'tolstenko/blog-imgs',
					'_tolstenko_nested' => array( 'tolstenko_deep' => 'wp:tolstenko/blog-table' ),
				),
				5                 => 'tolstenko/keep-numeric-key',
			),
			tolstenko_remap_koritan_namespace( $value )
		);
	}

	/**
	 * @dataProvider provideScalars
	 *
	 * @param mixed $value Value.
	 */
	public function test_remap_returns_non_string_scalars_unchanged( $value ): void {
		$this->assertSame( $value, tolstenko_remap_koritan_namespace( $value ) );
	}

	public static function provideScalars(): array {
		return array(
			'int'   => array( 42 ),
			'float' => array( 1.5 ),
			'bool'  => array( false ),
			'null'  => array( null ),
		);
	}

	public function test_parsed_block_name_is_remapped(): void {
		$this->assertSame(
			array( 'blockName' => 'tolstenko/blog-seo', 'attrs' => array( 'x' => 1 ) ),
			tolstenko_remap_parsed_block_name( array( 'blockName' => 'koritan/blog-seo', 'attrs' => array( 'x' => 1 ) ) )
		);
	}

	/**
	 * @dataProvider provideUntouchedParsedBlocks
	 */
	public function test_parsed_block_name_untouched( array $parsed ): void {
		$this->assertSame( $parsed, tolstenko_remap_parsed_block_name( $parsed ) );
	}

	public static function provideUntouchedParsedBlocks(): array {
		return array(
			'already tolstenko' => array( array( 'blockName' => 'tolstenko/blog-seo' ) ),
			'core block'        => array( array( 'blockName' => 'core/paragraph' ) ),
			'not a prefix'      => array( array( 'blockName' => 'x/koritan/blog-seo' ) ),
			'null name'         => array( array( 'blockName' => null ) ),
			'missing name'      => array( array( 'attrs' => array() ) ),
		);
	}

	public function test_migration_copies_old_options_and_marks_itself_done(): void {
		WP_Test_State::$options['koritan_block_defaults'] = array( 'koritan_blog_seo' => 'wp:koritan/blog-seo' );
		WP_Test_State::$options['koritan_blog_authors']   = array( array( 'name' => 'Иван' ) );

		tolstenko_run_koritan_rename_migration();

		$this->assertSame(
			array( 'tolstenko_blog_seo' => 'wp:tolstenko/blog-seo' ),
			WP_Test_State::$options['tolstenko_block_defaults']
		);
		$this->assertSame( array( array( 'name' => 'Иван' ) ), WP_Test_State::$options['tolstenko_blog_authors'] );
		$this->assertSame( '2', WP_Test_State::$options['tolstenko_koritan_rename_migrated'] );
	}

	public function test_migration_does_not_overwrite_existing_new_options(): void {
		WP_Test_State::$options['koritan_blog_authors']   = array( array( 'name' => 'Старый' ) );
		WP_Test_State::$options['tolstenko_blog_authors'] = array( array( 'name' => 'Новый' ) );

		tolstenko_run_koritan_rename_migration();

		$this->assertSame( array( array( 'name' => 'Новый' ) ), WP_Test_State::$options['tolstenko_blog_authors'] );
	}

	public function test_migration_remaps_existing_allowed_blocks_option(): void {
		WP_Test_State::$options['koritan_blog_allowed_blocks']    = array( 'koritan/blog-seo' );
		WP_Test_State::$options['tolstenko_blog_allowed_blocks'] = array( 'koritan/blog-video' );

		tolstenko_run_koritan_rename_migration();

		$this->assertSame(
			array( 'tolstenko/blog-video' ),
			WP_Test_State::$options['tolstenko_blog_allowed_blocks']
		);
	}

	public function test_migration_is_skipped_when_already_done(): void {
		WP_Test_State::$options['tolstenko_koritan_rename_migrated'] = '2';
		WP_Test_State::$options['koritan_blog_authors']              = array( array( 'name' => 'Иван' ) );

		tolstenko_run_koritan_rename_migration();

		$this->assertArrayNotHasKey( 'tolstenko_blog_authors', WP_Test_State::$options );
	}

	public function test_migration_moves_post_and_term_meta_to_new_keys(): void {
		/** @var wpdb $wpdb */
		$wpdb                 = $GLOBALS['wpdb'];
		$wpdb->postmeta_rows  = array(
			(object) array( 'post_id' => 5, 'meta_key' => '_koritan_block', 'meta_value' => serialize( array( 'name' => 'koritan/blog-seo' ) ) ),
			(object) array( 'post_id' => 5, 'meta_key' => 'koritan_flag', 'meta_value' => '1' ),
			(object) array( 'post_id' => 5, 'meta_key' => 'unrelated', 'meta_value' => 'x' ),
		);
		$wpdb->termmeta_rows = array(
			(object) array( 'term_id' => 7, 'meta_key' => 'koritan_hero', 'meta_value' => 'koritan/hero' ),
		);

		tolstenko_run_koritan_rename_migration();

		$this->assertSame(
			array( 'name' => 'tolstenko/blog-seo' ),
			WP_Test_State::$post_meta[5]['_tolstenko_block']
		);
		$this->assertSame( '1', WP_Test_State::$post_meta[5]['tolstenko_flag'] );
		$this->assertArrayNotHasKey( 'unrelated', WP_Test_State::$post_meta[5] );
		$this->assertSame( 'tolstenko/hero', WP_Test_State::$term_meta[7]['tolstenko_hero'] );
	}

	public function test_migration_keeps_already_migrated_meta_untouched(): void {
		/** @var wpdb $wpdb */
		$wpdb                = $GLOBALS['wpdb'];
		$wpdb->postmeta_rows = array(
			(object) array( 'post_id' => 5, 'meta_key' => 'koritan_flag', 'meta_value' => 'old' ),
		);
		$this->setMeta( 5, 'tolstenko_flag', 'new' );

		tolstenko_run_koritan_rename_migration();

		$this->assertSame( 'new', WP_Test_State::$post_meta[5]['tolstenko_flag'] );
	}

	public function test_migration_rewrites_block_namespace_in_post_content(): void {
		/** @var wpdb $wpdb */
		$wpdb      = $GLOBALS['wpdb'];
		$wpdb->col = array( 3, 4 );
		$post      = $this->makePost( 3, 'blog' );
		$post->post_content = '<!-- wp:koritan/blog-seo {"ref":"koritan/x"} -->';
		$this->makePost( 4, 'blog' );

		tolstenko_run_koritan_rename_migration();

		$this->assertCount( 1, $wpdb->updates, 'Обновляем только посты с изменившимся контентом.' );
		$this->assertSame( array( 'ID' => 3 ), $wpdb->updates[0]['where'] );
		$this->assertSame(
			'<!-- wp:tolstenko/blog-seo {"ref":"tolstenko/x"} -->',
			$wpdb->updates[0]['data']['post_content']
		);
	}

	public function test_block_aliases_are_registered_for_theme_blocks(): void {
		$registry = WP_Block_Type_Registry::get_instance();
		$registry->register(
			'tolstenko/blog-seo',
			array( 'title' => 'SEO', 'category' => 'tolstenko', 'api_version' => 2 )
		);
		$registry->register( 'core/paragraph', array( 'title' => 'Paragraph' ) );

		tolstenko_register_koritan_block_aliases();

		$this->assertTrue( $registry->is_registered( 'koritan/blog-seo' ) );
		$this->assertFalse( $registry->is_registered( 'koritan/paragraph' ) );

		$alias = $registry->get_all_registered()['koritan/blog-seo'];
		$this->assertSame( 'SEO', $alias->title );
		$this->assertSame( 2, $alias->api_version );
	}

	public function test_block_aliases_are_not_registered_twice(): void {
		$registry = WP_Block_Type_Registry::get_instance();
		$registry->register( 'tolstenko/blog-seo', array( 'title' => 'SEO' ) );
		$registry->register( 'koritan/blog-seo', array( 'title' => 'Старый' ) );

		tolstenko_register_koritan_block_aliases();

		$this->assertSame( 'Старый', $registry->get_all_registered()['koritan/blog-seo']->title );
	}
}
