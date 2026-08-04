<?php
/**
 * Текстовый блок на архиве подкатегории услуг (не Gutenberg).
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$theme_uri    = get_template_directory_uri();
$article_term = get_query_var( 'tolstenko_article_term', null );
$title_tag    = 'h1';
$subtitle_tag = 'h2';
$title        = '';
$subtitle     = '';
$inner_content = '';
$teaser_title = 'Подзаголовок';
$teaser_text  = 'Она заполняется в процессе строительства и фиксирует список проектных задач. К производственным бумагам относятся акты лабораторных исследований, подтверждающие выполненные манипуляции, сертификаты на стройматериалы, журналы выполнения рабочих действий, а также геодезические бумаги.';
$teaser_img   = $theme_uri . '/assets/img/article-teaser-img.png';
$col_title    = 'Подзаголовок';
$col_text     = 'Данная категория содержит информацию о техническом состоянии объекта и является основным источником данных на каждом этапе его жизненного цикла.';
$full_title   = 'Виды строительной документации';
$full_text    = '<b>Правоустанавливающая</b><br>В эту группу входят лицензии, разрешения и свидетельства.<br><br><b>Исполнительная</b><br>Данная категория содержит информацию о техническом состоянии объекта.';
$showmore_enabled = true;

if ( $article_term && $article_term instanceof WP_Term ) {
    $term_custom_title = '';
    if ( function_exists( 'get_field' ) ) {
        $v = get_field( 'service_category_article_title', $article_term );
        if ( $v !== null && $v !== '' ) {
            $term_custom_title = (string) $v;
        }
    }
    $title = $term_custom_title !== '' ? $term_custom_title : $article_term->name;
    if ( function_exists( 'tolstenko_sc_get_category_article_builder_html' ) ) {
        $inner_content = tolstenko_sc_get_category_article_builder_html( $article_term );
    }
    $term_description = isset( $article_term->description ) ? trim( (string) $article_term->description ) : '';
    if ( $inner_content === '' && $term_description !== '' ) {
        $inner_content = '<div class="article-2col"><div class="article-2col-text">' . wp_kses_post( wpautop( $term_description ) ) . '</div></div>';
    }
}

$title_tag    = tolstenko_normalize_heading_tag( $title_tag, 'h1' );
$subtitle_tag = tolstenko_normalize_heading_tag( $subtitle_tag, 'h2' );
$teaser_showmore = $showmore_enabled;
$col_showmore    = $showmore_enabled;
$needs_article_contacts_gap = function_exists( 'tolstenko_is_service_category_page' ) && tolstenko_is_service_category_page();
$article_classes            = 'article' . ( $needs_article_contacts_gap ? ' tolstenko-sc-article' : '' );
?>
<!-- ARTICLE -->
<div class="<?php echo esc_attr( $article_classes ); ?>" data-showmore-enabled="<?php echo $showmore_enabled ? '1' : '0'; ?>"<?php echo $needs_article_contacts_gap ? ' style="margin-bottom:20px;display:block;"' : ''; ?>>
    <div class="container">
        <div class="about-us-text">
            <div class="about-us-text-inner article-inner">
            <div class="article-titles">
                <<?php echo esc_attr( $title_tag ); ?> class="article-title"><?php echo tolstenko_kses_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
                <?php if ( trim( (string) $subtitle ) !== '' ) : ?>
                    <<?php echo esc_attr( $subtitle_tag ); ?> class="article-subtitle"><?php echo tolstenko_kses_html( $subtitle ); ?></<?php echo esc_attr( $subtitle_tag ); ?>>
                <?php endif; ?>
            </div>
            <?php if ( is_string( $inner_content ) && trim( $inner_content ) !== '' ) : ?>
                <div class="article-content-builder<?php echo $showmore_enabled ? ' article-content-builder--showmore-enabled' : ''; ?>">
                    <?php echo $inner_content; ?>
                </div>
            <?php else : ?>
                <div class="article-teaser">
                    <div class="article-teaser-img-wrap">
                        <img src="<?php echo esc_url( $teaser_img ); ?>" class="article-teaser-img" alt="">
                    </div>
                    <div class="article-teaser-body">
                        <div class="article-teaser-title"><?php echo tolstenko_kses_html( $teaser_title ); ?></div>
                        <div class="article-teaser-text<?php echo $teaser_showmore ? ' article-teaser-text--collapsible' : ''; ?>"><?php echo wp_kses_post( wpautop( $teaser_text ) ); ?></div>
                        <?php if ( $teaser_showmore ) : ?>
                        <button type="button" class="article-teaser-showmore">Читать далее</button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="article-decor"></div>
                <div class="article-2col">
                    <div class="article-2col-title"><?php echo tolstenko_kses_html( $col_title ); ?></div>
                    <div class="article-2col-text<?php echo $col_showmore ? ' article-2col-text--collapsible' : ''; ?>"><?php echo wp_kses_post( wpautop( $col_text ) ); ?></div>
                    <?php if ( $col_showmore ) : ?>
                    <button type="button" class="article-2col-showmore">Читать далее</button>
                    <?php endif; ?>
                </div>
                <div class="article-decor"></div>
                <div class="article-full article-full-1fr">
                    <div class="article-full-body">
                        <div class="article-full-title"><?php echo tolstenko_kses_html( $full_title ); ?></div>
                        <div class="article-full-text"><?php echo wp_kses( $full_text, tolstenko_kses_article_full_html() ); ?></div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="article-decor"></div>
            </div>
        </div>
    </div>
</div>
<?php if ( $needs_article_contacts_gap ) : ?>
<div class="tolstenko-sc-article-contacts-spacer" style="display:block;height:20px;min-height:20px;margin:0;padding:0;background-color:transparent;" aria-hidden="true"></div>
<?php endif; ?>
<!-- ARTICLE END-->
