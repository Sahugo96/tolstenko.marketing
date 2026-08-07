<?php
/**
 * Минимальные заглушки WordPress для юнит-тестов темы.
 *
 * Тема не грузит ядро WP в тестах: файлы из inc/ подключаются напрямую,
 * а нужные им функции WP реализованы здесь настолько, насколько это нужно
 * для проверки логики темы. Состояние (meta, options, transients, фильтры)
 * живёт в WP_Test_State и сбрасывается перед каждым тестом.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'WEEK_IN_SECONDS' ) ) {
	define( 'WEEK_IN_SECONDS', 604800 );
}

/**
 * Хранилище состояния заглушек.
 */
final class WP_Test_State {

	/** @var array<string, array<string, mixed>> */
	public static array $post_meta = array();

	/** @var array<string, array<string, mixed>> */
	public static array $term_meta = array();

	/** @var array<string, mixed> */
	public static array $options = array();

	/** @var array<string, mixed> */
	public static array $transients = array();

	/** @var array<int, string> */
	public static array $attachment_urls = array();

	/** @var array<string, array<int, callable>> */
	public static array $filters = array();

	/** @var array<int, array{hook:string,callback:mixed,priority:int}> */
	public static array $actions = array();

	/** @var array<string, mixed> */
	public static array $query_vars = array();

	/** @var array<int, WP_Post> */
	public static array $posts = array();

	/** @var string[] */
	public static array $post_types = array();

	/** @var string[] */
	public static array $taxonomies = array();

	/** @var array<string, mixed> */
	public static array $remote_responses = array();

	/** @var array<int, string> */
	public static array $template_parts = array();

	/** @var array<string, string> */
	public static array $shortcodes = array();

	/** @var array<string, array> */
	public static array $rest_routes = array();

	public static ?int $current_post_id = null;

	/** @var string[] */
	public static array $singular = array();

	public static function reset(): void {
		self::$post_meta        = array();
		self::$term_meta        = array();
		self::$options          = array();
		self::$transients       = array();
		self::$attachment_urls  = array();
		self::$filters          = array();
		self::$actions          = array();
		self::$query_vars       = array();
		self::$posts            = array();
		self::$post_types       = array();
		self::$taxonomies       = array();
		self::$remote_responses = array();
		self::$template_parts   = array();
		self::$shortcodes       = array();
		self::$rest_routes      = array();
		self::$current_post_id  = null;
		self::$singular         = array();
		$_POST                  = array();
		$_GET                   = array();
		$GLOBALS['wpdb']        = new \wpdb();
		\WP_Block_Type_Registry::reset_instance();
	}
}

if ( ! class_exists( 'WP_Post' ) ) {
	/**
	 * Упрощённый WP_Post.
	 */
	class WP_Post {

		public int $ID = 0;

		public string $post_type = 'post';

		public string $post_title = '';

		public string $post_content = '';

		public function __construct( int $id = 0, string $post_type = 'post', string $post_title = '' ) {
			$this->ID         = $id;
			$this->post_type  = $post_type;
			$this->post_title = $post_title;
		}
	}
}

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Упрощённый WP_Error.
	 */
	class WP_Error {

		public string $code;

		public string $message;

		public array $data;

		public function __construct( string $code = '', string $message = '', array $data = array() ) {
			$this->code    = $code;
			$this->message = $message;
			$this->data    = $data;
		}

		public function get_error_code(): string {
			return $this->code;
		}

		public function get_error_message(): string {
			return $this->message;
		}
	}
}

