<?php
/**
 * Настройки сайта → Видео-пузырь.
 * Хранится в tolstenko_block_defaults['video_bubble'] (merge-save).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Схема настроек видео-пузыря.
 *
 * @return array<string, mixed>
 */
function tolstenko_video_bubble_schema() {
	return array(
		'enabled'       => false,
		'source'        => 'file',
		'video'         => 0,
		'iframe_url'    => '',
		'btn_text'      => 'Консультация',
		'btn_url'       => '',
		'position'      => 'left',
		'delay_seconds' => 5,
		'memory_hours'  => 24,
	);
}

/**
 * Sanitize raw video_bubble settings.
 *
 * @param mixed $raw Raw input.
 * @return array<string, mixed>
 */
function tolstenko_sanitize_video_bubble_settings( $raw ) {
	$base = tolstenko_video_bubble_schema();
	if ( ! is_array( $raw ) ) {
		return $base;
	}

	$delay = isset( $raw['delay_seconds'] ) ? (int) $raw['delay_seconds'] : (int) $base['delay_seconds'];
	if ( $delay < 0 ) {
		$delay = 0;
	}
	if ( $delay > 120 ) {
		$delay = 120;
	}

	$memory = isset( $raw['memory_hours'] ) ? (int) $raw['memory_hours'] : (int) $base['memory_hours'];
	if ( $memory < 1 ) {
		$memory = 1;
	}
	if ( $memory > 8760 ) {
		$memory = 8760;
	}

	$source = sanitize_key( (string) ( $raw['source'] ?? $base['source'] ) );
	if ( ! in_array( $source, array( 'file', 'iframe' ), true ) ) {
		$source = 'file';
	}

	$pos = sanitize_key( (string) ( $raw['position'] ?? $base['position'] ) );
	if ( ! in_array( $pos, array( 'left', 'right' ), true ) ) {
		$pos = 'left';
	}

	return array(
		'enabled'       => ! empty( $raw['enabled'] ),
		'source'        => $source,
		'video'         => isset( $raw['video'] ) ? absint( $raw['video'] ) : 0,
		'iframe_url'    => esc_url_raw( $raw['iframe_url'] ?? '' ),
		'btn_text'      => sanitize_text_field( $raw['btn_text'] ?? '' ),
		'btn_url'       => esc_url_raw( $raw['btn_url'] ?? '' ),
		'position'      => $pos,
		'delay_seconds' => $delay,
		'memory_hours'  => $memory,
	);
}

add_action( 'admin_menu', 'tolstenko_register_video_bubble_admin_page', 20 );
add_action( 'admin_enqueue_scripts', 'tolstenko_video_bubble_admin_assets' );

function tolstenko_register_video_bubble_admin_page() {
	add_submenu_page(
		'tolstenko-site-settings',
		__( 'Видео-пузырь', 'tolstenko-theme' ),
		__( 'Видео-пузырь', 'tolstenko-theme' ),
		'manage_options',
		'tolstenko-video-bubble',
		'tolstenko_render_video_bubble_admin_page'
	);
}

/**
 * @param string $hook Hook.
 */
function tolstenko_video_bubble_admin_assets( $hook ) {
	unset( $hook );
	$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( $page !== 'tolstenko-video-bubble' ) {
		return;
	}
	wp_enqueue_media();
}

