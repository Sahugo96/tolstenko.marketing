<?php

namespace Tolstenko\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use WP_Post;
use WP_Test_State;

/**
 * База для тестов темы: чистое состояние заглушек WP на каждый тест.
 */
abstract class TestCase extends PHPUnitTestCase {

	protected function setUp(): void {
		parent::setUp();
		WP_Test_State::reset();
	}

	protected function tearDown(): void {
		WP_Test_State::reset();
		parent::tearDown();
	}

	/**
	 * Зарегистрировать пост в фейковом хранилище.
	 */
	protected function makePost( int $id, string $post_type = 'post', string $title = '' ): WP_Post {
		$post                    = new WP_Post( $id, $post_type, $title );
		WP_Test_State::$posts[]  = $post;
		return $post;
	}

	/**
	 * Зарегистрировать вложение с URL (и, опционально, alt).
	 */
	protected function makeAttachment( int $id, string $url, string $alt = '' ): void {
		WP_Test_State::$attachment_urls[ $id ] = $url;
		if ( $alt !== '' ) {
			WP_Test_State::$post_meta[ $id ]['_wp_attachment_image_alt'] = $alt;
		}
	}

	/**
	 * Задать post meta.
	 *
	 * @param mixed $value Value.
	 */
	protected function setMeta( int $post_id, string $key, $value ): void {
		WP_Test_State::$post_meta[ $post_id ][ $key ] = $value;
	}
}
