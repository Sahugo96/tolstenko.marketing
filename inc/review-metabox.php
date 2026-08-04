<?php
/**
 * Нативный метабокс CPT «Отзыв» — без ACF.
 * Мета-ключи совместимы с прежними ACF-именами.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'tolstenko_review_add_metabox' );
add_action( 'save_post_review', 'tolstenko_review_save_metabox', 10, 2 );
add_action( 'admin_enqueue_scripts', 'tolstenko_review_metabox_assets' );
add_filter( 'post_row_actions', 'tolstenko_review_row_actions', 10, 2 );
add_filter( 'get_sample_permalink_html', 'tolstenko_review_hide_sample_permalink', 10, 2 );

/**
 * Убрать «Просмотреть» у отзывов — публичных страниц нет.
 *
 * @param array   $actions
 * @param WP_Post $post
 * @return array
 */
function tolstenko_review_row_actions( $actions, $post ) {
	if ( $post instanceof WP_Post && $post->post_type === 'review' ) {
		unset( $actions['view'] );
	}
	return $actions;
}

/**
 * @param string $html
 * @param int    $post_id
 * @return string
 */
function tolstenko_review_hide_sample_permalink( $html, $post_id ) {
	if ( get_post_type( $post_id ) === 'review' ) {
		return '';
	}
	return $html;
}

/** @return array<string,string> */
function tolstenko_review_type_choices() {
	return array(
		'thanks'     => __( 'Благодарности', 'tolstenko-theme' ),
		'video'      => __( 'Видео', 'tolstenko-theme' ),
		'text'       => __( 'Текстовые', 'tolstenko-theme' ),
		'messengers' => __( 'Месседжеры', 'tolstenko-theme' ),
	);
}

function tolstenko_review_add_metabox() {
	add_meta_box(
		'tolstenko_review_fields',
		__( 'Тип отзыва', 'tolstenko-theme' ),
		'tolstenko_review_render_metabox',
		'review',
		'normal',
		'high'
	);
}

function tolstenko_review_metabox_assets( $hook ) {
	if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->post_type !== 'review' ) {
		return;
	}
	wp_enqueue_media();
	wp_enqueue_editor();
}

/**
 * @param int    $post_id Post ID.
 * @param string $key     Meta key.
 * @param mixed  $default Default.
 * @return mixed
 */
function tolstenko_review_meta( $post_id, $key, $default = '' ) {
	$val = get_post_meta( $post_id, $key, true );
	return ( $val === '' || $val === false || $val === null ) ? $default : $val;
}

function tolstenko_review_render_image_field( $name, $label, $attachment_id ) {
	$attachment_id = (int) $attachment_id;
	$url           = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	?>
	<p class="tolstenko-review-field">
		<label><strong><?php echo esc_html( $label ); ?></strong></label><br>
		<span class="tolstenko-review-media" data-media-type="image">
			<input type="hidden" class="tolstenko-review-media-id" name="<?php echo esc_attr( $name ); ?>" value="<?php echo (int) $attachment_id; ?>">
			<button type="button" class="button tolstenko-review-media-pick"><?php esc_html_e( 'Выбрать', 'tolstenko-theme' ); ?></button>
			<button type="button" class="button tolstenko-review-media-clear"<?php echo $attachment_id ? '' : ' style="display:none"'; ?>><?php esc_html_e( 'Убрать', 'tolstenko-theme' ); ?></button>
			<span class="tolstenko-review-media-preview" style="display:block;margin-top:8px;">
				<?php if ( $url ) : ?>
					<img src="<?php echo esc_url( $url ); ?>" alt="" style="max-width:220px;height:auto;">
				<?php endif; ?>
			</span>
		</span>
	</p>
	<?php
}

