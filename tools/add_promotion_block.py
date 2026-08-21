# -*- coding: utf-8 -*-
"""Добавляет блок promotion: таб в админке, панель, сохранение, JS-редактор, SCSS."""
import io
import os
import re

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))

def read(p):
    with io.open(os.path.join(BASE, p), 'r', encoding='utf-8') as f:
        return f.read()

def write(p, c):
    with io.open(os.path.join(BASE, p), 'w', encoding='utf-8', newline='') as f:
        f.write(c)

# 1) Таб в админ-панели дефолтов
p = 'inc/block-defaults-admin.php'
c = read(p)
old_tab = '<button type="button" class="tolstenko-df-tab" data-panel="result" data-group="main"><?php esc_html_e( \'Результат\', \'tolstenko-theme\' ); ?></button>'
new_tab = old_tab + '\n\t\t\t\t<button type="button" class="tolstenko-df-tab" data-panel="promotion" data-group="main"><?php esc_html_e( \'Продвижение\', \'tolstenko-theme\' ); ?></button>'
assert old_tab in c, 'tab not found'
c = c.replace(old_tab, new_tab, 1)

# 2) Панель дефолтов promotion (после панели result)
old_panel_end = '\t\t<div class="tolstenko-df-panel" data-panel="faq">'
promo_panel = '''\t\t<div class="tolstenko-df-panel" data-panel="promotion" data-group="main">
\t\t\t<?php
\t\t\t$promo = $all['promotion'] ?? array();
\t\t\t?>
\t\t\t<div class="row"><input type="text" name="tolstenko_block_defaults[promotion][subtitle]" value="<?php echo esc_attr( $promo['subtitle'] ?? '' ); ?>" style="width:100%" placeholder="Подзаголовок"></div>
\t\t\t<div class="row"><textarea name="tolstenko_block_defaults[promotion][title]" rows="2" style="width:100%" placeholder="Заголовок (HTML, span для акцента)"><?php echo esc_textarea( $promo['title'] ?? '' ); ?></textarea></div>
\t\t\t<div class="row"><textarea name="tolstenko_block_defaults[promotion][text]" rows="3" style="width:100%" placeholder="Текст слева (paragraph-15-25)"><?php echo esc_textarea( $promo['text'] ?? '' ); ?></textarea></div>
\t\t\t<div class="row">
\t\t\t\t<div class="muted"><?php esc_html_e( 'Карточки слева (иконка + заголовок + текст)', 'tolstenko-theme' ); ?></div>
\t\t\t\t<div data-repeater-list="promotion-items-list">
\t\t\t\t\t<?php foreach ( (array) ( $promo['items'] ?? array() ) as $idx => $it ) : ?>
\t\t\t\t\t\t<?php
\t\t\t\t\t\t$it = is_array( $it ) ? $it : array();
\t\t\t\t\t\t$ico_id = isset( $it['ico'] ) ? (int) $it['ico'] : 0;
\t\t\t\t\t\t$ico_url = $ico_id ? wp_get_attachment_image_url( $ico_id, 'thumbnail' ) : '';
\t\t\t\t\t\t?>
\t\t\t\t\t\t<div class="repeater-item" data-repeater-item>
\t\t\t\t\t\t\t<div class="cols">
\t\t\t\t\t\t\t\t<input type="text" name="tolstenko_block_defaults[promotion][items][<?php echo (int) $idx; ?>][title]" value="<?php echo esc_attr( $it['title'] ?? '' ); ?>" placeholder="Заголовок" style="flex:1">
\t\t\t\t\t\t\t\t<input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[promotion][items][<?php echo (int) $idx; ?>][ico]" value="<?php echo (int) $ico_id; ?>">
\t\t\t\t\t\t\t\t<button type="button" class="button tolstenko-defaults-pick-icon"><?php esc_html_e( 'Иконка', 'tolstenko-theme' ); ?></button>
\t\t\t\t\t\t\t\t<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
\t\t\t\t\t\t\t\t<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
\t\t\t\t\t\t\t\t<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t<div class="icon-preview" style="margin-top:8px;"><?php if ( $ico_url ) : ?><img src="<?php echo esc_url( $ico_url ); ?>" alt=""><?php endif; ?></div>
\t\t\t\t\t\t\t<div class="row"><textarea name="tolstenko_block_defaults[promotion][items][<?php echo (int) $idx; ?>][text]" rows="2" placeholder="Текст"><?php echo esc_textarea( $it['text'] ?? '' ); ?></textarea></div>
\t\t\t\t\t\t</div>
\t\t\t\t\t<?php endforeach; ?>
\t\t\t\t</div>
\t\t\t\t<div class="actions"><button type="button" class="button" data-add-item="promotion-items-list"><?php esc_html_e( 'Добавить карточку', 'tolstenko-theme' ); ?></button></div>
\t\t\t</div>
\t\t\t<div class="row"><input type="text" name="tolstenko_block_defaults[promotion][micro_title]" value="<?php echo esc_attr( $promo['micro_title'] ?? '' ); ?>" style="width:100%" placeholder="Микрозаголовок справа"></div>
\t\t\t<div class="row">
\t\t\t\t<div class="muted"><?php esc_html_e( 'Список справа (простые текстовые пункты)', 'tolstenko-theme' ); ?></div>
\t\t\t\t<div data-repeater-list="promotion-list">
\t\t\t\t\t<?php foreach ( (array) ( $promo['list'] ?? array() ) as $idx => $txt ) : ?>
\t\t\t\t\t\t<div class="repeater-item" data-repeater-item>
\t\t\t\t\t\t\t<div class="cols">
\t\t\t\t\t\t\t\t<input type="text" name="tolstenko_block_defaults[promotion][list][<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( is_array( $txt ) ? ( $txt['text'] ?? '' ) : (string) $txt ); ?>" placeholder="Текст пункта">
\t\t\t\t\t\t\t\t<button type="button" class="button move-btn" data-move-up title="Вверх">↑</button>
\t\t\t\t\t\t\t\t<button type="button" class="button move-btn" data-move-down title="Вниз">↓</button>
\t\t\t\t\t\t\t\t<button type="button" class="button" data-remove-item><?php esc_html_e( 'Удалить', 'tolstenko-theme' ); ?></button>
\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t</div>
\t\t\t\t\t<?php endforeach; ?>
\t\t\t\t</div>
\t\t\t\t<div class="actions"><button type="button" class="button" data-add-item="promotion-list"><?php esc_html_e( 'Добавить пункт', 'tolstenko-theme' ); ?></button></div>
\t\t\t</div>
\t\t</div>

'''
assert old_panel_end in c, 'faq panel not found'
c = c.replace(old_panel_end, promo_panel + old_panel_end, 1)

