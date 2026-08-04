<?php
/**
 * Шаблон главной страницы.
 *
 * Если в Настройки → Чтение выбрана статическая страница — выводим её контент (Gutenberg и т.п.).
 * Иначе — подсказка по настройке.
 *
 * Как завести главную: Настройки → Чтение → «Главная страница отображает» → статическую страницу → выберите страницу.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// На сайте задана статическая главная страница
if ( get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' ) ) {
	get_header();
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			?>
			<main class="main main-content">
				<div class="page-content">
					<?php the_content(); ?>
				</div>
			</main>
			<?php
		}
	}
	get_footer();
	return;
}

// Главная не задана — подсказка
get_header();
?>
<main class="main main-content">
	<div class="container">
		<h1>Главная страница</h1>
		<p>Настройте главную: <strong>Настройки → Чтение</strong> → «Главная страница отображает» → выберите <strong>статическую страницу</strong> и укажите нужную страницу.</p>
	</div>
</main>
<?php
get_footer();
