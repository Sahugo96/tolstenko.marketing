<?php
/**
 * Настройки сайта → Переменные.
 * {{year}} в контенте, title и meta заменяется на выбранный (или текущий) год.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TOLSTENKO_SITE_VARIABLES_OPTION', 'tolstenko_site_variables' );

/**
 * @return array{year: int}
 */
function tolstenko_site_variables_defaults() {
	return array(
		'year' => 0,
	);
}

/**
 * @param mixed $raw Raw option.
 * @return array{year: int}
 */
function tolstenko_sanitize_site_variables( $raw ) {
	$out  = tolstenko_site_variables_defaults();
	$data = is_array( $raw ) ? $raw : array();
	$year = isset( $data['year'] ) ? (int) $data['year'] : 0;
	if ( $year >= 2000 && $year <= 2100 ) {
		$out['year'] = $year;
	}
	return $out;
}

/**
 * @return array{year: int}
 */
function tolstenko_get_site_variables() {
	$saved = get_option( TOLSTENKO_SITE_VARIABLES_OPTION, array() );
	return tolstenko_sanitize_site_variables( is_array( $saved ) ? $saved : array() );
}

/**
 * Год для подстановки {{year}}. 0 в настройках = календарный год.
 *
 * @return string
 */
function tolstenko_get_site_variable_year() {
	$vars = tolstenko_get_site_variables();
	$year = (int) ( $vars['year'] ?? 0 );
	if ( $year < 2000 || $year > 2100 ) {
		$year = (int) wp_date( 'Y' );
	}
	return (string) $year;
}

/**
 * Подставить переменные в строку или дерево значений.
 *
 * @param mixed $value Text or nested array.
 * @return mixed
 */
function tolstenko_replace_site_variables( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = tolstenko_replace_site_variables( $item );
		}
		return $value;
	}
	if ( ! is_string( $value ) || $value === '' || strpos( $value, '{{' ) === false ) {
		return $value;
	}
	return preg_replace( '/\{\{\s*year\s*\}\}/i', tolstenko_get_site_variable_year(), $value );
}

add_action( 'admin_menu', 'tolstenko_register_site_variables_admin_page', 12 );
add_action( 'admin_post_tolstenko_save_site_variables', 'tolstenko_handle_save_site_variables' );

function tolstenko_register_site_variables_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Переменные', 'tolstenko-theme' ),
		__( 'Переменные', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-site-variables',
		'tolstenko_render_site_variables_admin_page'
	);
}

function tolstenko_handle_save_site_variables() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Недостаточно прав.', 'tolstenko-theme' ) );
	}
	check_admin_referer( 'tolstenko_site_variables_save', 'tolstenko_site_variables_nonce' );

	$raw = isset( $_POST['tolstenko_site_variables'] ) && is_array( $_POST['tolstenko_site_variables'] )
		? wp_unslash( $_POST['tolstenko_site_variables'] )
		: array();

	update_option( TOLSTENKO_SITE_VARIABLES_OPTION, tolstenko_sanitize_site_variables( $raw ), false );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'tolstenko-site-variables',
				'updated' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}

function tolstenko_render_site_variables_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$data    = tolstenko_get_site_variables();
	$current = (int) wp_date( 'Y' );
	$year    = (int) ( $data['year'] ?? 0 );
	$updated = isset( $_GET['updated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$min     = $current - 10;
	$max     = $current + 5;
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Переменные', 'tolstenko-theme' ); ?></h1>
		<?php if ( $updated ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Сохранено.', 'tolstenko-theme' ); ?></p></div>
		<?php endif; ?>
		<p class="description">
			<?php esc_html_e( 'Токен {{year}} в тексте страницы, заголовке и meta заменяется на выбранный год. Пустое поле — текущий календарный год.', 'tolstenko-theme' ); ?>
		</p>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="tolstenko_save_site_variables">
			<?php wp_nonce_field( 'tolstenko_site_variables_save', 'tolstenko_site_variables_nonce' ); ?>

			<div style="max-width:720px;background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:16px 18px;margin:16px 0;">
				<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
					<label for="tolstenko-sv-year" style="font-weight:600;margin:0;">
						<code>{{year}}</code>
						<?php esc_html_e( '— переменная года:', 'tolstenko-theme' ); ?>
					</label>
					<select id="tolstenko-sv-year" name="tolstenko_site_variables[year]">
						<option value="0" <?php selected( $year, 0 ); ?>>
							<?php echo esc_html( sprintf( __( 'Авто (%s)', 'tolstenko-theme' ), (string) $current ) ); ?>
						</option>
						<?php for ( $y = $max; $y >= $min; $y-- ) : ?>
							<option value="<?php echo (int) $y; ?>" <?php selected( $year, $y ); ?>><?php echo (int) $y; ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>

			<?php submit_button( __( 'Сохранить', 'tolstenko-theme' ) ); ?>
		</form>
	</div>
	<?php
}

/**
 * Не подменять токены в админке и служебных запросах.
 *
 * @return bool
 */
function tolstenko_should_replace_site_variables() {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return false;
	}
	if ( wp_doing_cron() ) {
		return false;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}
	return true;
}

/**
 * @param mixed $value Value.
 * @return mixed
 */
function tolstenko_filter_site_variables( $value ) {
	if ( ! tolstenko_should_replace_site_variables() ) {
		return $value;
	}
	return tolstenko_replace_site_variables( $value );
}

add_filter( 'the_title', 'tolstenko_filter_site_variables', 20 );
add_filter( 'the_content', 'tolstenko_filter_site_variables', 20 );
add_filter( 'the_excerpt', 'tolstenko_filter_site_variables', 20 );
add_filter( 'widget_title', 'tolstenko_filter_site_variables', 20 );
add_filter( 'widget_text', 'tolstenko_filter_site_variables', 20 );
add_filter( 'document_title_parts', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_title', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_metadesc', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_opengraph_title', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_opengraph_desc', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_twitter_title', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_twitter_description', 'tolstenko_filter_site_variables', 20 );
add_filter( 'wpseo_schema_graph', 'tolstenko_filter_site_variables', 20 );

add_action( 'template_redirect', 'tolstenko_start_site_variables_buffer', 0 );

function tolstenko_start_site_variables_buffer() {
	if ( ! tolstenko_should_replace_site_variables() ) {
		return;
	}
	if ( is_feed() || is_robots() || is_trackback() ) {
		return;
	}
	ob_start( 'tolstenko_replace_site_variables' );
}