if ( ! class_exists( 'WP_Query' ) ) {
	/**
	 * WP_Query поверх WP_Test_State::$posts (учитывает post__in / post__not_in / posts_per_page / paged).
	 */
	class WP_Query {

		public array $query_vars = array();

		/** @var WP_Post[] */
		public array $posts = array();

		public int $found_posts = 0;

		public int $max_num_pages = 0;

		private int $position = 0;

		public function __construct( array $args = array() ) {
			$this->query_vars = $args;

			$posts = array_values(
				array_filter(
					WP_Test_State::$posts,
					static function ( WP_Post $post ) use ( $args ) {
						return empty( $args['post_type'] ) || $post->post_type === $args['post_type'];
					}
				)
			);

			if ( ! empty( $args['post__in'] ) ) {
				$order = array_values( array_map( 'intval', (array) $args['post__in'] ) );
				$posts = array_values(
					array_filter(
						$posts,
						static function ( WP_Post $post ) use ( $order ) {
							return in_array( $post->ID, $order, true );
						}
					)
				);
				usort(
					$posts,
					static function ( WP_Post $a, WP_Post $b ) use ( $order ) {
						return array_search( $a->ID, $order, true ) <=> array_search( $b->ID, $order, true );
					}
				);
			}

			if ( ! empty( $args['post__not_in'] ) ) {
				$excluded = array_map( 'intval', (array) $args['post__not_in'] );
				$posts    = array_values(
					array_filter(
						$posts,
						static function ( WP_Post $post ) use ( $excluded ) {
							return ! in_array( $post->ID, $excluded, true );
						}
					)
				);
			}

			$this->found_posts = count( $posts );

			$per_page = isset( $args['posts_per_page'] ) ? (int) $args['posts_per_page'] : -1;
			if ( $per_page > 0 ) {
				$paged               = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;
				$this->max_num_pages = (int) ceil( $this->found_posts / $per_page );
				$posts               = array_slice( $posts, ( $paged - 1 ) * $per_page, $per_page );
			} else {
				$this->max_num_pages = $this->found_posts ? 1 : 0;
			}

			$this->posts = $posts;
		}

		public function have_posts(): bool {
			return $this->position < count( $this->posts );
		}

		public function the_post(): void {
			$post                            = $this->posts[ $this->position ];
			WP_Test_State::$current_post_id  = $post->ID;
			$this->position++;
		}
	}
}

if ( ! class_exists( 'Walker_Nav_Menu' ) ) {
	/**
	 * Заглушка родителя для Tolstenko_Walker_Flat_Menu.
	 */
	class Walker_Nav_Menu {
	}
}

if ( ! class_exists( 'wpdb' ) ) {
	/**
	 * Фейковый $wpdb: отдаёт заранее заданные строки и пишет запросы/обновления в лог.
	 */
	class wpdb {

		public string $postmeta = 'wp_postmeta';

		public string $termmeta = 'wp_termmeta';

		public string $posts = 'wp_posts';

		/** @var array<int, object> */
		public array $postmeta_rows = array();

		/** @var array<int, object> */
		public array $termmeta_rows = array();

		/** @var array<int, int> */
		public array $col = array();

		/** @var array<int, array{table:string,data:array,where:array}> */
		public array $updates = array();

		/** @var string[] */
		public array $queries = array();

		public function prepare( $query, ...$args ) {
			foreach ( $args as $arg ) {
				$query = preg_replace( '/%[sd]/', is_int( $arg ) ? (string) $arg : "'" . $arg . "'", (string) $query, 1 );
			}
			return $query;
		}

		public function esc_like( $text ) {
			return addcslashes( (string) $text, '_%\\' );
		}

		public function get_results( $query, $output = null ) {
			$this->queries[] = (string) $query;
			return strpos( (string) $query, $this->termmeta ) !== false
				? $this->termmeta_rows
				: $this->postmeta_rows;
		}

		public function get_col( $query, $column = 0 ) {
			$this->queries[] = (string) $query;
			return $this->col;
		}

		public function update( $table, $data, $where, $format = null, $where_format = null ) {
			$this->updates[] = array(
				'table' => (string) $table,
				'data'  => (array) $data,
				'where' => (array) $where,
			);
			return 1;
		}
	}
}

if ( ! class_exists( 'WP_Block_Type' ) ) {
	/**
	 * Упрощённый WP_Block_Type.
	 */
	class WP_Block_Type {

		public string $name = '';

		public int $api_version = 3;

		public string $title = '';

		public string $category = '';

		public string $icon = '';

		public string $description = '';

		/** @var mixed */
		public $render_callback = null;

		public array $attributes = array();

		public array $supports = array();

		public string $editor_script = '';

		public function __construct( string $name, array $args = array() ) {
			$this->name = $name;
			foreach ( $args as $key => $value ) {
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
			}
		}
	}
}

