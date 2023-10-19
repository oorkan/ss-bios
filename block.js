( function( blocks, editor, i18n, element, components, _ ) {
	const __ = i18n.__;
	const el = element.createElement;
	const RichText = editor.RichText;

	blocks.registerBlockType( 'ss-bios/ss-bios', {
		title: __( 'SS Bios', 'ss-bios' ),
		icon: 'businessman',
		category: 'layout',
		attributes: {
			names: {
				type: 'string',
				selector: '.names',
			},
			names_exclude: {
				type: 'boolean',
				selector: '.names-exclude'
			},
			categories: {
				type: 'string',
				selector: '.categories',
			},
			categories_exclude: {
				type: 'boolean',
				selector: '.categories-exclude'
			},
		},

		edit: function( props ) {
			let attributes = props.attributes;

			return el(
				'div',
				{ className: props.className },
				el( 'h3', {}, i18n.__( 'Names', 'ss-bios' ) ),
				el( RichText, {
					tagName: 'ul',
					multiline: 'li',
					placeholder: i18n.__(
						'Write names…',
						'ss-bios'
					),
					value: attributes.names,
					onChange: function( value ) {
						props.setAttributes( { names: value } );
					},
					className: 'names',
				} ),
				el( components.ToggleControl, {
					label: i18n.__(
						'Exclude',
						'ss-bios'
					),
					checked: attributes.names_exclude,
					onChange: function( value ) {
						props.setAttributes( { names_exclude: value } );
					},
					className: 'names-exclude',
				} ),
				el( 'h3', {}, i18n.__( 'Categories', 'ss-bios' ) ),
				el( RichText, {
					tagName: 'ul',
					multiline: 'li',
					placeholder: i18n.__(
						'Write categories…',
						'ss-bios'
					),
					value: attributes.categories,
					onChange: function( value ) {
						props.setAttributes( { categories: value } );
					},
					className: 'categories',
				} ),
				el( components.ToggleControl, {
					label: i18n.__(
						'Exclude',
						'ss-bios'
					),
					checked: attributes.categories_exclude,
					onChange: function( value ) {
						props.setAttributes( { categories_exclude: value } );
					},
					className: 'categories-exclude',
				} ),
			);
		},
		save: function ( props ) { return null; }
	} );
} )(
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.components,
	window._
);
