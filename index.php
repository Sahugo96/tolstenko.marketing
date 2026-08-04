<?php
/**
 * Fallback index template
 *
 * Требуется WordPress для корректной работы темы.
 * Для главной используется front-page.php, для страниц — page.php,
 * этот шаблон — запасной вариант.
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
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                ?>
                    <div class="page-content">
                        <?php the_content(); ?>
                    </div>

                <?php
            endwhile;
        else :
            ?>
            <h1>Tolstenko Theme</h1>
            <p>Тема активна, но пока здесь нет контента. Создайте страницу или запись.</p>
            <?php
        endif;
        ?>
</main>

<?php
get_footer();

