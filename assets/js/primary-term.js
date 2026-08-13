/**
 * Главная категория в редакторе Gutenberg.
 * Панель в сайдбаре + кнопки «Сделать главной» у отмеченных рубрик.
 */
( function () {
	'use strict';

	var cfg = window.tolstenkoPrimaryTerm;
	if ( ! cfg || ! cfg.taxonomies || ! cfg.taxonomies.length ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useSelect = wp.data.useSelect;
	var useDispatch = wp.data.useDispatch;
	var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
	var SelectControl = wp.components.SelectControl;
	var registerPlugin = wp.plugins.registerPlugin;
	var i18n = cfg.i18n || {};

	function getMetaValue( meta, key ) {
		if ( ! meta || typeof meta !== 'object' ) {
			return 0;
		}
		var v = meta[ key ];
		return v ? parseInt( v, 10 ) || 0 : 0;
	}

	function PrimaryTermPanel() {
		var taxonomies = cfg.taxonomies;
		var meta = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			return editor && editor.getEditedPostAttribute
				? editor.getEditedPostAttribute( 'meta' ) || {}
				: {};
		}, [] );
		var editPost = useDispatch( 'core/editor' ).editPost;

		var termIdsByTax = useSelect( function ( select ) {
			var editor = select( 'core/editor' );
			var out = {};
			taxonomies.forEach( function ( tax ) {
				var ids = editor && editor.getEditedPostAttribute
					? editor.getEditedPostAttribute( tax.name )
					: null;
				out[ tax.name ] = Array.isArray( ids ) ? ids.map( Number ) : [];
			} );
			return out;
		}, [ taxonomies ] );

		var termRecords = useSelect( function ( select ) {
			var core = select( 'core' );
			var out = {};
			taxonomies.forEach( function ( tax ) {
				var ids = termIdsByTax[ tax.name ] || [];
				out[ tax.name ] = ids.map( function ( id ) {
					return core.getEntityRecord( 'taxonomy', tax.name, id );
				} ).filter( Boolean );
			} );
			return out;
		}, [ taxonomies, termIdsByTax ] );

		function setPrimary( metaKey, termId ) {
			var next = Object.assign( {}, meta );
			next[ metaKey ] = termId ? parseInt( termId, 10 ) : 0;
			editPost( { meta: next } );
		}

		return el(
			PluginDocumentSettingPanel,
			{
				name: 'tolstenko-primary-term',
				title: i18n.panelTitle || 'Главная категория',
				className: 'tolstenko-primary-term-panel',
			},
			el( 'p', { className: 'description', style: { marginTop: 0 } }, i18n.help || '' ),
			taxonomies.map( function ( tax ) {
				var records = termRecords[ tax.name ] || [];
				var primary = getMetaValue( meta, tax.metaKey );
				var options = [ { label: '—', value: '0' } ].concat(
					records.map( function ( t ) {
						return {
							label: t.name || ( '#' + t.id ),
							value: String( t.id ),
						};
					} )
				);

				if ( ! records.length ) {
					return el(
						'p',
						{ key: tax.name, className: 'description' },
						( tax.panelLabel || tax.label ) + ': ' + ( i18n.none || '' )
					);
				}

				// Если primary не в списке — подсказать через value 0, sync на save сделает PHP.
				var value = String( primary );
				var ids = records.map( function ( t ) { return t.id; } );
				if ( primary && ids.indexOf( primary ) === -1 ) {
					value = '0';
				}

				return el( SelectControl, {
					key: tax.name,
					label: tax.panelLabel || tax.label,
					value: value,
					options: options,
					onChange: function ( v ) {
						setPrimary( tax.metaKey, v );
					},
				} );
			} )
		);
	}

	registerPlugin( 'tolstenko-primary-term', {
		render: PrimaryTermPanel,
		icon: 'star-filled',
	} );

	/**
	 * Кнопки у чекбоксов в панели категорий.
	 */
	function enhanceChecklists() {
		var editor = wp.data.select( 'core/editor' );
		if ( ! editor || ! editor.getEditedPostAttribute ) {
			return;
		}
		var meta = editor.getEditedPostAttribute( 'meta' ) || {};
		var editPost = wp.data.dispatch( 'core/editor' ).editPost;

		cfg.taxonomies.forEach( function ( tax ) {
			var selected = editor.getEditedPostAttribute( tax.name );
			selected = Array.isArray( selected ) ? selected.map( Number ) : [];
			var primary = getMetaValue( meta, tax.metaKey );

			var inputs = document.querySelectorAll(
				'.editor-post-taxonomies__hierarchical-terms-list input[type="checkbox"]'
			);
			if ( ! inputs.length ) {
				// Fallback: любые чекбоксы терминов в сайдбаре документа.
				inputs = document.querySelectorAll(
					'.components-panel__body input[type="checkbox"][id*="-"]'
				);
			}

			inputs.forEach( function ( input ) {
				var termId = parseInt( input.value, 10 );
				if ( ! termId ) {
					return;
				}
				// Только термины текущей таксономии (по выбранным id).
				if ( selected.indexOf( termId ) === -1 && ! input.checked ) {
					cleanupControls( input );
					return;
				}
				if ( ! input.checked ) {
					cleanupControls( input );
					return;
				}

				// Неоднозначность: один и тот же termId может быть в разных tax — редкий кейс.
				// Помечаем только если term входит в selected этой tax.
				if ( selected.indexOf( termId ) === -1 ) {
					return;
				}

				var row = input.closest( '.components-checkbox-control' ) ||
					input.closest( 'label' ) ||
					input.parentElement;
				if ( ! row ) {
					return;
				}

				cleanupControls( input );

				if ( primary === termId ) {
					var badge = document.createElement( 'span' );
					badge.className = 'tolstenko-primary-term-badge';
					badge.textContent = i18n.isPrimary || 'Главная';
					badge.setAttribute( 'data-tolstenko-primary', String( termId ) );
					appendNearLabel( row, badge );
				} else {
					var btn = document.createElement( 'button' );
					btn.type = 'button';
					btn.className = 'tolstenko-primary-term-btn';
					btn.textContent = i18n.makePrimary || 'Сделать главной';
					btn.setAttribute( 'data-tolstenko-primary-btn', String( termId ) );
					btn.addEventListener( 'click', function ( e ) {
						e.preventDefault();
						e.stopPropagation();
						var currentMeta = editor.getEditedPostAttribute( 'meta' ) || {};
						var next = Object.assign( {}, currentMeta );
						next[ tax.metaKey ] = termId;
						editPost( { meta: next } );
					} );
					appendNearLabel( row, btn );
				}
			} );
		} );
	}

	function appendNearLabel( row, node ) {
		var label = row.querySelector( 'label' ) || row;
		label.appendChild( node );
	}

	function cleanupControls( input ) {
		var row = input.closest( '.components-checkbox-control' ) ||
			input.closest( 'label' ) ||
			input.parentElement;
		if ( ! row ) {
			return;
		}
		row.querySelectorAll( '.tolstenko-primary-term-badge, .tolstenko-primary-term-btn' ).forEach( function ( n ) {
			n.remove();
		} );
	}

	var scheduled = false;
	function scheduleEnhance() {
		if ( scheduled ) {
			return;
		}
		scheduled = true;
		window.requestAnimationFrame( function () {
			scheduled = false;
			enhanceChecklists();
		} );
	}

	wp.data.subscribe( scheduleEnhance );
	document.addEventListener( 'click', scheduleEnhance );
	setTimeout( scheduleEnhance, 500 );
	setTimeout( scheduleEnhance, 1500 );
}() );