function tolstenko_save_video_bubble_from_request() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$raw = isset( $_POST['tolstenko_video_bubble'] ) ? wp_unslash( $_POST['tolstenko_video_bubble'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$out = tolstenko_sanitize_video_bubble_settings( is_array( $raw ) ? $raw : array() );

	$saved = get_option( 'tolstenko_block_defaults', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}
	$saved['video_bubble'] = $out;
	update_option( 'tolstenko_block_defaults', $saved, false );
}

function tolstenko_render_video_bubble_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['tolstenko_video_bubble_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_video_bubble_nonce'] ) ), 'tolstenko_video_bubble_save' )
	) {
		tolstenko_save_video_bubble_from_request();
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Настройки видео-пузыря сохранены.', 'tolstenko-theme' ) . '</p></div>';
	}

	$cfg = function_exists( 'tolstenko_get_block_defaults' )
		? tolstenko_get_block_defaults( 'video_bubble' )
		: tolstenko_video_bubble_schema();
	if ( ! is_array( $cfg ) ) {
		$cfg = tolstenko_video_bubble_schema();
	}
	$cfg = array_merge( tolstenko_video_bubble_schema(), $cfg );

	$source    = (string) ( $cfg['source'] ?? 'file' );
	$video_id  = (int) ( $cfg['video'] ?? 0 );
	$video_url = $video_id ? (string) wp_get_attachment_url( $video_id ) : '';
	$pos       = (string) ( $cfg['position'] ?? 'left' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Настройки сайта: Видео-пузырь', 'tolstenko-theme' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Плавающий сторис на всех страницах. Видео стартует сразу после появления. Кнопка «Консультация» — только после клика по видео.', 'tolstenko-theme' ); ?></p>
		<form method="post" action="">
			<?php wp_nonce_field( 'tolstenko_video_bubble_save', 'tolstenko_video_bubble_nonce' ); ?>
			<style>
				.tolstenko-vb .form-table th{width:220px}
				.tolstenko-vb .row{margin:10px 0}
				.tolstenko-vb .tolstenko-defaults-image-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
				.tolstenko-vb .icon-preview{color:#646970}
				.tolstenko-vb .muted{font-size:12px;color:#666}
			</style>
			<div class="tolstenko-vb" style="max-width:760px;background:#fff;border:1px solid #dcdcde;border-radius:4px;padding:16px 20px;margin-top:12px;">
				<div class="row">
					<label>
						<input type="checkbox" name="tolstenko_video_bubble[enabled]" value="1" <?php checked( ! empty( $cfg['enabled'] ) ); ?>>
						<?php esc_html_e( 'Показывать видео-пузырь на всех страницах', 'tolstenko-theme' ); ?>
					</label>
				</div>

				<div class="row">
					<label><strong><?php esc_html_e( 'Источник', 'tolstenko-theme' ); ?></strong></label><br>
					<label style="margin-right:16px;">
						<input type="radio" name="tolstenko_video_bubble[source]" value="file" <?php checked( $source, 'file' ); ?>>
						<?php esc_html_e( 'Файл с сайта (mp4)', 'tolstenko-theme' ); ?>
					</label>
					<label>
						<input type="radio" name="tolstenko_video_bubble[source]" value="iframe" <?php checked( $source, 'iframe' ); ?>>
						<?php esc_html_e( 'YouTube / Rutube / VK Video (iframe)', 'tolstenko-theme' ); ?>
					</label>
				</div>

				<div class="row tolstenko-defaults-image-row">
					<input type="hidden" class="tolstenko-vb-video-id" name="tolstenko_video_bubble[video]" value="<?php echo (int) $video_id; ?>">
					<button type="button" class="button tolstenko-vb-pick-video"><?php esc_html_e( 'Видеофайл (mp4)', 'tolstenko-theme' ); ?></button>
					<button type="button" class="button tolstenko-vb-clear-video" <?php disabled( ! $video_id ); ?>><?php esc_html_e( 'Очистить', 'tolstenko-theme' ); ?></button>
					<span class="icon-preview"><?php echo $video_url ? esc_html( basename( $video_url ) ) : ''; ?></span>
				</div>

				<div class="row">
					<label for="tolstenko_vb_iframe"><strong><?php esc_html_e( 'Ссылка iframe', 'tolstenko-theme' ); ?></strong></label><br>
					<input type="url" id="tolstenko_vb_iframe" name="tolstenko_video_bubble[iframe_url]" value="<?php echo esc_attr( (string) ( $cfg['iframe_url'] ?? '' ) ); ?>" style="width:100%" placeholder="YouTube / Rutube / https://vkvideo.ru/video-…">
				</div>

				<div class="row">
					<label for="tolstenko_vb_btn_text"><strong><?php esc_html_e( 'Текст кнопки', 'tolstenko-theme' ); ?></strong></label><br>
					<input type="text" id="tolstenko_vb_btn_text" name="tolstenko_video_bubble[btn_text]" value="<?php echo esc_attr( (string) ( $cfg['btn_text'] ?? '' ) ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Консультация', 'tolstenko-theme' ); ?>">
				</div>

				<div class="row">
					<label for="tolstenko_vb_btn_url"><strong><?php esc_html_e( 'Ссылка кнопки', 'tolstenko-theme' ); ?></strong></label><br>
					<input type="text" id="tolstenko_vb_btn_url" name="tolstenko_video_bubble[btn_url]" value="<?php echo esc_attr( (string) ( $cfg['btn_url'] ?? '' ) ); ?>" style="width:100%" placeholder="<?php esc_attr_e( 'Пусто = #modal', 'tolstenko-theme' ); ?>">
				</div>

				<div class="row">
					<label><strong><?php esc_html_e( 'Позиция', 'tolstenko-theme' ); ?></strong></label><br>
					<label style="margin-right:16px;">
						<input type="radio" name="tolstenko_video_bubble[position]" value="left" <?php checked( $pos, 'left' ); ?>>
						<?php esc_html_e( 'Слева', 'tolstenko-theme' ); ?>
					</label>
					<label>
						<input type="radio" name="tolstenko_video_bubble[position]" value="right" <?php checked( $pos, 'right' ); ?>>
						<?php esc_html_e( 'Справа', 'tolstenko-theme' ); ?>
					</label>
				</div>

				<div class="row">
					<label for="tolstenko_vb_delay"><strong><?php esc_html_e( 'Задержка показа, секунд', 'tolstenko-theme' ); ?></strong></label><br>
					<input type="number" id="tolstenko_vb_delay" name="tolstenko_video_bubble[delay_seconds]" value="<?php echo esc_attr( (string) (int) ( $cfg['delay_seconds'] ?? 5 ) ); ?>" min="0" max="120" step="1" style="width:120px">
				</div>

				<div class="row">
					<label for="tolstenko_vb_memory"><strong><?php esc_html_e( 'Память после закрытия, часов', 'tolstenko-theme' ); ?></strong></label><br>
					<input type="number" id="tolstenko_vb_memory" name="tolstenko_video_bubble[memory_hours]" value="<?php echo esc_attr( (string) (int) ( $cfg['memory_hours'] ?? 24 ) ); ?>" min="1" max="8760" step="1" style="width:120px">
					<p class="muted"><?php esc_html_e( 'Сколько часов не показывать пузырь после закрытия (1–8760). По умолчанию 24.', 'tolstenko-theme' ); ?></p>
				</div>

				<p class="submit" style="margin-bottom:0;">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Сохранить', 'tolstenko-theme' ); ?></button>
				</p>
			</div>
		</form>
	</div>
	<script>
	(function(){
		var pick = document.querySelector('.tolstenko-vb-pick-video');
		var clear = document.querySelector('.tolstenko-vb-clear-video');
		var input = document.querySelector('.tolstenko-vb-video-id');
		var preview = document.querySelector('.tolstenko-vb .icon-preview');
		if (!pick || !input) return;

		pick.addEventListener('click', function(ev){
			ev.preventDefault();
			if (typeof wp === 'undefined' || !wp.media) return;
			var frame = wp.media({
				title: 'Выберите видео',
				button: { text: 'Использовать' },
				multiple: false,
				library: { type: 'video' }
			});
			frame.on('select', function(){
				var sel = frame.state().get('selection').first();
				if (!sel) return;
				var json = sel.toJSON();
				input.value = json.id || 0;
				var name = (json.filename || json.title || json.url || '');
				if (name && name.indexOf('/') !== -1) name = name.split('/').pop();
				if (preview) preview.textContent = name || '';
				if (clear) clear.disabled = !input.value || input.value === '0';
			});
			frame.open();
		});

		if (clear) {
			clear.addEventListener('click', function(ev){
				ev.preventDefault();
				input.value = '0';
				if (preview) preview.textContent = '';
				clear.disabled = true;
			});
		}
	})();
	</script>
	<?php
}