# 3) JS: обработка добавления элементов репитера promotion
old_js_result = "} else if (key === 'result-list') {"
new_js_promo = """} else if (key === 'promotion-items-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[promotion][items][' + idx + '][title]" placeholder="Заголовок" style="flex:1"><input type="hidden" class="tolstenko-defaults-icon-id" name="tolstenko_block_defaults[promotion][items][' + idx + '][ico]" value="0"><button type="button" class="button tolstenko-defaults-pick-icon">Иконка</button><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div><div class="icon-preview" style="margin-top:8px;"></div><div class="row"><textarea name="tolstenko_block_defaults[promotion][items][' + idx + '][text]" rows="2" placeholder="Текст"></textarea></div></div>';
				} else if (key === 'promotion-list') {
					html = '<div class="repeater-item" data-repeater-item><div class="cols"><input type="text" name="tolstenko_block_defaults[promotion][list][' + idx + ']" placeholder="Текст пункта"><button type="button" class="button move-btn" data-move-up title="Вверх">↑</button><button type="button" class="button move-btn" data-move-down title="Вниз">↓</button><button type="button" class="button" data-remove-item>Удалить</button></div></div>';
				} else if (key === 'result-list') {"""
assert old_js_result in c, 'js result-list not found'
c = c.replace(old_js_result, new_js_promo, 1)

# 4) Сохранение дефолтов promotion (после $out['result'])
old_save_result = "\t$out['faq'] = array("
promo_save = """\t$out['promotion'] = array(
\t\t'subtitle'    => tolstenko_kses_html( $raw['promotion']['subtitle'] ?? '' ),
\t\t'title'       => tolstenko_kses_html( $raw['promotion']['title'] ?? '' ),
\t\t'text'        => tolstenko_kses_html( $raw['promotion']['text'] ?? '' ),
\t\t'micro_title' => tolstenko_kses_html( $raw['promotion']['micro_title'] ?? '' ),
\t\t'items'       => array(),
\t\t'list'        => array(),
\t);
\tif ( isset( $raw['promotion']['items'] ) && is_array( $raw['promotion']['items'] ) ) {
\t\tforeach ( $raw['promotion']['items'] as $it ) {
\t\t\tif ( ! is_array( $it ) ) {
\t\t\t\tcontinue;
\t\t\t}
\t\t\t$row = array(
\t\t\t\t'ico'   => isset( $it['ico'] ) ? (int) $it['ico'] : 0,
\t\t\t\t'title' => tolstenko_kses_html( $it['title'] ?? '' ),
\t\t\t\t'text'  => tolstenko_kses_html( $it['text'] ?? '' ),
\t\t\t);
\t\t\tif ( ! $row['ico'] && $row['title'] === '' && $row['text'] === '' ) {
\t\t\t\tcontinue;
\t\t\t}
\t\t\t$out['promotion']['items'][] = $row;
\t\t}
\t}
\tif ( isset( $raw['promotion']['list'] ) && is_array( $raw['promotion']['list'] ) ) {
\t\tforeach ( $raw['promotion']['list'] as $it ) {
\t\t\t$it = trim( is_array( $it ) ? (string) ( $it['text'] ?? '' ) : (string) $it );
\t\t\tif ( $it !== '' ) {
\t\t\t\t$out['promotion']['list'][] = sanitize_text_field( $it );
\t\t\t}
\t\t}
\t}

"""
assert old_save_result in c, 'save faq not found'
c = c.replace(old_save_result, promo_save + old_save_result, 1)

write(p, c)
print('block-defaults-admin.php OK')