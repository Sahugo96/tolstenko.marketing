<?php
/**
 * Шаблон страницы 404 (страница не найдена).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<main class="main main-content">
	<?php
	if ( function_exists( 'tolstenko_render_breadcrumb' ) ) {
		tolstenko_render_breadcrumb();
	}
	?>
	<div class="thanks">
		<div class="container thanks-inner">
			<h2 class="thanks-title">
				<?php esc_html_e( 'Страница не найдена', 'tolstenko-theme' ); ?>
			</h2>
			<p class="thanks-description">
				<?php esc_html_e( 'К сожалению, такой страницы нет. Возможно, она была удалена или вы ошиблись в адресе.', 'tolstenko-theme' ); ?>
			</p>
			<p class="thanks-description thanks-back-wrap">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="thanks-back-button">
					<?php esc_html_e( 'Вернуться на главную', 'tolstenko-theme' ); ?>
				</a>
			</p>
		</div>
	</div>
</main>

<?php
get_footer();

