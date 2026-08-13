<?php
/**
 * Footer template
 *
 * Внешний вид — Tolstenko-marketing; данные и функционал — текущий проект.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'modules/footer' );
get_template_part( 'modules/modal/modal' );
get_template_part( 'modules/modal/timed-modal' );
get_template_part( 'modules/video-bubble/video-bubble' );
?>

</div><!-- /.wrapper -->

<?php wp_footer(); ?>
</body>
</html>
