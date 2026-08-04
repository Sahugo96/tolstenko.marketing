<?php
/**
 * ACF: populate CF7 choices for callback modal field.
 * Used by field_block_callback_modal_cf7_id.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'acf/load_field/key=field_block_callback_modal_cf7_id', 'tolstenko_form_banner_populate_cf7_choices' );

function tolstenko_form_banner_populate_cf7_choices( $field ) {
    $field['choices'] = array( '' => '— не выбрана —' );
    if ( ! post_type_exists( 'wpcf7_contact_form' ) ) {
        return $field;
    }
    $forms = get_posts( array(
        'post_type'      => 'wpcf7_contact_form',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'post_status'    => 'any',
    ) );
    foreach ( $forms as $f ) {
        $title = $f->post_title ? $f->post_title : sprintf( __( 'Форма #%d', 'tolstenko-theme' ), $f->ID );
        $field['choices'][ (string) $f->ID ] = $title;
    }
    return $field;
}
