/**
* Handles showing and hiding fields conditionally
*/
jQuery( document ).ready( function( $ ) {

	// Show/hide elements as necessary when a conditional field is changed
	$( '#envira-albums-settings input:not([type=hidden]), #envira-albums-settings select, #envira-gallery-settings input:not([type=hidden]), #envira-gallery-settings select' ).conditions( 
		[

			{  // Sort Order
				conditions: [
					{
						element: '[name="_envira_gallery[tags_sorting]"], [name="_eg_album_data[config][tags_sorting]"]',
						type: 'value',
						operator: 'array',
						condition: [ 'manual' ]
					},
					{
						element: '[name="_envira_gallery[tags]"], [name="_eg_album_data[config][tags]"]',
						type: 'checked',
						operator: 'is'
					}
				],
				actions: {
					if: {
						element: '#envira-config-tags-manual-sorting',
						action: 'show'
					},
					else: {
						element: '#envira-config-tags-manual-sorting',
						action: 'hide'
					}
				}
			},
			{  // Main Tag Elements
				conditions: {
					element: '[name="_envira_gallery[tags]"], [name="_eg_album_data[config][tags]"]',
					type: 'checked',
					operator: 'is'
				},
				actions: {
					if: {
						element: '#envira-config-tags-position, #envira-config-tags-filtering-box, #envira-config-tags-all-enabled-box, #envira-config-tags-all-box, #envira-config-tags-sorting-box, #envira-config-tags-display-box, #envira-config-tags-scroll-box',
						action: 'show'
					},
					else: {
						element: '#envira-config-tags-position, #envira-config-tags-filtering-box, #envira-config-tags-all-enabled-box, #envira-config-tags-all-box, #envira-config-tags-sorting-box, #envira-config-tags-display-box, #envira-config-tags-scroll-box',
						action: 'hide'
					}
				}
			}

		]
	);

} );