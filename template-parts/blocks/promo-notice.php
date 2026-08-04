<?php
/**
 * Промо-баннер. Данные из блока (block_id) или дефолт.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$block_id       = get_query_var( 'tolstenko_block_id', 0 );
$is_acf_block   = get_query_var( 'tolstenko_acf_block', false );
$block_attrs    = get_query_var( 'tolstenko_block_attributes', array() );
$text1          = 'Получите гайд ';
$text2          = '"Как владельцу бизнеса контролировать подрядчика по маркетингу"';
if ( ! empty( $block_attrs ) && is_array( $block_attrs ) ) {
    if ( isset( $block_attrs['promo_text_1'] ) && $block_attrs['promo_text_1'] !== '' ) {
        $text1 = $block_attrs['promo_text_1'];
    }
    if ( isset( $block_attrs['promo_text_2'] ) && $block_attrs['promo_text_2'] !== '' ) {
        $text2 = $block_attrs['promo_text_2'];
    }
} elseif ( function_exists( 'get_field' ) ) {
    if ( $is_acf_block ) {
        $t1 = get_field( 'promo_text_1' );
        $t2 = get_field( 'promo_text_2' );
    } elseif ( $block_id ) {
        $t1 = get_field( 'promo_text_1', $block_id );
        $t2 = get_field( 'promo_text_2', $block_id );
    } else {
        $t1 = $t2 = null;
    }
    if ( $t1 !== null && $t1 !== '' ) {
        $text1 = $t1;
    }
    if ( $t2 !== null && $t2 !== '' ) {
        $text2 = $t2;
    }
}
?>
<div class="promo-notice">
    <div class="container">
        <div class="promo-notice-inner">
            <div class="promo-notice-close-fake"></div>
            <div class="promo-notice-text">
                <div class="promo-notice-text-content">
                    <span>
                        <span class="blue"><?php echo tolstenko_kses_html( $text1 ); ?></span>
                        <?php echo tolstenko_kses_html( $text2 ); ?>
                    </span>
                </div>
            </div>
            <div class="promo-notice-close">
                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/promo-notice-close.svg' ); ?>" alt="">
            </div>
        </div>
    </div>
</div>
