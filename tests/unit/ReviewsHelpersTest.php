<?php

namespace Tolstenko\Tests\Unit;

use Tolstenko\Tests\TestCase;
use WP_Error;
use WP_Test_State;

/**
 * @covers ::tolstenko_get_review_field
 * @covers ::tolstenko_review_image_attrs
 * @covers ::tolstenko_get_rutube_video_id
 * @covers ::tolstenko_parse_video_embed_src
 * @covers ::tolstenko_get_video_embed_poster
 * @covers ::tolstenko_get_reviews_grouped
 */
final class ReviewsHelpersTest extends TestCase {

	public function test_image_field_resolves_attachment_id_to_url_and_alt(): void {
		$this->makeAttachment( 21, 'https://example.test/logo.png', 'Логотип' );
		$this->setMeta( 4, 'review_logo', '21' );

		$this->assertSame(
			array(
				'ID'  => 21,
				'url' => 'https://example.test/logo.png',
				'alt' => 'Логотип',
			),
			tolstenko_get_review_field( 'review_logo', 4 )
		);
	}

	public function test_image_field_passes_through_acf_array_with_url(): void {
		$value = array( 'ID' => 9, 'url' => 'https://example.test/acf.png', 'alt' => 'a' );
		$this->setMeta( 4, 'review_photo', $value );

		$this->assertSame( $value, tolstenko_get_review_field( 'review_photo', 4 ) );
	}

	public function test_image_field_returns_empty_array_for_unresolvable_value(): void {
		$this->setMeta( 4, 'review_screen', 'not-an-id' );

		$this->assertSame( array(), tolstenko_get_review_field( 'review_screen', 4 ) );
		$this->assertSame( array(), tolstenko_get_review_field( 'review_thenks', 4 ) );
	}

	public function test_image_field_returns_empty_array_when_attachment_missing(): void {
		$this->setMeta( 4, 'review_thanks_image', 404 );

		$this->assertSame( array(), tolstenko_get_review_field( 'review_thanks_image', 4 ) );
	}

	public function test_contact_field_is_always_array(): void {
		$this->setMeta( 4, 'review_contact', 'wrong-type' );
		$this->assertSame( array(), tolstenko_get_review_field( 'review_contact', 4 ) );

		$link = array( 'url' => 'https://t.me/x', 'title' => 'TG', 'target' => '_blank' );
		$this->setMeta( 5, 'review_contact', $link );
		$this->assertSame( $link, tolstenko_get_review_field( 'review_contact', 5 ) );
	}

	public function test_case_field_returns_url_string_from_link_array(): void {
		$this->setMeta( 4, 'review_case', array( 'url' => 'https://example.test/case', 'title' => 'Кейс' ) );

		$this->assertSame( 'https://example.test/case', tolstenko_get_review_field( 'review_case', 4 ) );
	}

	public function test_plain_field_is_returned_as_is(): void {
		$this->setMeta( 4, 'review_type', 'video' );

		$this->assertSame( 'video', tolstenko_get_review_field( 'review_type', 4 ) );
	}

	public function test_review_image_attrs_variants(): void {
		$this->makeAttachment( 33, 'https://example.test/from-id.png', 'Alt из meta' );

		$this->assertSame(
			array( 'url' => 'https://example.test/x.png', 'alt' => 'Alt' ),
			tolstenko_review_image_attrs( array( 'url' => 'https://example.test/x.png', 'alt' => 'Alt' ) )
		);
		$this->assertSame(
			array( 'url' => 'https://example.test/x.png', 'alt' => '' ),
			tolstenko_review_image_attrs( array( 'url' => 'https://example.test/x.png' ) )
		);
		$this->assertSame(
			array( 'url' => 'https://example.test/from-id.png', 'alt' => 'Alt из meta' ),
			tolstenko_review_image_attrs( 33 )
		);
		$this->assertSame( array( 'url' => '', 'alt' => '' ), tolstenko_review_image_attrs( 0 ) );
		$this->assertSame( array( 'url' => '', 'alt' => '' ), tolstenko_review_image_attrs( 404 ) );
		$this->assertSame( array( 'url' => '', 'alt' => '' ), tolstenko_review_image_attrs( null ) );
	}

	/**
	 * @dataProvider provideRutubeUrls
	 */
	public function test_rutube_video_id( string $url, string $expected ): void {
		$this->assertSame( $expected, tolstenko_get_rutube_video_id( $url ) );
	}

	public static function provideRutubeUrls(): array {
		return array(
			'embed'       => array( 'https://rutube.ru/play/embed/abc123DEF/', 'abc123DEF' ),
			'video page'  => array( 'https://rutube.ru/video/xy_z-9/?t=1', 'xy_z-9' ),
			'no protocol' => array( 'rutube.ru/video/plain', 'plain' ),
			'youtube'     => array( 'https://www.youtube.com/embed/QWE', '' ),
			'empty'       => array( '', '' ),
		);
	}

	/**
	 * @dataProvider provideEmbedMarkup
	 */
	public function test_parse_video_embed_src( string $raw, string $expected ): void {
		$this->assertSame( $expected, tolstenko_parse_video_embed_src( $raw ) );
	}