if ( ! class_exists( 'WP_Block_Type_Registry' ) ) {
	/**
	 * Упрощённый реестр блоков.
	 */
	class WP_Block_Type_Registry {

		private static ?WP_Block_Type_Registry $instance = null;

		/** @var array<string, WP_Block_Type> */
		private array $registered = array();

		public static function get_instance(): WP_Block_Type_Registry {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public static function reset_instance(): void {
			self::$instance = null;
		}

		public function register( string $name, array $args = array() ): WP_Block_Type {
			$block                      = new WP_Block_Type( $name, $args );
			$this->registered[ $name ] = $block;
			return $block;
		}

		public function is_registered( string $name ): bool {
			return isset( $this->registered[ $name ] );
		}

		/**
		 * @return array<string, WP_Block_Type>
		 */
		public function get_all_registered(): array {
			return $this->registered;
		}
	}
}

function register_block_type( $name, $args = array() ) {
	return WP_Block_Type_Registry::get_instance()->register( (string) $name, (array) $args );
}

/* ------------------------------------------------------------------ hooks */

function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	WP_Test_State::$actions[] = array(
		'hook'     => (string) $hook,
		'callback' => $callback,
		'priority' => (int) $priority,
	);
	return true;
}

function remove_filter( $hook, $callback, $priority = 10 ) {
	unset( WP_Test_State::$filters[ (string) $hook ] );
	return true;
}

function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
	WP_Test_State::$filters[ (string) $hook ][ (int) $priority ][] = $callback;
	return true;
}

function apply_filters( $hook, $value, ...$args ) {
	$hook = (string) $hook;
	if ( empty( WP_Test_State::$filters[ $hook ] ) ) {
		return $value;
	}
	$by_priority = WP_Test_State::$filters[ $hook ];
	ksort( $by_priority );
	foreach ( $by_priority as $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$value = call_user_func( $callback, $value, ...$args );
		}
	}
	return $value;
}

function has_action( $hook, $callback = null ): bool {
	foreach ( WP_Test_State::$actions as $action ) {
		if ( $action['hook'] !== (string) $hook ) {
			continue;
		}
		if ( $callback === null || $action['callback'] === $callback ) {
			return true;
		}
	}
	return false;
}

/* ------------------------------------------------------------------- i18n */

function __( $text, $domain = 'default' ) {
	return (string) $text;
}

function esc_html__( $text, $domain = 'default' ) {
	return esc_html( (string) $text );
}

function esc_attr__( $text, $domain = 'default' ) {
	return esc_attr( (string) $text );
}

function _n( $single, $plural, $number, $domain = 'default' ) {
	return (int) $number === 1 ? (string) $single : (string) $plural;
}

/* ------------------------------------------------------- escaping / kses */

function esc_attr( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_html( $text ) {
	return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
}

function esc_url( $url ) {
	return esc_url_raw( $url );
}

function esc_url_raw( $url ) {
	$url = trim( (string) $url );
	// Достаточно для тестов: убираем пробелы и явно опасные схемы.
	if ( preg_match( '#^\s*javascript:#i', $url ) ) {
		return '';
	}
	return str_replace( array( '"', "'", '<', '>' ), '', $url );
}

function wp_strip_all_tags( $text, $remove_breaks = false ) {
	$text = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text );
	$text = strip_tags( (string) $text );
	if ( $remove_breaks ) {
		$text = preg_replace( '/[\r\n\t ]+/', ' ', $text );
	}
	return trim( $text );
}

function sanitize_text_field( $str ) {
	$str = wp_strip_all_tags( (string) $str );
	return trim( preg_replace( '/[\r\n\t]+/', ' ', $str ) );
}

function sanitize_key( $key ) {
	return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
}

function sanitize_title( $title ) {
	$title = mb_strtolower( trim( (string) $title ), 'UTF-8' );
	$title = preg_replace( '/[^\p{L}\p{N}]+/u', '-', $title );
	return trim( (string) $title, '-' );
}

function wp_kses( $html, $allowed_html, $allowed_protocols = array() ) {
	$allowed_tags = array();
	foreach ( (array) $allowed_html as $tag => $attrs ) {
		$allowed_tags[] = '<' . $tag . '>';
	}
	return strip_tags( (string) $html, implode( '', $allowed_tags ) );
}

function wp_kses_post( $html ) {
	return wp_kses( $html, wp_kses_allowed_html( 'post' ) );
}

function wp_kses_allowed_html( $context = '' ) {
	return array(
		'a'          => array( 'href' => true, 'title' => true, 'target' => true, 'rel' => true, 'class' => true ),
		'b'          => array(),
		'br'         => array(),
		'div'        => array( 'class' => true, 'id' => true ),
		'em'         => array(),
		'i'          => array(),
		'li'         => array( 'class' => true ),
		'ol'         => array( 'class' => true ),
		'p'          => array( 'class' => true ),
		'span'       => array( 'class' => true ),
		'strong'     => array(),
		'ul'         => array( 'class' => true ),
	);
}

