/**
 * Блоки темы в Gutenberg: плейсхолдер + поля для блоков с контентом (промо, баннер, контакты).
 */
(function () {
    var el = wp.element.createElement;
    var useBlockProps = wp.blockEditor && wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor && wp.blockEditor.InspectorControls;
    var MediaUpload = wp.blockEditor && wp.blockEditor.MediaUpload;
    var MediaUploadCheck = wp.blockEditor && wp.blockEditor.MediaUploadCheck;
    // RichText: поддержка новых и старых версий Gutenberg (пока не используем, но оставим на будущее)
    var RichText = (wp.blockEditor && wp.blockEditor.RichText) || (wp.editor && wp.editor.RichText);
    var TextControl = wp.components && wp.components.TextControl;
    var TextareaControl = wp.components && wp.components.TextareaControl;
    var SelectControl = wp.components && wp.components.SelectControl;
    var ToggleControl = wp.components && wp.components.ToggleControl;
    var Button = wp.components && wp.components.Button;
    var PanelBody = wp.components && wp.components.PanelBody;
    var FormTokenField = wp.components && wp.components.FormTokenField;
    var ComboboxControl = wp.components && wp.components.ComboboxControl;
    var useSelect = wp.data && wp.data.useSelect;
    var InnerBlocks = wp.blockEditor && wp.blockEditor.InnerBlocks;
    var blockDefaults = (typeof window !== 'undefined' && window.tolstenkoBlockDefaults) ? window.tolstenkoBlockDefaults : {};

    function getDefault(path, fallback) {
        try {
            var parts = String(path || '').split('.');
            var cur = blockDefaults;
            for (var i = 0; i < parts.length; i++) {
                if (!cur || typeof cur !== 'object' || !(parts[i] in cur)) return fallback;
                cur = cur[parts[i]];
            }
            return (cur === undefined || cur === null) ? fallback : cur;
        } catch (e) {
            return fallback;
        }
    }

    function valueOrEmptyIfDefault(value, def) {
        var v = (value === undefined || value === null) ? '' : String(value);
        var d = (def === undefined || def === null) ? '' : String(def);
        if (v !== '' && d !== '' && v.trim() === d.trim()) return '';
        return v;
    }

    function wrapBlock(blockProps, content) {
        return el('div', Object.assign({ className: 'tolstenko-block-edit' }, blockProps), content);
    }

    var headingTagOptions = [
        { label: 'H1', value: 'h1' },
        { label: 'H2', value: 'h2' },
        { label: 'H3', value: 'h3' },
        { label: 'H4', value: 'h4' },
        { label: 'H5', value: 'h5' },
        { label: 'H6', value: 'h6' }
    ];

    function renderHeadingTagSelect(attrs, set, key, label, fallback) {
        if (!SelectControl) return null;
        return el(SelectControl, {
            key: key,
            label: label || 'Уровень заголовка',
            value: attrs[key] || fallback || 'h2',
            options: headingTagOptions,
            onChange: function (v) {
                set((function () { var patch = {}; patch[key] = v; return patch; })());
            }
        });
    }

    function renderBlogAuthorSelect(attrs, set, key, label, emptyLabel, templateDefault) {
        if (!SelectControl) return null;
        var authors = Array.isArray(blockDefaults.blogAuthors) ? blockDefaults.blogAuthors : [];
        var options = [{ label: emptyLabel || 'По умолчанию (шаблон вакансии)', value: '' }];
        authors.forEach(function (author) {
            options.push({ label: author.label || ('Автор #' + author.index), value: String(author.index) });
        });
        var value = Object.prototype.hasOwnProperty.call(attrs, key) ? (attrs[key] || '') : (templateDefault || '');
        return el(SelectControl, {
            key: key,
            label: label || 'Автор',
            value: value,
            options: options,
            onChange: function (v) {
                var patch = {};
                patch[key] = v;
                set(patch);
            }
        });
    }

    function getMoveMeta(clientId) {
        if (!clientId || !wp.data || !wp.data.select || !wp.data.dispatch) return null;
        var select = wp.data.select('core/block-editor');
        var dispatch = wp.data.dispatch('core/block-editor');
        if (!select || !dispatch) return null;
        var parentIds = select.getBlockParents(clientId) || [];
        var parentId = parentIds.length ? parentIds[0] : '';
        var order = select.getBlockOrder(parentId) || [];
        var index = order.indexOf(clientId);
        if (index < 0) return null;
        return { parentId: parentId, index: index, total: order.length, dispatch: dispatch };
    }

    function renderMoveControls(props) {
        if (!Button || !props || !props.clientId) return null;
        var meta = getMoveMeta(props.clientId);
        if (!meta) return null;
        return el('div', {
            key: 'move-controls',
            style: { display: 'flex', gap: '8px', marginBottom: '10px' }
        }, [
            el(Button, {
                key: 'up',
                isSecondary: true,
                isSmall: true,
                disabled: meta.index <= 0,
                onClick: function () {
                    meta.dispatch.moveBlockToPosition(props.clientId, meta.parentId, meta.parentId, meta.index - 1);
                }
            }, 'Вверх'),
            el(Button, {
                key: 'down',
                isSecondary: true,
                isSmall: true,
                disabled: meta.index >= meta.total - 1,
                onClick: function () {
                    meta.dispatch.moveBlockToPosition(props.clientId, meta.parentId, meta.parentId, meta.index + 1);
                }
            }, 'Вниз')
        ]);
    }

    function pickPreviewUrlFromMedia(media) {
        if (!media) return '';
        var sizes = media.sizes || {};
        var details = media.media_details && media.media_details.sizes ? media.media_details.sizes : {};
        return (sizes.thumbnail && sizes.thumbnail.url)
            || (sizes.medium && sizes.medium.url)
            || (sizes.medium_large && sizes.medium_large.url)
            || (details.thumbnail && details.thumbnail.source_url)
            || (details.medium && details.medium.source_url)
            || (details.medium_large && details.medium_large.source_url)
            || (details.large && details.large.source_url)
            || '';
    }

    function getGalleryPreviewUrl(item) {
        if (!item) return '';
        if (item.previewUrl) return item.previewUrl;
        if (item.id && wp.data && wp.data.select) {
            var media = wp.data.select('core').getMedia(item.id);
            var p = pickPreviewUrlFromMedia(media);
            if (p) return p;
            // Если это attachment, ждём миниатюру из REST и не грузим full-size в превью.
            return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';
        }
        return item.url || '';
    }

    // Промо-плашка перенесена в глобальные настройки header-footer (не Gutenberg-блок).

    // Контакты (.contacts) — как «Главный баннер» / «Автор» / «Партнёры».
    // Пустые поля = дефолты из «Настройки сайта → Страница контактов».
    wp.blocks.registerBlockType('tolstenko/contacts-page', {
        title: 'Контакты',
        category: 'tolstenko-blocks-contacts',
        icon: 'phone',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var legacyItems = Array.isArray(attrs.block_contacts_page_items) ? attrs.block_contacts_page_items : [];
            var addresses = Array.isArray(attrs.block_contacts_page_addresses) ? attrs.block_contacts_page_addresses.slice() : [];
            var galleryIdsKey = addresses.map(function (a) {
                return (Array.isArray(a && a.gallery) ? a.gallery : []).join(',');
            }).join('|');
            if (useSelect) {
                useSelect(function (select) {
                    addresses.forEach(function (addr) {
                        (Array.isArray(addr && addr.gallery) ? addr.gallery : []).forEach(function (id) {
                            var n = parseInt(id, 10) || 0;
                            if (n) select('core').getMedia(n);
                        });
                        (Array.isArray(addr && addr.items) ? addr.items : []).forEach(function (item) {
                            var iconId = item && (parseInt(item.icon, 10) || 0);
                            if (iconId) select('core').getMedia(iconId);
                        });
                    });
                    return galleryIdsKey;
                }, [galleryIdsKey]);
            }

            function setAddresses(next) {
                set({ block_contacts_page_addresses: next, block_contacts_page_items: [] });
            }

            function updateAddress(i, patch) {
                var next = addresses.slice();
                next[i] = Object.assign({}, next[i] || { address: '', gallery: [], items: [] }, patch);
                setAddresses(next);
            }

            function getAddrItems(row) {
                if (Array.isArray(row.items) && row.items.length) return row.items;
                if (legacyItems.length) return legacyItems;
                return [];
            }

            function updateAddrItem(addrIndex, itemIndex, patch) {
                var row = addresses[addrIndex] && typeof addresses[addrIndex] === 'object'
                    ? addresses[addrIndex]
                    : { address: '', gallery: [], items: [] };
                var items = getAddrItems(row).slice();
                items[itemIndex] = Object.assign({}, items[itemIndex] || { title: '', icon: 0, links: [] }, patch);
                updateAddress(addrIndex, { items: items });
            }

            var nodes = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Контакты'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты из «Страница контактов». У каждого адреса свои контактные данные и галерея.'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_contacts_page_title || '',
                    placeholder: getDefault('contacts_page.title', ''),
                    onChange: function (v) { set({ block_contacts_page_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_contacts_page_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'atl', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Адреса (вкладки + галерея)')
            ];

            addresses.forEach(function (addr, i) {
                var row = addr && typeof addr === 'object' ? addr : { address: '', gallery: [], items: [] };
                var gallery = Array.isArray(row.gallery)
                    ? row.gallery.map(function (id) { return parseInt(id, 10) || 0; }).filter(Boolean)
                    : [];
                var items = getAddrItems(row);
                var itemNodes = items.map(function (item, ii) {
                    var it = item && typeof item === 'object' ? item : { title: '', icon: 0, links: [] };
                    var links = Array.isArray(it.links) ? it.links : [];
                    var iconId = parseInt(it.icon, 10) || 0;
                    return el('div', {
                        key: 'item-' + ii,
                        style: { marginTop: '8px', padding: '8px', border: '1px solid #e0e0e0', borderRadius: '4px', background: '#fff' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 'tt',
                            label: 'Заголовок пункта',
                            value: it.title || '',
                            onChange: function (v) { updateAddrItem(i, ii, { title: v }); }
                        }) : null,
                        MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'ico' },
                            el(MediaUpload, {
                                allowedTypes: ['image'],
                                value: iconId || undefined,
                                onSelect: function (media) { updateAddrItem(i, ii, { icon: media && media.id ? media.id : 0 }); },
                                render: function (obj) {
                                    return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, iconId ? 'Сменить иконку' : 'Иконка');
                                }
                            })
                        ) : null,
                        iconId && Button ? el(Button, {
                            key: 'icorm',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginLeft: '8px' },
                            onClick: function () { updateAddrItem(i, ii, { icon: 0 }); }
                        }, 'Убрать') : null,
                        el('p', { key: 'll', style: { margin: '10px 0 6px', fontWeight: '600' } }, 'Ссылки'),
                        links.map(function (link, li) {
                            var lk = link && typeof link === 'object' ? link : { text: '', link: '' };
                            return el('div', {
                                key: 'lk-' + li,
                                style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                            }, [
                                TextControl ? el(TextControl, {
                                    key: 't',
                                    label: 'Текст',
                                    value: lk.text || '',
                                    onChange: function (v) {
                                        var nextLinks = links.slice();
                                        nextLinks[li] = Object.assign({}, lk, { text: v || '' });
                                        updateAddrItem(i, ii, { links: nextLinks });
                                    }
                                }) : null,
                                TextControl ? el(TextControl, {
                                    key: 'u',
                                    label: 'URL',
                                    value: lk.link || '',
                                    onChange: function (v) {
                                        var nextLinks = links.slice();
                                        nextLinks[li] = Object.assign({}, lk, { link: v || '' });
                                        updateAddrItem(i, ii, { links: nextLinks });
                                    }
                                }) : null,
                                Button ? el(Button, {
                                    key: 'rm',
                                    isDestructive: true,
                                    isSmall: true,
                                    onClick: function () {
                                        updateAddrItem(i, ii, { links: links.filter(function (_, idx) { return idx !== li; }) });
                                    }
                                }, '×') : null
                            ]);
                        }),
                        Button ? el(Button, {
                            key: 'addlink',
                            isSecondary: true,
                            isSmall: true,
                            onClick: function () { updateAddrItem(i, ii, { links: links.concat([{ text: '', link: '' }]) }); }
                        }, 'Добавить ссылку') : null,
                        Button ? el(Button, {
                            key: 'rmitem',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginTop: '8px' },
                            onClick: function () {
                                updateAddress(i, { items: items.filter(function (_, idx) { return idx !== ii; }) });
                            }
                        }, 'Удалить пункт') : null
                    ]);
                });

                nodes.push(el('div', {
                    key: 'addr-' + i,
                    style: { marginBottom: '12px', padding: '10px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                }, [
                    TextControl ? el(TextControl, {
                        key: 'a',
                        label: 'Адрес (подпись вкладки)',
                        value: row.address || '',
                        onChange: function (v) { updateAddress(i, { address: v }); }
                    }) : null,
                    el('p', { key: 'ctl', style: { margin: '14px 0 6px', fontWeight: '600' } }, 'Контактные данные этого адреса'),
                    el('p', { key: 'cth', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Меняются слева при переключении вкладки.'),
                    itemNodes,
                    Button ? el(Button, {
                        key: 'additem',
                        isSecondary: true,
                        isSmall: true,
                        style: { marginTop: '8px' },
                        onClick: function () {
                            updateAddress(i, { items: items.concat([{ title: '', icon: 0, links: [{ text: '', link: '' }] }]) });
                        }
                    }, 'Добавить пункт') : null,
                    el('p', { key: 'gl', style: { margin: '14px 0 6px', fontWeight: '600' } }, 'Галерея (список фото)'),
                    el('p', { key: 'gh', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Каждый пункт — одно фото. «Добавить пункт» — ещё одно.'),
                    (gallery.length ? gallery : [0]).map(function (id, gi) {
                        var imgId = parseInt(id, 10) || 0;
                    return el('div', {
                            key: 'g-' + gi,
                            style: { marginBottom: '8px', padding: '8px', border: '1px solid #e0e0e0', borderRadius: '4px', background: '#fff' }
                        }, [
                            el('div', {
                                key: 'row',
                                style: { display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' }
                            }, [
                                imgId ? el('img', {
                                    key: 'ph',
                                    src: getGalleryPreviewUrl({ id: imgId }),
                                    alt: '',
                                    style: { width: '64px', height: '64px', objectFit: 'cover', border: '1px solid #ddd' }
                                }) : el('span', {
                                    key: 'ph-empty',
                                    style: { width: '64px', height: '64px', border: '1px dashed #ccc', display: 'inline-block' }
                                }),
                                MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'pick' },
                            el(MediaUpload, {
                                allowedTypes: ['image'],
                                        multiple: false,
                                        value: imgId || undefined,
                                        onSelect: function (media) {
                                            var next = (gallery.length ? gallery.slice() : []);
                                            while (next.length <= gi) next.push(0);
                                            next[gi] = media && media.id ? parseInt(media.id, 10) || 0 : 0;
                                            updateAddress(i, { gallery: next.filter(Boolean).length ? next : [0] });
                                        },
                                render: function (obj) {
                                            return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, imgId ? 'Сменить' : 'Выбрать');
                                }
                            })
                        ) : null,
                                imgId && Button ? el(Button, {
                                    key: 'clr',
                                    isSmall: true,
                                    onClick: function () {
                                        var next = gallery.slice();
                                        next[gi] = 0;
                                        updateAddress(i, { gallery: next });
                                    }
                                }, 'Очистить') : null
                            ]),
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                                style: { marginTop: '6px' },
                                onClick: function () {
                                    var next = gallery.filter(function (_, idx) { return idx !== gi; });
                                    updateAddress(i, { gallery: next.length ? next : [0] });
                                }
                        }, 'Удалить пункт') : null
                    ]);
                    }),
                    Button ? el(Button, {
                        key: 'addgal',
                        isSecondary: true,
                        isSmall: true,
                        style: { marginBottom: '8px' },
                        onClick: function () {
                            updateAddress(i, { gallery: (gallery.length ? gallery : []).concat([0]) });
                        }
                    }, 'Добавить пункт') : null,
                    Button ? el(Button, {
                        key: 'rmaddr',
                        isDestructive: true,
                        isSmall: true,
                        style: { marginTop: '8px', marginLeft: '8px' },
                        onClick: function () { setAddresses(addresses.filter(function (_, idx) { return idx !== i; })); }
                    }, 'Удалить адрес') : null
                ]));
            });

            nodes.push(Button ? el(Button, {
                key: 'addaddr',
                isSecondary: true,
                onClick: function () {
                    setAddresses(addresses.concat([{ address: '', gallery: [], items: [{ title: '', icon: 0, links: [{ text: '', link: '' }] }] }]));
                }
            }, 'Добавить адрес') : null);

            return el('div', blockProps, nodes);
        },
        save: function () { return null; }
    });

    // Реквизиты (.details) — как «Главный баннер»: заголовок + пункты списка + поля формы.
    wp.blocks.registerBlockType('tolstenko/contacts-details', {
        title: 'Реквизиты',
        category: 'tolstenko-blocks-contacts',
        icon: 'id-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_contacts_details_items) ? attrs.block_contacts_details_items.slice() : [];

            function setItems(next) { set({ block_contacts_details_items: next }); }
            function updateItem(i, v) {
                var next = items.slice();
                next[i] = v;
                setItems(next);
            }
            function removeItem(i) {
                setItems(items.filter(function (_, idx) { return idx !== i; }));
            }

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Реквизиты'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты из «Страница контактов».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_contacts_details_title || '',
                    placeholder: getDefault('contacts_details.title', ''),
                    onChange: function (v) { set({ block_contacts_details_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_contacts_details_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, 'Блоки реквизитов (HTML)'),
                items.map(function (item, i) {
                    var txt = typeof item === 'string' ? item : ((item && item.text) || '');
                    return el('div', {
                        key: 'it-' + i,
                        style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                    }, [
                        TextareaControl ? el(TextareaControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: txt,
                            onChange: function (v) { updateItem(i, v); },
                            rows: 3
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () { removeItem(i); }
                        }, '×') : null
                    ]);
                }),
                Button ? el(Button, {
                    key: 'add',
                    isSecondary: true,
                    onClick: function () { setItems(items.concat([''])); },
                    style: { marginBottom: '12px' }
                }, 'Добавить пункт') : null,
                TextControl ? el(TextControl, {
                    key: 'ft',
                    label: 'Заголовок формы',
                    value: attrs.block_contacts_details_form_title || '',
                    placeholder: getDefault('contacts_details.form_title', 'Свяжитесь с нами'),
                    onChange: function (v) { set({ block_contacts_details_form_title: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'fs',
                    label: 'Текст формы',
                    value: attrs.block_contacts_details_form_text || '',
                    placeholder: getDefault('contacts_details.form_text', 'Оставьте заявку и мы свяжемся с вами'),
                    onChange: function (v) { set({ block_contacts_details_form_text: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Карты (.maps) — заголовок + список адресов/iframe.
    wp.blocks.registerBlockType('tolstenko/contacts-maps', {
        title: 'Карты',
        category: 'tolstenko-blocks-contacts',
        icon: 'location-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_contacts_maps_items) ? attrs.block_contacts_maps_items.slice() : [];

            function setItems(next) { set({ block_contacts_maps_items: next }); }

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Карты'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты из «Страница контактов».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_contacts_maps_title || '',
                    placeholder: getDefault('contacts_maps.title', ''),
                    onChange: function (v) { set({ block_contacts_maps_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_contacts_maps_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'il', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Адреса на карте'),
                items.map(function (item, i) {
                    var row = item && typeof item === 'object' ? item : { address: '', map: '' };
                    return el('div', {
                        key: 'm-' + i,
                        style: { marginBottom: '8px', padding: '8px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 'a',
                            label: 'Адрес (вкладка)',
                            value: row.address || '',
                            onChange: function (v) {
                                var next = items.slice();
                                next[i] = Object.assign({}, row, { address: v || '' });
                                setItems(next);
                            }
                        }) : null,
                        TextareaControl ? el(TextareaControl, {
                            key: 'map',
                            label: 'Код карты (iframe)',
                            value: row.map || '',
                            onChange: function (v) {
                                var next = items.slice();
                                next[i] = Object.assign({}, row, { map: v || '' });
                                setItems(next);
                            },
                            rows: 3
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () { setItems(items.filter(function (_, idx) { return idx !== i; })); }
                        }, 'Удалить') : null
                    ]);
                }),
                Button ? el(Button, {
                    key: 'add',
                    isSecondary: true,
                    onClick: function () { setItems(items.concat([{ address: '', map: '' }])); }
                }, 'Добавить адрес') : null
            ]);
        },
        save: function () { return null; }
    });

    // Главный баннер (.hero из tolstenko).
    wp.blocks.registerBlockType('tolstenko/main-hero', {
        title: 'Главный баннер',
        category: 'tolstenko-blocks-new',
        icon: 'cover-image',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_main_hero_items) ? attrs.block_main_hero_items.slice() : [];
            var imageId = parseInt(attrs.block_main_hero_image, 10) || 0;
            var presentId = parseInt(attrs.block_main_hero_present_image, 10) || 0;
            var showPromoAttr = String(attrs.block_main_hero_show_promo || '');
            var showPromo = showPromoAttr === '1' || (showPromoAttr === '' && !!getDefault('main_hero.show_promo', true));

            function setItems(next) { set({ block_main_hero_items: next }); }
            function addItem() { setItems(items.concat([''])); }
            function updateItem(i, v) {
                var next = items.slice();
                next[i] = v;
                setItems(next);
            }
            function removeItem(i) {
                setItems(items.filter(function (_, idx) { return idx !== i; }));
            }

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Главный баннер'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты. Кнопка открывает модалку заявки.'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_main_hero_title || '',
                    placeholder: getDefault('main_hero.title', ''),
                    onChange: function (v) { set({ block_main_hero_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_main_hero_title_tag', 'Тег заголовка', 'h1'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст (HTML)',
                    value: attrs.block_main_hero_text || '',
                    placeholder: getDefault('main_hero.text', ''),
                    onChange: function (v) { set({ block_main_hero_text: v }); },
                    rows: 3
                }) : null,
                el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, 'Пункты списка (HTML)'),
                items.map(function (txt, i) {
                    return el('div', { key: 'it-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' } }, [
                        TextareaControl ? el(TextareaControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: typeof txt === 'string' ? txt : (txt && txt.text) || '',
                            onChange: function (v) { updateItem(i, v); },
                            rows: 2
                        }) : null,
                        Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { removeItem(i); } }, '×') : null
                    ]);
                }),
                Button ? el(Button, { key: 'add', isSecondary: true, onClick: addItem, style: { marginBottom: '12px' } }, 'Добавить пункт') : null,
                TextControl ? el(TextControl, {
                    key: 'btn',
                    label: 'Текст кнопки',
                    value: attrs.block_main_hero_btn_text || '',
                    placeholder: getDefault('main_hero.btn_text', 'Оставить заявку'),
                    onChange: function (v) { set({ block_main_hero_btn_text: v }); }
                }) : null,
                ToggleControl ? el(ToggleControl, {
                    key: 'sp',
                    label: 'Показать промо у кнопки',
                    checked: showPromo,
                    onChange: function (v) { set({ block_main_hero_show_promo: v ? '1' : '0' }); }
                }) : null,
                TextareaControl ? el(TextareaControl, {
                    key: 'pt',
                    label: 'Текст промо (HTML)',
                    value: attrs.block_main_hero_promo_text || '',
                    placeholder: getDefault('main_hero.promo_text', ''),
                    onChange: function (v) { set({ block_main_hero_promo_text: v }); },
                    rows: 2
                }) : null,
                MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'pr' },
                        el(MediaUpload, {
                        onSelect: function (media) {
                            set({ block_main_hero_present_image: media && media.id ? media.id : 0 });
                        },
                            allowedTypes: ['image'],
                        value: presentId,
                            render: function (obj) {
                            return el(Button, {
                                variant: 'secondary',
                                onClick: obj.open,
                                style: { marginBottom: '8px' }
                            }, presentId ? 'Сменить иконку подарка' : 'Выбрать иконку подарка');
                        }
                    })
                ) : null,
                presentId ? el(Button, {
                    key: 'prm',
                    isDestructive: true,
                    variant: 'link',
                    onClick: function () { set({ block_main_hero_present_image: 0 }); }
                }, 'Убрать иконку подарка') : null,
                TextControl ? el(TextControl, {
                    key: 'pn',
                    label: 'Имя персоны',
                    value: attrs.block_main_hero_person_name || '',
                    placeholder: getDefault('main_hero.person_name', ''),
                    onChange: function (v) { set({ block_main_hero_person_name: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'pp',
                    label: 'Должность',
                    value: attrs.block_main_hero_person_position || '',
                    placeholder: getDefault('main_hero.person_position', ''),
                    onChange: function (v) { set({ block_main_hero_person_position: v }); }
                }) : null,
                MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'img' },
                    el(MediaUpload, {
                        onSelect: function (media) {
                            set({ block_main_hero_image: media && media.id ? media.id : 0 });
                        },
                        allowedTypes: ['image'],
                        value: imageId,
                        render: function (obj) {
                            return el(Button, {
                                variant: 'secondary',
                                onClick: obj.open,
                                style: { marginTop: '8px' }
                            }, imageId ? 'Сменить изображение' : 'Выбрать изображение');
                        }
                    })
                ) : null,
                imageId ? el(Button, {
                    key: 'irm',
                    isDestructive: true,
                    variant: 'link',
                    onClick: function () { set({ block_main_hero_image: 0 }); }
                }, 'Убрать изображение') : null
            ]);
        },
        save: function () { return null; }
    });

    // Страница «Спасибо» — заголовок и описание после отправки формы
    wp.blocks.registerBlockType('tolstenko/thanks', {
        title: 'Страница «Спасибо»',
        category: 'tolstenko-blocks',
        icon: 'layout',
        edit: function (props) {
            var attrs = props.attributes;
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            if (!TextControl || !TextareaControl) {
                return wrapBlock(blockProps, 'Страница «Спасибо»');
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Страница «Спасибо»'),
                el(TextControl, {
                    key: 'title',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_thanks_title || '',
                    placeholder: getDefault('thanks.title', 'Спасибо за заявку!'),
                    onChange: function (v) { set({ block_thanks_title: v }); }
                }),
                renderHeadingTagSelect(attrs, set, 'block_thanks_title_tag', 'Тег заголовка', 'h2'),
                el(TextareaControl, {
                    key: 'desc',
                    label: 'Описание',
                    value: attrs.block_thanks_description || '',
                    placeholder: getDefault('thanks.description', 'Мы свяжемся с вами в ближайшее время.'),
                    onChange: function (v) { set({ block_thanks_description: v }); },
                    rows: 2
                })
            ]);
        },
        save: function () { return null; }
    });

    // Отзывы — как «Слайдер услуг»: заголовок, текст, выбор CPT review, показ reviews__items.
    wp.blocks.registerBlockType('tolstenko/reviews', {
        title: 'Отзывы',
        category: 'tolstenko-blocks-new',
        icon: 'format-status',
        edit: function ReviewsEdit(props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('reviews.title', 'Отзывы');
            var defText = getDefault('reviews.text', '');
            var defShowItems = !!getDefault('reviews.show_items', true);
            var selectedIds = Array.isArray(attrs.block_reviews_ids)
                ? attrs.block_reviews_ids.map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; })
                : [];
            var showItems = typeof attrs.block_reviews_show_items === 'boolean'
                ? attrs.block_reviews_show_items
                : defShowItems;

            var reviews = (useSelect ? useSelect(function (select) {
                var records = select('core').getEntityRecords('postType', 'review', {
                    per_page: 100,
                    status: 'publish',
                    orderby: 'title',
                    order: 'asc',
                    _fields: 'id,title'
                });
                return Array.isArray(records) ? records : [];
            }, []) : []);

            var idToTitle = {};
            var titleToId = {};
            var suggestions = [];
            reviews.forEach(function (post) {
                if (!post || !post.id) return;
                var t = (post.title && post.title.rendered) ? String(post.title.rendered) : ('#' + post.id);
                t = t.replace(/<[^>]+>/g, '').trim() || ('#' + post.id);
                var key = t;
                var n = 2;
                while (titleToId[key] && titleToId[key] !== post.id) {
                    key = t + ' (' + n + ')';
                    n += 1;
                }
                idToTitle[post.id] = key;
                titleToId[key] = post.id;
                suggestions.push(key);
            });

            var tokens = selectedIds.map(function (id) {
                return idToTitle[id] || ('#' + id);
            });

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Отзывы'),
                el('p', {
                    key: 'h',
                    style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' }
                }, 'Пустые поля и список отзывов — из дефолтов «Отзывы». Если отзывы не выбраны — показываются все. Контент каждого отзыва задаётся в CPT «Отзывы».'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_reviews_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_reviews_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_reviews_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_reviews_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_reviews_text: v }); }
                }) : null,
                ToggleControl ? el(ToggleControl, {
                    key: 'si',
                    label: 'Показывать блок рейтингов (reviews__items)',
                    checked: !!showItems,
                    onChange: function (v) { set({ block_reviews_show_items: !!v }); }
                }) : null,
                FormTokenField ? el(FormTokenField, {
                    key: 'ids',
                    label: 'Отзывы (пусто = дефолты / все)',
                    value: tokens,
                    suggestions: suggestions,
                    onChange: function (nextTokens) {
                        var nextIds = [];
                        (nextTokens || []).forEach(function (token) {
                            var id = titleToId[token];
                            if (!id && /^#\d+$/.test(token)) {
                                id = parseInt(token.slice(1), 10);
                            }
                            id = parseInt(id, 10);
                            if (id > 0 && nextIds.indexOf(id) === -1) {
                                nextIds.push(id);
                            }
                        });
                        set({ block_reviews_ids: nextIds });
                    },
                    __experimentalExpandOnFocus: true
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Консультации (WhatsApp / Telegram / телефон / бесплатная форма).
    function registerConsultationBlock(config) {
        wp.blocks.registerBlockType(config.name, {
            title: config.title,
            category: 'tolstenko-blocks-new',
            icon: config.icon || 'phone',
            edit: function (props) {
                var attrs = props.attributes || {};
                var set = props.setAttributes;
                var blockProps = useBlockProps ? useBlockProps() : {};
                var fields = [];
                fields.push(el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, config.title));
                fields.push(el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Дефолты блоков». Ссылки на соцсети при пустом значении берутся из «Шапка и подвал».'));
                (config.fields || []).forEach(function (f, i) {
                    if (f.type === 'headingTag') {
                        fields.push(renderHeadingTagSelect(attrs, set, f.key, f.label, f.fallback || 'h2'));
                        return;
                    }
                    if (f.type === 'textarea' && TextareaControl) {
                        fields.push(el(TextareaControl, {
                            key: 'f-' + i,
                            label: f.label,
                            value: attrs[f.key] || '',
                            placeholder: getDefault(f.defaultPath, f.placeholder || ''),
                            onChange: function (v) {
                                var patch = {};
                                patch[f.key] = v;
                                set(patch);
                            }
                        }));
                    return;
                }
                    if (f.type === 'image' && MediaUpload && MediaUploadCheck) {
                        var imgId = parseInt(attrs[f.key] || 0, 10) || 0;
                        fields.push(el('div', { key: 'img-' + i, style: { marginTop: '8px' } }, [
                            el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, f.label),
                            el(MediaUploadCheck, { key: 'muc' },
                            el(MediaUpload, {
                                allowedTypes: ['image'],
                                    value: imgId,
                                    onSelect: function (media) {
                                        var patch = {};
                                        patch[f.key] = media && media.id ? media.id : 0;
                                        set(patch);
                                    },
                                render: function (obj) {
                                        return el(Button, { isSecondary: true, onClick: obj.open }, imgId ? 'Заменить изображение' : 'Выбрать изображение');
                                }
                            })
                            ),
                            imgId && Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                                style: { marginLeft: '8px' },
                                onClick: function () {
                                    var patch = {};
                                    patch[f.key] = 0;
                                    set(patch);
                                }
                            }, 'Удалить') : null
                        ]));
                        return;
                    }
                    if (TextControl) {
                        fields.push(el(TextControl, {
                            key: 'f-' + i,
                            label: f.label,
                            value: attrs[f.key] || '',
                            placeholder: getDefault(f.defaultPath, f.placeholder || ''),
                            onChange: function (v) {
                                var patch = {};
                                patch[f.key] = v;
                                set(patch);
                            }
                        }));
                    }
                });
                return wrapBlock(blockProps, fields);
            },
            save: function () { return null; }
        });
    }

    registerConsultationBlock({
        name: 'tolstenko/consultation-whatsapp',
        title: 'Консультация WhatsApp',
        icon: 'format-chat',
        fields: [
            { key: 'block_consultation_whatsapp_title', label: 'Заголовок', defaultPath: 'consultation_whatsapp.title' },
            { type: 'headingTag', key: 'block_consultation_whatsapp_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_consultation_whatsapp_text', label: 'Текст', defaultPath: 'consultation_whatsapp.text' },
            { key: 'block_consultation_whatsapp_btn_text', label: 'Текст кнопки', defaultPath: 'consultation_whatsapp.btn_text' },
            { key: 'block_consultation_whatsapp_btn_url', label: 'Ссылка кнопки', defaultPath: 'consultation_whatsapp.btn_url', placeholder: 'https://wa.me/...' },
            { key: 'block_consultation_whatsapp_color', label: 'Цвет кнопки', defaultPath: 'consultation_whatsapp.color', placeholder: '#25D366' },
            { key: 'block_consultation_whatsapp_color_hover', label: 'Цвет hover', defaultPath: 'consultation_whatsapp.color_hover', placeholder: '#1EBE57' }
        ]
    });

    registerConsultationBlock({
        name: 'tolstenko/consultation-tg',
        title: 'Консультация Telegram',
        icon: 'share',
        fields: [
            { key: 'block_consultation_tg_title', label: 'Заголовок', defaultPath: 'consultation_tg.title' },
            { type: 'headingTag', key: 'block_consultation_tg_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_consultation_tg_text', label: 'Текст', defaultPath: 'consultation_tg.text' },
            { key: 'block_consultation_tg_btn_text', label: 'Текст кнопки', defaultPath: 'consultation_tg.btn_text' },
            { key: 'block_consultation_tg_btn_url', label: 'Ссылка Telegram', defaultPath: 'consultation_tg.btn_url' },
            { key: 'block_consultation_tg_text_btn', label: 'Подпись под кнопкой', defaultPath: 'consultation_tg.text_btn' },
            { type: 'image', key: 'block_consultation_tg_image', label: 'Фото / аватар' }
        ]
    });

    registerConsultationBlock({
        name: 'tolstenko/consultation-tel',
        title: 'Консультация телефон',
        icon: 'phone',
        fields: [
            { type: 'textarea', key: 'block_consultation_tel_title', label: 'Заголовок', defaultPath: 'consultation_tel.title' },
            { type: 'headingTag', key: 'block_consultation_tel_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_consultation_tel_message', label: 'Текст в пузыре', defaultPath: 'consultation_tel.message' },
            { key: 'block_consultation_tel_position', label: 'Должность', defaultPath: 'consultation_tel.position' },
            { key: 'block_consultation_tel_phone', label: 'Телефон', defaultPath: 'consultation_tel.phone' },
            { key: 'block_consultation_tel_btn_tel_text', label: 'Текст кнопки звонка', defaultPath: 'consultation_tel.btn_tel_text' },
            { key: 'block_consultation_tel_btn_messenger_text', label: 'Текст кнопки мессенджера', defaultPath: 'consultation_tel.btn_messenger_text' },
            { key: 'block_consultation_tel_btn_messenger_url', label: 'Ссылка мессенджера', defaultPath: 'consultation_tel.btn_messenger_url' },
            { key: 'block_consultation_tel_color', label: 'Цвет кнопки мессенджера', defaultPath: 'consultation_tel.color' },
            { key: 'block_consultation_tel_color_hover', label: 'Цвет hover', defaultPath: 'consultation_tel.color_hover' },
            { type: 'image', key: 'block_consultation_tel_image', label: 'Аватар менеджера' }
        ]
    });

    registerConsultationBlock({
        name: 'tolstenko/consultation-free',
        title: 'Бесплатная консультация',
        icon: 'email',
        fields: [
            { key: 'block_consultation_free_title', label: 'Заголовок', defaultPath: 'consultation_free.title' },
            { type: 'headingTag', key: 'block_consultation_free_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_consultation_free_text', label: 'Текст', defaultPath: 'consultation_free.text' },
            { key: 'block_consultation_free_subtitle', label: 'Подзаголовок формы', defaultPath: 'consultation_free.subtitle' },
            { key: 'block_consultation_free_contacts_label', label: 'Подпись контактов', defaultPath: 'consultation_free.contacts_label' },
            { key: 'block_consultation_free_phone', label: 'Телефон', defaultPath: 'consultation_free.phone' },
            { key: 'block_consultation_free_telegram_url', label: 'Telegram URL', defaultPath: 'consultation_free.telegram_url' },
            { key: 'block_consultation_free_whatsapp_url', label: 'WhatsApp URL', defaultPath: 'consultation_free.whatsapp_url' },
            { key: 'block_consultation_free_vk_url', label: 'VK URL', defaultPath: 'consultation_free.vk_url' },
            { type: 'image', key: 'block_consultation_free_image', label: 'Изображение справа' }
        ]
    });

    // Бесплатный аудит — список пунктов + кнопка.
    wp.blocks.registerBlockType('tolstenko/free-audit', {
        title: 'Бесплатный аудит',
        category: 'tolstenko-blocks-new',
        icon: 'yes-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_free_audit_items) ? attrs.block_free_audit_items : [];
            function addItem() { set({ block_free_audit_items: items.concat(['']) }); }
            function updateItem(i, v) {
                var next = items.slice();
                next[i] = v;
                set({ block_free_audit_items: next });
            }
            function removeItem(i) {
                set({ block_free_audit_items: items.filter(function (_, idx) { return idx !== i; }) });
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Бесплатный аудит'),
                el('p', { key: 'h', style: { marginTop: 0, fontSize: '12px', color: '#757575' } }, 'Пустой список/кнопка — из дефолтов. Пустая ссылка открывает модалку заявки.'),
                items.map(function (txt, i) {
                    return el('div', { key: 'it-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px' } }, [
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: typeof txt === 'string' ? txt : (txt && txt.text) || '',
                            onChange: function (v) { updateItem(i, v); }
                        }) : null,
                        Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { removeItem(i); } }, '×') : null
                    ]);
                }),
                Button ? el(Button, { key: 'add', isSecondary: true, onClick: addItem }, 'Добавить пункт') : null,
                TextControl ? el(TextControl, {
                    key: 'bt',
                    label: 'Текст кнопки',
                    value: attrs.block_free_audit_btn_text || '',
                    placeholder: getDefault('free_audit.btn_text', 'Получить аудит'),
                    onChange: function (v) { set({ block_free_audit_btn_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'bu',
                    label: 'Ссылка кнопки',
                    value: attrs.block_free_audit_btn_url || '',
                    placeholder: 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_free_audit_btn_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Решение — заголовок + два ряда пунктов.
    wp.blocks.registerBlockType('tolstenko/solution', {
        title: 'Решение',
        category: 'tolstenko-blocks-new',
        icon: 'yes',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_solution_items) ? attrs.block_solution_items.slice() : [];
            var itemsSecond = Array.isArray(attrs.block_solution_items_second) ? attrs.block_solution_items_second.slice() : [];

            function setItems(next) { set({ block_solution_items: next }); }
            function setItemsSecond(next) { set({ block_solution_items_second: next }); }
            function updateItem(i, v) {
                var next = items.slice();
                next[i] = v;
                setItems(next);
            }
            function updateItemSecond(i, v) {
                var next = itemsSecond.slice();
                next[i] = v;
                setItemsSecond(next);
            }
            function removeItem(i) { setItems(items.filter(function (_, idx) { return idx !== i; })); }
            function removeItemSecond(i) { setItemsSecond(itemsSecond.filter(function (_, idx) { return idx !== i; })); }

            function renderList(list, updateFn, removeFn, addFn, keyPrefix, label) {
                return [
                    el('p', { key: keyPrefix + '-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, label),
                    list.map(function (txt, i) {
                        return el('div', { key: keyPrefix + '-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' } }, [
                            TextareaControl ? el(TextareaControl, {
                                key: 't',
                                label: 'Пункт ' + (i + 1) + ' (HTML)',
                                value: typeof txt === 'string' ? txt : (txt && txt.text) || '',
                                onChange: function (v) { updateFn(i, v); },
                                rows: 2
                            }) : null,
                            Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { removeFn(i); } }, '×') : null
                        ]);
                    }),
                    Button ? el(Button, { key: keyPrefix + '-add', isSecondary: true, onClick: addFn, style: { marginBottom: '8px' } }, 'Добавить пункт') : null
                ];
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Решение'),
                TextareaControl ? el(TextareaControl, {
                    key: 'title',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_solution_title || '',
                    placeholder: getDefault('solution.title', ''),
                    onChange: function (v) { set({ block_solution_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_solution_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст под заголовком',
                    value: attrs.block_solution_text || '',
                    placeholder: getDefault('solution.text', ''),
                    onChange: function (v) { set({ block_solution_text: v }); },
                    rows: 3
                }) : null
            ].concat(
                renderList(items, updateItem, removeItem, function () { setItems(items.concat([''])); }, 'r1', 'Первый ряд'),
                renderList(itemsSecond, updateItemSecond, removeItemSecond, function () { setItemsSecond(itemsSecond.concat([''])); }, 'r2', 'Второй ряд (необязательно)')
            ));
        },
        save: function () { return null; }
    });

    // Одна команда — заголовок, кнопка, показатели.
    wp.blocks.registerBlockType('tolstenko/one-team', {
        title: 'Одна команда',
        category: 'tolstenko-blocks-new',
        icon: 'groups',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_one_team_items) ? attrs.block_one_team_items.slice() : [];

            function setItems(next) { set({ block_one_team_items: next }); }
            function updateItem(i, patch) {
                var next = items.slice();
                next[i] = Object.assign({}, next[i] || { value: '', text: '' }, patch);
                setItems(next);
            }
            function addItem() { setItems(items.concat([{ value: '', text: '' }])); }
            function removeItem(i) { setItems(items.filter(function (_, idx) { return idx !== i; })); }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Одна команда'),
                TextareaControl ? el(TextareaControl, {
                    key: 'title',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_one_team_title || '',
                    placeholder: getDefault('one_team.title', ''),
                    onChange: function (v) { set({ block_one_team_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_one_team_title_tag', 'Тег заголовка', 'h2'),
                TextControl ? el(TextControl, {
                    key: 'btn',
                    label: 'Текст кнопки',
                    value: attrs.block_one_team_btn_text || '',
                    placeholder: getDefault('one_team.btn_text', ''),
                    onChange: function (v) { set({ block_one_team_btn_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'url',
                    label: 'Ссылка кнопки',
                    value: attrs.block_one_team_btn_url || '',
                    placeholder: 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_one_team_btn_url: v }); }
                }) : null,
                el('p', { key: 'il', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Показатели'),
                items.map(function (item, i) {
                    var row = item && typeof item === 'object' ? item : { value: '', text: '' };
                    return el('div', {
                        key: 'it-' + i,
                        style: { marginBottom: '10px', padding: '10px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 'v',
                            label: 'Значение',
                            value: row.value || '',
                            onChange: function (v) { updateItem(i, { value: v }); }
                        }) : null,
                        TextareaControl ? el(TextareaControl, {
                            key: 't',
                            label: 'Подпись (HTML)',
                            value: row.text || '',
                            onChange: function (v) { updateItem(i, { text: v }); },
                            rows: 2
                        }) : null,
                        Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { removeItem(i); } }, 'Удалить') : null
                    ]);
                }),
                Button ? el(Button, { key: 'add', isSecondary: true, onClick: addItem }, 'Добавить показатель') : null
            ]);
        },
        save: function () { return null; }
    });

    // Автор — фото, имя, списки, показатели, ссылки, нижний блок.
    wp.blocks.registerBlockType('tolstenko/author', {
        title: 'Автор',
        category: 'tolstenko-blocks-new',
        icon: 'admin-users',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            function arrAttr(key, fallback) {
                return Array.isArray(attrs[key]) ? attrs[key].slice() : fallback;
            }

            var list = arrAttr('block_author_list', []);
            var items = arrAttr('block_author_items', []);
            var links = arrAttr('block_author_links', []);
            var sublist = arrAttr('block_author_sublist', []);
            var speeches = arrAttr('block_author_speeches', []);
            var photoId = parseInt(attrs.block_author_photo || 0, 10) || 0;
            var awardId = parseInt(attrs.block_author_award_image || 0, 10) || 0;
            var rightId = parseInt(attrs.block_author_right_image || 0, 10) || 0;
            var showBottom = attrs.block_author_show_bottom !== false;

            function renderImagePicker(label, id, attrKey) {
                return el('div', { key: attrKey, style: { marginBottom: '10px' } }, [
                    el('p', { key: 'l', style: { marginBottom: '6px', fontWeight: '600' } }, label),
                    MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                    el(MediaUpload, {
                        allowedTypes: ['image'],
                            value: id || undefined,
                            onSelect: function (m) {
                                var patch = {};
                                patch[attrKey] = m && m.id ? m.id : 0;
                                set(patch);
                            },
                        render: function (obj) {
                                return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, id ? 'Сменить' : 'Выбрать');
                        }
                    })
                ) : null,
                    id && Button ? el(Button, {
                        key: 'clear',
                        isDestructive: true,
                        isSmall: true,
                        style: { marginLeft: '8px' },
                        onClick: function () {
                            var patch = {};
                            patch[attrKey] = 0;
                            set(patch);
                        }
                    }, 'Убрать') : null
                ]);
            }

            function renderTextList(label, rows, attrKey, placeholder) {
                return el('div', { key: attrKey, style: { marginBottom: '12px' } }, [
                    el('p', { key: 'l', style: { margin: '10px 0 6px', fontWeight: '600' } }, label),
                    rows.map(function (row, i) {
                        var text = row && typeof row === 'object' ? (row.text || '') : String(row || '');
                        return el('div', {
                            key: attrKey + '-' + i,
                            style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                        }, [
                            TextControl ? el(TextControl, {
                                key: 't',
                                value: text,
                                placeholder: placeholder || 'Пункт',
                                onChange: function (v) {
                                    var next = rows.slice();
                                    next[i] = { text: v || '' };
                                    var patch = {};
                                    patch[attrKey] = next;
                                    set(patch);
                                }
                            }) : null,
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                onClick: function () {
                                    var next = rows.filter(function (_, idx) { return idx !== i; });
                                    var patch = {};
                                    patch[attrKey] = next;
                                    set(patch);
                                }
                            }, '×') : null
                        ]);
                    }),
                    Button ? el(Button, {
                        key: 'add',
                        isSecondary: true,
                        isSmall: true,
                        onClick: function () {
                            var patch = {};
                            patch[attrKey] = rows.concat([{ text: '' }]);
                            set(patch);
                        }
                    }, 'Добавить') : null
                ]);
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Автор'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '10px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля подставятся из «Настройки сайта → Дефолты блоков → Автор».'),
                TextareaControl ? el(TextareaControl, {
                    key: 'name',
                    label: 'Имя (HTML)',
                    value: attrs.block_author_name || '',
                    placeholder: getDefault('author.name', ''),
                    onChange: function (v) { set({ block_author_name: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_author_name_tag', 'Тег имени', 'h2'),
                renderImagePicker('Фото', photoId, 'block_author_photo'),
                TextControl ? el(TextControl, {
                    key: 'bt',
                    label: 'Текст кнопки под фото',
                    value: attrs.block_author_btn_text || '',
                    placeholder: getDefault('author.btn_text', ''),
                    onChange: function (v) { set({ block_author_btn_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'bu',
                    label: 'Ссылка кнопки под фото',
                    help: 'Пусто — открывается модалка заявки',
                    value: attrs.block_author_btn_url || '',
                    placeholder: 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_author_btn_url: v }); }
                }) : null,
                renderTextList('Список под именем', list, 'block_author_list', 'Пункт'),
                el('p', { key: 'stats-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Показатели'),
                items.map(function (item, i) {
                    var row = item && typeof item === 'object' ? item : { value: '', text: '' };
                    return el('div', {
                        key: 'st-' + i,
                        style: { marginBottom: '8px', padding: '8px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 'v',
                            label: 'Значение',
                            value: row.value || '',
                            onChange: function (v) {
                                var next = items.slice();
                                next[i] = Object.assign({}, row, { value: v || '' });
                                set({ block_author_items: next });
                            }
                        }) : null,
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Подпись',
                            value: row.text || '',
                            onChange: function (v) {
                                var next = items.slice();
                                next[i] = Object.assign({}, row, { text: v || '' });
                                set({ block_author_items: next });
                            }
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                set({ block_author_items: items.filter(function (_, idx) { return idx !== i; }) });
                            }
                        }, 'Удалить') : null
                    ]);
                }),
                Button ? el(Button, {
                    key: 'add-st',
                    isSecondary: true,
                    isSmall: true,
                    onClick: function () { set({ block_author_items: items.concat([{ value: '', text: '' }]) }); }
                }, 'Добавить показатель') : null,
                TextControl ? el(TextControl, {
                    key: 'll',
                    label: 'Подпись над ссылками',
                    value: attrs.block_author_links_label || '',
                    placeholder: getDefault('author.links_label', 'Делюсь экспертизой'),
                    onChange: function (v) { set({ block_author_links_label: v }); }
                }) : null,
                el('p', { key: 'links-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Ссылки'),
                links.map(function (item, i) {
                    var row = item && typeof item === 'object' ? item : { title: '', url: '', icon: 0 };
                    var iconId = parseInt(row.icon || 0, 10) || 0;
                    return el('div', {
                        key: 'lk-' + i,
                        style: { marginBottom: '8px', padding: '8px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Текст',
                            value: row.title || '',
                            onChange: function (v) {
                                var next = links.slice();
                                next[i] = Object.assign({}, row, { title: v || '' });
                                set({ block_author_links: next });
                            }
                        }) : null,
                        TextControl ? el(TextControl, {
                            key: 'u',
                            label: 'URL',
                            value: row.url || '',
                            onChange: function (v) {
                                var next = links.slice();
                                next[i] = Object.assign({}, row, { url: v || '' });
                                set({ block_author_links: next });
                            }
                        }) : null,
                        MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                    el(MediaUpload, {
                        allowedTypes: ['image'],
                                value: iconId || undefined,
                                onSelect: function (m) {
                                    var next = links.slice();
                                    next[i] = Object.assign({}, row, { icon: m && m.id ? m.id : 0 });
                                    set({ block_author_links: next });
                                },
                        render: function (obj) {
                                    return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, iconId ? 'Сменить иконку' : 'Иконка');
                        }
                    })
                ) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginLeft: '8px' },
                            onClick: function () {
                                set({ block_author_links: links.filter(function (_, idx) { return idx !== i; }) });
                            }
                        }, 'Удалить') : null
                    ]);
                }),
                Button ? el(Button, {
                    key: 'add-lk',
                    isSecondary: true,
                    isSmall: true,
                    onClick: function () { set({ block_author_links: links.concat([{ title: '', url: '', icon: 0 }]) }); }
                }, 'Добавить ссылку') : null,
                ToggleControl ? el(ToggleControl, {
                    key: 'sb',
                    label: 'Показывать нижний блок',
                    checked: showBottom,
                    onChange: function (v) { set({ block_author_show_bottom: !!v }); }
                }) : null,
                showBottom ? el('div', { key: 'bottom' }, [
                    TextareaControl ? el(TextareaControl, {
                        key: 'sub',
                        label: 'Подзаголовок (HTML)',
                        value: attrs.block_author_subtitle || '',
                        placeholder: getDefault('author.subtitle', ''),
                        onChange: function (v) { set({ block_author_subtitle: v }); },
                        rows: 2
                    }) : null,
                    TextareaControl ? el(TextareaControl, {
                        key: 'txt',
                        label: 'Текст',
                        value: attrs.block_author_text || '',
                        placeholder: getDefault('author.text', ''),
                        onChange: function (v) { set({ block_author_text: v }); }
                    }) : null,
                    renderTextList('Список нижнего блока', sublist, 'block_author_sublist', 'Пункт'),
                    TextControl ? el(TextControl, {
                        key: 'bmt',
                        label: 'Текст кнопки «Подробнее»',
                        value: attrs.block_author_btn_more_text || '',
                        onChange: function (v) { set({ block_author_btn_more_text: v }); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'bmu',
                        label: 'Ссылка кнопки «Подробнее»',
                        value: attrs.block_author_btn_more_url || '',
                        onChange: function (v) { set({ block_author_btn_more_url: v }); }
                    }) : null,
                    TextareaControl ? el(TextareaControl, {
                        key: 'aw',
                        label: 'Текст награды',
                        value: attrs.block_author_award || '',
                        onChange: function (v) { set({ block_author_award: v }); },
                        rows: 2
                    }) : null,
                    renderImagePicker('Изображение награды', awardId, 'block_author_award_image'),
                    renderImagePicker('Правое изображение', rightId, 'block_author_right_image'),
                    el('p', { key: 'sp-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Выступления'),
                    speeches.map(function (item, i) {
                        var row = item && typeof item === 'object' ? item : { text: '', image: 0 };
                        var imgId = parseInt(row.image || 0, 10) || 0;
                        return el('div', {
                            key: 'sp-' + i,
                            style: { marginBottom: '8px', padding: '8px', border: '1px solid #ddd', borderRadius: '4px', background: '#fafafa' }
                        }, [
                            TextControl ? el(TextControl, {
                                key: 't',
                                label: 'Подпись',
                                value: row.text || '',
                                onChange: function (v) {
                                    var next = speeches.slice();
                                    next[i] = Object.assign({}, row, { text: v || '' });
                                    set({ block_author_speeches: next });
                                }
                            }) : null,
                            MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                    el(MediaUpload, {
                        allowedTypes: ['image'],
                                    value: imgId || undefined,
                                    onSelect: function (m) {
                                        var next = speeches.slice();
                                        next[i] = Object.assign({}, row, { image: m && m.id ? m.id : 0 });
                                        set({ block_author_speeches: next });
                                    },
                        render: function (obj) {
                                        return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, imgId ? 'Сменить фото' : 'Фото');
                                    }
                                })
                            ) : null,
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                style: { marginLeft: '8px' },
                                onClick: function () {
                                    set({ block_author_speeches: speeches.filter(function (_, idx) { return idx !== i; }) });
                                }
                            }, 'Удалить') : null
                        ]);
                    }),
                    Button ? el(Button, {
                        key: 'add-sp',
                        isSecondary: true,
                        isSmall: true,
                        onClick: function () { set({ block_author_speeches: speeches.concat([{ text: '', image: 0 }]) }); }
                    }, 'Добавить выступление') : null,
                    TextControl ? el(TextControl, {
                        key: 'it',
                        label: 'Текст кнопки приглашения',
                        value: attrs.block_author_btn_invite_text || '',
                        onChange: function (v) { set({ block_author_btn_invite_text: v }); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'iu',
                        label: 'Ссылка кнопки приглашения',
                        value: attrs.block_author_btn_invite_url || '',
                        onChange: function (v) { set({ block_author_btn_invite_url: v }); }
                    }) : null
                ]) : null
            ]);
        },
        save: function () { return null; }
    });

    // Разный опыт.
    wp.blocks.registerBlockType('tolstenko/different-experiences', {
        title: 'Разный опыт',
        category: 'tolstenko-blocks-new',
        icon: 'groups',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_different_experiences_items) ? attrs.block_different_experiences_items : [];
            function addItem() { set({ block_different_experiences_items: items.concat(['']) }); }
            function updateItem(i, v) {
                var next = items.slice();
                next[i] = v;
                set({ block_different_experiences_items: next });
            }
            function removeItem(i) {
                set({ block_different_experiences_items: items.filter(function (_, idx) { return idx !== i; }) });
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Разный опыт'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_different_experiences_title || '',
                    placeholder: getDefault('different_experiences.title', ''),
                    onChange: function (v) { set({ block_different_experiences_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_different_experiences_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_different_experiences_text || '',
                    placeholder: getDefault('different_experiences.text', ''),
                    onChange: function (v) { set({ block_different_experiences_text: v }); }
                }) : null,
                items.map(function (txt, i) {
                    return el('div', { key: 'it-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px' } }, [
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: typeof txt === 'string' ? txt : (txt && txt.text) || '',
                            onChange: function (v) { updateItem(i, v); }
                        }) : null,
                        Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { removeItem(i); } }, '×') : null
                    ]);
                }),
                Button ? el(Button, { key: 'add', isSecondary: true, onClick: addItem }, 'Добавить пункт') : null,
                TextControl ? el(TextControl, {
                    key: 'tg_t',
                    label: 'Текст кнопки Telegram',
                    value: attrs.block_different_experiences_tg_text || '',
                    placeholder: getDefault('different_experiences.tg_text', ''),
                    onChange: function (v) { set({ block_different_experiences_tg_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'tg_u',
                    label: 'Ссылка Telegram',
                    value: attrs.block_different_experiences_tg_url || '',
                    placeholder: 'Пусто = из шапки/подвала',
                    onChange: function (v) { set({ block_different_experiences_tg_url: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'm_t',
                    label: 'Текст кнопки заявки',
                    value: attrs.block_different_experiences_modal_text || '',
                    placeholder: getDefault('different_experiences.modal_text', ''),
                    onChange: function (v) { set({ block_different_experiences_modal_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'm_u',
                    label: 'Ссылка заявки',
                    value: attrs.block_different_experiences_modal_url || '',
                    placeholder: 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_different_experiences_modal_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Партнёры — как сертификаты: галерея логотипов.
    wp.blocks.registerBlockType('tolstenko/partners', {
        title: 'Партнёры',
        category: 'tolstenko-blocks-new',
        icon: 'businessman',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_partners_items) ? attrs.block_partners_items : [];
            function addMany(mediaItems) {
                var list = Array.isArray(mediaItems) ? mediaItems : [];
                if (!list.length) return;
                var next = items.slice();
                var existing = {};
                next.forEach(function (img) {
                    if (img && img.id) existing[String(img.id)] = true;
                });
                list.forEach(function (m) {
                    if (!m || !m.id || !m.url) return;
                    var key = String(m.id);
                    if (existing[key]) return;
                    existing[key] = true;
                    next.push({ id: m.id, url: m.url, previewUrl: pickPreviewUrlFromMedia(m), title: m.alt || m.title || '' });
                });
                set({ block_partners_items: next });
            }
            function removeItem(index) {
                set({ block_partners_items: items.filter(function (_, i) { return i !== index; }) });
            }
            function moveItem(from, to) {
                if (from === to || from < 0 || to < 0 || from >= items.length || to >= items.length) return;
                var next = items.slice();
                var item = next.splice(from, 1)[0];
                next.splice(to, 0, item);
                set({ block_partners_items: next });
            }
            function updateTitle(index, value) {
                var next = items.slice();
                if (!next[index]) return;
                next[index] = Object.assign({}, next[index], { title: value });
                set({ block_partners_items: next });
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Партнёры'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_partners_title || '',
                    placeholder: getDefault('partners.title', 'Наши партнёры'),
                    onChange: function (v) { set({ block_partners_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_partners_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_partners_text || '',
                    placeholder: getDefault('partners.text', ''),
                    onChange: function (v) { set({ block_partners_text: v }); }
                }) : null,
                MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                    el(MediaUpload, {
                        allowedTypes: ['image'],
                        multiple: true,
                        gallery: true,
                        onSelect: addMany,
                        render: function (obj) {
                            return el(Button, { isSecondary: true, onClick: obj.open }, 'Добавить логотипы');
                        }
                    })
                ) : null,
                items.length ? el('div', { key: 'list', style: { marginTop: '12px', display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(120px, 1fr))', gap: '10px' } },
                    items.map(function (img, index) {
                        return el('div', { key: 'p-' + index, style: { border: '1px solid #ddd', borderRadius: '6px', padding: '6px', background: '#fff' } }, [
                            el('img', { key: 'ph', src: getGalleryPreviewUrl(img), alt: '', style: { width: '100%', height: '70px', objectFit: 'contain' } }),
                            TextControl ? el(TextControl, {
                                key: 'alt',
                                label: 'Название',
                                value: img.title || '',
                                onChange: function (v) { updateTitle(index, v); }
                            }) : null,
                            el('div', { key: 'move', style: { display: 'flex', gap: '6px' } }, [
                                Button ? el(Button, { key: 'l', isSmall: true, isSecondary: true, disabled: index === 0, onClick: function () { moveItem(index, index - 1); } }, '←') : null,
                                Button ? el(Button, { key: 'r', isSmall: true, isSecondary: true, disabled: index === items.length - 1, onClick: function () { moveItem(index, index + 1); } }, '→') : null
                            ]),
                            Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, style: { width: '100%', marginTop: '6px' }, onClick: function () { removeItem(index); } }, 'Удалить') : null
                        ]);
                    })
                ) : el('p', { key: 'empty', style: { fontSize: '12px', color: '#757575' } }, 'Если логотипы не заданы — из дефолтов.')
            ]);
        },
        save: function () { return null; }
    });

    // Стратегия / team-cards / tg-channel / three-steps — упрощённый редактор (основные поля; детали в дефолтах).
    function registerSimpleItemsBlock(config) {
        wp.blocks.registerBlockType(config.name, {
            title: config.title,
            category: 'tolstenko-blocks-new',
            icon: config.icon || 'layout',
        edit: function (props) {
                var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
                var itemsKey = config.itemsKey;
                var items = itemsKey && Array.isArray(attrs[itemsKey]) ? attrs[itemsKey] : [];
                var fields = [
                    el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, config.title),
                    el('p', { key: 'h', style: { marginTop: 0, fontSize: '12px', color: '#757575' } }, 'Пустые поля и списки подставятся из «Дефолты блоков».')
                ];
                (config.fields || []).forEach(function (f, i) {
                    if (f.type === 'headingTag') {
                        fields.push(renderHeadingTagSelect(attrs, set, f.key, f.label, f.fallback || 'h2'));
                        return;
                    }
                    if (f.type === 'textarea' && TextareaControl) {
                        fields.push(el(TextareaControl, {
                            key: 'f' + i, label: f.label, value: attrs[f.key] || '',
                            placeholder: getDefault(f.defaultPath, ''),
                            onChange: function (v) { var p = {}; p[f.key] = v; set(p); }
                        }));
                        return;
                    }
                    if (f.type === 'image' && MediaUpload && MediaUploadCheck) {
                        var imgId = parseInt(attrs[f.key] || 0, 10) || 0;
                        fields.push(el('div', { key: 'img' + i, style: { marginTop: '8px' } }, [
                            el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, f.label),
                            el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                                allowedTypes: ['image'], value: imgId,
                                onSelect: function (m) { var p = {}; p[f.key] = m && m.id ? m.id : 0; set(p); },
                                render: function (obj) { return el(Button, { isSecondary: true, onClick: obj.open }, imgId ? 'Заменить' : 'Выбрать'); }
                            })),
                            imgId && Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, style: { marginLeft: '8px' }, onClick: function () { var p = {}; p[f.key] = 0; set(p); } }, 'Удалить') : null
                        ]));
                        return;
                    }
                    if (TextControl) {
                        fields.push(el(TextControl, {
                            key: 'f' + i, label: f.label, value: attrs[f.key] || '',
                            placeholder: getDefault(f.defaultPath, f.placeholder || ''),
                            onChange: function (v) { var p = {}; p[f.key] = v; set(p); }
                        }));
                    }
                });
                if (itemsKey && config.simpleItems) {
                    items.forEach(function (txt, i) {
                        fields.push(el('div', { key: 'it' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px' } }, [
                            TextControl ? el(TextControl, {
                                key: 't', label: 'Пункт ' + (i + 1),
                                value: typeof txt === 'string' ? txt : (txt && txt.text) || '',
                                onChange: function (v) {
                                    var next = items.slice();
                                    next[i] = v;
                                    var p = {}; p[itemsKey] = next; set(p);
                                }
                            }) : null,
                            Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () {
                                var p = {}; p[itemsKey] = items.filter(function (_, idx) { return idx !== i; }); set(p);
                            } }, '×') : null
                        ]));
                    });
                    if (Button) {
                        fields.push(el(Button, { key: 'add', isSecondary: true, onClick: function () {
                            var p = {}; p[itemsKey] = items.concat(['']); set(p);
                        } }, 'Добавить пункт'));
                    }
                }
                if (config.note) {
                    fields.push(el('p', { key: 'note', style: { fontSize: '12px', color: '#757575' } }, config.note));
                }
                return wrapBlock(blockProps, fields);
            },
            save: function () { return null; }
        });
    }

    registerSimpleItemsBlock({
        name: 'tolstenko/strategy',
        title: 'Стратегия',
        icon: 'chart-area',
        itemsKey: 'block_strategy_items',
        simpleItems: true,
        fields: [
            { key: 'block_strategy_title', label: 'Заголовок', defaultPath: 'strategy.title' },
            { type: 'headingTag', key: 'block_strategy_title_tag', label: 'Тег заголовка' },
            { key: 'block_strategy_subtitle', label: 'Подзаголовок', defaultPath: 'strategy.subtitle' },
            { type: 'textarea', key: 'block_strategy_text', label: 'Текст', defaultPath: 'strategy.text' },
            { key: 'block_strategy_btn_text', label: 'Текст кнопки', defaultPath: 'strategy.btn_text' },
            { key: 'block_strategy_btn_url', label: 'Ссылка кнопки', placeholder: 'Пусто = модалка' },
            { key: 'block_strategy_file_text', label: 'Текст кнопки файла', defaultPath: 'strategy.file_text' },
            { key: 'block_strategy_file_url', label: 'URL файла' },
            { type: 'image', key: 'block_strategy_image', label: 'Изображение desktop' },
            { type: 'image', key: 'block_strategy_image_mob', label: 'Изображение mobile' }
        ]
    });

    registerSimpleItemsBlock({
        name: 'tolstenko/team-cards',
        title: 'Команда',
        icon: 'groups',
        note: 'Карточки редактируются в «Дефолты блоков → Команда». Существующий блок «Команда» берёт людей из CPT.',
        fields: [
            { key: 'block_team_cards_title', label: 'Заголовок', defaultPath: 'team_cards.title' },
            { type: 'headingTag', key: 'block_team_cards_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_team_cards_text', label: 'Текст', defaultPath: 'team_cards.text' }
        ]
    });

    registerSimpleItemsBlock({
        name: 'tolstenko/tg-channel',
        title: 'Telegram-канал',
        icon: 'share',
        itemsKey: 'block_tg_channel_items',
        simpleItems: true,
        fields: [
            { key: 'block_tg_channel_title', label: 'Заголовок', defaultPath: 'tg_channel.title' },
            { type: 'headingTag', key: 'block_tg_channel_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_tg_channel_text', label: 'Текст', defaultPath: 'tg_channel.text' },
            { key: 'block_tg_channel_btn_text', label: 'Текст кнопки', defaultPath: 'tg_channel.btn_text' },
            { key: 'block_tg_channel_btn_url', label: 'Ссылка', placeholder: 'Пусто = Telegram из шапки' },
            { type: 'image', key: 'block_tg_channel_image', label: 'Изображение' }
        ]
    });

    registerSimpleItemsBlock({
        name: 'tolstenko/three-steps',
        title: 'Три шага',
        icon: 'editor-ol',
        itemsKey: 'block_three_steps_items',
        simpleItems: true,
        fields: [
            { key: 'block_three_steps_title', label: 'Заголовок', defaultPath: 'three_steps.title' },
            { type: 'headingTag', key: 'block_three_steps_title_tag', label: 'Тег заголовка' },
            { type: 'textarea', key: 'block_three_steps_text', label: 'Текст', defaultPath: 'three_steps.text' }
        ]
    });

    wp.blocks.registerBlockType('tolstenko/faq', {
        title: 'FAQ',
        category: 'tolstenko-blocks-new',
        icon: 'editor-help',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_faq_items) ? attrs.block_faq_items.slice() : [];
            if (!items.length) {
                var defItems = getDefault('faq.items', []);
                if (Array.isArray(defItems) && defItems.length) {
                    items = defItems.map(function (it) {
                        return {
                            title: (it && it.title) || '',
                            redactor: (it && it.redactor) || ''
                        };
                    });
                } else {
                    items = [{ title: '', redactor: '' }];
                }
            }
            function setItems(next) { set({ block_faq_items: next }); }
            function updateItem(index, patch) {
                var next = items.slice();
                next[index] = Object.assign({}, next[index] || { title: '', redactor: '' }, patch);
                setItems(next);
            }
            function addItem() { setItems(items.concat([{ title: '', redactor: '' }])); }
            function removeItem(index) {
                var next = items.filter(function (_, i) { return i !== index; });
                setItems(next.length ? next : [{ title: '', redactor: '' }]);
            }
            var fotoId = parseInt(attrs.block_faq_foto || 0, 10) || 0;

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'FAQ'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Дефолты блоков».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_faq_title || '',
                    placeholder: getDefault('faq.title', 'Частые вопросы'),
                    onChange: function (v) { set({ block_faq_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_faq_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_faq_text || '',
                    placeholder: getDefault('faq.text', ''),
                    onChange: function (v) { set({ block_faq_text: v }); }
                }) : null,
                el('p', { key: 'items-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Вопросы и ответы'),
                el('div', { key: 'items' }, items.map(function (item, index) {
                        return el('div', {
                        key: 'faq-' + index,
                            style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                        }, [
                        TextControl ? el(TextControl, {
                            key: 'q',
                            label: 'Вопрос ' + (index + 1),
                            value: (item && item.title) || '',
                            onChange: function (v) { updateItem(index, { title: v }); }
                        }) : null,
                        el('p', { key: 'a-l', style: { margin: '8px 0 4px', fontSize: '11px', fontWeight: '600', textTransform: 'uppercase', color: '#1e1e1e' } }, 'Ответ'),
                        RichText ? el(RichText, {
                            key: 'a',
                            tagName: 'div',
                            className: 'tolstenko-faq-answer-editor',
                            placeholder: 'Ответ…',
                            value: (item && item.redactor) || '',
                            onChange: function (v) { updateItem(index, { redactor: v }); },
                            allowedFormats: ['core/bold', 'core/italic', 'core/link', 'core/list', 'core/strikethrough']
                        }) : (TextareaControl ? el(TextareaControl, {
                            key: 'a',
                            label: 'Ответ',
                            value: (item && item.redactor) || '',
                            onChange: function (v) { updateItem(index, { redactor: v }); },
                            rows: 4
                        }) : null),
                        Button ? el(Button, {
                                    key: 'rm',
                                    isDestructive: true,
                            isSmall: true,
                                    onClick: function () { removeItem(index); }
                        }, 'Удалить вопрос') : null
                    ]);
                })),
                Button ? el(Button, { key: 'add', isSecondary: true, isSmall: true, onClick: addItem }, 'Добавить вопрос') : null,
                el('p', { key: 'form-l', style: { margin: '14px 0 6px', fontWeight: '600' } }, 'Форма справа'),
                TextControl ? el(TextControl, {
                    key: 'ft',
                    label: 'Заголовок формы',
                    value: attrs.block_faq_form_title || '',
                    placeholder: getDefault('faq.form_title', ''),
                    onChange: function (v) { set({ block_faq_form_title: v }); }
                }) : null,
                TextareaControl ? el(TextareaControl, {
                    key: 'ftext',
                    label: 'Текст формы',
                    value: attrs.block_faq_form_text || '',
                    placeholder: getDefault('faq.form_text', ''),
                    onChange: function (v) { set({ block_faq_form_text: v }); }
                }) : null,
                el('div', { key: 'foto', style: { marginTop: '8px' } }, [
                    el('p', { key: 'fl', style: { marginBottom: '6px', fontWeight: '600' } }, 'Фото справа'),
                    MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                                el(MediaUpload, {
                                    allowedTypes: ['image'],
                            value: fotoId || undefined,
                            onSelect: function (m) { set({ block_faq_foto: m && m.id ? m.id : 0 }); },
                                    render: function (obj) {
                                return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, fotoId ? 'Сменить фото' : 'Выбрать фото');
                                    }
                                })
                            ) : null,
                    fotoId && Button ? el(Button, {
                        key: 'clear',
                        isDestructive: true,
                        isSmall: true,
                        style: { marginLeft: '8px' },
                        onClick: function () { set({ block_faq_foto: 0 }); }
                    }, 'Удалить') : null
                ]),
                TextareaControl ? el(TextareaControl, {
                    key: 'fototext',
                    label: 'Текст рядом с фото (HTML)',
                    value: attrs.block_faq_foto_text || '',
                    placeholder: getDefault('faq.foto_text', ''),
                    onChange: function (v) { set({ block_faq_foto_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'phone',
                    label: 'Телефон',
                    value: attrs.block_faq_phone || '',
                    placeholder: getDefault('faq.phone', '') || 'Пусто = из шапки',
                    onChange: function (v) { set({ block_faq_phone: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'tg',
                    label: 'Telegram URL',
                    value: attrs.block_faq_telegram_url || '',
                    placeholder: getDefault('faq.telegram_url', '') || 'Пусто = из шапки',
                    onChange: function (v) { set({ block_faq_telegram_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    var hiddenSeoAllowedBlocks = [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/quote',
        'core/html'
    ];

    wp.blocks.registerBlockType('tolstenko/hidden-seo', {
        title: 'Скрытый seo',
        category: 'tolstenko-blocks-new',
        icon: 'hidden',
        description: 'SEO-текст, скрытый на сайте от посетителей (class hide).',
        edit: function (props) {
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Скрытый seo'),
                el('p', {
                    key: 'hint',
                    style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' }
                }, 'SEO-текст. На сайте скрыт классом hide — посетители его не видят. Контент задаётся на каждой странице.'),
                InnerBlocks ? el('div', {
                    key: 'inner',
                    style: {
                        marginTop: '8px',
                        padding: '12px',
                        border: '1px dashed #ccc',
                        borderRadius: '4px',
                        background: '#fafafa',
                        minHeight: '96px'
                    }
                }, el(InnerBlocks, {
                    allowedBlocks: hiddenSeoAllowedBlocks,
                    template: [['core/paragraph', {}]],
                    templateLock: false
                })) : el('p', { key: 'no-ib' }, 'Редактор недоступен')
            ]);
        },
        save: function () {
            return InnerBlocks ? el(InnerBlocks.Content) : null;
        }
    });

    // SEO продвижение: гибкое содержимое из раскладок + сворачивание тела секции.
    var seoSectionLayouts = [
        { label: 'Фото + текст', value: 'image_text' },
        { label: 'Текст + фото', value: 'text_image' },
        { label: 'Две колонки', value: 'two_columns' },
        { label: 'Галерея', value: 'gallery' },
        { label: 'Текст', value: 'text' },
        { label: 'Редактор (HTML)', value: 'redactor' }
    ];

    function makeSeoSectionRow(layout) {
        return {
            layout: layout || 'image_text',
            title: '',
            title_center: false,
            image: 0,
            text: '',
            btn_text: '',
            btn_url: '',
            btn_wide: false,
            columns: ['', ''],
            items: [],
            gallery: [],
            redactor: ''
        };
    }

    function normalizeSeoSectionRow(raw) {
        var row = makeSeoSectionRow((raw && (raw.layout || raw.acf_fc_layout)) || 'image_text');
        if (!raw || typeof raw !== 'object') return row;
        row.title = raw.title || '';
        row.title_center = !!raw.title_center;
        row.image = parseInt(raw.image || 0, 10) || 0;
        row.text = raw.text || '';
        row.btn_text = raw.btn_text || '';
        row.btn_url = raw.btn_url || '';
        row.btn_wide = !!raw.btn_wide;
        row.redactor = raw.redactor || '';
        row.columns = Array.isArray(raw.columns)
            ? raw.columns.map(function (c) { return (c && typeof c === 'object') ? (c.text || '') : (c || ''); })
            : ['', ''];
        while (row.columns.length < 2) row.columns.push('');
        row.items = Array.isArray(raw.items)
            ? raw.items.map(function (it) {
                if (!it || typeof it !== 'object') return { title: '', text: String(it || '') };
                return { title: it.title || '', text: it.text || '' };
            })
            : [];
        row.gallery = Array.isArray(raw.gallery)
            ? raw.gallery.map(function (g) {
                return parseInt((g && typeof g === 'object') ? (g.ID || g.id || 0) : g, 10) || 0;
            }).filter(function (id) { return id > 0; })
            : [];
        return row;
    }

    wp.blocks.registerBlockType('tolstenko/seo-section', {
        title: 'SEO продвижение',
        category: 'tolstenko-blocks-new',
        icon: 'search',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            var rows = Array.isArray(attrs.block_seo_section_blocks) ? attrs.block_seo_section_blocks : [];
            if (!rows.length) {
                var defRows = getDefault('seo_section.blocks', []);
                rows = Array.isArray(defRows) ? defRows : [];
            }
            rows = rows.map(normalizeSeoSectionRow);

            function setRows(next) { set({ block_seo_section_blocks: next }); }
            function updateRow(index, patch) {
                var next = rows.slice();
                next[index] = Object.assign({}, next[index], patch);
                setRows(next);
            }
            function addRow() { setRows(rows.concat([makeSeoSectionRow('image_text')])); }
            function removeRow(index) {
                setRows(rows.filter(function (_, i) { return i !== index; }));
            }
            function moveRow(index, delta) {
                var target = index + delta;
                if (target < 0 || target >= rows.length) return;
                var next = rows.slice();
                var moved = next.splice(index, 1)[0];
                next.splice(target, 0, moved);
                setRows(next);
            }

            function renderRowFields(row, index) {
                var layout = row.layout;
                var fields = [];

                if (TextControl) {
                    fields.push(el(TextControl, {
                        key: 'title',
                        label: 'Заголовок блока',
                        value: row.title,
                        onChange: function (v) { updateRow(index, { title: v }); }
                    }));
                }
                if (ToggleControl) {
                    fields.push(el(ToggleControl, {
                        key: 'title-center',
                        label: 'Заголовок по центру',
                        checked: row.title_center,
                        onChange: function (v) { updateRow(index, { title_center: !!v }); }
                    }));
                }

                if (layout === 'image_text' || layout === 'text_image') {
                    fields.push(el('div', { key: 'img', style: { margin: '8px 0' } }, [
                        MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                            el(MediaUpload, {
                                allowedTypes: ['image'],
                                value: row.image || undefined,
                                onSelect: function (m) { updateRow(index, { image: (m && m.id) ? m.id : 0 }); },
                                render: function (obj) {
                                    return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, row.image ? 'Сменить изображение' : 'Выбрать изображение');
                                }
                            })
                        ) : null,
                        (row.image && Button) ? el(Button, {
                            key: 'clear',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginLeft: '8px' },
                            onClick: function () { updateRow(index, { image: 0 }); }
                        }, 'Удалить изображение') : null
                    ]));
                    if (TextareaControl) {
                        fields.push(el(TextareaControl, {
                            key: 'text',
                            label: 'Текст',
                            rows: 4,
                            value: row.text,
                            onChange: function (v) { updateRow(index, { text: v }); }
                        }));
                    }
                    if (TextControl) {
                        fields.push(el(TextControl, {
                            key: 'btn-text',
                            label: 'Текст кнопки',
                            value: row.btn_text,
                            onChange: function (v) { updateRow(index, { btn_text: v }); }
                        }));
                        fields.push(el(TextControl, {
                            key: 'btn-url',
                            label: 'Ссылка кнопки',
                            help: 'Пусто = модалка заявки',
                            value: row.btn_url,
                            onChange: function (v) { updateRow(index, { btn_url: v }); }
                        }));
                    }
                    if (ToggleControl) {
                        fields.push(el(ToggleControl, {
                            key: 'btn-wide',
                            label: 'Широкая кнопка',
                            checked: row.btn_wide,
                            onChange: function (v) { updateRow(index, { btn_wide: !!v }); }
                        }));
                    }
                } else if (layout === 'two_columns') {
                    if (TextareaControl) {
                        fields.push(el(TextareaControl, {
                            key: 'col-1',
                            label: 'Левая колонка',
                            rows: 4,
                            value: row.columns[0] || '',
                            onChange: function (v) {
                                var columns = row.columns.slice();
                                columns[0] = v;
                                updateRow(index, { columns: columns });
                            }
                        }));
                        fields.push(el(TextareaControl, {
                            key: 'col-2',
                            label: 'Правая колонка',
                            rows: 4,
                            value: row.columns[1] || '',
                            onChange: function (v) {
                                var columns = row.columns.slice();
                                columns[1] = v;
                                updateRow(index, { columns: columns });
                            }
                        }));
                    }
                    fields.push(el('p', {
                        key: 'items-l',
                        style: { margin: '10px 0 4px', fontSize: '11px', fontWeight: '600', textTransform: 'uppercase' }
                    }, 'Пункты под колонками'));
                    fields.push(el('div', { key: 'items' }, row.items.map(function (item, itemIndex) {
                        return el('div', {
                            key: 'item-' + itemIndex,
                            style: { border: '1px dashed #ccc', borderRadius: '4px', padding: '6px', marginBottom: '6px' }
                        }, [
                            TextControl ? el(TextControl, {
                                key: 'it',
                                label: 'Заголовок пункта ' + (itemIndex + 1),
                                value: item.title,
                                onChange: function (v) {
                                    var items = row.items.slice();
                                    items[itemIndex] = Object.assign({}, items[itemIndex], { title: v });
                                    updateRow(index, { items: items });
                                }
                            }) : null,
                            TextareaControl ? el(TextareaControl, {
                                key: 'ix',
                                label: 'Текст пункта',
                                rows: 2,
                                value: item.text,
                                onChange: function (v) {
                                    var items = row.items.slice();
                                    items[itemIndex] = Object.assign({}, items[itemIndex], { text: v });
                                    updateRow(index, { items: items });
                                }
                            }) : null,
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                onClick: function () {
                                    updateRow(index, {
                                        items: row.items.filter(function (_, i) { return i !== itemIndex; })
                                    });
                                }
                            }, 'Удалить пункт') : null
                        ]);
                    })));
                    if (Button) {
                        fields.push(el(Button, {
                            key: 'add-item',
                            isSecondary: true,
                            isSmall: true,
                            onClick: function () { updateRow(index, { items: row.items.concat([{ title: '', text: '' }]) }); }
                        }, 'Добавить пункт'));
                    }
                } else if (layout === 'gallery') {
                    fields.push(el('div', { key: 'gallery', style: { margin: '8px 0' } }, [
                        MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                            el(MediaUpload, {
                                allowedTypes: ['image'],
                                multiple: true,
                                gallery: true,
                                value: row.gallery,
                                onSelect: function (media) {
                                    var ids = (media || []).map(function (m) { return (m && m.id) ? m.id : 0; })
                                        .filter(function (id) { return id > 0; });
                                    updateRow(index, { gallery: ids });
                                },
                                render: function (obj) {
                                    return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open },
                                        row.gallery.length ? ('Изменить галерею (' + row.gallery.length + ')') : 'Выбрать изображения');
                                }
                            })
                        ) : null,
                        (row.gallery.length && Button) ? el(Button, {
                            key: 'clear',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginLeft: '8px' },
                            onClick: function () { updateRow(index, { gallery: [] }); }
                        }, 'Очистить') : null
                    ]));
                } else if (layout === 'text') {
                    if (TextareaControl) {
                        fields.push(el(TextareaControl, {
                            key: 'text',
                            label: 'Текст',
                            rows: 5,
                            value: row.text,
                            onChange: function (v) { updateRow(index, { text: v }); }
                        }));
                    }
                } else if (layout === 'redactor') {
                    fields.push(el('p', {
                        key: 'red-l',
                        style: { margin: '8px 0 4px', fontSize: '11px', fontWeight: '600', textTransform: 'uppercase' }
                    }, 'Содержимое'));
                    fields.push(RichText ? el(RichText, {
                        key: 'red',
                        tagName: 'div',
                        className: 'tolstenko-seo-redactor-editor',
                        placeholder: 'Текст…',
                        value: row.redactor,
                        onChange: function (v) { updateRow(index, { redactor: v }); },
                        allowedFormats: ['core/bold', 'core/italic', 'core/link', 'core/list', 'core/strikethrough']
                    }) : (TextareaControl ? el(TextareaControl, {
                        key: 'red',
                        label: 'Содержимое',
                        rows: 6,
                        value: row.redactor,
                        onChange: function (v) { updateRow(index, { redactor: v }); }
                    }) : null));
                }

                return fields;
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'SEO продвижение'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Дефолты блоков». Тело секции на фронте свёрнуто, раскрывает кнопка «Читать далее».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_seo_section_title || '',
                    placeholder: getDefault('seo_section.title', ''),
                    onChange: function (v) { set({ block_seo_section_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_seo_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'subtitle',
                    label: 'Подзаголовок',
                    value: attrs.block_seo_section_subtitle || '',
                    placeholder: getDefault('seo_section.subtitle', ''),
                    onChange: function (v) { set({ block_seo_section_subtitle: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'more',
                    label: 'Текст кнопки раскрытия',
                    value: attrs.block_seo_section_more_text || '',
                    placeholder: getDefault('seo_section.more_text', 'Читать далее'),
                    onChange: function (v) { set({ block_seo_section_more_text: v }); }
                }) : null,
                el('p', { key: 'rows-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Блоки содержимого'),
                el('div', { key: 'rows' }, rows.map(function (row, index) {
                    return el('div', {
                        key: 'seo-row-' + index,
                        style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                    }, [
                        SelectControl ? el(SelectControl, {
                            key: 'layout',
                            label: 'Раскладка ' + (index + 1),
                            value: row.layout,
                            options: seoSectionLayouts,
                            onChange: function (v) { updateRow(index, { layout: v }); }
                        }) : null,
                        el('div', { key: 'fields' }, renderRowFields(row, index)),
                        el('div', { key: 'row-actions', style: { display: 'flex', gap: '8px', marginTop: '8px' } }, [
                            Button ? el(Button, {
                                key: 'up',
                                isSecondary: true,
                                isSmall: true,
                                disabled: index === 0,
                                onClick: function () { moveRow(index, -1); }
                            }, 'Вверх') : null,
                            Button ? el(Button, {
                                key: 'down',
                                isSecondary: true,
                                isSmall: true,
                                disabled: index === rows.length - 1,
                                onClick: function () { moveRow(index, 1); }
                            }, 'Вниз') : null,
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                onClick: function () { removeRow(index); }
                            }, 'Удалить блок') : null
                        ])
                    ]);
                })),
                Button ? el(Button, { key: 'add', isSecondary: true, isSmall: true, onClick: addRow }, 'Добавить блок') : null
            ]);
        },
        save: function () { return null; }
    });

    // Партнёры: «Мы можем»
    wp.blocks.registerBlockType('tolstenko/we-can', {
        title: 'Мы можем',
        category: 'tolstenko-blocks-partner',
        icon: 'yes-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            function toTextList(raw) {
                if (!Array.isArray(raw)) {
                    return [];
                }
                return raw.map(function (it) {
                    return typeof it === 'string' ? it : ((it && it.text) || '');
                });
            }

            // Только атрибуты блока. Пустой список на фронте = дефолты из настроек.
            var items = toTextList(attrs.block_we_can_items);
            var list = toTextList(attrs.block_we_can_list);

            function setItems(next) { set({ block_we_can_items: next.slice() }); }
            function setList(next) { set({ block_we_can_list: next.slice() }); }

            function renderTextList(current, onChange, listKey, label, addLabel, defPath) {
                var def = getDefault(defPath, []);
                var defHint = (Array.isArray(def) && def.length)
                    ? ('Сейчас пусто — на сайте будут дефолты (' + def.length + ' шт.). Добавьте пункты, чтобы задать свой список.')
                    : 'Сейчас пусто — на сайте подставятся дефолты.';
                var rows = [
                    el('p', { key: 'l', style: { margin: '0 0 6px', fontWeight: '600' } }, label)
                ];
                if (!current.length) {
                    rows.push(el('p', { key: 'empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, defHint));
                }
                current.forEach(function (txt, i) {
                    rows.push(el('div', {
                        key: listKey + '-' + i,
                        style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: txt || '',
                            onChange: function (v) {
                                var next = current.slice();
                                next[i] = v;
                                onChange(next);
                            }
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                onChange(current.filter(function (_, idx) { return idx !== i; }));
                            }
                        }, '×') : null
                    ]));
                });
                if (Button) {
                    rows.push(el(Button, {
                        key: 'add',
                        isSecondary: true,
                        onClick: function () {
                            if (!current.length && Array.isArray(def) && def.length) {
                                // Стартуем от дефолтов, чтобы было удобно править количество.
                                onChange(def.map(function (it) {
                                    return typeof it === 'string' ? it : ((it && it.text) || '');
                                }).concat(['']));
                                return;
                            }
                            onChange(current.concat(['']));
                        }
                    }, addLabel));
                }
                return el('div', { key: listKey, style: { marginTop: '12px' } }, rows);
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Мы можем'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля и списки подставятся из «Дефолты блоков → Партнёры блоки».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_we_can_title || '',
                    placeholder: getDefault('we_can.title', 'Мы можем'),
                    onChange: function (v) { set({ block_we_can_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_we_can_title_tag', 'Тег заголовка', 'h2'),
                renderTextList(items, setItems, 'we-can-items', 'Пункты («мы можем»)', 'Добавить пункт', 'we_can.items'),
                TextControl ? el(TextControl, {
                    key: 'list-title',
                    label: 'Заголовок условий',
                    value: attrs.block_we_can_list_title || '',
                    placeholder: getDefault('we_can.list_title', 'Условия выплат'),
                    onChange: function (v) { set({ block_we_can_list_title: v }); }
                }) : null,
                renderTextList(list, setList, 'we-can-list', 'Условия выплат', 'Добавить условие', 'we_can.list'),
                el('p', { key: 'form-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Форма слева'),
                TextControl ? el(TextControl, {
                    key: 'form-title',
                    label: 'Заголовок формы',
                    value: attrs.block_we_can_form_title || '',
                    placeholder: getDefault('we_can.form_title', 'Не нашли ответ на свой вопрос?'),
                    onChange: function (v) { set({ block_we_can_form_title: v }); }
                }) : null,
                TextareaControl ? el(TextareaControl, {
                    key: 'form-text',
                    label: 'Текст формы',
                    value: attrs.block_we_can_form_text || '',
                    placeholder: getDefault('we_can.form_text', ''),
                    onChange: function (v) { set({ block_we_can_form_text: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Партнёры: «Рефералка»
    wp.blocks.registerBlockType('tolstenko/referal', {
        title: 'Рефералка',
        category: 'tolstenko-blocks-partner',
        icon: 'money-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            function toTextList(raw) {
                if (!Array.isArray(raw)) {
                    return [];
                }
                return raw.map(function (it) {
                    return typeof it === 'string' ? it : ((it && it.text) || '');
                });
            }

            var items = toTextList(attrs.block_referal_items);
            var list = toTextList(attrs.block_referal_list);

            function setItems(next) { set({ block_referal_items: next.slice() }); }
            function setList(next) { set({ block_referal_list: next.slice() }); }

            function renderTextList(current, onChange, listKey, label, addLabel, defPath) {
                var def = getDefault(defPath, []);
                var defHint = (Array.isArray(def) && def.length)
                    ? ('Сейчас пусто — на сайте будут дефолты (' + def.length + ' шт.). Добавьте пункты, чтобы задать свой список.')
                    : 'Сейчас пусто — на сайте подставятся дефолты.';
                var rows = [
                    el('p', { key: 'l', style: { margin: '0 0 6px', fontWeight: '600' } }, label)
                ];
                if (!current.length) {
                    rows.push(el('p', { key: 'empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, defHint));
                }
                current.forEach(function (txt, i) {
                    rows.push(el('div', {
                        key: listKey + '-' + i,
                        style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 't',
                            label: 'Пункт ' + (i + 1),
                            value: txt || '',
                            onChange: function (v) {
                                var next = current.slice();
                                next[i] = v;
                                onChange(next);
                            }
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                onChange(current.filter(function (_, idx) { return idx !== i; }));
                            }
                        }, '×') : null
                    ]));
                });
                if (Button) {
                    rows.push(el(Button, {
                        key: 'add',
                        isSecondary: true,
                        onClick: function () {
                            if (!current.length && Array.isArray(def) && def.length) {
                                onChange(def.map(function (it) {
                                    return typeof it === 'string' ? it : ((it && it.text) || '');
                                }).concat(['']));
                                return;
                            }
                            onChange(current.concat(['']));
                        }
                    }, addLabel));
                }
                return el('div', { key: listKey, style: { marginTop: '12px' } }, rows);
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Рефералка'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля и списки подставятся из «Дефолты блоков → Партнёры блоки».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_referal_title || '',
                    placeholder: getDefault('referal.title', 'Рефералка'),
                    onChange: function (v) { set({ block_referal_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_referal_title_tag', 'Тег заголовка', 'h2'),
                renderTextList(items, setItems, 'referal-items', 'Пункты слева', 'Добавить пункт', 'referal.items'),
                TextControl ? el(TextControl, {
                    key: 'list-title',
                    label: 'Заголовок условий',
                    value: attrs.block_referal_list_title || '',
                    placeholder: getDefault('referal.list_title', 'Условия выплат'),
                    onChange: function (v) { set({ block_referal_list_title: v }); }
                }) : null,
                renderTextList(list, setList, 'referal-list', 'Условия выплат', 'Добавить условие', 'referal.list'),
                TextControl ? el(TextControl, {
                    key: 'btn',
                    label: 'Текст кнопки',
                    value: attrs.block_referal_btn_text || '',
                    placeholder: getDefault('referal.btn_text', ''),
                    onChange: function (v) { set({ block_referal_btn_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'btn-url',
                    label: 'Ссылка кнопки',
                    value: attrs.block_referal_btn_url || '',
                    placeholder: getDefault('referal.btn_url', '') || 'Пусто = модалка',
                    onChange: function (v) { set({ block_referal_btn_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Партнёры: «Вознаграждение»
    wp.blocks.registerBlockType('tolstenko/commission', {
        title: 'Вознаграждение',
        category: 'tolstenko-blocks-partner',
        icon: 'chart-pie',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defItems = getDefault('commission.items', []);

            function normalizeItems(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    it = it || {};
                    return {
                        ico: parseInt(it.ico, 10) || 0,
                        title: it.title || '',
                        summa: it.summa || '',
                        time: it.time || '',
                        commission: it.commission || '',
                        remark: it.remark || ''
                    };
                });
            }

            var items = normalizeItems(attrs.block_commission_items);
            function setItems(next) { set({ block_commission_items: next.slice() }); }
            function updateItem(index, patch) {
                var next = items.slice();
                next[index] = Object.assign({}, items[index] || {}, patch);
                setItems(next);
            }

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Вознаграждение'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Дефолты блоков → Партнёры блоки».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_commission_title || '',
                    placeholder: getDefault('commission.title', 'Вознаграждение'),
                    onChange: function (v) { set({ block_commission_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_commission_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_commission_text || '',
                    placeholder: getDefault('commission.text', ''),
                    onChange: function (v) { set({ block_commission_text: v }); }
                }) : null,
                el('p', { key: 'items-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Карточки'),
                !items.length ? el('p', { key: 'empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные карточки.') : null
            ];

            items.forEach(function (item, index) {
                var icoId = parseInt(item.ico, 10) || 0;
                fields.push(el('div', {
                    key: 'c-' + index,
                    style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                }, [
                    el('p', { key: 'n', style: { margin: '0 0 6px', fontWeight: '600' } }, 'Карточка ' + (index + 1)),
                    MediaUpload && MediaUploadCheck ? el('div', { key: 'ico', style: { marginBottom: '8px' } }, [
                        el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                            allowedTypes: ['image'],
                            value: icoId,
                            onSelect: function (m) { updateItem(index, { ico: m && m.id ? m.id : 0 }); },
                            render: function (obj) {
                                return el(Button, { isSecondary: true, onClick: obj.open }, icoId ? 'Заменить иконку' : 'Иконка (SVG)');
                            }
                        })),
                        icoId && Button ? el(Button, {
                            key: 'rm-ico', isDestructive: true, isSmall: true, style: { marginLeft: '8px' },
                            onClick: function () { updateItem(index, { ico: 0 }); }
                        }, 'Удалить') : null
                    ]) : null,
                    TextControl ? el(TextControl, { key: 't', label: 'Заголовок', value: item.title || '', onChange: function (v) { updateItem(index, { title: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 's', label: 'Сумма («Клиент заказал»)', value: item.summa || '', onChange: function (v) { updateItem(index, { summa: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 'tm', label: 'Сроки / тип («Разовая» = тип услуги)', value: item.time || '', onChange: function (v) { updateItem(index, { time: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 'cm', label: 'Вознаграждение', value: item.commission || '', onChange: function (v) { updateItem(index, { commission: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 'rm', label: 'Примечание', value: item.remark || '', onChange: function (v) { updateItem(index, { remark: v }); } }) : null,
                    Button ? el(Button, {
                        key: 'del', isDestructive: true, isSmall: true,
                        onClick: function () { setItems(items.filter(function (_, i) { return i !== index; })); }
                    }, 'Удалить карточку') : null
                ]));
            });

            if (Button) {
                fields.push(el(Button, {
                    key: 'add',
                    isSecondary: true,
                    onClick: function () {
                        var empty = { ico: 0, title: '', summa: '', time: '', commission: '', remark: '' };
                        if (!items.length && Array.isArray(defItems) && defItems.length) {
                            setItems(normalizeItems(defItems).concat([empty]));
                    return;
                }
                        setItems(items.concat([empty]));
                    }
                }, 'Добавить карточку'));
            }

            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Партнёры: «Преимущества»
    wp.blocks.registerBlockType('tolstenko/benefits-cooperation', {
        title: 'Преимущества',
        category: 'tolstenko-blocks-partner',
        icon: 'awards',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defItems = getDefault('benefits_cooperation.items', []);

            function normalizeCols(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (col) {
                    col = col || {};
                    var list = Array.isArray(col.list) ? col.list.map(function (el) {
                        el = el || {};
                        return { title: el.title || '', text: el.text || '' };
                    }) : [];
                    return {
                        list: list,
                        btn_text: col.btn_text || '',
                        btn_url: col.btn_url || ''
                    };
                });
            }

            var columns = normalizeCols(attrs.block_benefits_cooperation_items);
            function setColumns(next) { set({ block_benefits_cooperation_items: next.slice() }); }
            function updateCol(index, patch) {
                var next = columns.slice();
                next[index] = Object.assign({}, columns[index] || { list: [], btn_text: '', btn_url: '' }, patch);
                setColumns(next);
            }
            function updateElem(colIndex, elemIndex, patch) {
                var col = columns[colIndex] || { list: [], btn_text: '', btn_url: '' };
                var list = (col.list || []).slice();
                list[elemIndex] = Object.assign({}, list[elemIndex] || { title: '', text: '' }, patch);
                updateCol(colIndex, { list: list });
            }

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Преимущества'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Дефолты блоков → Партнёры блоки».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_benefits_cooperation_title || '',
                    placeholder: getDefault('benefits_cooperation.title', 'Преимущества'),
                    onChange: function (v) { set({ block_benefits_cooperation_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_benefits_cooperation_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'cols-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Колонки'),
                !columns.length ? el('p', { key: 'empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные колонки.') : null
            ];

            columns.forEach(function (col, cIndex) {
                var list = Array.isArray(col.list) ? col.list : [];
                var listFields = list.map(function (elem, eIndex) {
                return el('div', {
                        key: 'e-' + cIndex + '-' + eIndex,
                        style: { marginBottom: '8px', padding: '8px', background: '#fff', border: '1px solid #e0e0e0' }
                    }, [
                        TextControl ? el(TextControl, {
                            key: 'et', label: 'Пункт ' + (eIndex + 1) + ' — заголовок',
                            value: elem.title || '',
                            onChange: function (v) { updateElem(cIndex, eIndex, { title: v }); }
                        }) : null,
                        TextareaControl ? el(TextareaControl, {
                            key: 'ex', label: 'Текст',
                            value: elem.text || '',
                            onChange: function (v) { updateElem(cIndex, eIndex, { text: v }); }
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm', isDestructive: true, isSmall: true,
                            onClick: function () {
                                updateCol(cIndex, { list: list.filter(function (_, i) { return i !== eIndex; }) });
                            }
                        }, 'Удалить пункт') : null
                ]);
            });

                fields.push(el('div', {
                    key: 'col-' + cIndex,
                    style: { marginBottom: '12px', border: '1px solid #ddd', borderRadius: '4px', padding: '10px', background: '#fafafa' }
                }, [
                    el('p', { key: 'n', style: { margin: '0 0 8px', fontWeight: '600' } }, 'Колонка ' + (cIndex + 1)),
                    el('div', { key: 'list' }, listFields),
                    Button ? el(Button, {
                        key: 'add-e', isSecondary: true, isSmall: true, style: { marginBottom: '10px' },
                        onClick: function () { updateCol(cIndex, { list: list.concat([{ title: '', text: '' }]) }); }
                    }, 'Добавить пункт') : null,
                    TextControl ? el(TextControl, {
                        key: 'bt', label: 'Текст кнопки',
                        value: col.btn_text || '',
                        onChange: function (v) { updateCol(cIndex, { btn_text: v }); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'bu', label: 'Ссылка кнопки',
                        value: col.btn_url || '',
                        placeholder: 'Пусто = модалка',
                        onChange: function (v) { updateCol(cIndex, { btn_url: v }); }
                    }) : null,
                    Button ? el(Button, {
                        key: 'del', isDestructive: true, isSmall: true,
                        onClick: function () { setColumns(columns.filter(function (_, i) { return i !== cIndex; })); }
                    }, 'Удалить колонку') : null
                ]));
            });

            if (Button) {
                fields.push(el(Button, {
                    key: 'add-col',
                    isSecondary: true,
                    onClick: function () {
                        var empty = { list: [{ title: '', text: '' }], btn_text: '', btn_url: '' };
                        if (!columns.length && Array.isArray(defItems) && defItems.length) {
                            setColumns(normalizeCols(defItems).concat([empty]));
                            return;
                        }
                        setColumns(columns.concat([empty]));
                    }
                }, 'Добавить колонку'));
            }

            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Пресс-портрет: «Образование»
    wp.blocks.registerBlockType('tolstenko/aducation', {
        title: 'Образование',
        category: 'tolstenko-blocks-press',
        icon: 'welcome-learn-more',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defItems = getDefault('aducation.items', []);
            var defImages = getDefault('aducation.images', []);

            function normalizeItems(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    it = it || {};
                    return {
                        year: it.year || '',
                        type: it.type || '',
                        title: it.title || '',
                        speciality: it.speciality || ''
                    };
                });
            }
            function normalizeImages(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    if (typeof it === 'number') return { image: it };
                    it = it || {};
                    return { image: parseInt(it.image || it.id, 10) || 0 };
                }).filter(function (it) { return it.image > 0; });
            }

            var items = normalizeItems(attrs.block_aducation_items);
            var images = normalizeImages(attrs.block_aducation_images);
            function setItems(next) { set({ block_aducation_items: next.slice() }); }
            function setImages(next) { set({ block_aducation_images: next.slice() }); }
            function updateItem(i, patch) {
                var next = items.slice();
                next[i] = Object.assign({}, items[i] || {}, patch);
                setItems(next);
            }

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Образование'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля — из «Дефолты блоков → Пресс-портрет».'),
                TextControl ? el(TextControl, {
                    key: 'title', label: 'Заголовок',
                    value: attrs.block_aducation_title || '',
                    placeholder: getDefault('aducation.title', 'Образование'),
                    onChange: function (v) { set({ block_aducation_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_aducation_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'il', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Этапы'),
                !items.length ? el('p', { key: 'ie', style: { fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные этапы.') : null
            ];
            items.forEach(function (item, index) {
                fields.push(el('div', {
                    key: 'it-' + index,
                    style: { marginBottom: '8px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                }, [
                    TextControl ? el(TextControl, { key: 'y', label: 'Год', value: item.year || '', onChange: function (v) { updateItem(index, { year: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 'ty', label: 'Тип', value: item.type || '', onChange: function (v) { updateItem(index, { type: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 't', label: 'Заголовок', value: item.title || '', onChange: function (v) { updateItem(index, { title: v }); } }) : null,
                    TextControl ? el(TextControl, { key: 's', label: 'Специальность', value: item.speciality || '', onChange: function (v) { updateItem(index, { speciality: v }); } }) : null,
                    Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { setItems(items.filter(function (_, i) { return i !== index; })); } }, 'Удалить') : null
                ]));
            });
            if (Button) {
                fields.push(el(Button, {
                    key: 'add-i', isSecondary: true,
                    onClick: function () {
                        var empty = { year: '', type: '', title: '', speciality: '' };
                        if (!items.length && Array.isArray(defItems) && defItems.length) {
                            setItems(normalizeItems(defItems).concat([empty]));
                            return;
                        }
                        setItems(items.concat([empty]));
                    }
                }, 'Добавить этап'));
            }

            fields.push(el('p', { key: 'img-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Фото справа'));
            if (MediaUpload && MediaUploadCheck) {
                fields.push(el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                    allowedTypes: ['image'], multiple: true, gallery: true,
                    onSelect: function (mediaItems) {
                        var list = Array.isArray(mediaItems) ? mediaItems : (mediaItems ? [mediaItems] : []);
                        var next = images.slice();
                        var seen = {};
                        next.forEach(function (it) { seen[String(it.image)] = true; });
                        list.forEach(function (m) {
                            if (!m || !m.id || seen[String(m.id)]) return;
                            seen[String(m.id)] = true;
                            next.push({ image: m.id });
                        });
                        setImages(next);
                    },
                    render: function (obj) { return el(Button, { isSecondary: true, onClick: obj.open }, 'Добавить фото'); }
                })));
            }
            if (!images.length) {
                fields.push(el('p', { key: 'img-e', style: { fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные фото' + (Array.isArray(defImages) && defImages.length ? ' (' + defImages.length + ')' : '') + '.'));
            }
            images.forEach(function (img, index) {
                fields.push(el('div', { key: 'img-' + index, style: { display: 'flex', gap: '8px', alignItems: 'center', marginTop: '6px' } }, [
                    el('span', { key: 'id' }, '#' + img.image),
                    Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { setImages(images.filter(function (_, i) { return i !== index; })); } }, '×') : null
                ]));
            });

            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Пресс-портрет: «Клиенты»
    wp.blocks.registerBlockType('tolstenko/clients', {
        title: 'Клиенты',
        category: 'tolstenko-blocks-press',
        icon: 'groups',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            function normalizeLogoList(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    it = it || {};
                    return {
                        image: parseInt(it.image || it.id, 10) || 0,
                        name: it.name || it.title || '',
                        link: it.link || ''
                    };
                });
            }

            var items = normalizeLogoList(attrs.block_clients_items);
            var smi = normalizeLogoList(attrs.block_clients_smi);
            function setItems(next) { set({ block_clients_items: next.slice() }); }
            function setSmi(next) { set({ block_clients_smi: next.slice() }); }

            function renderLogoEditor(list, onChange, withLink, label, addLabel) {
                var rows = [
                    el('p', { key: 'l', style: { margin: '12px 0 6px', fontWeight: '600' } }, label),
                    !list.length ? el('p', { key: 'e', style: { fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолты.') : null
                ];
                list.forEach(function (item, index) {
                    var imgId = parseInt(item.image, 10) || 0;
                    rows.push(el('div', {
                        key: label + '-' + index,
                        style: { marginBottom: '8px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                    }, [
                        MediaUpload && MediaUploadCheck ? el('div', { key: 'm', style: { marginBottom: '6px' } }, [
                            el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                                allowedTypes: ['image'], value: imgId,
                                onSelect: function (m) {
                                    var next = list.slice();
                                    next[index] = Object.assign({}, item, { image: m && m.id ? m.id : 0, name: item.name || (m && (m.alt || m.title)) || '' });
                                    onChange(next);
                                },
                                render: function (obj) { return el(Button, { isSecondary: true, onClick: obj.open }, imgId ? 'Заменить логотип' : 'Логотип'); }
                            }))
                        ]) : null,
                        TextControl ? el(TextControl, {
                            key: 'n', label: 'Название / alt', value: item.name || '',
                            onChange: function (v) {
                                var next = list.slice();
                                next[index] = Object.assign({}, item, { name: v });
                                onChange(next);
                            }
                        }) : null,
                        withLink && TextControl ? el(TextControl, {
                            key: 'u', label: 'Ссылка', value: item.link || '',
                            onChange: function (v) {
                                var next = list.slice();
                                next[index] = Object.assign({}, item, { link: v });
                                onChange(next);
                            }
                        }) : null,
                        Button ? el(Button, {
                            key: 'rm', isDestructive: true, isSmall: true,
                            onClick: function () { onChange(list.filter(function (_, i) { return i !== index; })); }
                        }, 'Удалить') : null
                    ]));
                });
                if (Button) {
                    rows.push(el(Button, {
                        key: 'add', isSecondary: true,
                        onClick: function () { onChange(list.concat([{ image: 0, name: '', link: '' }])); }
                    }, addLabel));
                }
                return el('div', { key: label }, rows);
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Клиенты'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля — из «Дефолты блоков → Пресс-портрет».'),
                TextControl ? el(TextControl, {
                    key: 'title', label: 'Заголовок',
                    value: attrs.block_clients_title || '',
                    placeholder: getDefault('clients.title', 'Клиенты'),
                    onChange: function (v) { set({ block_clients_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_clients_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text', label: 'Текст',
                    value: attrs.block_clients_text || '',
                    placeholder: getDefault('clients.text', ''),
                    onChange: function (v) { set({ block_clients_text: v }); }
                }) : null,
                renderLogoEditor(items, setItems, false, 'Логотипы клиентов', 'Добавить логотип'),
                TextControl ? el(TextControl, {
                    key: 'sub', label: 'Подзаголовок (СМИ)',
                    value: attrs.block_clients_subtitle || '',
                    placeholder: getDefault('clients.subtitle', ''),
                    onChange: function (v) { set({ block_clients_subtitle: v }); }
                }) : null,
                renderLogoEditor(smi, setSmi, true, 'СМИ', 'Добавить СМИ')
            ]);
        },
        save: function () { return null; }
    });

    // Пресс-портрет: «Темы обучений и выступлений»
    wp.blocks.registerBlockType('tolstenko/themes', {
        title: 'Темы обучений и выступлений',
        category: 'tolstenko-blocks-press',
        icon: 'megaphone',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defItems = getDefault('themes.items', []);

            function toTextList(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    return typeof it === 'string' ? it : ((it && it.text) || '');
                });
            }
            var items = toTextList(attrs.block_themes_items);
            function setItems(next) { set({ block_themes_items: next.slice() }); }
            var imgId = parseInt(attrs.block_themes_image || 0, 10) || 0;

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Темы обучений и выступлений'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля — из «Дефолты блоков → Пресс-портрет».'),
                TextControl ? el(TextControl, {
                    key: 'title', label: 'Заголовок',
                    value: attrs.block_themes_title || '',
                    placeholder: getDefault('themes.title', 'Темы обучений и выступлений'),
                    onChange: function (v) { set({ block_themes_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_themes_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'il', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Темы'),
                !items.length ? el('p', { key: 'ie', style: { fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные темы.') : null
            ];
            items.forEach(function (txt, i) {
                fields.push(el('div', { key: 't-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px' } }, [
                    TextControl ? el(TextControl, {
                        key: 'v', label: 'Тема ' + (i + 1), value: txt || '',
                        onChange: function (v) { var next = items.slice(); next[i] = v; setItems(next); }
                    }) : null,
                    Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { setItems(items.filter(function (_, idx) { return idx !== i; })); } }, '×') : null
                ]));
            });
            if (Button) {
                fields.push(el(Button, {
                    key: 'add', isSecondary: true,
                    onClick: function () {
                        if (!items.length && Array.isArray(defItems) && defItems.length) {
                            setItems(toTextList(defItems).concat(['']));
                            return;
                        }
                        setItems(items.concat(['']));
                    }
                }, 'Добавить тему'));
            }
            fields.push(TextControl ? el(TextControl, {
                key: 'more', label: 'Текст внизу списка',
                value: attrs.block_themes_more_text || '',
                placeholder: getDefault('themes.more_text', 'и многое другое'),
                onChange: function (v) { set({ block_themes_more_text: v }); }
            }) : null);
            fields.push(TextControl ? el(TextControl, {
                key: 'btn', label: 'Текст кнопки',
                value: attrs.block_themes_btn_text || '',
                placeholder: getDefault('themes.btn_text', ''),
                onChange: function (v) { set({ block_themes_btn_text: v }); }
            }) : null);
            fields.push(TextControl ? el(TextControl, {
                key: 'btn-u', label: 'Ссылка кнопки',
                value: attrs.block_themes_btn_url || '',
                placeholder: 'Пусто = модалка',
                onChange: function (v) { set({ block_themes_btn_url: v }); }
            }) : null);
            if (MediaUpload && MediaUploadCheck) {
                fields.push(el('div', { key: 'img', style: { marginTop: '8px' } }, [
                    el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, 'Изображение'),
                    el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                        allowedTypes: ['image'], value: imgId,
                        onSelect: function (m) { set({ block_themes_image: m && m.id ? m.id : 0 }); },
                        render: function (obj) { return el(Button, { isSecondary: true, onClick: obj.open }, imgId ? 'Заменить' : 'Выбрать'); }
                    })),
                    imgId && Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, style: { marginLeft: '8px' }, onClick: function () { set({ block_themes_image: 0 }); } }, 'Удалить') : null
                ]));
            }
            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Пресс-портрет: «Форматы сотрудничества»
    wp.blocks.registerBlockType('tolstenko/collaboration', {
        title: 'Форматы сотрудничества',
        category: 'tolstenko-blocks-press',
        icon: 'networking',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defItems = getDefault('collaboration.items', []);

            function toTextList(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function (it) {
                    return typeof it === 'string' ? it : ((it && it.text) || '');
                });
            }
            var items = toTextList(attrs.block_collaboration_items);
            function setItems(next) { set({ block_collaboration_items: next.slice() }); }
            var imgId = parseInt(attrs.block_collaboration_image || 0, 10) || 0;

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Форматы сотрудничества'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля — из «Дефолты блоков → Пресс-портрет».'),
                TextControl ? el(TextControl, {
                    key: 'title', label: 'Заголовок',
                    value: attrs.block_collaboration_title || '',
                    placeholder: getDefault('collaboration.title', 'Форматы сотрудничества'),
                    onChange: function (v) { set({ block_collaboration_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_collaboration_title_tag', 'Тег заголовка', 'h2'),
                el('p', { key: 'il', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Пункты'),
                !items.length ? el('p', { key: 'ie', style: { fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные пункты.') : null
            ];
            items.forEach(function (txt, i) {
                fields.push(el('div', { key: 't-' + i, style: { display: 'flex', gap: '8px', marginBottom: '6px' } }, [
                    TextControl ? el(TextControl, {
                        key: 'v', label: 'Пункт ' + (i + 1), value: txt || '',
                        onChange: function (v) { var next = items.slice(); next[i] = v; setItems(next); }
                    }) : null,
                    Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, onClick: function () { setItems(items.filter(function (_, idx) { return idx !== i; })); } }, '×') : null
                ]));
            });
            if (Button) {
                fields.push(el(Button, {
                    key: 'add', isSecondary: true,
                    onClick: function () {
                        if (!items.length && Array.isArray(defItems) && defItems.length) {
                            setItems(toTextList(defItems).concat(['']));
                    return;
                }
                        setItems(items.concat(['']));
                    }
                }, 'Добавить пункт'));
            }
            fields.push(TextControl ? el(TextControl, {
                key: 'btn', label: 'Текст кнопки',
                value: attrs.block_collaboration_btn_text || '',
                placeholder: getDefault('collaboration.btn_text', ''),
                onChange: function (v) { set({ block_collaboration_btn_text: v }); }
            }) : null);
            fields.push(TextControl ? el(TextControl, {
                key: 'btn-u', label: 'Ссылка кнопки',
                value: attrs.block_collaboration_btn_url || '',
                placeholder: 'Пусто = модалка',
                onChange: function (v) { set({ block_collaboration_btn_url: v }); }
            }) : null);
            if (MediaUpload && MediaUploadCheck) {
                fields.push(el('div', { key: 'img', style: { marginTop: '8px' } }, [
                    el('p', { key: 'il', style: { marginBottom: '6px', fontWeight: '600' } }, 'Изображение'),
                    el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                        allowedTypes: ['image'], value: imgId,
                        onSelect: function (m) { set({ block_collaboration_image: m && m.id ? m.id : 0 }); },
                        render: function (obj) { return el(Button, { isSecondary: true, onClick: obj.open }, imgId ? 'Заменить' : 'Выбрать'); }
                    })),
                    imgId && Button ? el(Button, { key: 'rm', isDestructive: true, isSmall: true, style: { marginLeft: '8px' }, onClick: function () { set({ block_collaboration_image: 0 }); } }, 'Удалить') : null
                ]));
            }
            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Партнёры: «Рекомендации»
    wp.blocks.registerBlockType('tolstenko/recomendation', {
        title: 'Рекомендации',
        category: 'tolstenko-blocks-partner',
        icon: 'star-filled',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};

            function normalizeItems(raw) {
                if (!Array.isArray(raw)) {
                    return [];
                }
                return raw.map(function (it) {
                    it = it || {};
                    return {
                        ico: parseInt(it.ico, 10) || 0,
                        title: it.title || '',
                        text: it.text || ''
                    };
                });
            }

            function toTextList(raw) {
                if (!Array.isArray(raw)) {
                    return [];
                }
                return raw.map(function (it) {
                    return typeof it === 'string' ? it : ((it && it.text) || '');
                });
            }

            var items = normalizeItems(attrs.block_recomendation_items);
            var list = toTextList(attrs.block_recomendation_list);

            function setItems(next) { set({ block_recomendation_items: next.slice() }); }
            function setList(next) { set({ block_recomendation_list: next.slice() }); }
            function updateItem(index, patch) {
                var next = items.slice();
                next[index] = Object.assign({}, next[index] || { ico: 0, title: '', text: '' }, patch);
                setItems(next);
            }

            var defItems = getDefault('recomendation.items', []);
            var defList = getDefault('recomendation.list', []);

            var fields = [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Рекомендации'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля и списки подставятся из «Дефолты блоков → Партнёры блоки».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_recomendation_title || '',
                    placeholder: getDefault('recomendation.title', 'Рекомендации'),
                    onChange: function (v) { set({ block_recomendation_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_recomendation_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_recomendation_text || '',
                    placeholder: getDefault('recomendation.text', ''),
                    onChange: function (v) { set({ block_recomendation_text: v }); }
                }) : null,
                el('p', { key: 'items-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Карточки вариантов'),
                !items.length ? el('p', { key: 'items-empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтные карточки. Нажмите «Добавить», чтобы задать свои.') : null
            ];

            items.forEach(function (item, index) {
                var icoId = parseInt(item.ico, 10) || 0;
                fields.push(el('div', {
                    key: 'item-' + index,
                    style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                }, [
                    el('p', { key: 'n', style: { margin: '0 0 6px', fontWeight: '600' } }, 'Карточка ' + (index + 1)),
                    MediaUpload && MediaUploadCheck ? el('div', { key: 'ico', style: { marginBottom: '8px' } }, [
                        el('p', { key: 'il', style: { margin: '0 0 4px', fontSize: '12px' } }, 'Иконка (лучше SVG)'),
                        el(MediaUploadCheck, { key: 'muc' }, el(MediaUpload, {
                            allowedTypes: ['image'],
                            value: icoId,
                            onSelect: function (m) { updateItem(index, { ico: m && m.id ? m.id : 0 }); },
                            render: function (obj) {
                                return el(Button, { isSecondary: true, onClick: obj.open }, icoId ? 'Заменить иконку' : 'Выбрать иконку');
                            }
                        })),
                        icoId && Button ? el(Button, {
                            key: 'rm-ico',
                            isDestructive: true,
                            isSmall: true,
                            style: { marginLeft: '8px' },
                            onClick: function () { updateItem(index, { ico: 0 }); }
                        }, 'Удалить') : null
                    ]) : null,
                    TextControl ? el(TextControl, {
                        key: 't',
                        label: 'Заголовок карточки',
                            value: item.title || '',
                        onChange: function (v) { updateItem(index, { title: v }); }
                    }) : null,
                    TextareaControl ? el(TextareaControl, {
                        key: 'tx',
                        label: 'Текст карточки',
                        value: item.text || '',
                        onChange: function (v) { updateItem(index, { text: v }); }
                    }) : null,
                        Button ? el(Button, {
                            key: 'rm',
                        isDestructive: true,
                            isSmall: true,
                        onClick: function () { setItems(items.filter(function (_, i) { return i !== index; })); }
                    }, 'Удалить карточку') : null
                ]));
            });

            if (Button) {
                fields.push(el(Button, {
                    key: 'add-item',
                    isSecondary: true,
                    onClick: function () {
                        if (!items.length && Array.isArray(defItems) && defItems.length) {
                            setItems(normalizeItems(defItems).concat([{ ico: 0, title: '', text: '' }]));
                            return;
                        }
                        setItems(items.concat([{ ico: 0, title: '', text: '' }]));
                    }
                }, 'Добавить карточку'));
            }

            fields.push(TextControl ? el(TextControl, {
                key: 'list-title',
                label: 'Заголовок справа',
                value: attrs.block_recomendation_list_title || '',
                placeholder: getDefault('recomendation.list_title', 'При любом варианте'),
                onChange: function (v) { set({ block_recomendation_list_title: v }); }
            }) : null);

            fields.push(el('p', { key: 'list-l', style: { margin: '12px 0 6px', fontWeight: '600' } }, 'Список справа'));
            if (!list.length) {
                fields.push(el('p', { key: 'list-empty', style: { margin: '0 0 8px', fontSize: '12px', color: '#757575' } }, 'Пусто — на сайте дефолтный список.'));
            }
            list.forEach(function (txt, i) {
                fields.push(el('div', {
                    key: 'list-' + i,
                    style: { display: 'flex', gap: '8px', marginBottom: '6px', alignItems: 'flex-start' }
                }, [
                    TextControl ? el(TextControl, {
                        key: 't',
                        label: 'Пункт ' + (i + 1),
                        value: txt || '',
                        onChange: function (v) {
                            var next = list.slice();
                            next[i] = v;
                            setList(next);
                        }
                    }) : null,
                    Button ? el(Button, {
                        key: 'rm',
                            isDestructive: true,
                        isSmall: true,
                        onClick: function () { setList(list.filter(function (_, idx) { return idx !== i; })); }
                    }, '×') : null
                ]));
            });
            if (Button) {
                fields.push(el(Button, {
                    key: 'add-list',
                    isSecondary: true,
                    onClick: function () {
                        if (!list.length && Array.isArray(defList) && defList.length) {
                            setList(toTextList(defList).concat(['']));
                            return;
                        }
                        setList(list.concat(['']));
                    }
                }, 'Добавить пункт'));
            }

            fields.push(TextControl ? el(TextControl, {
                key: 'btn',
                label: 'Текст кнопки',
                value: attrs.block_recomendation_btn_text || '',
                placeholder: getDefault('recomendation.btn_text', ''),
                onChange: function (v) { set({ block_recomendation_btn_text: v }); }
            }) : null);
            fields.push(TextControl ? el(TextControl, {
                key: 'btn-url',
                label: 'Ссылка кнопки',
                value: attrs.block_recomendation_btn_url || '',
                placeholder: getDefault('recomendation.btn_url', '') || 'Пусто = модалка',
                onChange: function (v) { set({ block_recomendation_btn_url: v }); }
            }) : null);

            return wrapBlock(blockProps, fields);
        },
        save: function () { return null; }
    });

    // Акции, бонусы, подарки (слайдер) — карточки из репитера; CPT actions только как ссылка.
    wp.blocks.registerBlockType('tolstenko/actions', {
        title: 'Акции, бонусы, подарки',
        category: 'tolstenko-blocks-new',
        icon: 'tickets-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('actions.title', 'Акции, бонусы, подарки');
            var defItems = getDefault('actions.items', []);
            var items = Array.isArray(attrs.block_actions_items) ? attrs.block_actions_items.slice() : [];
            if (!items.length && Array.isArray(defItems) && defItems.length) {
                items = defItems.map(function (it) {
                    return {
                        type: (it && it.type) || '',
                        title: (it && it.title) || '',
                        text: (it && it.text) || '',
                        action_id: (it && it.action_id) ? parseInt(it.action_id, 10) : 0
                    };
                });
            }
            var actionPosts = (typeof tolstenkoBlockDefaults !== 'undefined' && Array.isArray(tolstenkoBlockDefaults.actionPosts))
                ? tolstenkoBlockDefaults.actionPosts
                : [];
            var actionOptions = [{ label: '— Без ссылки —', value: '0' }].concat(actionPosts.map(function (p) {
                return { label: p.title || ('#' + p.id), value: String(p.id) };
            }));

            function setItems(next) {
                set({ block_actions_items: next.slice(0, 4) });
            }
            function updateItem(i, patch) {
                var next = items.map(function (it, idx) {
                    return idx === i ? Object.assign({}, it || {}, patch) : it;
                });
                setItems(next);
            }

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Акции, бонусы, подарки'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Карточки свои (до 4). Запись «Акции» — только ссылка на страницу.'),
                TextControl ? el(TextControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_actions_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_actions_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_actions_title_tag', 'Тег заголовка', 'h2'),
                el('div', { key: 'items', style: { marginTop: '12px' } }, [
                    el('p', { key: 'il', style: { margin: '0 0 8px', fontWeight: '600' } }, 'Карточки'),
                    items.map(function (item, index) {
                        item = item || {};
                        return el('div', {
                            key: 'item-' + index,
                            style: { border: '1px solid #ddd', padding: '10px', marginBottom: '8px', background: '#fafafa' }
                        }, [
                            TextControl ? el(TextControl, {
                                key: 'type',
                                label: 'Тип / метка',
                                value: item.type || '',
                                onChange: function (v) { updateItem(index, { type: v }); }
                            }) : null,
                            TextControl ? el(TextControl, {
                                key: 'title',
                                label: 'Заголовок карточки',
                                value: item.title || '',
                                onChange: function (v) { updateItem(index, { title: v }); }
                            }) : null,
                            TextareaControl ? el(TextareaControl, {
                    key: 'text',
                                label: 'Текст',
                                value: item.text || '',
                                rows: 2,
                                onChange: function (v) { updateItem(index, { text: v }); }
                            }) : null,
                            SelectControl ? el(SelectControl, {
                                key: 'aid',
                                label: 'Ссылка на акцию',
                                value: String(item.action_id || 0),
                                options: actionOptions,
                                onChange: function (v) { updateItem(index, { action_id: parseInt(v, 10) || 0 }); }
                            }) : null,
                            el('button', {
                                key: 'rm',
                                type: 'button',
                                className: 'button',
                                style: { marginTop: '6px' },
                                onClick: function () {
                                    setItems(items.filter(function (_, i) { return i !== index; }));
                                }
                            }, 'Удалить')
                        ]);
                    }),
                    items.length < 4 ? el('button', {
                        key: 'add',
                        type: 'button',
                        className: 'button',
                        onClick: function () {
                            setItems(items.concat([{ type: '', title: '', text: '', action_id: 0 }]));
                        }
                    }, 'Добавить карточку') : null
                ])
            ]);
        },
        save: function () { return null; }
    });

    // Плитка акций — заголовок/текст секции; карточки из CPT «Акции».
    wp.blocks.registerBlockType('tolstenko/actions-section', {
        title: 'Плитка акций',
        category: 'tolstenko-blocks-new',
        icon: 'megaphone',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('actions_section.title', 'Акции');
            var defText = getDefault('actions_section.text', '');

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Плитка акций'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты. Карточки — из записей «Акции».'),
                TextControl ? el(TextControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_actions_section_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_actions_section_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_actions_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_actions_section_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_actions_section_text: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Города — заголовок/текст; чипы из CPT «Город».
    wp.blocks.registerBlockType('tolstenko/city', {
        title: 'Города',
        category: 'tolstenko-blocks-new',
        icon: 'location-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('city.title', 'Города');
            var defText = getDefault('city.text', '');

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Города'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Пустые поля = дефолты. Список — из записей «Город».'),
                TextControl ? el(TextControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_city_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_city_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_city_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_city_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_city_text: v }); }
                        }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Баннер вакансий.
    wp.blocks.registerBlockType('tolstenko/vacancies-banner', {
        title: 'Баннер вакансий',
        category: 'tolstenko-blocks-new',
        icon: 'id-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('vacancies_banner.title', 'Вакансии');
            var defText = getDefault('vacancies_banner.text', '');
            var imageId = parseInt(attrs.block_vacancies_banner_image, 10) || 0;

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Баннер вакансий'),
                TextControl ? el(TextControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_vacancies_banner_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_vacancies_banner_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_vacancies_banner_title_tag', 'Тег заголовка', 'h1'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_vacancies_banner_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_vacancies_banner_text: v }); }
                }) : null,
                MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'mu' },
                        el(MediaUpload, {
                        onSelect: function (media) {
                            set({ block_vacancies_banner_image: media && media.id ? media.id : 0 });
                        },
                        allowedTypes: ['image'],
                        value: imageId,
                            render: function (obj) {
                            return el(Button, {
                                variant: 'secondary',
                                onClick: obj.open
                            }, imageId ? 'Сменить изображение' : 'Выбрать изображение');
                        }
                    })
                ) : null,
                imageId ? el(Button, {
                    key: 'rm',
                    isDestructive: true,
                    variant: 'link',
                    onClick: function () { set({ block_vacancies_banner_image: 0 }); }
                }, 'Убрать изображение') : null
            ]);
        },
        save: function () { return null; }
    });

    // Секция вакансий (фильтр REST + vacancy-card).
    wp.blocks.registerBlockType('tolstenko/vacancies-section', {
        title: 'Секция вакансий',
        category: 'tolstenko-blocks-new',
        icon: 'groups',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('vacancies_section.title', 'Открытые вакансии');
            var defText = getDefault('vacancies_section.text', '');

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Секция вакансий'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Карточки и фильтр — из CPT «Вакансии» / vacancy_cat.'),
                TextControl ? el(TextControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_vacancies_section_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_vacancies_section_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_vacancies_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_vacancies_section_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_vacancies_section_text: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Кейсы (фильтр REST + case-card + Swiper).
    wp.blocks.registerBlockType('tolstenko/case-section', {
        title: 'Кейсы',
        category: 'tolstenko-blocks-new',
        icon: 'awards',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('case_section.title', 'Кейсы');
            var defText = getDefault('case_section.text', '');
            var defPpp = getDefault('case_section.posts_per_page', 4);

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Кейсы'),
                el('p', { key: 'h', style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' } }, 'Карточки и фильтр — из CPT «Кейсы» / case_cat. Изображение карточки — миниатюра записи.'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_case_section_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_case_section_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_case_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_case_section_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_case_section_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'ppp',
                    label: 'Количество кейсов (−1 = все)',
                    type: 'number',
                    value: attrs.block_case_section_posts_per_page != null ? String(attrs.block_case_section_posts_per_page) : String(defPpp),
                    onChange: function (v) {
                        var n = parseInt(v, 10);
                        set({ block_case_section_posts_per_page: isNaN(n) ? 4 : n });
                    }
                        }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Слайдер услуг / Слайдер услуг (фильтры).
    function registerServiceSectionVariant(name, title, label, defaultsKey) {
        wp.blocks.registerBlockType(name, {
            title: title,
            category: 'tolstenko-blocks-new',
            icon: 'hammer',
            edit: function ServiceSectionEdit(props) {
                var attrs = props.attributes || {};
                var set = props.setAttributes;
                var blockProps = useBlockProps ? useBlockProps() : {};
                var defKey = defaultsKey || 'service_section';
                var defTitle = getDefault(defKey + '.title', 'Услуги');
                var defText = getDefault(defKey + '.text', '');
                var defPpp = getDefault(defKey + '.posts_per_page', 6);
                var withFilters = name === 'tolstenko/service-section';
                var selectedIds = Array.isArray(attrs.block_service_section_ids)
                    ? attrs.block_service_section_ids.map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; })
                    : [];

                var services = (useSelect ? useSelect(function (select) {
                    var records = select('core').getEntityRecords('postType', 'service', {
                        per_page: 100,
                        status: 'publish',
                        orderby: 'title',
                        order: 'asc',
                        _fields: 'id,title'
                    });
                    return Array.isArray(records) ? records : [];
                }, []) : []);

                var idToTitle = {};
                var titleToId = {};
                var suggestions = [];
                services.forEach(function (post) {
                    if (!post || !post.id) return;
                    var t = (post.title && post.title.rendered) ? String(post.title.rendered) : ('#' + post.id);
                    // strip tags from rendered title
                    t = t.replace(/<[^>]+>/g, '').trim() || ('#' + post.id);
                    // unique titles for FormTokenField
                    var key = t;
                    var n = 2;
                    while (titleToId[key] && titleToId[key] !== post.id) {
                        key = t + ' (' + n + ')';
                        n += 1;
                    }
                    idToTitle[post.id] = key;
                    titleToId[key] = post.id;
                    suggestions.push(key);
                });

                var tokens = selectedIds.map(function (id) {
                    return idToTitle[id] || ('#' + id);
                });

                return el('div', blockProps, [
                    el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, label),
                    el('p', {
                        key: 'h',
                        style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' }
                    }, withFilters
                        ? 'Пустые поля и список услуг — из дефолтов «Слайдер услуг (фильтры)». Если услуги не выбраны ни в блоке, ни в дефолтах — N новых. Фильтр категорий сужает выборку.'
                        : 'Пустые поля и список услуг — из дефолтов «Слайдер услуг». Если услуги не выбраны ни в блоке, ни в дефолтах — N новых.'),
                    TextareaControl ? el(TextareaControl, {
                        key: 't',
                        label: 'Заголовок (HTML)',
                        value: attrs.block_service_section_title || '',
                        placeholder: defTitle,
                        onChange: function (v) { set({ block_service_section_title: v }); },
                        rows: 2
                    }) : null,
                    renderHeadingTagSelect(attrs, set, 'block_service_section_title_tag', 'Тег заголовка', 'h2'),
                    TextareaControl ? el(TextareaControl, {
                        key: 'tx',
                        label: 'Текст',
                        value: attrs.block_service_section_text || '',
                        placeholder: defText,
                        onChange: function (v) { set({ block_service_section_text: v }); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'ppp',
                        label: 'Количество услуг, если ничего не выбрано (−1 = все)',
                        type: 'number',
                        value: attrs.block_service_section_posts_per_page != null ? String(attrs.block_service_section_posts_per_page) : String(defPpp),
                        onChange: function (v) {
                            var n = parseInt(v, 10);
                            set({ block_service_section_posts_per_page: isNaN(n) ? 6 : n });
                        }
                    }) : null,
                    FormTokenField ? el(FormTokenField, {
                        key: 'ids',
                        label: 'Услуги (пусто = дефолты настроек, иначе самые новые)',
                        value: tokens,
                        suggestions: suggestions,
                        onChange: function (nextTokens) {
                            var nextIds = [];
                            (nextTokens || []).forEach(function (token) {
                                var id = titleToId[token];
                                if (!id && /^#\d+$/.test(token)) {
                                    id = parseInt(token.slice(1), 10);
                                }
                                id = parseInt(id, 10);
                                if (id > 0 && nextIds.indexOf(id) === -1) {
                                    nextIds.push(id);
                                }
                            });
                            set({ block_service_section_ids: nextIds });
                        },
                        __experimentalExpandOnFocus: true
                        }) : null
            ]);
        },
            save: function () { return null; }
        });
    }
    registerServiceSectionVariant('tolstenko/service-section-simple', 'Слайдер услуг', 'Слайдер услуг', 'service_section');
    registerServiceSectionVariant('tolstenko/service-section', 'Слайдер услуг (фильтры)', 'Слайдер услуг (фильтры)', 'service_section_filters');

    wp.blocks.registerBlockType('tolstenko/blog-section-simple', {
        title: 'Слайдер статей',
        category: 'tolstenko-blocks-new',
        icon: 'admin-post',
        edit: function BlogSectionSimpleEdit(props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('blog_section.title', 'Похожие статьи');
            var defText = getDefault('blog_section.text', '');
            var defPpp = getDefault('blog_section.posts_per_page', 6);
            var selectedIds = Array.isArray(attrs.block_blog_section_ids)
                ? attrs.block_blog_section_ids.map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; })
                : [];

            var blogs = (useSelect ? useSelect(function (select) {
                var records = select('core').getEntityRecords('postType', 'blog', {
                    per_page: 100,
                    status: 'publish',
                    orderby: 'title',
                    order: 'asc',
                    _fields: 'id,title'
                });
                return Array.isArray(records) ? records : [];
            }, []) : []);

            var idToTitle = {};
            var titleToId = {};
            var suggestions = [];
            blogs.forEach(function (post) {
                if (!post || !post.id) return;
                var t = (post.title && post.title.rendered) ? String(post.title.rendered) : ('#' + post.id);
                t = t.replace(/<[^>]+>/g, '').trim() || ('#' + post.id);
                var key = t;
                var n = 2;
                while (titleToId[key] && titleToId[key] !== post.id) {
                    key = t + ' (' + n + ')';
                    n += 1;
                }
                idToTitle[post.id] = key;
                titleToId[key] = post.id;
                suggestions.push(key);
            });

            var tokens = selectedIds.map(function (id) {
                return idToTitle[id] || ('#' + id);
            });

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Слайдер статей'),
                el('p', {
                    key: 'h',
                    style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' }
                }, 'Пустые поля и список статей — из дефолтов «Слайдер статей». Заголовок по умолчанию — «Похожие статьи». Если статьи не выбраны — N новых. На single статьи текущая исключается автоматически.'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_blog_section_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_blog_section_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_blog_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_blog_section_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_blog_section_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'ppp',
                    label: 'Количество статей, если ничего не выбрано (−1 = все)',
                    type: 'number',
                    value: attrs.block_blog_section_posts_per_page != null ? String(attrs.block_blog_section_posts_per_page) : String(defPpp),
                    onChange: function (v) {
                        var n = parseInt(v, 10);
                        set({ block_blog_section_posts_per_page: isNaN(n) ? 6 : n });
                    }
                }) : null,
                FormTokenField ? el(FormTokenField, {
                    key: 'ids',
                    label: 'Статьи (пусто = дефолты настроек, иначе самые новые)',
                    value: tokens,
                    suggestions: suggestions,
                    onChange: function (nextTokens) {
                        var nextIds = [];
                        (nextTokens || []).forEach(function (token) {
                            var id = titleToId[token];
                            if (!id && /^#\d+$/.test(token)) {
                                id = parseInt(token.slice(1), 10);
                            }
                            id = parseInt(id, 10);
                            if (id > 0 && nextIds.indexOf(id) === -1) {
                                nextIds.push(id);
                            }
                        });
                        set({ block_blog_section_ids: nextIds });
                    },
                    __experimentalExpandOnFocus: true
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    function registerBlogSectionVariant(name, title, label, defaultsKey, options) {
        options = options || {};
        var withFilters = !!options.withFilters;
        var isTile = !!options.isTile;
        var defaultPpp = isTile ? 9 : 6;
        wp.blocks.registerBlockType(name, {
            title: title,
            category: 'tolstenko-blocks-new',
            icon: 'admin-post',
            edit: function BlogSectionVariantEdit(props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
                var defTitle = getDefault(defaultsKey + '.title', 'Статьи');
                var defText = getDefault(defaultsKey + '.text', '');
                var defPpp = getDefault(defaultsKey + '.posts_per_page', defaultPpp);
                var selectedIds = Array.isArray(attrs.block_blog_section_ids)
                    ? attrs.block_blog_section_ids.map(function (id) { return parseInt(id, 10); }).filter(function (id) { return id > 0; })
                    : [];

                var blogs = (useSelect ? useSelect(function (select) {
                    var records = select('core').getEntityRecords('postType', 'blog', {
                        per_page: 100,
                        status: 'publish',
                        orderby: 'title',
                        order: 'asc',
                        _fields: 'id,title'
                    });
                    return Array.isArray(records) ? records : [];
                }, []) : []);

                var idToTitle = {};
                var titleToId = {};
                var suggestions = [];
                blogs.forEach(function (post) {
                    if (!post || !post.id) return;
                    var t = (post.title && post.title.rendered) ? String(post.title.rendered) : ('#' + post.id);
                    t = t.replace(/<[^>]+>/g, '').trim() || ('#' + post.id);
                    var key = t;
                    var n = 2;
                    while (titleToId[key] && titleToId[key] !== post.id) {
                        key = t + ' (' + n + ')';
                        n += 1;
                    }
                    idToTitle[post.id] = key;
                    titleToId[key] = post.id;
                    suggestions.push(key);
                });

                var tokens = selectedIds.map(function (id) {
                    return idToTitle[id] || ('#' + id);
                });

                var hint = isTile
                    ? 'Плитка статей с фильтром рубрик и постраничной навигацией. Пустые поля — из дефолтов «Статьи плитка».'
                    : (withFilters
                        ? 'Слайдер статей с фильтром рубрик. Пустые поля и список — из дефолтов «Статьи».'
                        : 'Пустые поля и список статей — из дефолтов.');

                return el('div', blockProps, [
                    el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, label),
                    el('p', {
                        key: 'h',
                        style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' }
                    }, hint),
                    TextareaControl ? el(TextareaControl, {
                        key: 't',
                        label: 'Заголовок (HTML)',
                        value: attrs.block_blog_section_title || '',
                        placeholder: defTitle,
                        onChange: function (v) { set({ block_blog_section_title: v }); },
                        rows: 2
                    }) : null,
                    renderHeadingTagSelect(attrs, set, 'block_blog_section_title_tag', 'Тег заголовка', 'h2'),
                    TextareaControl ? el(TextareaControl, {
                        key: 'tx',
                        label: 'Текст',
                        value: attrs.block_blog_section_text || '',
                        placeholder: defText,
                        onChange: function (v) { set({ block_blog_section_text: v }); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'ppp',
                        label: isTile
                            ? 'Статей на странице (−1 = все на одной)'
                            : 'Количество статей, если ничего не выбрано (−1 = все)',
                        type: 'number',
                        value: attrs.block_blog_section_posts_per_page != null ? String(attrs.block_blog_section_posts_per_page) : String(defPpp),
                        onChange: function (v) {
                            var n = parseInt(v, 10);
                            set({ block_blog_section_posts_per_page: isNaN(n) ? defaultPpp : n });
                        }
                    }) : null,
                    FormTokenField ? el(FormTokenField, {
                        key: 'ids',
                        label: 'Статьи (пусто = дефолты настроек, иначе самые новые)',
                        value: tokens,
                        suggestions: suggestions,
                        onChange: function (nextTokens) {
                            var nextIds = [];
                            (nextTokens || []).forEach(function (token) {
                                var id = titleToId[token];
                                if (!id && /^#\d+$/.test(token)) {
                                    id = parseInt(token.slice(1), 10);
                                }
                                id = parseInt(id, 10);
                                if (id > 0 && nextIds.indexOf(id) === -1) {
                                    nextIds.push(id);
                                }
                            });
                            set({ block_blog_section_ids: nextIds });
                        },
                        __experimentalExpandOnFocus: true
                    }) : null
                ]);
            },
            save: function () { return null; }
        });
    }
    registerBlogSectionVariant('tolstenko/blog-section', 'Статьи', 'Статьи', 'blog_section_filters', { withFilters: true });
    registerBlogSectionVariant('tolstenko/blog-section-tile', 'Статьи плитка', 'Статьи плитка', 'blog_section_tile', { isTile: true });

    // —— Блоки тела статьи (flexible content Tolstenko) ——
    function openWpMediaImage(onSelect) {
        if (typeof wp === 'undefined' || !wp.media) {
            return;
        }
        var frame = wp.media({
            title: 'Выбрать изображение',
            button: { text: 'Выбрать' },
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function () {
            var att = frame.state().get('selection').first();
            var id = att && att.get ? (att.get('id') || 0) : 0;
            onSelect(parseInt(id, 10) || 0);
        });
        frame.open();
    }

    function mediaImageControl(label, id, onSelect, onRemove, keyPrefix) {
        var mid = parseInt(id, 10) || 0;
        var k = keyPrefix || ('media-' + String(label || 'img').replace(/\s+/g, '-'));
        var openPicker = function () { openWpMediaImage(onSelect); };
        return el('div', { key: k, style: { marginBottom: '12px' } }, [
            el('p', { key: k + '-l', style: { margin: '0 0 4px', fontWeight: '600' } }, label),
            el('div', { key: k + '-row', style: { display: 'flex', gap: '8px', alignItems: 'center', flexWrap: 'wrap' } }, [
                                Button ? el(Button, {
                    key: k + '-pick',
                                    isSecondary: true,
                                    isSmall: true,
                    onClick: openPicker
                }, mid ? ('Сменить (#' + mid + ')') : 'Выбрать') : el('button', {
                    key: k + '-pick',
                    type: 'button',
                    className: 'button',
                    onClick: openPicker
                }, mid ? ('Сменить (#' + mid + ')') : 'Выбрать'),
                mid && Button ? el(Button, {
                    key: k + '-rm',
                                isDestructive: true,
                                isSmall: true,
                    onClick: onRemove
                }, 'Убрать') : null
            ])
        ]);
    }

    wp.blocks.registerBlockType('tolstenko/blog-large-img', {
        title: 'Статья: крупное фото',
        category: 'tolstenko-blocks-new',
        icon: 'format-image',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: крупное фото'),
                mediaImageControl(
                    'Изображение',
                    attrs.block_blog_large_img_id || getDefault('blog_large_img.image', 0),
                    function (id) { set({ block_blog_large_img_id: id }); },
                    function () { set({ block_blog_large_img_id: 0 }); }
                )
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-imgs', {
        title: 'Статья: два фото',
        category: 'tolstenko-blocks-new',
        icon: 'images-alt2',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: два фото'),
                mediaImageControl(
                    'Левое',
                    attrs.block_blog_imgs_left || getDefault('blog_imgs.left', 0),
                    function (id) { set({ block_blog_imgs_left: id }); },
                    function () { set({ block_blog_imgs_left: 0 }); }
                ),
                mediaImageControl(
                    'Правое',
                    attrs.block_blog_imgs_right || getDefault('blog_imgs.right', 0),
                    function (id) { set({ block_blog_imgs_right: id }); },
                    function () { set({ block_blog_imgs_right: 0 }); }
                )
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-video', {
        title: 'Статья: видео',
        category: 'tolstenko-blocks-new',
        icon: 'video-alt3',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: видео'),
                mediaImageControl(
                    'Превью',
                    attrs.block_blog_video_preview || getDefault('blog_video.preview', 0),
                    function (id) { set({ block_blog_video_preview: id }); },
                    function () { set({ block_blog_video_preview: 0 }); }
                ),
                TextControl ? el(TextControl, {
                    key: 'url',
                    label: 'URL видео (mp4 и т.п.)',
                    value: attrs.block_blog_video_url || '',
                    placeholder: getDefault('blog_video.url', ''),
                    onChange: function (v) { set({ block_blog_video_url: v }); }
                }) : null,
                TextareaControl ? el(TextareaControl, {
                    key: 'iframe',
                    label: 'Rutube / iframe HTML (если задан — приоритетнее URL)',
                    value: attrs.block_blog_video_iframe || '',
                    placeholder: getDefault('blog_video.iframe', ''),
                    onChange: function (v) { set({ block_blog_video_iframe: v }); },
                    rows: 3
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-blockquote', {
        title: 'Статья: цитата',
        category: 'tolstenko-blocks-new',
        icon: 'format-quote',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var showAuthor = Object.prototype.hasOwnProperty.call(attrs, 'block_blog_blockquote_show_author')
                ? !!attrs.block_blog_blockquote_show_author
                : !!getDefault('blog_blockquote.show_author', false);
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: цитата'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Текст цитаты',
                    value: attrs.block_blog_blockquote_text || '',
                    placeholder: getDefault('blog_blockquote.text', ''),
                    onChange: function (v) { set({ block_blog_blockquote_text: v }); },
                    rows: 4
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'cite',
                    label: 'Ссылка cite (необяз.)',
                    value: attrs.block_blog_blockquote_link || '',
                    placeholder: getDefault('blog_blockquote.link', ''),
                    onChange: function (v) { set({ block_blog_blockquote_link: v }); }
                }) : null,
                ToggleControl ? el(ToggleControl, {
                    key: 'show',
                    label: 'Показать автора справа',
                    checked: showAuthor,
                    onChange: function (v) { set({ block_blog_blockquote_show_author: !!v }); }
                }) : null,
                showAuthor ? mediaImageControl(
                    'Фото автора',
                    attrs.block_blog_blockquote_image || getDefault('blog_blockquote.image', 0),
                    function (id) { set({ block_blog_blockquote_image: id }); },
                    function () { set({ block_blog_blockquote_image: 0 }); },
                    'bq-author-img'
                ) : null,
                showAuthor && TextControl ? el(TextControl, {
                    key: 'a',
                    label: 'Имя автора',
                    value: attrs.block_blog_blockquote_author || '',
                    placeholder: getDefault('blog_blockquote.author', ''),
                    onChange: function (v) { set({ block_blog_blockquote_author: v }); }
                }) : null,
                showAuthor && TextControl ? el(TextControl, {
                    key: 'u',
                    label: 'Подпись под именем',
                    value: attrs.block_blog_blockquote_author_under || '',
                    placeholder: getDefault('blog_blockquote.author_under', ''),
                    onChange: function (v) { set({ block_blog_blockquote_author_under: v }); }
                }) : null,
                showAuthor && TextControl ? el(TextControl, {
                    key: 'bt',
                    label: 'Текст кнопки',
                    value: attrs.block_blog_blockquote_btn_text || '',
                    placeholder: getDefault('blog_blockquote.btn_text', ''),
                    onChange: function (v) { set({ block_blog_blockquote_btn_text: v }); }
                }) : null,
                showAuthor && TextControl ? el(TextControl, {
                    key: 'bu',
                    label: 'URL кнопки',
                    value: attrs.block_blog_blockquote_btn_url || '',
                    placeholder: getDefault('blog_blockquote.btn_url', '') || 'Пусто = модалка (#modal)',
                    onChange: function (v) { set({ block_blog_blockquote_btn_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-number-list', {
        title: 'Статья: нумерованный список',
        category: 'tolstenko-blocks-new',
        icon: 'editor-ol',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_blog_number_list_items) ? attrs.block_blog_number_list_items.slice() : [];
            if (!items.length) {
                var defItems = getDefault('blog_number_list.items', []);
                items = Array.isArray(defItems) && defItems.length ? defItems.map(function (it) {
                    return { text: (it && it.text) || String(it || '') };
                }) : [{ text: '' }];
            }
            function setItems(next) { set({ block_blog_number_list_items: next }); }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: нумерованный список'),
                el('div', { key: 'items' }, items.map(function (item, index) {
                    return el('div', { key: 'n-' + index, style: { marginBottom: '8px', display: 'flex', gap: '8px', alignItems: 'flex-start' } }, [
                        TextareaControl ? el(TextareaControl, {
                            label: 'Пункт ' + (index + 1),
                            value: (item && item.text) || '',
                            onChange: function (v) {
                                var next = items.slice();
                                next[index] = { text: v };
                                setItems(next);
                            },
                            rows: 2
                        }) : null,
                        Button ? el(Button, {
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                var next = items.filter(function (_, i) { return i !== index; });
                                setItems(next.length ? next : [{ text: '' }]);
                            }
                        }, '×') : null
                    ]);
                })),
                Button ? el(Button, {
                    key: 'add',
                    isSecondary: true,
                    isSmall: true,
                    onClick: function () { setItems(items.concat([{ text: '' }])); }
                }, 'Добавить пункт') : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-warning', {
        title: 'Статья: предупреждения',
        category: 'tolstenko-blocks-new',
        icon: 'warning',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_blog_warning_items) ? attrs.block_blog_warning_items.slice() : [];
            if (!items.length) {
                var defWarn = getDefault('blog_warning.items', []);
                items = Array.isArray(defWarn) && defWarn.length ? defWarn.map(function (it) {
                    return {
                        type: (it && it.type) || 'warn',
                        text: (it && it.text) || '',
                        icon: (it && it.icon) || 0
                    };
                }) : [{ type: 'warn', text: '', icon: 0 }];
            }
            function setItems(next) { set({ block_blog_warning_items: next }); }
            var typeOptions = [
                { label: 'Внимание', value: 'warn' },
                { label: 'Подметить', value: 'pin' },
                { label: 'Идея', value: 'ide' },
                { label: 'Кастомный', value: 'custom' }
            ];
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: предупреждения'),
                el('div', { key: 'items' }, items.map(function (item, index) {
                    var type = (item && item.type) || 'warn';
                    return el('div', {
                        key: 'w-' + index,
                        style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                    }, [
                        SelectControl ? el(SelectControl, {
                            label: 'Тип иконки',
                            value: type,
                            options: typeOptions,
                            onChange: function (v) {
                var next = items.slice();
                                next[index] = Object.assign({}, item || {}, { type: v });
                setItems(next);
            }
                        }) : null,
                        TextareaControl ? el(TextareaControl, {
                            label: 'Текст',
                            value: (item && item.text) || '',
                            onChange: function (v) {
                var next = items.slice();
                                next[index] = Object.assign({}, item || {}, { text: v });
                setItems(next);
                            },
                            rows: 2
                        }) : null,
                        type === 'custom' ? mediaImageControl(
                            'Иконка (SVG/изображение)',
                            item && item.icon,
                            function (id) {
                var next = items.slice();
                                next[index] = Object.assign({}, item || {}, { icon: id });
                                setItems(next);
                            },
                            function () {
                                var next = items.slice();
                                next[index] = Object.assign({}, item || {}, { icon: 0 });
                setItems(next);
            }
                        ) : null,
                        Button ? el(Button, {
                            isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                var next = items.filter(function (_, i) { return i !== index; });
                                setItems(next.length ? next : [{ type: 'warn', text: '', icon: 0 }]);
                            }
                        }, 'Удалить') : null
                    ]);
                })),
                Button ? el(Button, {
                    key: 'add',
                    isSecondary: true,
                    isSmall: true,
                    onClick: function () { setItems(items.concat([{ type: 'warn', text: '', icon: 0 }])); }
                }, 'Добавить пункт') : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-seo', {
        title: 'Статья: SEO / CTA',
        category: 'tolstenko-blocks-new',
        icon: 'megaphone',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: SEO / CTA'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок',
                    value: attrs.block_blog_seo_title || '',
                    placeholder: getDefault('blog_seo.title', ''),
                    onChange: function (v) { set({ block_blog_seo_title: v }); },
                    rows: 2
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'b',
                    label: 'Текст кнопки',
                    value: attrs.block_blog_seo_btn || '',
                    placeholder: getDefault('blog_seo.btn', ''),
                    onChange: function (v) { set({ block_blog_seo_btn: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'u',
                    label: 'Ссылка кнопки (пусто = #modal)',
                    value: attrs.block_blog_seo_btn_url || '',
                    placeholder: getDefault('blog_seo.btn_url', '') || '#modal',
                    onChange: function (v) { set({ block_blog_seo_btn_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/blog-table', {
        title: 'Статья: таблица',
        category: 'tolstenko-blocks-new',
        icon: 'editor-table',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var useHeader = Object.prototype.hasOwnProperty.call(attrs, 'block_blog_table_use_header')
                ? !!attrs.block_blog_table_use_header
                : !!getDefault('blog_table.use_header', true);
            var defHeader = getDefault('blog_table.header', ['', '']);
            var defRows = getDefault('blog_table.rows', [{ cells: ['', ''] }]);
            var header = Array.isArray(attrs.block_blog_table_header) && attrs.block_blog_table_header.length
                ? attrs.block_blog_table_header.slice()
                : (Array.isArray(defHeader) && defHeader.length ? defHeader.slice() : ['', '']);
            var rows = Array.isArray(attrs.block_blog_table_rows) && attrs.block_blog_table_rows.length
                ? attrs.block_blog_table_rows.slice()
                : (Array.isArray(defRows) && defRows.length ? defRows.map(function (r) {
                    return { cells: (r && Array.isArray(r.cells)) ? r.cells.slice() : [''] };
                }) : [{ cells: ['', ''] }]);
            var cols = Math.max(header.length, 1);
            rows.forEach(function (r) {
                var c = (r && Array.isArray(r.cells)) ? r.cells.length : 0;
                if (c > cols) cols = c;
            });
            function normalizeHeader(h, n) {
                var out = (h || []).slice(0, n);
                while (out.length < n) out.push('');
                return out;
            }
            function normalizeRows(rs, n) {
                return (rs || []).map(function (r) {
                    var cells = (r && Array.isArray(r.cells)) ? r.cells.slice(0, n) : [];
                    while (cells.length < n) cells.push('');
                    return { cells: cells };
                });
            }
            header = normalizeHeader(header, cols);
            rows = normalizeRows(rows, cols);
            function commit(nextHeader, nextRows) {
                set({
                    block_blog_table_header: nextHeader,
                    block_blog_table_rows: nextRows
                });
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { fontWeight: '600', marginBottom: '8px' } }, 'Статья: таблица'),
                ToggleControl ? el(ToggleControl, {
                    key: 'uh',
                    label: 'Показывать шапку',
                    checked: useHeader,
                    onChange: function (v) { set({ block_blog_table_use_header: !!v }); }
                }) : null,
                useHeader ? el('div', { key: 'head', style: { marginBottom: '10px' } }, [
                    el('p', { style: { fontWeight: '600', margin: '0 0 6px' } }, 'Шапка'),
                    el('div', { style: { display: 'flex', gap: '6px', flexWrap: 'wrap' } }, header.map(function (cell, ci) {
                        return TextControl ? el(TextControl, {
                            key: 'h' + ci,
                            label: 'Кол. ' + (ci + 1),
                            value: cell || '',
                            onChange: function (v) {
                                var next = header.slice();
                                next[ci] = v;
                                commit(next, rows);
                            }
                        }) : null;
                    }))
                ]) : null,
                el('p', { key: 'rl', style: { fontWeight: '600', margin: '8px 0 6px' } }, 'Строки'),
                el('div', { key: 'rows' }, rows.map(function (row, ri) {
                    var cells = (row && row.cells) || [];
                return el('div', {
                        key: 'r' + ri,
                        style: { display: 'flex', gap: '6px', flexWrap: 'wrap', alignItems: 'flex-end', marginBottom: '8px', padding: '8px', background: '#fafafa', border: '1px solid #ddd' }
                    }, [
                        cells.map(function (cell, ci) {
                            return TextControl ? el(TextControl, {
                                key: 'c' + ci,
                                label: 'Яч. ' + (ci + 1),
                                value: cell || '',
                                onChange: function (v) {
                                    var next = rows.slice();
                                    var nextCells = ((next[ri] && next[ri].cells) || []).slice();
                                    nextCells[ci] = v;
                                    next[ri] = { cells: nextCells };
                                    commit(header, next);
                                }
                            }) : null;
                                }),
                                Button ? el(Button, {
                                    isDestructive: true,
                            isSmall: true,
                            onClick: function () {
                                var next = rows.filter(function (_, i) { return i !== ri; });
                                commit(header, next.length ? next : [{ cells: header.map(function () { return ''; }) }]);
                            }
                        }, 'Удалить строку') : null
                    ]);
                })),
                el('div', { key: 'acts', style: { display: 'flex', gap: '8px', flexWrap: 'wrap' } }, [
                        Button ? el(Button, {
                            isSecondary: true,
                            isSmall: true,
                        onClick: function () {
                            commit(header, rows.concat([{ cells: header.map(function () { return ''; }) }]));
                        }
                    }, 'Добавить строку') : null,
                    Button ? el(Button, {
                        isSecondary: true,
                        isSmall: true,
                        onClick: function () {
                            commit(header.concat(['']), rows.map(function (r) {
                                return { cells: ((r && r.cells) || []).concat(['']) };
                            }));
                        }
                    }, 'Добавить колонку') : null,
                    cols > 1 && Button ? el(Button, {
                        isDestructive: true,
                        isSmall: true,
                        onClick: function () {
                            commit(header.slice(0, -1), rows.map(function (r) {
                                return { cells: ((r && r.cells) || []).slice(0, -1) };
                            }));
                        }
                    }, 'Убрать колонку') : null
                ])
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/service-section-tile', {
        title: 'Услуги (плитка)',
        category: 'tolstenko-blocks-new',
        icon: 'hammer',
        edit: function ServiceSectionTileEdit(props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var defTitle = getDefault('service_section_tile.title', 'Услуги');
            var defText = getDefault('service_section_tile.text', '');

            return el('div', blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Услуги (плитка)'),
                el('p', {
                    key: 'h',
                    style: { marginTop: 0, marginBottom: '12px', opacity: 0.7, fontSize: '12px' }
                }, 'Сетка всех услуг, фильтр по категориям и кнопка «Показать ещё» после 6 карточек. Пустые поля — из дефолтов «Услуги (плитка)».'),
                TextareaControl ? el(TextareaControl, {
                    key: 't',
                    label: 'Заголовок (HTML)',
                    value: attrs.block_service_section_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_service_section_title: v }); },
                    rows: 2
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_service_section_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'tx',
                    label: 'Текст',
                    value: attrs.block_service_section_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_service_section_text: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    // Сертификаты — заголовок, текст, галерея изображений (дефолты из Настройки сайта).
    wp.blocks.registerBlockType('tolstenko/certificates', {
        title: 'Сертификаты',
        category: 'tolstenko-blocks-new',
        icon: 'awards',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_certificates_items) ? attrs.block_certificates_items : [];
            var defTitle = getDefault('certificates.title', 'Сертификаты и лицензии');
            var defText = getDefault('certificates.text', '');

            function addMany(mediaItems) {
                var list = Array.isArray(mediaItems) ? mediaItems : [];
                if (!list.length) return;
                var next = items.slice();
                var existing = {};
                next.forEach(function (img) {
                    if (img && img.id) existing[String(img.id)] = true;
                });
                list.forEach(function (m) {
                    if (!m || !m.id || !m.url) return;
                    var key = String(m.id);
                    if (existing[key]) return;
                    existing[key] = true;
                    next.push({
                        id: m.id,
                        url: m.url,
                        previewUrl: pickPreviewUrlFromMedia(m),
                        title: m.alt || m.title || ''
                    });
                });
                set({ block_certificates_items: next });
            }
            function removeItem(index) {
                set({ block_certificates_items: items.filter(function (_, i) { return i !== index; }) });
            }
            function moveItem(from, to) {
                if (from === to || from < 0 || to < 0 || from >= items.length || to >= items.length) return;
                var next = items.slice();
                var item = next.splice(from, 1)[0];
                next.splice(to, 0, item);
                set({ block_certificates_items: next });
            }
            function updateItemTitle(index, value) {
                var next = items.slice();
                if (!next[index]) return;
                next[index] = Object.assign({}, next[index], { title: value });
                set({ block_certificates_items: next });
            }

            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Сертификаты'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Дефолты блоков».'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_certificates_title || '',
                    placeholder: defTitle,
                    onChange: function (v) { set({ block_certificates_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_certificates_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'text',
                    label: 'Текст',
                    value: attrs.block_certificates_text || '',
                    placeholder: defText,
                    onChange: function (v) { set({ block_certificates_text: v }); }
                }) : null,
                MediaUpload && MediaUploadCheck ? el('div', { key: 'actions', style: { display: 'flex', gap: '8px', flexWrap: 'wrap', marginTop: '8px' } }, [
                    el(MediaUploadCheck, { key: 'muc' },
                        el(MediaUpload, {
                            allowedTypes: ['image'],
                            multiple: true,
                            gallery: true,
                            onSelect: addMany,
                            render: function (obj) {
                                return el(Button, { isSecondary: true, onClick: obj.open }, 'Добавить изображения');
                            }
                        })
                    )
                ]) : null,
                items.length ? el('div', { key: 'list', style: { marginTop: '12px', display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(140px, 1fr))', gap: '10px' } },
                    items.map(function (img, index) {
                        return el('div', {
                            key: 'cert-' + index,
                            style: { border: '1px solid #ddd', borderRadius: '6px', padding: '6px', background: '#fff' }
                        }, [
                            el('img', { key: 'ph', src: getGalleryPreviewUrl(img), alt: '', loading: 'lazy', decoding: 'async', style: { width: '100%', height: '120px', objectFit: 'cover', borderRadius: '4px' } }),
                            TextControl ? el(TextControl, {
                                key: 'alt',
                                label: 'Подпись',
                                value: img.title || '',
                                onChange: function (v) { updateItemTitle(index, v); }
                            }) : null,
                            el('div', { key: 'move', style: { display: 'flex', gap: '6px', marginTop: '6px' } }, [
                                Button ? el(Button, {
                                    key: 'left',
                                    isSmall: true,
                                    isSecondary: true,
                                    disabled: index === 0,
                                    style: { minWidth: '34px', padding: '0 8px' },
                                    onClick: function () { moveItem(index, index - 1); }
                                }, '←') : null,
                                Button ? el(Button, {
                                    key: 'right',
                                    isSmall: true,
                                    isSecondary: true,
                                    disabled: index === items.length - 1,
                                    style: { minWidth: '34px', padding: '0 8px' },
                                    onClick: function () { moveItem(index, index + 1); }
                                }, '→') : null
                            ]),
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                style: { marginTop: '6px', width: '100%' },
                                onClick: function () { removeItem(index); }
                            }, 'Удалить') : null
                        ]);
                    })
                ) : el('p', { key: 'empty', style: { marginTop: '8px', fontSize: '12px', color: '#757575' } }, 'Если изображения не заданы, подставятся из дефолтов.')
            ]);
        },
        save: function () { return null; }
    });

    var CheckboxControl = wp.components && wp.components.CheckboxControl;

    function vacancyNormalizeList(items) {
        if (!Array.isArray(items)) return [];
        return items.map(function (val) {
            return (typeof val === 'object' && val) ? String(val.text || '') : String(val || '');
        });
    }

    /** Список пунктов в стиле баннера: карточки с рамкой. */
    function vacancyListEditor(label, items, onChange, defaultItems) {
        var attrList = vacancyNormalizeList(items);
        var defaults = vacancyNormalizeList(defaultItems);
        var list = attrList.slice();
        if (!list.length) {
            list = defaults.length ? defaults.slice() : [''];
        }

        function setList(next) {
            onChange(next);
        }
        function updateItem(index, value) {
            var next = list.slice();
            next[index] = value;
            setList(next);
        }
        function removeItem(index) {
            var next = list.filter(function (_, i) { return i !== index; });
            if (next.length) {
                setList(next);
                return;
            }
            if (defaults.length) {
                setList(defaults.slice());
                return;
            }
            setList(['']);
        }
        function addItem() {
            setList(list.concat(['']));
        }

        return el('div', { key: label, style: { marginTop: '12px' } }, [
            el('p', { key: 'l', style: { margin: '0 0 6px', fontWeight: '600' } }, label),
            el('div', { key: 'wrap' }, list.map(function (text, index) {
                return el('div', {
                    key: 'row-' + index,
                    style: { marginBottom: '10px', border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                }, [
                    TextControl ? el(TextControl, {
                        key: 'inp',
                        label: 'Текст пункта ' + (index + 1),
                        value: text,
                        placeholder: defaults[index] || '',
                        onChange: function (v) { updateItem(index, v); }
                    }) : null,
                    Button ? el(Button, {
                        key: 'rm',
                        isDestructive: true,
                        isSmall: true,
                        onClick: function () { removeItem(index); }
                    }, 'Удалить пункт') : null
                ]);
            })),
            Button ? el(Button, {
                key: 'add',
                isSecondary: true,
                isSmall: true,
                onClick: addItem
            }, 'Добавить пункт') : null
        ]);
    }

    function vacancyMediaControl(label, id, onChange) {
        var mediaId = parseInt(id, 10) || 0;
        return el('div', { key: label, style: { marginTop: '8px' } }, [
            el('p', { key: 'l', style: { marginBottom: '6px', fontWeight: '600' } }, label),
            MediaUpload && MediaUploadCheck ? el(MediaUploadCheck, { key: 'muc' },
                el(MediaUpload, {
                    allowedTypes: ['image'],
                    value: mediaId || undefined,
                    onSelect: function (m) { onChange(m && m.id ? m.id : 0); },
                    render: function (obj) {
                        return el(Button, { isSecondary: true, isSmall: true, onClick: obj.open }, mediaId ? 'Сменить изображение' : 'Выбрать изображение');
                    }
                })
            ) : null,
            mediaId && Button ? el(Button, {
                key: 'clear',
                isDestructive: true,
                isSmall: true,
                style: { marginLeft: '8px' },
                onClick: function () { onChange(0); }
            }, 'Удалить') : null
        ]);
    }

    wp.blocks.registerBlockType('tolstenko/hero-vacancy', {
        title: 'Баннер вакансии',
        category: 'tolstenko-blocks-new',
        icon: 'id-alt',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Баннер вакансии'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Шаблон вакансии». Пустой заголовок = название записи.'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_hero_vacancy_title || '',
                    placeholder: getDefault('hero_vacancy.title', '') || 'Название записи',
                    onChange: function (v) { set({ block_hero_vacancy_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_hero_vacancy_title_tag', 'Тег заголовка', 'h1'),
                TextControl ? el(TextControl, {
                    key: 'cost',
                    label: 'Зарплата / стоимость',
                    value: attrs.block_hero_vacancy_cost || '',
                    placeholder: getDefault('hero_vacancy.cost', ''),
                    onChange: function (v) { set({ block_hero_vacancy_cost: v }); }
                }) : null,
                vacancyListEditor(
                    'Условия (чипы)',
                    attrs.block_hero_vacancy_conditions || [],
                    function (next) { set({ block_hero_vacancy_conditions: next }); },
                    getDefault('hero_vacancy.conditions', [])
                ),
                vacancyListEditor(
                    'Пункты списка',
                    attrs.block_hero_vacancy_items || [],
                    function (next) { set({ block_hero_vacancy_items: next }); },
                    getDefault('hero_vacancy.items', [])
                ),
                TextControl ? el(TextControl, {
                    key: 'btn_text',
                    label: 'Текст кнопки',
                    value: attrs.block_hero_vacancy_btn_text || '',
                    placeholder: getDefault('hero_vacancy.btn_text', ''),
                    onChange: function (v) { set({ block_hero_vacancy_btn_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'btn_url',
                    label: 'Ссылка кнопки',
                    value: attrs.block_hero_vacancy_btn_url || '',
                    placeholder: 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_hero_vacancy_btn_url: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'btn_close',
                    label: 'Текст рядом с кнопкой',
                    value: attrs.block_hero_vacancy_btn_close || '',
                    placeholder: getDefault('hero_vacancy.btn_close_text', ''),
                    onChange: function (v) { set({ block_hero_vacancy_btn_close: v }); }
                }) : null,
                vacancyMediaControl('Изображение баннера', attrs.block_hero_vacancy_image || 0, function (id) {
                    set({ block_hero_vacancy_image: id });
                })
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/vacancy-content', {
        title: 'Контент вакансии',
        category: 'tolstenko-blocks-new',
        icon: 'media-text',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Контент вакансии'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Шаблон вакансии». Текст удобнее править там (визуальный редактор).'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_vacancy_content_title || '',
                    placeholder: getDefault('vacancy_content.title', '') || 'Название записи',
                    onChange: function (v) { set({ block_vacancy_content_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_vacancy_content_title_tag', 'Тег заголовка', 'h2'),
                TextareaControl ? el(TextareaControl, {
                    key: 'html',
                    label: 'Текст',
                    value: attrs.block_vacancy_content_html || '',
                    placeholder: 'Пусто = из шаблона вакансии',
                    onChange: function (v) { set({ block_vacancy_content_html: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'apply',
                    label: 'Текст кнопки заявки',
                    value: attrs.block_vacancy_content_apply_text || '',
                    placeholder: getDefault('vacancy_content.apply_text', 'Отправить заявку'),
                    onChange: function (v) { set({ block_vacancy_content_apply_text: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'apply_url',
                    label: 'Ссылка кнопки заявки',
                    value: attrs.block_vacancy_content_apply_url || '',
                    placeholder: getDefault('vacancy_content.apply_url', '') || 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_vacancy_content_apply_url: v }); }
                }) : null,
                el('p', { key: 'side', style: { margin: '14px 0 6px', fontWeight: '600' } }, 'Сайдбар'),
                renderBlogAuthorSelect(
                    attrs,
                    set,
                    'block_vacancy_content_sidebar_author',
                    'Автор',
                    'По умолчанию (шаблон вакансии)',
                    getDefault('vacancy_content.sidebar_author', '')
                ),
                TextControl ? el(TextControl, {
                    key: 'sbtn',
                    label: 'Текст кнопки',
                    value: attrs.block_vacancy_content_sidebar_btn || '',
                    placeholder: getDefault('vacancy_content.sidebar_btn', 'Бесплатный аудит'),
                    onChange: function (v) { set({ block_vacancy_content_sidebar_btn: v }); }
                }) : null,
                TextControl ? el(TextControl, {
                    key: 'sbtn_url',
                    label: 'Ссылка кнопки',
                    value: attrs.block_vacancy_content_sidebar_btn_url || '',
                    placeholder: getDefault('vacancy_content.sidebar_btn_url', '') || 'Пусто = модалка заявки',
                    onChange: function (v) { set({ block_vacancy_content_sidebar_btn_url: v }); }
                }) : null
            ]);
        },
        save: function () { return null; }
    });

    wp.blocks.registerBlockType('tolstenko/same-vacancy', {
        title: 'Похожие вакансии',
        category: 'tolstenko-blocks-new',
        icon: 'groups',
        edit: function (props) {
            var attrs = props.attributes || {};
            var set = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var items = Array.isArray(attrs.block_same_vacancy_items) ? attrs.block_same_vacancy_items.map(function (id) { return parseInt(id, 10); }).filter(function (n) { return n > 0; }) : [];
            var catalog = Array.isArray(blockDefaults.vacancyPosts) ? blockDefaults.vacancyPosts : [];
            function toggleId(id, checked) {
                var next = items.slice();
                var idx = next.indexOf(id);
                if (checked && idx < 0) next.push(id);
                if (!checked && idx >= 0) next.splice(idx, 1);
                set({ block_same_vacancy_items: next });
            }
            return wrapBlock(blockProps, [
                el('p', { key: 'l', style: { marginBottom: '8px', fontWeight: '600' } }, 'Похожие вакансии'),
                el('p', { key: 'hint', style: { marginTop: 0, marginBottom: '8px', fontSize: '12px', color: '#757575' } }, 'Пустые поля подставятся из «Настройки сайта → Шаблон вакансии». Пустой список = последние вакансии.'),
                TextControl ? el(TextControl, {
                    key: 'title',
                    label: 'Заголовок',
                    value: attrs.block_same_vacancy_title || '',
                    placeholder: getDefault('same_vacancy.title', 'Другие вакансии'),
                    onChange: function (v) { set({ block_same_vacancy_title: v }); }
                }) : null,
                renderHeadingTagSelect(attrs, set, 'block_same_vacancy_title_tag', 'Тег заголовка', 'h2'),
                el('div', { key: 'list-wrap', style: { marginTop: '12px' } }, [
                    el('p', { key: 'list-l', style: { margin: '0 0 6px', fontWeight: '600' } }, 'Вакансии в слайдере'),
                    catalog.length ? el('div', {
                        key: 'catalog',
                        style: { border: '1px solid #ddd', borderRadius: '4px', padding: '8px', background: '#fafafa' }
                    }, catalog.map(function (post) {
                        var id = parseInt(post.id, 10);
                        return CheckboxControl ? el(CheckboxControl, {
                            key: 'v-' + id,
                            label: post.title || ('#' + id),
                            checked: items.indexOf(id) >= 0,
                            onChange: function (checked) { toggleId(id, checked); }
                        }) : null;
                    })) : el('p', { key: 'empty', style: { fontSize: '12px', color: '#757575', margin: 0 } }, 'Пока нет вакансий для выбора.')
                ])
            ]);
        },
        save: function () { return null; }
    });

    // Остальные блоки — только плейсхолдер
    var simpleBlocks = [
    ];
    simpleBlocks.forEach(function (b) {
        var blockName = 'tolstenko/' + b.name;
        if (wp.blocks.getBlockType(blockName)) return;
        wp.blocks.registerBlockType(blockName, {
            title: b.title,
            category: 'tolstenko-blocks',
            icon: 'layout',
            edit: function () {
                var blockProps = useBlockProps ? useBlockProps() : {};
                return wrapBlock(blockProps, b.title);
            },
            save: function () { return null; }
        });
    });

    // Правая панель записи «Акция»: описание и цены для плитки.
    var registerPlugin = wp.plugins && wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = (wp.editor && wp.editor.PluginDocumentSettingPanel)
        || (wp.editPost && wp.editPost.PluginDocumentSettingPanel);
    var useSelect = wp.data && wp.data.useSelect;
    var useDispatch = wp.data && wp.data.useDispatch;

    if (registerPlugin && PluginDocumentSettingPanel && useSelect && useDispatch) {
        registerPlugin('tolstenko-action-fields', {
            render: function TolstenkoActionFieldsPanel() {
                var postType = useSelect(function (select) {
                    var editor = select('core/editor');
                    return editor && editor.getCurrentPostType ? editor.getCurrentPostType() : null;
                }, []);

                var meta = useSelect(function (select) {
                    var editor = select('core/editor');
                    return (editor && editor.getEditedPostAttribute)
                        ? (editor.getEditedPostAttribute('meta') || {})
                        : {};
                }, []);

                var editPost = useDispatch('core/editor').editPost;

                if (postType !== 'actions') {
                    return null;
                }

                function updateMeta(key, value) {
                    var next = Object.assign({}, meta);
                    next[key] = value == null ? '' : String(value);
                    editPost({ meta: next });
                }

                return el(
                    PluginDocumentSettingPanel,
                    {
                        name: 'tolstenko-action-tile-fields',
                        title: 'Данные акции (плитка)',
                        icon: 'megaphone'
                    },
                    TextareaControl ? el(TextareaControl, {
                        key: 'description',
                        label: 'Краткое описание',
                        value: meta.action_description || '',
                        onChange: function (v) { updateMeta('action_description', v); },
                        rows: 3
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'same_cost',
                        label: 'Цена от (₽)',
                        value: meta.action_same_cost || '',
                        onChange: function (v) { updateMeta('action_same_cost', v); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'cost',
                        label: 'Старая цена (зачёркнутая, ₽)',
                        value: meta.action_cost || '',
                        onChange: function (v) { updateMeta('action_cost', v); }
                    }) : null
                );
            }
        });

        registerPlugin('tolstenko-case-fields', {
            render: function TolstenkoCaseFieldsPanel() {
                var postType = useSelect(function (select) {
                    var editor = select('core/editor');
                    return editor && editor.getCurrentPostType ? editor.getCurrentPostType() : null;
                }, []);

                var meta = useSelect(function (select) {
                    var editor = select('core/editor');
                    return (editor && editor.getEditedPostAttribute)
                        ? (editor.getEditedPostAttribute('meta') || {})
                        : {};
                }, []);

                var editPost = useDispatch('core/editor').editPost;

                if (postType !== 'case') {
                    return null;
                }

                var items = Array.isArray(meta.case_items) ? meta.case_items.slice() : [];

                var services = (useSelect ? useSelect(function (select) {
                    var records = select('core').getEntityRecords('postType', 'service', {
                        per_page: 100,
                        status: 'publish',
                        orderby: 'title',
                        order: 'asc',
                        _fields: 'id,title'
                    });
                    return Array.isArray(records) ? records : [];
                }, []) : []);

                var serviceOptions = [{ label: '— Не выбрано —', value: '' }];
                services.forEach(function (post) {
                    if (!post || !post.id) return;
                    var t = (post.title && post.title.rendered) ? String(post.title.rendered) : ('#' + post.id);
                    t = t.replace(/<[^>]+>/g, '').trim() || ('#' + post.id);
                    serviceOptions.push({ label: t, value: String(post.id) });
                });

                var selectedService = meta.case_service ? String(meta.case_service) : '';

                function updateMeta(key, value) {
                    var next = Object.assign({}, meta);
                    next[key] = value;
                    editPost({ meta: next });
                }

                function setItems(next) {
                    updateMeta('case_items', next);
                }

                return el(
                    PluginDocumentSettingPanel,
                    {
                        name: 'tolstenko-case-card-fields',
                        title: 'Данные кейса (карточка)',
                        icon: 'awards'
                    },
                    TextControl ? el(TextControl, {
                        key: 'title',
                        label: 'Заголовок на карточке',
                        help: 'Пусто = заголовок записи',
                        value: meta.case_title || '',
                        onChange: function (v) { updateMeta('case_title', v == null ? '' : String(v)); }
                    }) : null,
                    TextareaControl ? el(TextareaControl, {
                        key: 'text',
                        label: 'Текст на карточке',
                        value: meta.case_text || '',
                        onChange: function (v) { updateMeta('case_text', v == null ? '' : String(v)); },
                        rows: 3
                    }) : null,
                    ComboboxControl ? el(ComboboxControl, {
                        key: 'service',
                        label: 'Услуга (ссылка «Подробнее об услуге»)',
                        value: selectedService,
                        options: serviceOptions,
                        onChange: function (v) {
                            var n = parseInt(v, 10);
                            updateMeta('case_service', isNaN(n) || n <= 0 ? 0 : n);
                        }
                    }) : (SelectControl ? el(SelectControl, {
                        key: 'service',
                        label: 'Услуга (ссылка «Подробнее об услуге»)',
                        value: selectedService,
                        options: serviceOptions,
                        onChange: function (v) {
                            var n = parseInt(v, 10);
                            updateMeta('case_service', isNaN(n) || n <= 0 ? 0 : n);
                        }
                    }) : null),
                    el('p', { key: 'items-label', style: { fontWeight: 600, marginBottom: '8px' } }, 'Показатели'),
                    items.map(function (item, i) {
                        var row = item && typeof item === 'object' ? item : { value: '', text: '' };
                        return el('div', {
                            key: 'item-' + i,
                            style: {
                                display: 'flex',
                                flexDirection: 'column',
                                gap: '4px',
                                marginBottom: '12px',
                                padding: '10px',
                                border: '1px solid #dcdcde',
                                borderRadius: '4px',
                                background: '#fff'
                            }
                        }, [
                            TextControl ? el(TextControl, {
                                key: 'v',
                                label: 'Значение',
                                value: row.value || '',
                                onChange: function (v) {
                                    var next = items.slice();
                                    next[i] = Object.assign({}, row, { value: v || '' });
                                    setItems(next);
                                }
                            }) : null,
                            TextControl ? el(TextControl, {
                                key: 't',
                                label: 'Подпись',
                                value: row.text || '',
                                onChange: function (v) {
                                    var next = items.slice();
                                    next[i] = Object.assign({}, row, { text: v || '' });
                                    setItems(next);
                                }
                            }) : null,
                            Button ? el(Button, {
                                key: 'rm',
                                isDestructive: true,
                                isSmall: true,
                                onClick: function () {
                                    var next = items.slice();
                                    next.splice(i, 1);
                                    setItems(next);
                                }
                            }, 'Удалить') : null
                        ]);
                    }),
                    Button ? el(Button, {
                        key: 'add',
                        isSecondary: true,
                        isSmall: true,
                        onClick: function () {
                            setItems(items.concat([{ value: '', text: '' }]));
                        }
                    }, 'Добавить показатель') : null,
                    el('p', {
                        key: 'thumb-hint',
                        style: { marginTop: '12px', opacity: 0.7, fontSize: '12px' }
                    }, 'Изображение карточки — миниатюра записи (панель «Изображение записи»).')
                );
            }
        });

        registerPlugin('tolstenko-service-fields', {
            render: function TolstenkoServiceFieldsPanel() {
                var postType = useSelect(function (select) {
                    var editor = select('core/editor');
                    return editor && editor.getCurrentPostType ? editor.getCurrentPostType() : null;
                }, []);

                var meta = useSelect(function (select) {
                    var editor = select('core/editor');
                    return (editor && editor.getEditedPostAttribute)
                        ? (editor.getEditedPostAttribute('meta') || {})
                        : {};
                }, []);

                var editPost = useDispatch('core/editor').editPost;

                if (postType !== 'service') {
                    return null;
                }

                function updateMeta(key, value) {
                    var next = Object.assign({}, meta);
                    next[key] = value;
                    editPost({ meta: next });
                }

                return el(
                    PluginDocumentSettingPanel,
                    {
                        name: 'tolstenko-service-card-fields',
                        title: 'Данные услуги (карточка)',
                        icon: 'hammer'
                    },
                    TextareaControl ? el(TextareaControl, {
                        key: 'description',
                        label: 'Описание на карточке',
                        value: meta.service_description || '',
                        onChange: function (v) { updateMeta('service_description', v == null ? '' : String(v)); },
                        rows: 3
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'price_from',
                        label: 'Цена от (₽)',
                        value: meta.service_price_from || '',
                        onChange: function (v) { updateMeta('service_price_from', v == null ? '' : String(v)); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'price_old',
                        label: 'Старая цена (зачёркнутая, ₽)',
                        value: meta.service_price_old || '',
                        onChange: function (v) { updateMeta('service_price_old', v == null ? '' : String(v)); }
                    }) : null,
                    ToggleControl ? el(ToggleControl, {
                        key: 'is_hit',
                        label: 'Показывать бейдж «хит»',
                        checked: !!meta.service_is_hit,
                        onChange: function (v) { updateMeta('service_is_hit', !!v); }
                    }) : null,
                    TextControl ? el(TextControl, {
                        key: 'discount',
                        label: 'Скидка на бейдже (%)',
                        value: meta.service_discount || '',
                        onChange: function (v) { updateMeta('service_discount', v == null ? '' : String(v)); }
                    }) : null,
                    el('p', {
                        key: 'thumb-hint',
                        style: { marginTop: '12px', opacity: 0.7, fontSize: '12px' }
                    }, 'Изображение карточки — миниатюра записи. Тег — из категории услуги.')
                );
            }
        });
    }
})();