	public static function provideEmbedMarkup(): array {
		return array(
			'iframe double quotes' => array(
				'<iframe width="720" src="https://rutube.ru/play/embed/abc/" allowfullscreen></iframe>',
				'https://rutube.ru/play/embed/abc/',
			),
			'iframe single quotes' => array(
				"<iframe src='https://rutube.ru/play/embed/abc/'></iframe>",
				'https://rutube.ru/play/embed/abc/',
			),
			'encoded entities' => array(
				'&lt;iframe src=&quot;https://rutube.ru/play/embed/abc/?a=1&amp;b=2&quot;&gt;&lt;/iframe&gt;',
				'https://rutube.ru/play/embed/abc/?a=1&b=2',
			),
			'bare url'   => array( '  https://youtu.be/QWE  ', 'https://youtu.be/QWE' ),
			'no url'     => array( '<div>без ссылки</div>', '' ),
			'empty'      => array( '   ', '' ),
		);
	}

	public function test_poster_from_rutube_api_is_cached(): void {
		$url = 'https://rutube.ru/play/embed/abc123/';
		WP_Test_State::$remote_responses['https://rutube.ru/api/video/abc123/'] = array(
			'body' => '{"thumbnail_url":"https://pic.rutube.ru/abc123.jpg"}',
		);

		$this->assertSame( 'https://pic.rutube.ru/abc123.jpg', tolstenko_get_video_embed_poster( $url ) );
		$this->assertSame(
			'https://pic.rutube.ru/abc123.jpg',
			WP_Test_State::$transients['tolstenko_rutube_poster_abc123'] ?? null
		);

		// Кэш отдаётся без повторного запроса.
		WP_Test_State::$remote_responses = array();
		$this->assertSame( 'https://pic.rutube.ru/abc123.jpg', tolstenko_get_video_embed_poster( $url ) );
	}

	public function test_poster_falls_back_to_rutube_oembed(): void {
		WP_Test_State::$remote_responses['https://rutube.ru/api/video/abc123/'] = array( 'body' => '{}' );
		WP_Test_State::$remote_responses['https://rutube.ru/api/oembed/?url=' . rawurlencode( 'https://rutube.ru/video/abc123/' )] = array(
			'body' => '{"thumbnail_url":"https://pic.rutube.ru/oembed.jpg"}',
		);

		$this->assertSame(
			'https://pic.rutube.ru/oembed.jpg',
			tolstenko_get_video_embed_poster( 'https://rutube.ru/video/abc123/' )
		);
	}

	public function test_poster_returns_empty_when_rutube_requests_fail(): void {
		$this->assertSame( '', tolstenko_get_video_embed_poster( 'https://rutube.ru/video/abc123/' ) );
		$this->assertArrayNotHasKey( 'tolstenko_rutube_poster_abc123', WP_Test_State::$transients );
	}

	public function test_poster_for_youtube_is_built_from_video_id(): void {
		$this->assertSame(
			'https://img.youtube.com/vi/QWE123/hqdefault.jpg',
			tolstenko_get_video_embed_poster( 'https://www.youtube.com/embed/QWE123?rel=0' )
		);
		$this->assertSame(
			'https://img.youtube.com/vi/QWE123/hqdefault.jpg',
			tolstenko_get_video_embed_poster( 'https://youtu.be/QWE123' )
		);
	}

	public function test_poster_is_empty_for_unknown_or_empty_url(): void {
		$this->assertSame( '', tolstenko_get_video_embed_poster( '' ) );
		$this->assertSame( '', tolstenko_get_video_embed_poster( 'https://vimeo.com/123' ) );
	}

	public function test_reviews_grouped_maps_legacy_type_labels(): void {
		$types = array(
			1 => 'thenks',
			2 => 'Благодарности',
			3 => 'Видео',
			4 => 'Текстовые',
			5 => 'Месседжеры',
			6 => 'messenger',
			7 => 'unknown-type',
			8 => 'video',
		);
		foreach ( $types as $id => $type ) {
			$this->makePost( $id, 'review' );
			$this->setMeta( $id, 'review_type', $type );
		}

		$grouped = tolstenko_get_reviews_grouped();

		$this->assertSame( array( 1, 2 ), $this->ids( $grouped['thanks'] ) );
		$this->assertSame( array( 3, 8 ), $this->ids( $grouped['video'] ) );
		$this->assertSame( array( 4 ), $this->ids( $grouped['text'] ) );
		$this->assertSame( array( 5, 6 ), $this->ids( $grouped['messengers'] ) );
	}

	public function test_reviews_grouped_keeps_requested_id_order_and_drops_duplicates(): void {
		foreach ( array( 1, 2, 3 ) as $id ) {
			$this->makePost( $id, 'review' );
			$this->setMeta( $id, 'review_type', 'text' );
		}

		$grouped = tolstenko_get_reviews_grouped( array( '3', 1, 3, 0, 'x' ) );

		$this->assertSame( array( 3, 1 ), $this->ids( $grouped['text'] ) );
	}

	public function test_reviews_grouped_ignores_other_post_types(): void {
		$this->makePost( 1, 'post' );
		$this->setMeta( 1, 'review_type', 'text' );

		$grouped = tolstenko_get_reviews_grouped();

		$this->assertSame(
			array( 'thanks' => array(), 'video' => array(), 'text' => array(), 'messengers' => array() ),
			$grouped
		);
	}

	/**
	 * @param object[] $posts Posts.
	 * @return int[]
	 */
	private function ids( array $posts ): array {
		return array_map(
			static function ( $post ) {
				return $post->ID;
			},
			$posts
		);
	}
}