function wpautop( $text, $br = true ) {
	$text  = trim( (string) $text );
	if ( $text === '' ) {
		return '';
	}
	$parts = preg_split( '/\n\s*\n/', $text );
	$out   = '';
	foreach ( $parts as $part ) {
		$part = trim( $part );
		$out .= '<p>' . ( $br ? nl2br( $part ) : $part ) . "</p>\n";
	}
	return $out;
}

function wp_unslash( $value ) {
	if ( is_array( $value ) ) {
		return array_map( 'wp_unslash', $value );
	}
	return is_string( $value ) ? stripslashes( $value ) : $value;
}

function html_entity_decode_stub( $value ) {
	return html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
}

/* -------------------------------------------------------- meta & options */

function get_post_meta( $post_id, $key = '', $single = false ) {
	$bucket = WP_Test_State::$post_meta[ (int) $post_id ] ?? array();
	if ( $key === '' ) {
		return $bucket;
	}
	if ( ! array_key_exists( $key, $bucket ) ) {
		return $single ? '' : array();
	}
	return $single ? $bucket[ $key ] : array( $bucket[ $key ] );
}

function update_post_meta( $post_id, $key, $value, $prev = '' ) {
	WP_Test_State::$post_meta[ (int) $post_id ][ (string) $key ] = $value;
	return true;
}

function get_term_meta( $term_id, $key = '', $single = false ) {
	$bucket = WP_Test_State::$term_meta[ (int) $term_id ] ?? array();
	if ( $key === '' ) {
		return $bucket;
	}
	if ( ! array_key_exists( $key, $bucket ) ) {
		return $single ? '' : array();
	}
	return $single ? $bucket[ $key ] : array( $bucket[ $key ] );
}

function update_term_meta( $term_id, $key, $value, $prev = '' ) {
	WP_Test_State::$term_meta[ (int) $term_id ][ (string) $key ] = $value;
	return true;
}

function metadata_exists( $meta_type, $object_id, $meta_key ) {
	$store = $meta_type === 'term' ? WP_Test_State::$term_meta : WP_Test_State::$post_meta;
	return isset( $store[ (int) $object_id ] ) && array_key_exists( (string) $meta_key, $store[ (int) $object_id ] );
}

function get_option( $name, $default = false ) {
	return array_key_exists( (string) $name, WP_Test_State::$options )
		? WP_Test_State::$options[ (string) $name ]
		: $default;
}

function update_option( $name, $value, $autoload = null ) {
	WP_Test_State::$options[ (string) $name ] = $value;
	return true;
}

function delete_option( $name ) {
	unset( WP_Test_State::$options[ (string) $name ] );
	return true;
}

function get_transient( $key ) {
	return WP_Test_State::$transients[ (string) $key ] ?? false;
}

function set_transient( $key, $value, $expiration = 0 ) {
	WP_Test_State::$transients[ (string) $key ] = $value;
	return true;
}

function maybe_unserialize( $value ) {
	if ( is_string( $value ) ) {
		$data = @unserialize( $value ); // phpcs:ignore
		if ( $data !== false || $value === serialize( false ) ) {
			return $data;
		}
	}
	return $value;
}

/* --------------------------------------------------------- posts & query */

function get_the_ID() {
	return WP_Test_State::$current_post_id ?? 0;
}

function get_post( $post = null ) {
	$id = $post === null ? WP_Test_State::$current_post_id : (int) $post;
	foreach ( WP_Test_State::$posts as $item ) {
		if ( $item->ID === (int) $id ) {
			return $item;
		}
	}
	return null;
}

function get_post_type( $post = null ) {
	$found = get_post( $post );
	return $found ? $found->post_type : false;
}

function get_post_field( $field, $post_id ) {
	$found = get_post( $post_id );
	return $found && isset( $found->$field ) ? $found->$field : '';
}

function get_posts( $args = array() ) {
	$query = new WP_Query( $args );
	return $query->posts;
}

function post_type_exists( $post_type ) {
	return in_array( (string) $post_type, WP_Test_State::$post_types, true );
}

function taxonomy_exists( $taxonomy ) {
	return in_array( (string) $taxonomy, WP_Test_State::$taxonomies, true );
}

