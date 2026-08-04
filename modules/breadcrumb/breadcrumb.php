<?php
/**
 * Хлебные крошки (как в tolstenko-mark modules/breadcrumb).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Вывод ancestors (родительских элементов).
 *
 * @param array       $items          ID терминов или страниц.
 * @param string      $type           term|page.
 * @param callable|null $link_callback Не используется (совместимость с исходником).
 */
if ( ! function_exists( 'tolstenko_render_breadcrumb_ancestors' ) ) {
	function tolstenko_render_breadcrumb_ancestors( $items, $type = 'term', $link_callback = null ) {
		if ( empty( $items ) ) {
			return;
		}

		$output = '';
		foreach ( $items as $item_id ) {
			if ( 'term' === $type ) {
				$term = get_term( $item_id );
				if ( is_wp_error( $term ) || ! $term ) {
					continue;
				}
				$url  = get_term_link( $term );
				$name = $term->name;
			} else {
				$url  = get_permalink( $item_id );
				$name = get_the_title( $item_id );
			}

			if ( is_wp_error( $url ) || ! $url ) {
				continue;
			}

			$output .= '<div class="breadcrumbs__item">
            <a class="breadcrumbs__link line-13-15" href="' . esc_url( $url ) . '">
                ' . esc_html( $name ) . '
                        <svg viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0.5 0.5L6.5 6L0.5 11.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
            </a>
        </div>';
		}
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	}
}
?>

<div class="breadcrumbs container">
	<div class="breadcrumbs__item">
		<a class="breadcrumbs__link line-13-15" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M7.6875 8.87531V12.0003H10.6816C9.7693 12.6656 8.66101 13.0176 7.51074 13.0003H7.48828C6.33904 13.0176 5.22901 12.6663 4.31641 12.0003H7.3125V8.87531H7.6875ZM12.958 8.25031C12.8113 9.43535 12.2715 10.54 11.3945 11.3763L11.375 11.3958V8.25031H12.958ZM3.625 8.25031V11.3948H3.62402L3.6123 11.3831L3.59961 11.3714L3.4082 11.1771C2.64767 10.3675 2.17654 9.3443 2.04102 8.25031H3.625ZM7.48828 2.00031H7.51074C8.97002 1.97836 10.3684 2.54792 11.377 3.59991L11.3877 3.61163L11.3994 3.62238C12.3891 4.57109 12.952 5.8649 12.9971 7.2298L12.6992 6.95148L8.01172 2.57648L7.48145 2.08234L6.96973 2.59503L2.59473 6.97003L2 7.56378V7.48859C1.9781 6.02963 2.54752 4.63243 3.60449 3.62433L3.61719 3.61261L3.62891 3.59991C4.63792 2.54744 6.02991 1.97842 7.48828 2.00031Z" stroke-width="1.5" />
			</svg>
		</a>
	</div>

	<?php if ( is_post_type_archive() ) :
		$post_type_obj = get_post_type_object( get_query_var( 'post_type' ) );
		?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php echo esc_html( $post_type_obj ? $post_type_obj->labels->name : '' ); ?></span>
		</div>

	<?php elseif ( is_tax() ) :
		$current_term = get_queried_object();
		$post_type    = get_query_var( 'post_type' );

		if ( empty( $post_type ) && $current_term && ! empty( $current_term->taxonomy ) ) {
			$tax_obj = get_taxonomy( $current_term->taxonomy );
			if ( $tax_obj && ! empty( $tax_obj->object_type[0] ) ) {
				$post_type = $tax_obj->object_type[0];
			}
		}

		$listing = function_exists( 'tolstenko_get_cpt_listing_breadcrumb' )
			? tolstenko_get_cpt_listing_breadcrumb( (string) $post_type )
			: null;

		if ( $listing ) :
			?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<a class="breadcrumbs__link line-13-15" href="<?php echo esc_url( $listing['url'] ); ?>">
				<?php echo esc_html( $listing['label'] ); ?>
			</a>
		</div>
			<?php
		endif;
		?>

		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php echo esc_html( $current_term->name ?? '' ); ?></span>
		</div>

	<?php elseif ( is_single() ) :
		$post_type = get_post_type();
		$listing   = function_exists( 'tolstenko_get_cpt_listing_breadcrumb' )
			? tolstenko_get_cpt_listing_breadcrumb( (string) $post_type )
			: null;

		if ( $listing ) :
			?>
			<div class="breadcrumbs__item">
				<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
				</svg>

				<a class="breadcrumbs__link line-13-15" href="<?php echo esc_url( $listing['url'] ); ?>">
					<?php echo esc_html( $listing['label'] ); ?>
				</a>
			</div>
			<?php
		endif;
		?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php the_title(); ?></span>
		</div>

	<?php elseif ( is_page() ) :
		global $post;
		if ( $post && $post->post_parent ) {
			$ancestors = array_reverse( get_post_ancestors( $post->ID ) );
			tolstenko_render_breadcrumb_ancestors( $ancestors, 'page' );
		}
		?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php the_title(); ?></span>
		</div>

	<?php elseif ( is_search() ) : ?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php esc_html_e( 'Поиск', 'tolstenko-theme' ); ?></span>
		</div>

	<?php elseif ( is_404() ) : ?>
		<div class="breadcrumbs__item">
			<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M6.25 3.75L9.55806 7.05806C9.80214 7.30214 9.80214 7.69786 9.55806 7.94194L6.25 11.25" stroke-width="1.5" stroke-linecap="round" />
			</svg>

			<span class="breadcrumbs__current line-13-15"><?php esc_html_e( 'Страница не найдена', 'tolstenko-theme' ); ?></span>
		</div>

	<?php endif; ?>

	<a class="breadcrumbs__link breadcrumbs__link--back line-13-15" href="<?php echo esc_url( home_url( '/' ) ); ?>" data-home="<?php echo esc_url( home_url( '/' ) ); ?>">
		<svg viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
			<path d="M11.8749 7.5H3.74991M6.87491 3.75L3.56685 7.05806C3.32277 7.30214 3.32277 7.69786 3.56685 7.94194L6.87491 11.25" stroke-width="1.5" stroke-linecap="round" />
		</svg>

		<?php esc_html_e( 'Назад', 'tolstenko-theme' ); ?>
	</a>
</div>
