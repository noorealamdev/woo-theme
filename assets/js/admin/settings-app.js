/**
 * Noorifa Theme Settings — a real React app built entirely on WordPress
 * core's own bundled packages (`wp-element`, `wp-components`,
 * `wp-api-fetch`, `wp-i18n`) — no npm, no build step. Reads/saves through
 * the real `noorifa/v1/settings` REST route (see Rest_Controller.php).
 *
 * Initial data (`noorifaSettingsData`) is localized by Admin_Page.php.
 */
( function () {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var apiFetch = wp.apiFetch;
	var c = wp.components;

	apiFetch.use( apiFetch.createNonceMiddleware( noorifaSettingsData.nonce ) );

	var DATA = noorifaSettingsData;

	/**
	 * Full sidebar nav, matching the approved reference layout. Sections
	 * without `real: true` have no backing settings yet and render the
	 * shared "coming soon" placeholder instead of a fabricated form.
	 */
	var SECTIONS = [
		{ id: 'dashboard', label: __( 'Dashboard', 'noorifa' ), icon: 'dashboard', real: true },
		{ id: 'layout', label: __( 'Layout', 'noorifa' ), icon: 'layout', real: true },
		{ id: 'typography', label: __( 'Typography', 'noorifa' ), icon: 'editor-textcolor', real: true },
		{ id: 'branding', label: __( 'Colors', 'noorifa' ), icon: 'color-picker', real: true },
		{ id: 'header', label: __( 'Header', 'noorifa' ), icon: 'align-center', real: true },
		{ id: 'topbar', label: __( 'Topbar', 'noorifa' ), icon: 'megaphone', real: true },
		{ id: 'footer', label: __( 'Footer', 'noorifa' ), icon: 'align-wide', real: true },
		{ id: 'newsletter', label: __( 'Newsletter', 'noorifa' ), icon: 'email', real: true },
		{ id: 'shop', label: __( 'Shop', 'noorifa' ), icon: 'store', real: true },
		{ id: 'product', label: __( 'Product Page', 'noorifa' ), icon: 'products' },
		{ id: 'cart-checkout', label: __( 'Cart & Checkout', 'noorifa' ), icon: 'cart' },
		{ id: 'blog', label: __( 'Blog', 'noorifa' ), icon: 'welcome-write-blog', real: true },
		{ id: 'page-header', label: __( 'Page Header', 'noorifa' ), icon: 'excerpt-view', real: true },
		{ id: 'buttons', label: __( 'Buttons', 'noorifa' ), icon: 'button', real: true },
		{ id: 'performance', label: __( 'Performance', 'noorifa' ), icon: 'performance', real: true },
		{ id: 'seo', label: __( 'SEO', 'noorifa' ), icon: 'tag', real: true },
		{ id: 'integrations', label: __( 'Integrations', 'noorifa' ), icon: 'admin-plugins', real: true },
		{ id: 'import-export', label: __( 'Import / Export', 'noorifa' ), icon: 'migrate', real: true },
		{ id: 'license', label: __( 'License', 'noorifa' ), icon: 'awards' },
		{ id: 'updates', label: __( 'Updates', 'noorifa' ), icon: 'update' },
		{ id: 'developer', label: __( 'Developer', 'noorifa' ), icon: 'code-standards' },
	];

	/**
	 * Small helper: sets a value at a dot-path inside a plain object,
	 * returning a new object (no external deps, just enough for this
	 * settings shape).
	 */
	function setPath( obj, path, value ) {
		var keys = path.split( '.' );
		var next = Array.isArray( obj ) ? obj.slice() : Object.assign( {}, obj );
		var cursor = next;
		for ( var i = 0; i < keys.length - 1; i++ ) {
			var key = keys[ i ];
			cursor[ key ] = Array.isArray( cursor[ key ] ) ? cursor[ key ].slice() : Object.assign( {}, cursor[ key ] );
			cursor = cursor[ key ];
		}
		cursor[ keys[ keys.length - 1 ] ] = value;
		return next;
	}

	function getPath( obj, path ) {
		return path.split( '.' ).reduce( function ( acc, key ) {
			return acc && typeof acc === 'object' ? acc[ key ] : undefined;
		}, obj );
	}

	function Field( { label, help, children } ) {
		return el(
			'div',
			{ className: 'noorifa-field' },
			el( 'span', { className: 'noorifa-field__label' }, label ),
			children,
			help ? el( 'p', { className: 'noorifa-field__help' }, help ) : null
		);
	}

	function TextField( { settings, path, label, type, placeholder, help, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.TextControl, {
				type: type || 'text',
				placeholder: placeholder,
				value: getPath( settings, path ) || '',
				onChange: function ( value ) {
					onChange( setPath( settings, path, value ) );
				},
			} )
		);
	}

	function TextareaField( { settings, path, label, help, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.TextareaControl, {
				value: getPath( settings, path ) || '',
				onChange: function ( value ) {
					onChange( setPath( settings, path, value ) );
				},
			} )
		);
	}

	/**
	 * A single image picker backed by the real WP media library
	 * (`wp.media`, enqueued via `wp_enqueue_media()` in Admin_Page.php) —
	 * stores the selected attachment's URL as a plain string, same as any
	 * other text-type field.
	 */
	function ImageField( { settings, path, label, help, onChange } ) {
		var value = getPath( settings, path ) || '';

		function openLibrary() {
			var frame = wp.media( {
				title: label,
				button: { text: __( 'Use this image', 'noorifa' ) },
				multiple: false,
			} );
			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				onChange( setPath( settings, path, attachment.url || '' ) );
			} );
			frame.open();
		}

		return el(
			Field,
			{ label: label, help: help },
			el(
				'div',
				{ className: 'noorifa-image-field' },
				value ? el( 'img', { src: value, alt: '', className: 'noorifa-image-field__preview' } ) : null,
				el(
					'div',
					{ className: 'noorifa-image-field__actions' },
					el( c.Button, { variant: 'secondary', onClick: openLibrary }, value ? __( 'Change Image', 'noorifa' ) : __( 'Select Image', 'noorifa' ) ),
					value
						? el(
								c.Button,
								{
									variant: 'tertiary',
									isDestructive: true,
									onClick: function () {
										onChange( setPath( settings, path, '' ) );
									},
								},
								__( 'Remove', 'noorifa' )
						  )
						: null
				)
			)
		);
	}

	function ToggleField( { settings, path, label, onChange } ) {
		return el(
			'div',
			{ className: 'noorifa-field noorifa-field--toggle' },
			el( c.ToggleControl, {
				label: label,
				checked: !! getPath( settings, path ),
				onChange: function ( value ) {
					onChange( setPath( settings, path, value ) );
				},
			} )
		);
	}

	function RangeField( { settings, path, label, help, min, max, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.RangeControl, {
				value: getPath( settings, path ),
				min: min,
				max: max,
				withInputField: true,
				onChange: function ( value ) {
					onChange( setPath( settings, path, value ) );
				},
			} )
		);
	}

	function SelectField( { settings, path, label, help, options, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.SelectControl, {
				value: getPath( settings, path ) || '',
				options: options,
				onChange: function ( value ) {
					onChange( setPath( settings, path, value ) );
				},
			} )
		);
	}

	/**
	 * A searchable, type-to-filter select — used for long option lists
	 * (e.g. the ~1,900 real Google Fonts) where a plain `<select>` would
	 * be unusable.
	 */
	function ComboboxField( { settings, path, label, help, options, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.ComboboxControl, {
				value: getPath( settings, path ) || '',
				options: options,
				hideLabelFromVision: true,
				onChange: function ( value ) {
					onChange( setPath( settings, path, value || '' ) );
				},
			} )
		);
	}

	/**
	 * Live sample text rendered in whichever font is currently selected —
	 * loads only that one font (via a single, self-updating <link>), not
	 * every option in the list.
	 */
	function FontPreview( { fontFamily, previewId, fontSize } ) {
		var linkElId = 'noorifa-font-preview-link-' + previewId;

		useEffect(
			function () {
				var existing = document.getElementById( linkElId );
				if ( ! fontFamily ) {
					if ( existing ) {
						existing.remove();
					}
					return;
				}
				var href = 'https://fonts.googleapis.com/css2?family=' + encodeURIComponent( fontFamily ).replace( /%20/g, '+' ) + ':wght@400;600&display=swap';
				if ( ! existing ) {
					existing = document.createElement( 'link' );
					existing.id = linkElId;
					existing.rel = 'stylesheet';
					document.head.appendChild( existing );
				}
				existing.href = href;
			},
			[ fontFamily ]
		);

		var style = { fontFamily: fontFamily ? "'" + fontFamily + "'" : 'inherit' };
		if ( fontSize ) {
			style.fontSize = fontSize + 'px';
		}

		return el(
			'p',
			{
				className: 'noorifa-font-preview',
				style: style,
			},
			fontFamily
				? __( 'The quick brown fox jumps over the lazy dog — Aa Bb Cc 0123', 'noorifa' )
				: __( "Using the theme's default font.", 'noorifa' )
		);
	}

	function ChoiceField( { settings, path, label, help, choices, onChange } ) {
		var value = getPath( settings, path );
		return el(
			Field,
			{ label: label, help: help },
			el(
				c.ButtonGroup,
				null,
				Object.keys( choices ).map( function ( choiceValue ) {
					return el(
						c.Button,
						{
							key: choiceValue,
							variant: value === choiceValue ? 'primary' : 'secondary',
							isPressed: value === choiceValue,
							onClick: function () {
								onChange( setPath( settings, path, choiceValue ) );
							},
						},
						choices[ choiceValue ]
					);
				} )
			)
		);
	}

	function ColorField( { settings, path, label, onChange } ) {
		var value = getPath( settings, path ) || '#000000';
		return el(
			Field,
			{ label: label },
			el(
				c.Dropdown,
				{
					className: 'noorifa-color-dropdown',
					contentClassName: 'noorifa-color-dropdown__content',
					renderToggle: function ( toggle ) {
						return el(
							'button',
							{
								type: 'button',
								className: 'noorifa-color-swatch-trigger',
								onClick: toggle.onToggle,
								'aria-expanded': toggle.isOpen,
							},
							el( 'span', { className: 'noorifa-color-swatch', style: { backgroundColor: value } } ),
							el( 'span', { className: 'noorifa-color-swatch__hex' }, value )
						);
					},
					renderContent: function () {
						return el( c.ColorPicker, {
							color: value,
							onChange: function ( next ) {
								onChange( setPath( settings, path, next ) );
							},
							enableAlpha: false,
						} );
					},
				}
			)
		);
	}

	/**
	 * One draggable module chip — used both inside a zone column and in
	 * the "Available Modules" pool. Dropping a chip onto another chip
	 * inserts it immediately before that chip (precise reordering);
	 * dropping on empty column space (handled by the column itself)
	 * appends to the end.
	 */
	function HeaderModuleChip( { id, label, isDragging, onDragStartItem, onDropBefore, onDragEndItem, onRemove } ) {
		return el(
			'div',
			{
				className: 'noorifa-zone-chip' + ( isDragging ? ' is-dragging' : '' ),
				draggable: true,
				onDragStart: function () {
					onDragStartItem( id );
				},
				onDragOver: function ( event ) {
					event.preventDefault();
					event.stopPropagation();
				},
				onDrop: function ( event ) {
					event.preventDefault();
					event.stopPropagation();
					onDropBefore();
				},
				onDragEnd: onDragEndItem,
			},
			el( 'span', { className: 'noorifa-zone-chip__handle', 'aria-hidden': 'true' }, '⠿' ),
			el( 'span', { className: 'noorifa-zone-chip__label' }, label ),
			onRemove
				? el(
						'button',
						{
							type: 'button',
							className: 'noorifa-zone-chip__remove',
							'aria-label': __( 'Hide', 'noorifa' ) + ' ' + label,
							title: __( 'Hide from header', 'noorifa' ),
							draggable: true,
							onMouseDown: function ( event ) {
								event.stopPropagation();
							},
							onDragStart: function ( event ) {
								event.preventDefault();
								event.stopPropagation();
							},
							onClick: function ( event ) {
								event.preventDefault();
								event.stopPropagation();
								onRemove( id );
							},
						},
						'×'
				  )
				: null
		);
	}

	/**
	 * One drop target — either a zone column ('left'/'center'/'right') or
	 * the 'available' (hidden/unplaced modules) pool. Dropping directly on
	 * the column's own empty space appends to the end of that zone.
	 */
	function HeaderZoneColumn( { zoneId, title, items, choices, draggingId, onDragStartItem, onDropAt, onDragEndItem, emptyLabel, onRemoveItem } ) {
		return el(
			'div',
			{ className: 'noorifa-zone-column' },
			el( 'h4', { className: 'noorifa-zone-column__title' }, title ),
			el(
				'div',
				{
					className: 'noorifa-zone-column__drop' + ( items.length ? '' : ' is-empty' ),
					onDragOver: function ( event ) {
						event.preventDefault();
					},
					onDrop: function ( event ) {
						event.preventDefault();
						onDropAt( zoneId, items.length );
					},
				},
				items.length
					? items.map( function ( id, index ) {
							return el( HeaderModuleChip, {
								key: id,
								id: id,
								label: choices[ id ] || id,
								isDragging: draggingId === id,
								onDragStartItem: onDragStartItem,
								onDropBefore: function () {
									onDropAt( zoneId, index );
								},
								onDragEndItem: onDragEndItem,
								onRemove: onRemoveItem,
							} );
					  } )
					: el( 'p', { className: 'noorifa-zone-column__placeholder' }, emptyLabel )
			)
		);
	}

	/**
	 * The real 3-zone (left/center/right) header builder — any of the 5
	 * header modules can be dragged freely between zones (including mixed
	 * zones, e.g. an icon next to the logo) or back into the "Available"
	 * pool to hide it. A module's zone is its only visibility control now
	 * — there's no separate show/hide toggle.
	 */
	function HeaderZoneBuilder( { settings, onChange } ) {
		var draggingState = useState( null );
		var draggingId = draggingState[ 0 ];
		var setDraggingId = draggingState[ 1 ];

		var zones = getPath( settings, 'header.zones' ) || { left: [], center: [], right: [] };
		var choices = DATA.choices.header.toggleable;
		var allModules = Object.keys( choices );
		var placed = zones.left.concat( zones.center, zones.right );
		var available = allModules.filter( function ( id ) {
			return placed.indexOf( id ) === -1;
		} );

		function moveModule( id, toZone, toIndex ) {
			var next = { left: zones.left.slice(), center: zones.center.slice(), right: zones.right.slice() };
			var fromZone = zones.left.indexOf( id ) !== -1 ? 'left' : zones.center.indexOf( id ) !== -1 ? 'center' : zones.right.indexOf( id ) !== -1 ? 'right' : null;

			if ( fromZone ) {
				var fromIndex = next[ fromZone ].indexOf( id );
				next[ fromZone ].splice( fromIndex, 1 );
				if ( fromZone === toZone && fromIndex < toIndex ) {
					toIndex -= 1;
				}
			}

			if ( 'available' !== toZone ) {
				var insertAt = Math.min( toIndex, next[ toZone ].length );
				next[ toZone ].splice( insertAt, 0, id );
			}

			onChange( setPath( settings, 'header.zones', next ) );
		}

		function removeModule( id ) {
			moveModule( id, 'available', 0 );
		}

		function handleDragStart( id ) {
			setDraggingId( id );
		}

		function handleDragEnd() {
			setDraggingId( null );
		}

		function handleDrop( toZone, toIndex ) {
			if ( null === draggingId ) {
				return;
			}
			moveModule( draggingId, toZone, toIndex );
			setDraggingId( null );
		}

		return el(
			'div',
			{ className: 'noorifa-zone-builder' },
			el(
				'div',
				{
					className: 'noorifa-zone-pool' + ( available.length ? '' : ' is-empty' ),
					onDragOver: function ( event ) {
						event.preventDefault();
					},
					onDrop: function ( event ) {
						event.preventDefault();
						handleDrop( 'available', 0 );
					},
				},
				el( 'h4', { className: 'noorifa-zone-column__title' }, __( 'Available Modules (drag into a zone below)', 'noorifa' ) ),
				el(
					'div',
					{ className: 'noorifa-zone-pool__items' },
					available.length
						? available.map( function ( id ) {
								return el( HeaderModuleChip, {
									key: id,
									id: id,
									label: choices[ id ],
									isDragging: draggingId === id,
									onDragStartItem: handleDragStart,
									onDropBefore: function () {
										handleDrop( 'available', 0 );
									},
									onDragEndItem: handleDragEnd,
								} );
						  } )
						: el( 'p', { className: 'noorifa-zone-column__placeholder' }, __( 'Every module is placed in a zone.', 'noorifa' ) )
				)
			),
			el(
				'div',
				{ className: 'noorifa-zone-columns' },
				el( HeaderZoneColumn, {
					zoneId: 'left',
					title: __( 'Left', 'noorifa' ),
					items: zones.left,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'noorifa' ),
				} ),
				el( HeaderZoneColumn, {
					zoneId: 'center',
					title: __( 'Center', 'noorifa' ),
					items: zones.center,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'noorifa' ),
				} ),
				el( HeaderZoneColumn, {
					zoneId: 'right',
					title: __( 'Right', 'noorifa' ),
					items: zones.right,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'noorifa' ),
				} )
			)
		);
	}

	/**
	 * A single-list version of the same "Available pool" + drop-target
	 * pattern `HeaderZoneBuilder` uses (reuses `HeaderModuleChip` and
	 * `HeaderZoneColumn` directly — neither was ever header-specific).
	 * Used by the Footer Builder's top row and bottom bar: one ordered
	 * "active" list (placement = visibility, like the header zones) plus
	 * a pool of hidden items to drag/click back in.
	 */
	function SingleListBuilder( { settings, path, modules, choices, onChange, activeTitle, poolTitle, poolHint, emptyLabel } ) {
		var draggingState = useState( null );
		var draggingId = draggingState[ 0 ];
		var setDraggingId = draggingState[ 1 ];

		var active = getPath( settings, path ) || [];
		var available = modules.filter( function ( id ) {
			return active.indexOf( id ) === -1;
		} );

		function moveItem( id, toZone, toIndex ) {
			var next = active.slice();
			var fromIndex = next.indexOf( id );
			if ( -1 !== fromIndex ) {
				next.splice( fromIndex, 1 );
				if ( 'active' === toZone && fromIndex < toIndex ) {
					toIndex -= 1;
				}
			}
			if ( 'active' === toZone ) {
				var insertAt = Math.min( toIndex, next.length );
				next.splice( insertAt, 0, id );
			}
			onChange( setPath( settings, path, next ) );
		}

		function removeItem( id ) {
			moveItem( id, 'available', 0 );
		}

		function handleDragStart( id ) {
			setDraggingId( id );
		}

		function handleDragEnd() {
			setDraggingId( null );
		}

		function handleDrop( toZone, toIndex ) {
			if ( null === draggingId ) {
				return;
			}
			moveItem( draggingId, toZone, toIndex );
			setDraggingId( null );
		}

		return el(
			'div',
			{ className: 'noorifa-zone-builder' },
			el(
				'div',
				{
					className: 'noorifa-zone-pool' + ( available.length ? '' : ' is-empty' ),
					onDragOver: function ( event ) {
						event.preventDefault();
					},
					onDrop: function ( event ) {
						event.preventDefault();
						handleDrop( 'available', 0 );
					},
				},
				el( 'h4', { className: 'noorifa-zone-column__title' }, poolTitle ),
				el(
					'div',
					{ className: 'noorifa-zone-pool__items' },
					available.length
						? available.map( function ( id ) {
								return el( HeaderModuleChip, {
									key: id,
									id: id,
									label: choices[ id ] || id,
									isDragging: draggingId === id,
									onDragStartItem: handleDragStart,
									onDropBefore: function () {
										handleDrop( 'available', 0 );
									},
									onDragEndItem: handleDragEnd,
								} );
						  } )
						: el( 'p', { className: 'noorifa-zone-column__placeholder' }, poolHint )
				)
			),
			el(
				'div',
				{ className: 'noorifa-zone-columns noorifa-zone-columns--single' },
				el( HeaderZoneColumn, {
					zoneId: 'active',
					title: activeTitle,
					items: active,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeItem,
					emptyLabel: emptyLabel,
				} )
			)
		);
	}

	function DashboardSection() {
		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Welcome to Noorifa Theme Settings', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'p', null, __( 'Configure your store’s contact details, social links, brand colors, and the header/footer builders from the sections on the left.', 'noorifa' ) ),
				el( 'p', { className: 'noorifa-section-intro' }, __( 'Changes are saved to this site only and take effect immediately after clicking Save Settings.', 'noorifa' ) )
			)
		);
	}

	function ComingSoon( { label } ) {
		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, label ) ),
			el(
				c.CardBody,
				null,
				el(
					'div',
					{ className: 'noorifa-coming-soon' },
					el( c.Icon, { icon: 'clock', size: 32 } ),
					el( 'p', null, __( 'Coming soon.', 'noorifa' ) ),
					el( 'p', { className: 'noorifa-section-intro' }, __( 'This section isn’t wired up to a real setting yet.', 'noorifa' ) )
				)
			)
		);
	}

	/**
	 * A real repeatable-field list — each row is `{ id, url }` (`id` picks
	 * which network/icon, from `DATA.choices.social`). Add/remove rows
	 * freely; no reordering (the footer always renders whichever networks
	 * are present, in list order, same as the header/footer builders'
	 * "presence is visibility" rule).
	 */
	function SocialLinksField( { settings, path, label, help, choices, onChange } ) {
		var rows = getPath( settings, path ) || [];
		var networkIds = Object.keys( choices );

		function updateRow( index, patch ) {
			var next = rows.slice();
			next[ index ] = Object.assign( {}, next[ index ], patch );
			onChange( setPath( settings, path, next ) );
		}

		function addRow() {
			var used = rows.map( function ( row ) { return row.id; } );
			var nextId = networkIds.filter( function ( id ) { return used.indexOf( id ) === -1; } )[ 0 ] || networkIds[ 0 ];
			onChange( setPath( settings, path, rows.concat( [ { id: nextId, url: '' } ] ) ) );
		}

		function removeRow( index ) {
			var next = rows.slice();
			next.splice( index, 1 );
			onChange( setPath( settings, path, next ) );
		}

		return el(
			Field,
			{ label: label, help: help },
			el(
				'div',
				{ className: 'noorifa-repeater' },
				rows.map( function ( row, index ) {
					return el(
						'div',
						{ className: 'noorifa-repeater__row', key: index },
						el( c.SelectControl, {
							value: row.id,
							options: networkIds.map( function ( id ) { return { label: choices[ id ], value: id }; } ),
							onChange: function ( value ) {
								updateRow( index, { id: value } );
							},
						} ),
						el( c.TextControl, {
							type: 'url',
							placeholder: __( 'https://…', 'noorifa' ),
							value: row.url || '',
							onChange: function ( value ) {
								updateRow( index, { url: value } );
							},
						} ),
						el(
							'button',
							{
								type: 'button',
								className: 'noorifa-repeater__remove',
								onClick: function () {
									removeRow( index );
								},
								'aria-label': __( 'Remove', 'noorifa' ),
							},
							'×'
						)
					);
				} ),
				el(
					c.Button,
					{ variant: 'secondary', onClick: addRow },
					__( '+ Add Social Link', 'noorifa' )
				)
			)
		);
	}

	function TopbarSection( { settings, onChange } ) {
		var bounds = DATA.fieldBounds.topbar_font_size;

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Topbar', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( ToggleField, { settings: settings, path: 'topbar.enabled', label: __( 'Show announcement bar', 'noorifa' ), onChange: onChange } ),
				el( TextareaField, {
					settings: settings,
					path: 'topbar.message',
					label: __( 'Message', 'noorifa' ),
					help: __( 'Basic HTML is allowed here (e.g. a link or bold text).', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, { settings: settings, path: 'topbar.background_color', label: __( 'Background Color', 'noorifa' ), onChange: onChange } ),
				el( ColorField, {
					settings: settings,
					path: 'topbar.text_color',
					label: __( 'Text Color', 'noorifa' ),
					help: __( 'Change this to keep the message readable if you pick a light Background Color.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'topbar.font_size',
					label: __( 'Font Size', 'noorifa' ),
					help: __( 'The announcement message text size, in pixels.', 'noorifa' ),
					min: bounds.min,
					max: bounds.max,
					onChange: onChange,
				} )
			)
		);
	}

	function BrandingSection( { settings, onChange } ) {
		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Colors', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'div',
					{ className: 'noorifa-branding-colors' },
					el( ColorField, { settings: settings, path: 'branding.color_primary', label: __( 'Primary Color', 'noorifa' ), onChange: onChange } ),
					el( ColorField, { settings: settings, path: 'branding.color_secondary', label: __( 'Secondary Color', 'noorifa' ), onChange: onChange } ),
					el( ColorField, { settings: settings, path: 'branding.body_background_color', label: __( 'Body Background', 'noorifa' ), onChange: onChange } )
				)
			)
		);
	}

	function TypographySection( { settings, onChange } ) {
		var bounds = DATA.fieldBounds.font_size_base;
		var fontOptions = [ { value: '', label: __( '— Theme default —', 'noorifa' ) } ].concat(
			DATA.choices.googleFonts.map( function ( font ) {
				return { value: font, label: font };
			} )
		);

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Typography', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( ComboboxField, {
					settings: settings,
					path: 'typography.font_body',
					label: __( 'Body Font', 'noorifa' ),
					help: __( "Used for paragraph text. Start typing to search. Leave blank to use the theme's built-in font.", 'noorifa' ),
					options: fontOptions,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'typography.font_size_base',
					label: __( 'Base Font Size', 'noorifa' ),
					help: __( 'Paragraph and menu text size, in pixels. Headings already have their own sizes and are unaffected.', 'noorifa' ),
					min: bounds.min,
					max: bounds.max,
					onChange: onChange,
				} ),
				el( FontPreview, {
					fontFamily: getPath( settings, 'typography.font_body' ),
					fontSize: getPath( settings, 'typography.font_size_base' ),
					previewId: 'body',
				} ),
				el( ComboboxField, {
					settings: settings,
					path: 'typography.font_heading',
					label: __( 'Heading Font', 'noorifa' ),
					help: __( 'Used for H1–H6 headings. Start typing to search. Leave blank to match the Body Font.', 'noorifa' ),
					options: fontOptions,
					onChange: onChange,
				} ),
				el( FontPreview, { fontFamily: getPath( settings, 'typography.font_heading' ), previewId: 'heading' } ),
				el( ComboboxField, {
					settings: settings,
					path: 'typography.font_menu',
					label: __( 'Menu Font', 'noorifa' ),
					help: __( 'Used for the header and mobile navigation menu. Start typing to search. Leave blank to match the Body Font.', 'noorifa' ),
					options: fontOptions,
					onChange: onChange,
				} ),
				el( FontPreview, { fontFamily: getPath( settings, 'typography.font_menu' ), previewId: 'menu' } )
			)
		);
	}

	function LayoutSection( { settings, onChange } ) {
		var widthBounds = DATA.fieldBounds.container_width;
		var isBoxed = getPath( settings, 'layout.site_width' ) !== 'full-width';

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Layout', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( ChoiceField, {
					settings: settings,
					path: 'layout.site_width',
					label: __( 'Site Width', 'noorifa' ),
					help: __( 'Boxed keeps the header, content and footer capped at a max width. Full Width stretches them to fill the browser.', 'noorifa' ),
					choices: DATA.choices.layout.siteWidth,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'layout.container_width',
					label: __( 'Container Width', 'noorifa' ),
					help: isBoxed
						? __( 'The max width of the header, content and footer, in pixels.', 'noorifa' )
						: __( 'Only applies while Site Width is Boxed.', 'noorifa' ),
					min: widthBounds.min,
					max: widthBounds.max,
					onChange: onChange,
				} )
			)
		);
	}

	function HeaderSection( { settings, onChange } ) {
		var widthBounds = DATA.fieldBounds.header_container_width;
		var menuFontSizeBounds = DATA.fieldBounds.header_menu_font_size;

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Header Builder', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Drag any module into the Left, Center or Right zone — including mixing modules within a zone. Click the × on a module (or drag it back to Available) to hide it.', 'noorifa' )
				),
				el( HeaderZoneBuilder, { settings: settings, onChange: onChange } ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Menu Style', 'noorifa' ) ),
				el( ColorField, {
					settings: settings,
					path: 'header.menu_color',
					label: __( 'Menu Color', 'noorifa' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'header.menu_font_size',
					label: __( 'Menu Font Size', 'noorifa' ),
					help: __( 'The navigation menu text size, in pixels.', 'noorifa' ),
					min: menuFontSizeBounds.min,
					max: menuFontSizeBounds.max,
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.menu_uppercase',
					label: __( 'Uppercase menu text', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.menu_bold',
					label: __( 'Bold menu text', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Background', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'header.background_color_enabled',
					label: __( 'Override header background color', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'header.background_color',
					label: __( 'Header Background Color', 'noorifa' ),
					help: __( 'Only applies while the toggle above is on — otherwise the header just shows the Body Background color through.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Layout', 'noorifa' ) ),
				el( RangeField, {
					settings: settings,
					path: 'header.container_width',
					label: __( 'Header Width', 'noorifa' ),
					help: __( 'The max width of the header row and its mega menu, in pixels — independent from the main content width.', 'noorifa' ),
					min: widthBounds.min,
					max: widthBounds.max,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Behavior', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'header.sticky',
					label: __( 'Stick header to the top of the screen on scroll', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.force_mobile_menu',
					label: __( 'Always use the mobile menu, even on desktop', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Mobile Menu', 'noorifa' ) ),
				el( TextField, {
					settings: settings,
					path: 'header.whatsapp_number',
					label: __( 'WhatsApp Number', 'noorifa' ),
					placeholder: __( 'e.g. 8801XXXXXXXXX', 'noorifa' ),
					help: __( 'Shown in bold in the mobile menu’s "Need Help?" box and links straight to a WhatsApp chat. Include the country code, digits only — no spaces, dashes, or a leading +.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	function FooterSection( { settings, onChange } ) {
		var topModules = Object.keys( DATA.choices.footer.top );
		var bottomModules = Object.keys( DATA.choices.footer.bottom );

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Footer Builder', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Drag columns between Active and Available (or click × to hide) — and drag to reorder within Active.', 'noorifa' )
				),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Background', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'footer.background_color_enabled',
					label: __( 'Override footer background & text colors', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'footer.background_color',
					label: __( 'Footer Background Color', 'noorifa' ),
					help: __( 'Only applies while the toggle above is on — otherwise the footer just shows the Body Background color through.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'footer.text_color',
					label: __( 'Footer Text Color', 'noorifa' ),
					help: __( 'Applies to all footer headings, text, icons and borders — pick a light color for a dark background, or vice versa, so everything stays readable.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Top Row Columns', 'noorifa' ) ),
				el( SingleListBuilder, {
					settings: settings,
					path: 'footer.top',
					modules: topModules,
					choices: DATA.choices.footer.top,
					onChange: onChange,
					activeTitle: __( 'Active', 'noorifa' ),
					poolTitle: __( 'Available (drag into Active below)', 'noorifa' ),
					poolHint: __( 'Every column is active.', 'noorifa' ),
					emptyLabel: __( 'Drop a column here', 'noorifa' ),
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Info Card', 'noorifa' ) ),
				el( ImageField, {
					settings: settings,
					path: 'footer.info_logo',
					label: __( 'Logo', 'noorifa' ),
					help: __( 'Optional — if left blank, the site logo (Customizer > Site Identity) is used instead.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextareaField, {
					settings: settings,
					path: 'footer.info_description',
					label: __( 'Description', 'noorifa' ),
					help: __( 'Basic HTML is allowed here (e.g. a link or bold text).', 'noorifa' ),
					onChange: onChange,
				} ),
				el( SocialLinksField, {
					settings: settings,
					path: 'footer.social_links',
					label: __( 'Social Links', 'noorifa' ),
					help: __( 'An icon with no URL is never shown — add a row per network you want in the footer.', 'noorifa' ),
					choices: DATA.choices.social,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Column Content', 'noorifa' ) ),
				el( TextField, { settings: settings, path: 'footer.company_heading', label: __( 'Company Menu Heading', 'noorifa' ), onChange: onChange } ),
				el( TextField, { settings: settings, path: 'footer.customer_heading', label: __( 'Customer Care Menu Heading', 'noorifa' ), onChange: onChange } ),
				el(
					'p',
					{ className: 'noorifa-field__help' },
					__( 'The Newsletter column’s heading, description and signup form now live in their own Newsletter section on the left.', 'noorifa' )
				),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Bottom Bar Items', 'noorifa' ) ),
				el( SingleListBuilder, {
					settings: settings,
					path: 'footer.bottom',
					modules: bottomModules,
					choices: DATA.choices.footer.bottom,
					onChange: onChange,
					activeTitle: __( 'Active', 'noorifa' ),
					poolTitle: __( 'Available (drag into Active below)', 'noorifa' ),
					poolHint: __( 'Every item is active.', 'noorifa' ),
					emptyLabel: __( 'Drop an item here', 'noorifa' ),
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Bottom Bar Content', 'noorifa' ) ),
				el( TextField, {
					settings: settings,
					path: 'footer.copyright_text',
					label: __( 'Copyright Text', 'noorifa' ),
					help: __( 'Use {year} and {site_name} as placeholders — they’re replaced automatically.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ImageField, {
					settings: settings,
					path: 'footer.payment_icons_image',
					label: __( 'Payment Icons', 'noorifa' ),
					help: __( 'Optional — upload a single image with all accepted payment icons. Leave blank to use the theme’s own built-in icon set.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	function NewsletterSection( { settings, onChange } ) {
		var isCustom = getPath( settings, 'newsletter.provider' ) === 'custom';
		var providerOptions = Object.keys( DATA.choices.newsletter.provider ).map( function ( value ) {
			return { value: value, label: DATA.choices.newsletter.provider[ value ] };
		} );

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Newsletter', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( TextField, { settings: settings, path: 'footer.newsletter_heading', label: __( 'Heading', 'noorifa' ), onChange: onChange } ),
				el( TextField, { settings: settings, path: 'footer.newsletter_description', label: __( 'Description', 'noorifa' ), onChange: onChange } ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Signup Form', 'noorifa' ) ),
				el( SelectField, {
					settings: settings,
					path: 'newsletter.provider',
					label: __( 'Form Source', 'noorifa' ),
					help: __( 'Theme Default Form uses the theme’s own real signup form. Custom Embed Code replaces it with a snippet from your email marketing provider.', 'noorifa' ),
					options: providerOptions,
					onChange: onChange,
				} ),
				isCustom
					? el( TextareaField, {
							settings: settings,
							path: 'newsletter.embed_code',
							label: __( 'Embed Code', 'noorifa' ),
							help: __( 'Paste the full embed snippet from Mailchimp, MailerLite, etc. (HTML and <script> tags are both allowed here, unlike other fields).', 'noorifa' ),
							onChange: onChange,
					  } )
					: null
			)
		);
	}

	function ShopSection( { settings, onChange } ) {
		var columnsOptions = Object.keys( DATA.choices.shop.gridColumns ).map( function ( value ) {
			return { value: value, label: DATA.choices.shop.gridColumns[ value ] };
		} );
		var perPageOptions = Object.keys( DATA.choices.shop.productsPerPage ).map( function ( value ) {
			return { value: value, label: DATA.choices.shop.productsPerPage[ value ] };
		} );
		var titleFontBounds = DATA.fieldBounds.shop_title_font_size;
		var metaFontBounds = DATA.fieldBounds.shop_meta_font_size;
		var fontWeightOptions = Object.keys( DATA.choices.shop.fontWeight ).map( function ( value ) {
			return { value: value, label: DATA.choices.shop.fontWeight[ value ] };
		} );

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Shop', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( SelectField, {
					settings: settings,
					path: 'shop.grid_columns',
					label: __( 'Grid Columns', 'noorifa' ),
					help: __( 'How many products per row on the shop and category pages.', 'noorifa' ),
					options: columnsOptions,
					onChange: onChange,
				} ),
				el( SelectField, {
					settings: settings,
					path: 'shop.products_per_page',
					label: __( 'Products Per Page', 'noorifa' ),
					options: perPageOptions,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Product Card Typography', 'noorifa' ) ),
				el( RangeField, {
					settings: settings,
					path: 'shop.title_font_size',
					label: __( 'Product Title Font Size', 'noorifa' ),
					min: titleFontBounds.min,
					max: titleFontBounds.max,
					onChange: onChange,
				} ),
				el( SelectField, {
					settings: settings,
					path: 'shop.title_font_weight',
					label: __( 'Product Title Font Weight', 'noorifa' ),
					options: fontWeightOptions,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'shop.meta_font_size',
					label: __( 'Product Meta (Price) Font Size', 'noorifa' ),
					help: __( 'The current price shown on each product card. The crossed-out original price stays its own smaller size.', 'noorifa' ),
					min: metaFontBounds.min,
					max: metaFontBounds.max,
					onChange: onChange,
				} )
			)
		);
	}

	function BlogSection( { settings, onChange } ) {
		var gridColumnsOptions = Object.keys( DATA.choices.blog.gridColumns ).map( function ( value ) {
			return { value: value, label: DATA.choices.blog.gridColumns[ value ] };
		} );
		var excerptBounds = DATA.fieldBounds.blog_excerpt_length;
		var recentCountBounds = DATA.fieldBounds.blog_sidebar_recent_posts_count;
		var tagsCountBounds = DATA.fieldBounds.blog_sidebar_tags_count;
		var relatedCountBounds = DATA.fieldBounds.blog_related_posts_count;

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Blog', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Layout', 'noorifa' ) ),
				el( SelectField, {
					settings: settings,
					path: 'blog.grid_columns',
					label: __( 'Grid Columns', 'noorifa' ),
					help: __( 'How many post cards per row on the blog, category, tag and search results pages.', 'noorifa' ),
					options: gridColumnsOptions,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'blog.excerpt_length',
					label: __( 'Excerpt Length', 'noorifa' ),
					help: __( 'Maximum number of words shown in each post card’s excerpt.', 'noorifa' ),
					min: excerptBounds.min,
					max: excerptBounds.max,
					onChange: onChange,
				} ),

				el( 'h3', { className: 'noorifa-subheading' }, __( 'Sidebar', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.sidebar_enabled',
					label: __( 'Show sidebar', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.sidebar_categories',
					label: __( 'Show Categories', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.sidebar_recent_posts',
					label: __( 'Show Recent Posts', 'noorifa' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'blog.sidebar_recent_posts_count',
					label: __( 'Recent Posts Count', 'noorifa' ),
					min: recentCountBounds.min,
					max: recentCountBounds.max,
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.sidebar_tags',
					label: __( 'Show Popular Tags', 'noorifa' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'blog.sidebar_tags_count',
					label: __( 'Popular Tags Count', 'noorifa' ),
					min: tagsCountBounds.min,
					max: tagsCountBounds.max,
					onChange: onChange,
				} ),

				el( 'h3', { className: 'noorifa-subheading' }, __( 'Related Posts', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.related_posts_enabled',
					label: __( 'Show related posts on single posts', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextField, {
					settings: settings,
					path: 'blog.related_posts_heading',
					label: __( 'Heading', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextField, {
					settings: settings,
					path: 'blog.related_posts_subtitle',
					label: __( 'Subtitle', 'noorifa' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'blog.related_posts_count',
					label: __( 'Related Posts Count', 'noorifa' ),
					min: relatedCountBounds.min,
					max: relatedCountBounds.max,
					onChange: onChange,
				} ),

				el( 'h3', { className: 'noorifa-subheading' }, __( 'Share Buttons', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.share_buttons_enabled',
					label: __( 'Show share buttons on single posts', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.share_facebook',
					label: __( 'Facebook', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.share_x',
					label: __( 'X (Twitter)', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'blog.share_pinterest',
					label: __( 'Pinterest', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	/**
	 * Visual swatch picker for the Buttons > Style setting — each option is
	 * a live preview reproducing the real frontend variant (see the
	 * `body.noorifa-btn-style-{n}` rules in main.css and their standalone
	 * admin-only version in admin-settings.css), not just a text dropdown.
	 */
	function ButtonStylePicker( { settings, path, choices, onChange } ) {
		var value = getPath( settings, path );

		return el(
			'div',
			{ className: 'noorifa-btn-style-grid' },
			Object.keys( choices ).map( function ( id ) {
				var isSelected = value === id;
				return el(
					'div',
					{
						key: id,
						className: 'noorifa-btn-style-option' + ( isSelected ? ' is-selected' : '' ),
						onClick: function () {
							onChange( setPath( settings, path, id ) );
						},
					},
					el( 'span', { className: 'noorifa-btn-swatch style-' + id }, __( 'Button', 'noorifa' ) ),
					el( 'span', { className: 'noorifa-btn-style-option__label' }, choices[ id ] )
				);
			} )
		);
	}

	function ButtonsSection( { settings, onChange } ) {
		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Buttons', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Pick a button style — it applies to every button across the whole site immediately.', 'noorifa' )
				),
				el( ButtonStylePicker, {
					settings: settings,
					path: 'buttons.style',
					choices: DATA.choices.buttons.style,
					onChange: onChange,
				} )
			)
		);
	}

	function PageHeaderSection( { settings, onChange } ) {
		var alignmentOptions = Object.keys( DATA.choices.pageHeader.alignment ).map( function ( value ) {
			return { value: value, label: DATA.choices.pageHeader.alignment[ value ] };
		} );

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Page Header', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'The title banner shown at the top of the Shop, Cart, Checkout, My Account, blog archives, search results, 404, and static pages.', 'noorifa' )
				),
				el( ToggleField, {
					settings: settings,
					path: 'page_header.breadcrumbs_enabled',
					label: __( 'Show breadcrumbs', 'noorifa' ),
					onChange: onChange,
				} ),
				el( SelectField, {
					settings: settings,
					path: 'page_header.alignment',
					label: __( 'Alignment', 'noorifa' ),
					options: alignmentOptions,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Background', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'page_header.background_enabled',
					label: __( 'Override page header background color', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'page_header.background_color',
					label: __( 'Page Header Background Color', 'noorifa' ),
					help: __( 'Only applies while the toggle above is on — otherwise the banner just shows the Body Background color through.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'page_header.text_color',
					label: __( 'Page Header Text Color', 'noorifa' ),
					help: __( 'Also applies to the breadcrumb links and icons. Only applies while the toggle above is on.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	function PerformanceSection( { settings, onChange } ) {
		var alwaysOn = [
			__( 'Emoji detection scripts and styles removed', 'noorifa' ),
			__( 'RSD, Windows Live Writer manifest, and shortlink discovery links removed', 'noorifa' ),
			__( 'REST API and oEmbed discovery links removed (the REST API and oEmbed themselves still work)', 'noorifa' ),
			__( 'WordPress version number removed from page source', 'noorifa' ),
			__( 'oEmbed’s resize script (wp-embed.min.js) is not loaded', 'noorifa' ),
			__( 'WooCommerce’s default frontend styles are not loaded — the theme provides its own', 'noorifa' ),
		];

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Performance', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Always Active', 'noorifa' ) ),
				el(
					'ul',
					{ className: 'noorifa-performance-list' },
					alwaysOn.map( function ( text, index ) {
						return el(
							'li',
							{ key: index, className: 'noorifa-performance-list__item' },
							el( c.Icon, { icon: 'yes-alt' } ),
							el( 'span', null, text )
						);
					} )
				),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Optional', 'noorifa' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'performance.disable_xmlrpc',
					label: __( 'Disable XML-RPC', 'noorifa' ),
					help: __( 'Reduces server load from pingback and brute-force traffic. Leave this off if you use the WordPress mobile app or another tool that relies on XML-RPC to publish.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'performance.remove_version_strings',
					label: __( 'Remove version query strings from CSS & JS', 'noorifa' ),
					help: __( 'Can improve cache hit rates on some CDNs/proxies. If your host or CDN caches aggressively, you may need to manually purge its cache after a theme update instead of relying on the version bump.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	function SEOSection( { settings, onChange } ) {
		var alwaysOn = [
			__( 'Meta description, Open Graph and Twitter Card tags on every page — built from that page’s own real content (never fabricated)', 'noorifa' ),
			__( 'Organization and WebSite structured data (JSON-LD), including your real logo and social links', 'noorifa' ),
			__( 'BreadcrumbList structured data, matching the breadcrumbs already visible on the page', 'noorifa' ),
			__( 'Real WooCommerce product structured data on every product page', 'noorifa' ),
			__( 'WordPress’ own built-in XML sitemap (/wp-sitemap.xml)', 'noorifa' ),
		];

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'SEO', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Always Active', 'noorifa' ) ),
				el(
					'ul',
					{ className: 'noorifa-performance-list' },
					alwaysOn.map( function ( text, index ) {
						return el(
							'li',
							{ key: index, className: 'noorifa-performance-list__item' },
							el( c.Icon, { icon: 'yes-alt' } ),
							el( 'span', null, text )
						);
					} )
				),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Fallbacks', 'noorifa' ) ),
				el( TextareaField, {
					settings: settings,
					path: 'seo.default_description',
					label: __( 'Default Meta Description', 'noorifa' ),
					help: __( 'Used only on pages with no real content to summarize on their own — e.g. the Shop page. Leave blank to print no description tag there, same as today.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( ImageField, {
					settings: settings,
					path: 'seo.default_image',
					label: __( 'Default Social Share Image', 'noorifa' ),
					help: __( 'Used only when a page has no product image, featured image, or logo of its own to share.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Social Accounts', 'noorifa' ) ),
				el( TextField, {
					settings: settings,
					path: 'seo.twitter_username',
					label: __( 'Twitter / X Username', 'noorifa' ),
					help: __( 'With or without the @ — adds a twitter:site tag so shared links credit your account.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextField, {
					settings: settings,
					path: 'seo.facebook_app_id',
					label: __( 'Facebook App ID', 'noorifa' ),
					help: __( 'Only needed if you track link shares through a real Facebook App.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Search Engine Verification', 'noorifa' ) ),
				el( TextField, {
					settings: settings,
					path: 'seo.google_verification',
					label: __( 'Google Search Console', 'noorifa' ),
					help: __( 'Paste just the content value from Google’s HTML tag verification method, not the full <meta> tag.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextField, {
					settings: settings,
					path: 'seo.bing_verification',
					label: __( 'Bing Webmaster Tools', 'noorifa' ),
					help: __( 'Paste just the content value from Bing’s meta tag verification method, not the full <meta> tag.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	function IntegrationsSection( { settings, onChange } ) {
		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Integrations', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Paste the full snippet each vendor gives you — not just an ID — exactly as they show it in your dashboard. It prints in <head> as-is, only once something is pasted below.', 'noorifa' )
				),
				el( TextareaField, {
					settings: settings,
					path: 'integrations.google_analytics_code',
					label: __( 'Google Analytics', 'noorifa' ),
					help: __( 'From Google Analytics > Admin > Data Streams > your stream > "View tag instructions" — copy the whole snippet shown there.', 'noorifa' ),
					onChange: onChange,
				} ),
				el( TextareaField, {
					settings: settings,
					path: 'integrations.facebook_pixel_code',
					label: __( 'Meta / Facebook Pixel', 'noorifa' ),
					help: __( 'From Meta Events Manager > Data Sources > your Pixel > Settings > "Set up manually" — copy the whole base code shown there.', 'noorifa' ),
					onChange: onChange,
				} )
			)
		);
	}

	/**
	 * Export downloads the real, already-loaded `settings` state as a JSON
	 * file — no extra API call needed. Import reads a file client-side and
	 * loads it into that same local `settings` state via `onChange` (the
	 * `setSettings` passed down from App()) — nothing is sent to the server
	 * until the site owner reviews it and clicks the real "Save Changes"
	 * button, which still runs the real `Schema::sanitize()` validation on
	 * every field exactly like any other save.
	 */
	function ImportExportSection( { settings, onChange } ) {
		var fileState = useState( null );
		var file = fileState[ 0 ];
		var setFile = fileState[ 1 ];

		var messageState = useState( { type: '', text: '' } );
		var message = messageState[ 0 ];
		var setMessage = messageState[ 1 ];

		function exportSettings() {
			var json = JSON.stringify( settings, null, 2 );
			var blob = new Blob( [ json ], { type: 'application/json' } );
			var url = URL.createObjectURL( blob );
			var link = document.createElement( 'a' );
			link.href = url;
			link.download = 'noorifa-settings-' + new Date().toISOString().slice( 0, 10 ) + '.json';
			document.body.appendChild( link );
			link.click();
			link.remove();
			URL.revokeObjectURL( url );
		}

		function handleFileChange( event ) {
			setFile( event.target.files && event.target.files[ 0 ] ? event.target.files[ 0 ] : null );
			setMessage( { type: '', text: '' } );
		}

		function importSettings() {
			if ( ! file ) {
				return;
			}
			if ( ! window.confirm( __( 'Load this file into the form below for review? It replaces every field currently shown — nothing is saved until you click Save Changes.', 'noorifa' ) ) ) {
				return;
			}

			var reader = new FileReader();
			reader.onload = function () {
				var parsed;
				try {
					parsed = JSON.parse( reader.result );
				} catch ( e ) {
					setMessage( { type: 'error', text: __( 'That file isn’t valid JSON.', 'noorifa' ) } );
					return;
				}
				if ( ! parsed || typeof parsed !== 'object' || Array.isArray( parsed ) ) {
					setMessage( { type: 'error', text: __( 'That file doesn’t look like an Noorifa settings export.', 'noorifa' ) } );
					return;
				}
				onChange( parsed );
				setFile( null );
				setMessage( { type: 'success', text: __( 'Loaded — review the settings, then click Save Changes above to apply them.', 'noorifa' ) } );
			};
			reader.onerror = function () {
				setMessage( { type: 'error', text: __( 'Couldn’t read that file — please try again.', 'noorifa' ) } );
			};
			reader.readAsText( file );
		}

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Import / Export', 'noorifa' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'h3', { className: 'noorifa-subheading' }, __( 'Export', 'noorifa' ) ),
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Download every real Noorifa setting as a JSON file — useful for backups or copying settings to another site.', 'noorifa' )
				),
				el( c.Button, { variant: 'secondary', onClick: exportSettings }, __( 'Export Settings', 'noorifa' ) ),

				el( 'h3', { className: 'noorifa-subheading' }, __( 'Import', 'noorifa' ) ),
				el(
					'p',
					{ className: 'noorifa-section-intro' },
					__( 'Upload a previously exported JSON file. It loads into the form for review — nothing is saved until you click Save Changes.', 'noorifa' )
				),
				el(
					'div',
					{ className: 'noorifa-import-row' },
					el( 'input', { type: 'file', accept: 'application/json,.json', onChange: handleFileChange } ),
					el(
						c.Button,
						{ variant: 'primary', onClick: importSettings, disabled: ! file },
						__( 'Load File', 'noorifa' )
					)
				),
				message.text
					? el(
							'p',
							{ className: 'noorifa-field__help' + ( 'error' === message.type ? ' noorifa-field__help--error' : '' ) },
							message.text
					  )
					: null
			)
		);
	}

	var REAL_SECTION_COMPONENTS = {
		dashboard: DashboardSection,
		topbar: TopbarSection,
		branding: BrandingSection,
		typography: TypographySection,
		layout: LayoutSection,
		header: HeaderSection,
		footer: FooterSection,
		newsletter: NewsletterSection,
		shop: ShopSection,
		blog: BlogSection,
		'page-header': PageHeaderSection,
		buttons: ButtonsSection,
		performance: PerformanceSection,
		seo: SEOSection,
		integrations: IntegrationsSection,
		'import-export': ImportExportSection,
	};

	function sectionComponent( section ) {
		if ( REAL_SECTION_COMPONENTS[ section.id ] ) {
			return REAL_SECTION_COMPONENTS[ section.id ];
		}
		return function () {
			return el( ComingSoon, { label: section.label } );
		};
	}

	var SECTION_COMPONENTS = SECTIONS.reduce( function ( map, section ) {
		map[ section.id ] = sectionComponent( section );
		return map;
	}, {} );

	function App() {
		var activeState = useState( 'dashboard' );
		var active = activeState[ 0 ];
		var setActive = activeState[ 1 ];

		var searchState = useState( '' );
		var search = searchState[ 0 ];
		var setSearch = searchState[ 1 ];

		var settingsState = useState( DATA.settings );
		var settings = settingsState[ 0 ];
		var setSettings = settingsState[ 1 ];

		var savingState = useState( false );
		var saving = savingState[ 0 ];
		var setSaving = savingState[ 1 ];

		var resettingState = useState( false );
		var resetting = resettingState[ 0 ];
		var setResetting = resettingState[ 1 ];

		var statusState = useState( '' );
		var status = statusState[ 0 ];
		var setStatus = statusState[ 1 ];

		function save() {
			setSaving( true );
			setStatus( '' );
			apiFetch( { path: '/noorifa/v1/settings', method: 'POST', data: settings } )
				.then( function ( saved ) {
					setSettings( saved );
					setSaving( false );
					setStatus( 'success' );
					setTimeout( function () {
						setStatus( '' );
					}, 2500 );
				} )
				.catch( function () {
					setSaving( false );
					setStatus( 'error' );
				} );
		}

		function resetToDefaults() {
			if ( ! window.confirm( __( 'Reset every Noorifa setting to its default value? This cannot be undone.', 'noorifa' ) ) ) {
				return;
			}
			setResetting( true );
			setStatus( '' );
			apiFetch( { path: '/noorifa/v1/settings', method: 'DELETE' } )
				.then( function ( defaults ) {
					setSettings( defaults );
					setResetting( false );
					setStatus( 'success' );
					setTimeout( function () {
						setStatus( '' );
					}, 2500 );
				} )
				.catch( function () {
					setResetting( false );
					setStatus( 'error' );
				} );
		}

		var ActiveSection = SECTION_COMPONENTS[ active ];
		var query = search.trim().toLowerCase();
		var visibleSections = query
			? SECTIONS.filter( function ( section ) {
					return section.label.toLowerCase().indexOf( query ) !== -1;
			  } )
			: SECTIONS;

		return el(
			'div',
			{ className: 'noorifa-settings-shell' },
			el(
				'nav',
				{ className: 'noorifa-settings-nav' },
				el(
					'div',
					{ className: 'noorifa-settings-nav__brand' },
					el( 'span', { className: 'noorifa-settings-nav__brand-icon' }, el( c.Icon, { icon: 'admin-customizer', size: 20 } ) ),
					el(
						'div',
						null,
						el( 'div', { className: 'noorifa-settings-nav__brand-title' }, __( 'Noorifa', 'noorifa' ) ),
						el( 'div', { className: 'noorifa-settings-nav__brand-subtitle' }, __( 'Theme Settings', 'noorifa' ) )
					)
				),
				el(
					'div',
					{ className: 'noorifa-settings-nav__items' },
					visibleSections.length
						? visibleSections.map( function ( section ) {
								return el(
									'button',
									{
										key: section.id,
										type: 'button',
										className: 'noorifa-settings-nav__item' + ( active === section.id ? ' is-active' : '' ),
										onClick: function () {
											setActive( section.id );
										},
									},
									el( c.Icon, { icon: section.icon } ),
									el( 'span', null, section.label )
								);
						  } )
						: el( 'p', { className: 'noorifa-settings-nav__empty' }, __( 'No matching settings.', 'noorifa' ) )
				)
			),
			el(
				'div',
				{ className: 'noorifa-settings-right' },
				el(
					'div',
					{ className: 'noorifa-settings-topbar' },
					el(
						'div',
						{ className: 'noorifa-settings-search' },
						el( c.Icon, { icon: 'search', size: 18 } ),
						el( 'input', {
							type: 'text',
							placeholder: __( 'Search settings…', 'noorifa' ),
							value: search,
							onChange: function ( event ) {
								setSearch( event.target.value );
							},
						} )
					),
					el(
						'div',
						{ className: 'noorifa-settings-topbar__actions' },
						status === 'success' ? el( 'span', { className: 'noorifa-save-status is-success' }, __( 'Saved', 'noorifa' ) ) : null,
						status === 'error' ? el( 'span', { className: 'noorifa-save-status is-error' }, __( 'Save failed', 'noorifa' ) ) : null,
						el(
							c.Button,
							{ variant: 'tertiary', isDestructive: true, isBusy: resetting, disabled: saving || resetting, onClick: resetToDefaults },
							resetting ? __( 'Resetting…', 'noorifa' ) : __( 'Reset to Defaults', 'noorifa' )
						),
						el(
							c.Button,
							{ variant: 'primary', isBusy: saving, disabled: saving || resetting, onClick: save },
							saving ? __( 'Saving…', 'noorifa' ) : __( 'Save Changes', 'noorifa' )
						)
					)
				),
				el(
					'main',
					{ className: 'noorifa-settings-content' },
					el( ActiveSection, { settings: settings, onChange: setSettings } )
				)
			)
		);
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'noorifa-settings-app' );
		if ( root ) {
			wp.element.createRoot( root ).render( el( App ) );
		}
	} );
} )();