function is_singular( $types = '' ) {
	if ( $types === '' ) {
		return ! empty( WP_Test_State::$singular );
	}
	foreach ( (array) $types as $type ) {
		if ( in_array( (string) $type, WP_Test_State::$singular, true ) ) {
			return true;
		}
	}
	return false;
}

function set_query_var( $var, $value ) {
	WP_Test_State::$query_vars[ (string) $var ] = $value;
}

function get_query_var( $var, $default = '' ) {
	return WP_Test_State::$query_vars[ (string) $var ] ?? $default;
}

function wp_reset_postdata() {
	WP_Test_State::$current_post_id = null;
}

function get_template_part( $slug, $name = null, $args = array() ) {
	WP_Test_State::$template_parts[] = (string) $slug;
	echo '<!--part:' . esc_attr( (string) $slug ) . '-->';
}

function get_template_directory() {
	return rtrim( ABSPATH, '/' );
}

function clean_post_cache( $post_id ) {
	return true;
}

/* --------------------------------------------------------- attachments */

function wp_get_attachment_image_url( $id, $size = 'full' ) {
	return WP_Test_State::$attachment_urls[ (int) $id ] ?? false;
}

function wp_get_attachment_image_srcset( $id, $size = 'full' ) {
	$url = wp_get_attachment_image_url( $id, $size );
	return $url ? $url . ' 1x' : false;
}

function wp_get_attachment_image_sizes( $id, $size = 'full' ) {
	return wp_get_attachment_image_url( $id, $size ) ? '(max-width: 100vw) 100vw' : false;
}

/* ------------------------------------------------------------ http / rest */

function add_query_arg( ...$args ) {
	if ( count( $args ) === 3 ) {
		list( $key, $value, $url ) = $args;
		$pairs                     = array( (string) $key => $value );
	} else {
		list( $pairs, $url ) = $args;
	}
	$separator = strpos( (string) $url, '?' ) === false ? '?' : '&';
	return $url . $separator . http_build_query( $pairs );
}

function wp_remote_get( $url, $args = array() ) {
	return WP_Test_State::$remote_responses[ (string) $url ] ?? new WP_Error( 'http_request_failed', 'No stubbed response for ' . $url );
}

function wp_remote_retrieve_body( $response ) {
	return is_array( $response ) ? (string) ( $response['body'] ?? '' ) : '';
}

function is_wp_error( $thing ) {
	return $thing instanceof WP_Error;
}

if ( ! class_exists( 'WP_REST_Request' ) ) {
	/**
	 * Упрощённый WP_REST_Request.
	 */
	class WP_REST_Request {

		/** @var array<string, mixed> */
		private array $params;

		public function __construct( array $params = array() ) {
			$this->params = $params;
		}

		/**
		 * @return mixed
		 */
		public function get_param( string $key ) {
			return $this->params[ $key ] ?? null;
		}
	}
}

if ( ! class_exists( 'WP_REST_Response' ) ) {
	/**
	 * Упрощённый WP_REST_Response.
	 */
	class WP_REST_Response {

		/** @var mixed */
		public $data;

		public int $status;

		public function __construct( $data = null, int $status = 200 ) {
			$this->data   = $data;
			$this->status = $status;
		}

		/**
		 * @return mixed
		 */
		public function get_data() {
			return $this->data;
		}
	}
}

if ( ! class_exists( 'WP_REST_Server' ) ) {
	/**
	 * Константы методов REST.
	 */
	class WP_REST_Server {

		const READABLE = 'GET';
	}
}

function rest_ensure_response( $response ) {
	if ( $response instanceof WP_REST_Response || $response instanceof WP_Error ) {
		return $response;
	}
	return new WP_REST_Response( $response );
}

function register_rest_route( $namespace, $route, $args = array(), $override = false ) {
	WP_Test_State::$rest_routes[ $namespace . $route ] = $args;
	return true;
}

/* ------------------------------------------------------------ shortcodes */

function shortcode_exists( $tag ) {
	return isset( WP_Test_State::$shortcodes[ (string) $tag ] );
}

function do_shortcode( $content ) {
	foreach ( WP_Test_State::$shortcodes as $tag => $output ) {
		$content = preg_replace( '/\[' . preg_quote( (string) $tag, '/' ) . '[^\]]*\]/', (string) $output, (string) $content );
	}
	return (string) $content;
}
