/**
 * Ecombon Theme Settings — a real React app built entirely on WordPress
 * core's own bundled packages (`wp-element`, `wp-components`,
 * `wp-api-fetch`, `wp-i18n`) — no npm, no build step. Reads/saves through
 * the real `ecombon/v1/settings` REST route (see Rest_Controller.php).
 *
 * Initial data (`ecombonSettingsData`) is localized by Admin_Page.php.
 */
( function () {
	'use strict';

	var el = wp.element.createElement;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var apiFetch = wp.apiFetch;
	var c = wp.components;

	apiFetch.use( apiFetch.createNonceMiddleware( ecombonSettingsData.nonce ) );

	var DATA = ecombonSettingsData;

	/**
	 * Full sidebar nav, matching the approved reference layout. Sections
	 * without `real: true` have no backing settings yet and render the
	 * shared "coming soon" placeholder instead of a fabricated form.
	 */
	var SECTIONS = [
		{ id: 'dashboard', label: __( 'Dashboard', 'ecombon' ), icon: 'dashboard', real: true },
		{ id: 'layout', label: __( 'Layout', 'ecombon' ), icon: 'layout', real: true },
		{ id: 'typography', label: __( 'Typography', 'ecombon' ), icon: 'editor-textcolor', real: true },
		{ id: 'branding', label: __( 'Colors', 'ecombon' ), icon: 'color-picker', real: true },
		{ id: 'header', label: __( 'Header', 'ecombon' ), icon: 'align-center', real: true },
		{ id: 'topbar', label: __( 'Topbar', 'ecombon' ), icon: 'megaphone', real: true },
		{ id: 'footer', label: __( 'Footer', 'ecombon' ), icon: 'align-wide', real: true },
		{ id: 'newsletter', label: __( 'Newsletter', 'ecombon' ), icon: 'email', real: true },
		{ id: 'shop', label: __( 'Shop', 'ecombon' ), icon: 'store', real: true },
		{ id: 'product', label: __( 'Product Page', 'ecombon' ), icon: 'products' },
		{ id: 'cart-checkout', label: __( 'Cart & Checkout', 'ecombon' ), icon: 'cart' },
		{ id: 'blog', label: __( 'Blog', 'ecombon' ), icon: 'welcome-write-blog' },
		{ id: 'buttons', label: __( 'Buttons', 'ecombon' ), icon: 'button' },
		{ id: 'forms', label: __( 'Forms', 'ecombon' ), icon: 'forms' },
		{ id: 'sidebar', label: __( 'Sidebar', 'ecombon' ), icon: 'menu-alt3' },
		{ id: 'mega-menu', label: __( 'Mega Menu', 'ecombon' ), icon: 'menu-alt' },
		{ id: 'mobile', label: __( 'Mobile', 'ecombon' ), icon: 'smartphone' },
		{ id: 'performance', label: __( 'Performance', 'ecombon' ), icon: 'performance' },
		{ id: 'seo', label: __( 'SEO', 'ecombon' ), icon: 'tag' },
		{ id: 'integrations', label: __( 'Integrations', 'ecombon' ), icon: 'admin-plugins' },
		{ id: 'import-export', label: __( 'Import / Export', 'ecombon' ), icon: 'migrate' },
		{ id: 'license', label: __( 'License', 'ecombon' ), icon: 'awards' },
		{ id: 'updates', label: __( 'Updates', 'ecombon' ), icon: 'update' },
		{ id: 'developer', label: __( 'Developer', 'ecombon' ), icon: 'code-standards' },
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
			{ className: 'ecombon-field' },
			el( 'span', { className: 'ecombon-field__label' }, label ),
			children,
			help ? el( 'p', { className: 'ecombon-field__help' }, help ) : null
		);
	}

	function TextField( { settings, path, label, type, help, onChange } ) {
		return el(
			Field,
			{ label: label, help: help },
			el( c.TextControl, {
				type: type || 'text',
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
				button: { text: __( 'Use this image', 'ecombon' ) },
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
				{ className: 'ecombon-image-field' },
				value ? el( 'img', { src: value, alt: '', className: 'ecombon-image-field__preview' } ) : null,
				el(
					'div',
					{ className: 'ecombon-image-field__actions' },
					el( c.Button, { variant: 'secondary', onClick: openLibrary }, value ? __( 'Change Image', 'ecombon' ) : __( 'Select Image', 'ecombon' ) ),
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
								__( 'Remove', 'ecombon' )
						  )
						: null
				)
			)
		);
	}

	function ToggleField( { settings, path, label, onChange } ) {
		return el(
			'div',
			{ className: 'ecombon-field ecombon-field--toggle' },
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
		var linkElId = 'ecombon-font-preview-link-' + previewId;

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
				className: 'ecombon-font-preview',
				style: style,
			},
			fontFamily
				? __( 'The quick brown fox jumps over the lazy dog — Aa Bb Cc 0123', 'ecombon' )
				: __( "Using the theme's default font.", 'ecombon' )
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
					className: 'ecombon-color-dropdown',
					contentClassName: 'ecombon-color-dropdown__content',
					renderToggle: function ( toggle ) {
						return el(
							'button',
							{
								type: 'button',
								className: 'ecombon-color-swatch-trigger',
								onClick: toggle.onToggle,
								'aria-expanded': toggle.isOpen,
							},
							el( 'span', { className: 'ecombon-color-swatch', style: { backgroundColor: value } } ),
							el( 'span', { className: 'ecombon-color-swatch__hex' }, value )
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
				className: 'ecombon-zone-chip' + ( isDragging ? ' is-dragging' : '' ),
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
			el( 'span', { className: 'ecombon-zone-chip__handle', 'aria-hidden': 'true' }, '⠿' ),
			el( 'span', { className: 'ecombon-zone-chip__label' }, label ),
			onRemove
				? el(
						'button',
						{
							type: 'button',
							className: 'ecombon-zone-chip__remove',
							'aria-label': __( 'Hide', 'ecombon' ) + ' ' + label,
							title: __( 'Hide from header', 'ecombon' ),
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
			{ className: 'ecombon-zone-column' },
			el( 'h4', { className: 'ecombon-zone-column__title' }, title ),
			el(
				'div',
				{
					className: 'ecombon-zone-column__drop' + ( items.length ? '' : ' is-empty' ),
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
					: el( 'p', { className: 'ecombon-zone-column__placeholder' }, emptyLabel )
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
			{ className: 'ecombon-zone-builder' },
			el(
				'div',
				{
					className: 'ecombon-zone-pool' + ( available.length ? '' : ' is-empty' ),
					onDragOver: function ( event ) {
						event.preventDefault();
					},
					onDrop: function ( event ) {
						event.preventDefault();
						handleDrop( 'available', 0 );
					},
				},
				el( 'h4', { className: 'ecombon-zone-column__title' }, __( 'Available Modules (drag into a zone below)', 'ecombon' ) ),
				el(
					'div',
					{ className: 'ecombon-zone-pool__items' },
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
						: el( 'p', { className: 'ecombon-zone-column__placeholder' }, __( 'Every module is placed in a zone.', 'ecombon' ) )
				)
			),
			el(
				'div',
				{ className: 'ecombon-zone-columns' },
				el( HeaderZoneColumn, {
					zoneId: 'left',
					title: __( 'Left', 'ecombon' ),
					items: zones.left,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'ecombon' ),
				} ),
				el( HeaderZoneColumn, {
					zoneId: 'center',
					title: __( 'Center', 'ecombon' ),
					items: zones.center,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'ecombon' ),
				} ),
				el( HeaderZoneColumn, {
					zoneId: 'right',
					title: __( 'Right', 'ecombon' ),
					items: zones.right,
					choices: choices,
					draggingId: draggingId,
					onDragStartItem: handleDragStart,
					onDropAt: handleDrop,
					onDragEndItem: handleDragEnd,
					onRemoveItem: removeModule,
					emptyLabel: __( 'Drop a module here', 'ecombon' ),
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
			{ className: 'ecombon-zone-builder' },
			el(
				'div',
				{
					className: 'ecombon-zone-pool' + ( available.length ? '' : ' is-empty' ),
					onDragOver: function ( event ) {
						event.preventDefault();
					},
					onDrop: function ( event ) {
						event.preventDefault();
						handleDrop( 'available', 0 );
					},
				},
				el( 'h4', { className: 'ecombon-zone-column__title' }, poolTitle ),
				el(
					'div',
					{ className: 'ecombon-zone-pool__items' },
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
						: el( 'p', { className: 'ecombon-zone-column__placeholder' }, poolHint )
				)
			),
			el(
				'div',
				{ className: 'ecombon-zone-columns ecombon-zone-columns--single' },
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Welcome to Ecombon Theme Settings', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( 'p', null, __( 'Configure your store’s contact details, social links, brand colors, and the header/footer builders from the sections on the left.', 'ecombon' ) ),
				el( 'p', { className: 'ecombon-section-intro' }, __( 'Changes are saved to this site only and take effect immediately after clicking Save Settings.', 'ecombon' ) )
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
					{ className: 'ecombon-coming-soon' },
					el( c.Icon, { icon: 'clock', size: 32 } ),
					el( 'p', null, __( 'Coming soon.', 'ecombon' ) ),
					el( 'p', { className: 'ecombon-section-intro' }, __( 'This section isn’t wired up to a real setting yet.', 'ecombon' ) )
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
				{ className: 'ecombon-repeater' },
				rows.map( function ( row, index ) {
					return el(
						'div',
						{ className: 'ecombon-repeater__row', key: index },
						el( c.SelectControl, {
							value: row.id,
							options: networkIds.map( function ( id ) { return { label: choices[ id ], value: id }; } ),
							onChange: function ( value ) {
								updateRow( index, { id: value } );
							},
						} ),
						el( c.TextControl, {
							type: 'url',
							placeholder: __( 'https://…', 'ecombon' ),
							value: row.url || '',
							onChange: function ( value ) {
								updateRow( index, { url: value } );
							},
						} ),
						el(
							'button',
							{
								type: 'button',
								className: 'ecombon-repeater__remove',
								onClick: function () {
									removeRow( index );
								},
								'aria-label': __( 'Remove', 'ecombon' ),
							},
							'×'
						)
					);
				} ),
				el(
					c.Button,
					{ variant: 'secondary', onClick: addRow },
					__( '+ Add Social Link', 'ecombon' )
				)
			)
		);
	}

	function TopbarSection( { settings, onChange } ) {
		var bounds = DATA.fieldBounds.topbar_font_size;

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Topbar', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( ToggleField, { settings: settings, path: 'topbar.enabled', label: __( 'Show announcement bar', 'ecombon' ), onChange: onChange } ),
				el( TextareaField, {
					settings: settings,
					path: 'topbar.message',
					label: __( 'Message', 'ecombon' ),
					help: __( 'Basic HTML is allowed here (e.g. a link or bold text).', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ColorField, { settings: settings, path: 'topbar.background_color', label: __( 'Background Color', 'ecombon' ), onChange: onChange } ),
				el( ColorField, {
					settings: settings,
					path: 'topbar.text_color',
					label: __( 'Text Color', 'ecombon' ),
					help: __( 'Change this to keep the message readable if you pick a light Background Color.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'topbar.font_size',
					label: __( 'Font Size', 'ecombon' ),
					help: __( 'The announcement message text size, in pixels.', 'ecombon' ),
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Colors', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'div',
					{ className: 'ecombon-branding-colors' },
					el( ColorField, { settings: settings, path: 'branding.color_primary', label: __( 'Primary Color', 'ecombon' ), onChange: onChange } ),
					el( ColorField, { settings: settings, path: 'branding.color_secondary', label: __( 'Secondary Color', 'ecombon' ), onChange: onChange } ),
					el( ColorField, { settings: settings, path: 'branding.body_background_color', label: __( 'Body Background', 'ecombon' ), onChange: onChange } )
				)
			)
		);
	}

	function TypographySection( { settings, onChange } ) {
		var bounds = DATA.fieldBounds.font_size_base;
		var fontOptions = [ { value: '', label: __( '— Theme default —', 'ecombon' ) } ].concat(
			DATA.choices.googleFonts.map( function ( font ) {
				return { value: font, label: font };
			} )
		);

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Typography', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( ComboboxField, {
					settings: settings,
					path: 'typography.font_body',
					label: __( 'Body Font', 'ecombon' ),
					help: __( "Used for paragraph text. Start typing to search. Leave blank to use the theme's built-in font.", 'ecombon' ),
					options: fontOptions,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'typography.font_size_base',
					label: __( 'Base Font Size', 'ecombon' ),
					help: __( 'Paragraph and menu text size, in pixels. Headings already have their own sizes and are unaffected.', 'ecombon' ),
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
					label: __( 'Heading Font', 'ecombon' ),
					help: __( 'Used for H1–H6 headings. Start typing to search. Leave blank to match the Body Font.', 'ecombon' ),
					options: fontOptions,
					onChange: onChange,
				} ),
				el( FontPreview, { fontFamily: getPath( settings, 'typography.font_heading' ), previewId: 'heading' } ),
				el( ComboboxField, {
					settings: settings,
					path: 'typography.font_menu',
					label: __( 'Menu Font', 'ecombon' ),
					help: __( 'Used for the header and mobile navigation menu. Start typing to search. Leave blank to match the Body Font.', 'ecombon' ),
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Layout', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( ChoiceField, {
					settings: settings,
					path: 'layout.site_width',
					label: __( 'Site Width', 'ecombon' ),
					help: __( 'Boxed keeps the header, content and footer capped at a max width. Full Width stretches them to fill the browser.', 'ecombon' ),
					choices: DATA.choices.layout.siteWidth,
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'layout.container_width',
					label: __( 'Container Width', 'ecombon' ),
					help: isBoxed
						? __( 'The max width of the header, content and footer, in pixels.', 'ecombon' )
						: __( 'Only applies while Site Width is Boxed.', 'ecombon' ),
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Header Builder', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'ecombon-section-intro' },
					__( 'Drag any module into the Left, Center or Right zone — including mixing modules within a zone. Click the × on a module (or drag it back to Available) to hide it.', 'ecombon' )
				),
				el( HeaderZoneBuilder, { settings: settings, onChange: onChange } ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Menu Style', 'ecombon' ) ),
				el( ColorField, {
					settings: settings,
					path: 'header.menu_color',
					label: __( 'Menu Color', 'ecombon' ),
					onChange: onChange,
				} ),
				el( RangeField, {
					settings: settings,
					path: 'header.menu_font_size',
					label: __( 'Menu Font Size', 'ecombon' ),
					help: __( 'The navigation menu text size, in pixels.', 'ecombon' ),
					min: menuFontSizeBounds.min,
					max: menuFontSizeBounds.max,
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.menu_uppercase',
					label: __( 'Uppercase menu text', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.menu_bold',
					label: __( 'Bold menu text', 'ecombon' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Background', 'ecombon' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'header.background_color_enabled',
					label: __( 'Override header background color', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'header.background_color',
					label: __( 'Header Background Color', 'ecombon' ),
					help: __( 'Only applies while the toggle above is on — otherwise the header just shows the Body Background color through.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Layout', 'ecombon' ) ),
				el( RangeField, {
					settings: settings,
					path: 'header.container_width',
					label: __( 'Header Width', 'ecombon' ),
					help: __( 'The max width of the header row and its mega menu, in pixels — independent from the main content width.', 'ecombon' ),
					min: widthBounds.min,
					max: widthBounds.max,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Behavior', 'ecombon' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'header.sticky',
					label: __( 'Stick header to the top of the screen on scroll', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ToggleField, {
					settings: settings,
					path: 'header.force_mobile_menu',
					label: __( 'Always use the mobile menu, even on desktop', 'ecombon' ),
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Footer Builder', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el(
					'p',
					{ className: 'ecombon-section-intro' },
					__( 'Drag columns between Active and Available (or click × to hide) — and drag to reorder within Active.', 'ecombon' )
				),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Background', 'ecombon' ) ),
				el( ToggleField, {
					settings: settings,
					path: 'footer.background_color_enabled',
					label: __( 'Override footer background & text colors', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'footer.background_color',
					label: __( 'Footer Background Color', 'ecombon' ),
					help: __( 'Only applies while the toggle above is on — otherwise the footer just shows the Body Background color through.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ColorField, {
					settings: settings,
					path: 'footer.text_color',
					label: __( 'Footer Text Color', 'ecombon' ),
					help: __( 'Applies to all footer headings, text, icons and borders — pick a light color for a dark background, or vice versa, so everything stays readable.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Top Row Columns', 'ecombon' ) ),
				el( SingleListBuilder, {
					settings: settings,
					path: 'footer.top',
					modules: topModules,
					choices: DATA.choices.footer.top,
					onChange: onChange,
					activeTitle: __( 'Active', 'ecombon' ),
					poolTitle: __( 'Available (drag into Active below)', 'ecombon' ),
					poolHint: __( 'Every column is active.', 'ecombon' ),
					emptyLabel: __( 'Drop a column here', 'ecombon' ),
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Info Card', 'ecombon' ) ),
				el( ImageField, {
					settings: settings,
					path: 'footer.info_logo',
					label: __( 'Logo', 'ecombon' ),
					help: __( 'Optional — if left blank, the site logo (Customizer > Site Identity) is used instead.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( TextareaField, {
					settings: settings,
					path: 'footer.info_description',
					label: __( 'Description', 'ecombon' ),
					help: __( 'Basic HTML is allowed here (e.g. a link or bold text).', 'ecombon' ),
					onChange: onChange,
				} ),
				el( SocialLinksField, {
					settings: settings,
					path: 'footer.social_links',
					label: __( 'Social Links', 'ecombon' ),
					help: __( 'An icon with no URL is never shown — add a row per network you want in the footer.', 'ecombon' ),
					choices: DATA.choices.social,
					onChange: onChange,
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Column Content', 'ecombon' ) ),
				el( TextField, { settings: settings, path: 'footer.company_heading', label: __( 'Company Menu Heading', 'ecombon' ), onChange: onChange } ),
				el( TextField, { settings: settings, path: 'footer.customer_heading', label: __( 'Customer Care Menu Heading', 'ecombon' ), onChange: onChange } ),
				el(
					'p',
					{ className: 'ecombon-field__help' },
					__( 'The Newsletter column’s heading, description and signup form now live in their own Newsletter section on the left.', 'ecombon' )
				),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Bottom Bar Items', 'ecombon' ) ),
				el( SingleListBuilder, {
					settings: settings,
					path: 'footer.bottom',
					modules: bottomModules,
					choices: DATA.choices.footer.bottom,
					onChange: onChange,
					activeTitle: __( 'Active', 'ecombon' ),
					poolTitle: __( 'Available (drag into Active below)', 'ecombon' ),
					poolHint: __( 'Every item is active.', 'ecombon' ),
					emptyLabel: __( 'Drop an item here', 'ecombon' ),
				} ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Bottom Bar Content', 'ecombon' ) ),
				el( TextField, {
					settings: settings,
					path: 'footer.copyright_text',
					label: __( 'Copyright Text', 'ecombon' ),
					help: __( 'Use {year} and {site_name} as placeholders — they’re replaced automatically.', 'ecombon' ),
					onChange: onChange,
				} ),
				el( ImageField, {
					settings: settings,
					path: 'footer.payment_icons_image',
					label: __( 'Payment Icons', 'ecombon' ),
					help: __( 'Optional — upload a single image with all accepted payment icons. Leave blank to use the theme’s own built-in icon set.', 'ecombon' ),
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
			el( c.CardHeader, null, el( 'h2', null, __( 'Newsletter', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( TextField, { settings: settings, path: 'footer.newsletter_heading', label: __( 'Heading', 'ecombon' ), onChange: onChange } ),
				el( TextField, { settings: settings, path: 'footer.newsletter_description', label: __( 'Description', 'ecombon' ), onChange: onChange } ),
				el( 'h3', { className: 'ecombon-subheading' }, __( 'Signup Form', 'ecombon' ) ),
				el( SelectField, {
					settings: settings,
					path: 'newsletter.provider',
					label: __( 'Form Source', 'ecombon' ),
					help: __( 'Theme Default Form uses the theme’s own real signup form. Custom Embed Code replaces it with a snippet from your email marketing provider.', 'ecombon' ),
					options: providerOptions,
					onChange: onChange,
				} ),
				isCustom
					? el( TextareaField, {
							settings: settings,
							path: 'newsletter.embed_code',
							label: __( 'Embed Code', 'ecombon' ),
							help: __( 'Paste the full embed snippet from Mailchimp, MailerLite, etc. (HTML and <script> tags are both allowed here, unlike other fields).', 'ecombon' ),
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

		return el(
			c.Card,
			null,
			el( c.CardHeader, null, el( 'h2', null, __( 'Shop', 'ecombon' ) ) ),
			el(
				c.CardBody,
				null,
				el( SelectField, {
					settings: settings,
					path: 'shop.grid_columns',
					label: __( 'Grid Columns', 'ecombon' ),
					help: __( 'How many products per row on the shop and category pages.', 'ecombon' ),
					options: columnsOptions,
					onChange: onChange,
				} ),
				el( SelectField, {
					settings: settings,
					path: 'shop.products_per_page',
					label: __( 'Products Per Page', 'ecombon' ),
					options: perPageOptions,
					onChange: onChange,
				} )
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
			apiFetch( { path: '/ecombon/v1/settings', method: 'POST', data: settings } )
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
			if ( ! window.confirm( __( 'Reset every Ecombon setting to its default value? This cannot be undone.', 'ecombon' ) ) ) {
				return;
			}
			setResetting( true );
			setStatus( '' );
			apiFetch( { path: '/ecombon/v1/settings', method: 'DELETE' } )
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
			{ className: 'ecombon-settings-shell' },
			el(
				'nav',
				{ className: 'ecombon-settings-nav' },
				el(
					'div',
					{ className: 'ecombon-settings-nav__brand' },
					el( 'span', { className: 'ecombon-settings-nav__brand-icon' }, el( c.Icon, { icon: 'admin-customizer', size: 20 } ) ),
					el(
						'div',
						null,
						el( 'div', { className: 'ecombon-settings-nav__brand-title' }, __( 'Ecombon', 'ecombon' ) ),
						el( 'div', { className: 'ecombon-settings-nav__brand-subtitle' }, __( 'Theme Settings', 'ecombon' ) )
					)
				),
				el(
					'div',
					{ className: 'ecombon-settings-nav__items' },
					visibleSections.length
						? visibleSections.map( function ( section ) {
								return el(
									'button',
									{
										key: section.id,
										type: 'button',
										className: 'ecombon-settings-nav__item' + ( active === section.id ? ' is-active' : '' ),
										onClick: function () {
											setActive( section.id );
										},
									},
									el( c.Icon, { icon: section.icon } ),
									el( 'span', null, section.label )
								);
						  } )
						: el( 'p', { className: 'ecombon-settings-nav__empty' }, __( 'No matching settings.', 'ecombon' ) )
				)
			),
			el(
				'div',
				{ className: 'ecombon-settings-right' },
				el(
					'div',
					{ className: 'ecombon-settings-topbar' },
					el(
						'div',
						{ className: 'ecombon-settings-search' },
						el( c.Icon, { icon: 'search', size: 18 } ),
						el( 'input', {
							type: 'text',
							placeholder: __( 'Search settings…', 'ecombon' ),
							value: search,
							onChange: function ( event ) {
								setSearch( event.target.value );
							},
						} )
					),
					el(
						'div',
						{ className: 'ecombon-settings-topbar__actions' },
						status === 'success' ? el( 'span', { className: 'ecombon-save-status is-success' }, __( 'Saved', 'ecombon' ) ) : null,
						status === 'error' ? el( 'span', { className: 'ecombon-save-status is-error' }, __( 'Save failed', 'ecombon' ) ) : null,
						el(
							c.Button,
							{ variant: 'tertiary', isDestructive: true, isBusy: resetting, disabled: saving || resetting, onClick: resetToDefaults },
							resetting ? __( 'Resetting…', 'ecombon' ) : __( 'Reset to Defaults', 'ecombon' )
						),
						el(
							c.Button,
							{ variant: 'primary', isBusy: saving, disabled: saving || resetting, onClick: save },
							saving ? __( 'Saving…', 'ecombon' ) : __( 'Save Changes', 'ecombon' )
						)
					)
				),
				el(
					'main',
					{ className: 'ecombon-settings-content' },
					el( ActiveSection, { settings: settings, onChange: setSettings } )
				)
			)
		);
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var root = document.getElementById( 'ecombon-settings-app' );
		if ( root ) {
			wp.element.createRoot( root ).render( el( App ) );
		}
	} );
} )();