function tolstenko_review_render_metabox( $post ) {
	wp_nonce_field( 'tolstenko_review_save', 'tolstenko_review_nonce' );

	$type    = (string) tolstenko_review_meta( $post->ID, 'review_type', 'text' );
	$choices = tolstenko_review_type_choices();
	if ( ! isset( $choices[ $type ] ) ) {
		$type = 'text';
	}

	$contact = tolstenko_review_meta( $post->ID, 'review_contact', array() );
	if ( ! is_array( $contact ) ) {
		$contact = array();
	}
	$contact_url   = (string) ( $contact['url'] ?? '' );
	$contact_title = (string) ( $contact['title'] ?? '' );

	$rating = (string) tolstenko_review_meta( $post->ID, 'review_rating', '5' );
	if ( ! in_array( $rating, array( '1', '2', '3', '4', '5' ), true ) ) {
		$rating = '5';
	}
	$redactor = (string) tolstenko_review_meta( $post->ID, 'review_redactor', '' );
	?>
	<style>
		.tolstenko-review-box .tolstenko-review-field{margin:0 0 14px}
		.tolstenko-review-box .tolstenko-review-field input[type=text],
		.tolstenko-review-box .tolstenko-review-field input[type=url],
		.tolstenko-review-box .tolstenko-review-field input[type=number],
		.tolstenko-review-box .tolstenko-review-field select,
		.tolstenko-review-box .tolstenko-review-field textarea{width:100%;max-width:640px}
		.tolstenko-review-box .tolstenko-review-panel{display:none;margin-top:16px;padding-top:12px;border-top:1px solid #dcdcde}
		.tolstenko-review-box .tolstenko-review-panel.is-active{display:block}
		.tolstenko-review-box .tolstenko-review-rating label{margin-right:10px}
	</style>

	<div class="tolstenko-review-box" id="tolstenko-review-box">
		<p class="tolstenko-review-field">
			<label for="tolstenko_review_type"><strong><?php esc_html_e( 'Тип', 'tolstenko-theme' ); ?></strong></label><br>
			<select id="tolstenko_review_type" name="tolstenko_review_type">
				<?php foreach ( $choices as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $type, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>

		<div class="tolstenko-review-panel" data-type="thanks">
			<?php tolstenko_review_render_image_field( 'tolstenko_review_thenks', __( 'Фото благодарности', 'tolstenko-theme' ), (int) tolstenko_review_meta( $post->ID, 'review_thenks', 0 ) ); ?>
		</div>

		<div class="tolstenko-review-panel" data-type="video">
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_video"><strong><?php esc_html_e( 'Видео (iframe или URL)', 'tolstenko-theme' ); ?></strong></label><br>
				<textarea id="tolstenko_review_video" name="tolstenko_review_video" rows="3"><?php echo esc_textarea( (string) tolstenko_review_meta( $post->ID, 'review_video', '' ) ); ?></textarea>
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_video_name"><strong><?php esc_html_e( 'Имя (под видео)', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_review_video_name" name="tolstenko_review_video_name" value="<?php echo esc_attr( (string) tolstenko_review_meta( $post->ID, 'review_video_name', '' ) ); ?>">
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_video_text"><strong><?php esc_html_e( 'Описание (под видео)', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_review_video_text" name="tolstenko_review_video_text" value="<?php echo esc_attr( (string) tolstenko_review_meta( $post->ID, 'review_video_text', '' ) ); ?>">
			</p>
			<?php tolstenko_review_render_image_field( 'tolstenko_review_logo', __( 'Логотип', 'tolstenko-theme' ), (int) tolstenko_review_meta( $post->ID, 'review_logo', 0 ) ); ?>
		</div>

		<div class="tolstenko-review-panel" data-type="text">
			<?php tolstenko_review_render_image_field( 'tolstenko_review_photo', __( 'Фото', 'tolstenko-theme' ), (int) tolstenko_review_meta( $post->ID, 'review_photo', 0 ) ); ?>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_name"><strong><?php esc_html_e( 'Имя', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_review_name" name="tolstenko_review_name" value="<?php echo esc_attr( (string) tolstenko_review_meta( $post->ID, 'review_name', '' ) ); ?>">
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_position"><strong><?php esc_html_e( 'Должность / компания', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_review_position" name="tolstenko_review_position" value="<?php echo esc_attr( (string) tolstenko_review_meta( $post->ID, 'review_position', '' ) ); ?>">
			</p>
			<p class="tolstenko-review-field tolstenko-review-rating">
				<strong><?php esc_html_e( 'Рейтинг', 'tolstenko-theme' ); ?></strong><br>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<label><input type="radio" name="tolstenko_review_rating" value="<?php echo (int) $i; ?>" <?php checked( $rating, (string) $i ); ?>> <?php echo (int) $i; ?> ★</label>
				<?php endfor; ?>
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_redactor"><strong><?php esc_html_e( 'Текст отзыва', 'tolstenko-theme' ); ?></strong></label>
			</p>
			<?php
			wp_editor(
				$redactor,
				'tolstenko_review_redactor',
				array(
					'textarea_name' => 'tolstenko_review_redactor',
					'textarea_rows' => 8,
					'media_buttons' => false,
					'teeny'         => true,
					'quicktags'     => true,
				)
			);
			?>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_contact_title"><strong><?php esc_html_e( 'Кнопка контакта — текст', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="text" id="tolstenko_review_contact_title" name="tolstenko_review_contact_title" value="<?php echo esc_attr( $contact_title ); ?>">
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_contact_url"><strong><?php esc_html_e( 'Кнопка контакта — ссылка', 'tolstenko-theme' ); ?></strong></label><br>
				<input type="url" id="tolstenko_review_contact_url" name="tolstenko_review_contact_url" value="<?php echo esc_attr( $contact_url ); ?>" placeholder="https://… или пусто → модалка">
			</p>
			<p class="tolstenko-review-field">
				<label for="tolstenko_review_case"><strong><?php esc_html_e( 'Файл кейса (URL)', 'tolstenko-theme' ); ?></strong></label><br>
				<span class="tolstenko-review-media" data-media-type="file">
					<input type="text" class="tolstenko-review-media-url" id="tolstenko_review_case" name="tolstenko_review_case" value="<?php echo esc_attr( (string) tolstenko_review_meta( $post->ID, 'review_case', '' ) ); ?>" placeholder="https://… или выберите файл">
					<button type="button" class="button tolstenko-review-media-pick"><?php esc_html_e( 'Выбрать файл', 'tolstenko-theme' ); ?></button>
				</span>
			</p>
		</div>

		<div class="tolstenko-review-panel" data-type="messengers">
			<?php tolstenko_review_render_image_field( 'tolstenko_review_screen', __( 'Скриншот переписки', 'tolstenko-theme' ), (int) tolstenko_review_meta( $post->ID, 'review_screen', 0 ) ); ?>
		</div>
	</div>

	<script>
	(function(){
		var box = document.getElementById('tolstenko-review-box');
		if (!box) return;
		var typeSelect = document.getElementById('tolstenko_review_type');
		function syncPanels() {
			var type = typeSelect ? typeSelect.value : '';
			box.querySelectorAll('.tolstenko-review-panel').forEach(function(panel){
				panel.classList.toggle('is-active', panel.getAttribute('data-type') === type);
			});
		}
		if (typeSelect) typeSelect.addEventListener('change', syncPanels);
		syncPanels();

		box.querySelectorAll('.tolstenko-review-media').forEach(function(wrap){
			var pick = wrap.querySelector('.tolstenko-review-media-pick');
			var clear = wrap.querySelector('.tolstenko-review-media-clear');
			var idInput = wrap.querySelector('.tolstenko-review-media-id');
			var urlInput = wrap.querySelector('.tolstenko-review-media-url');
			var preview = wrap.querySelector('.tolstenko-review-media-preview');
			var mediaType = wrap.getAttribute('data-media-type') || 'image';
			if (!pick) return;

			pick.addEventListener('click', function(e){
				e.preventDefault();
				if (typeof wp === 'undefined' || !wp.media) return;
				var frame = wp.media({
					title: mediaType === 'file' ? 'Выберите файл' : 'Выберите изображение',
					button: { text: 'Использовать' },
					multiple: false,
					library: mediaType === 'image' ? { type: 'image' } : {}
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					if (idInput) idInput.value = att.id || 0;
					if (urlInput) urlInput.value = att.url || '';
					if (preview) {
						preview.innerHTML = att.url ? '<img src="'+att.url+'" alt="" style="max-width:220px;height:auto;">' : '';
					}
					if (clear) clear.style.display = '';
				});
				frame.open();
			});

			if (clear) {
				clear.addEventListener('click', function(e){
					e.preventDefault();
					if (idInput) idInput.value = '0';
					if (urlInput) urlInput.value = '';
					if (preview) preview.innerHTML = '';
					clear.style.display = 'none';
				});
			}
		});
	})();
	</script>
	<?php
}

/**
 * Sanitize video embed: iframe или URL (wp_kses_post вырезает iframe).
 *
 * @param string $html Raw value.
 * @return string
 */
function tolstenko_kses_video_embed( $html ) {
	$html = trim( (string) $html );
	if ( $html === '' ) {
		return '';
	}

	// Чистый URL без разметки.
	if ( preg_match( '#^https?://[^\s<>"\']+$#i', $html ) ) {
		return esc_url_raw( $html );
	}

	$allowed = array(
		'iframe' => array(
			'src'             => true,
			'width'           => true,
			'height'          => true,
			'style'           => true,
			'allow'           => true,
			'allowfullscreen' => true,
			'frameborder'     => true,
			'loading'         => true,
			'referrerpolicy'  => true,
			'title'           => true,
			'class'           => true,
			'id'              => true,
		),
	);

	$clean = wp_kses( $html, $allowed );
	if ( $clean !== '' ) {
		return $clean;
	}

	// Fallback: вытащить src, если kses всё съел.
	if ( function_exists( 'tolstenko_parse_video_embed_src' ) ) {
		$src = tolstenko_parse_video_embed_src( $html );
		if ( $src !== '' ) {
			return esc_url_raw( $src );
		}
	}

	return '';
}

function tolstenko_review_save_metabox( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['tolstenko_review_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tolstenko_review_nonce'] ) ), 'tolstenko_review_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$choices = tolstenko_review_type_choices();
	$type    = isset( $_POST['tolstenko_review_type'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_review_type'] ) ) : 'text';
	if ( ! isset( $choices[ $type ] ) ) {
		$type = 'text';
	}
	update_post_meta( $post_id, 'review_type', $type );

	$image_keys = array(
		'tolstenko_review_thenks' => 'review_thenks',
		'tolstenko_review_logo'   => 'review_logo',
		'tolstenko_review_photo'  => 'review_photo',
		'tolstenko_review_screen' => 'review_screen',
	);
	foreach ( $image_keys as $post_key => $meta_key ) {
		$id = isset( $_POST[ $post_key ] ) ? (int) $_POST[ $post_key ] : 0;
		if ( $id > 0 ) {
			update_post_meta( $post_id, $meta_key, $id );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	$text_keys = array(
		'tolstenko_review_video'      => 'review_video',
		'tolstenko_review_video_name' => 'review_video_name',
		'tolstenko_review_video_text' => 'review_video_text',
		'tolstenko_review_name'       => 'review_name',
		'tolstenko_review_position'   => 'review_position',
	);
	foreach ( $text_keys as $post_key => $meta_key ) {
		if ( $post_key === 'tolstenko_review_video' ) {
			$val = isset( $_POST[ $post_key ] ) ? tolstenko_kses_video_embed( wp_unslash( $_POST[ $post_key ] ) ) : '';
		} else {
			$val = isset( $_POST[ $post_key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) ) : '';
		}
		if ( $val !== '' ) {
			update_post_meta( $post_id, $meta_key, $val );
		} else {
			delete_post_meta( $post_id, $meta_key );
		}
	}

	$rating = isset( $_POST['tolstenko_review_rating'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_review_rating'] ) ) : '5';
	if ( ! in_array( $rating, array( '1', '2', '3', '4', '5' ), true ) ) {
		$rating = '5';
	}
	update_post_meta( $post_id, 'review_rating', $rating );

	$redactor = isset( $_POST['tolstenko_review_redactor'] ) ? wp_kses_post( wp_unslash( $_POST['tolstenko_review_redactor'] ) ) : '';
	if ( $redactor !== '' ) {
		update_post_meta( $post_id, 'review_redactor', $redactor );
	} else {
		delete_post_meta( $post_id, 'review_redactor' );
	}

	$contact = array(
		'url'   => isset( $_POST['tolstenko_review_contact_url'] ) ? esc_url_raw( wp_unslash( $_POST['tolstenko_review_contact_url'] ) ) : '',
		'title' => isset( $_POST['tolstenko_review_contact_title'] ) ? sanitize_text_field( wp_unslash( $_POST['tolstenko_review_contact_title'] ) ) : '',
	);
	if ( $contact['url'] !== '' || $contact['title'] !== '' ) {
		update_post_meta( $post_id, 'review_contact', $contact );
	} else {
		delete_post_meta( $post_id, 'review_contact' );
	}

	$case = isset( $_POST['tolstenko_review_case'] ) ? esc_url_raw( wp_unslash( $_POST['tolstenko_review_case'] ) ) : '';
	if ( $case !== '' ) {
		update_post_meta( $post_id, 'review_case', $case );
	} else {
		delete_post_meta( $post_id, 'review_case' );
	}
}
