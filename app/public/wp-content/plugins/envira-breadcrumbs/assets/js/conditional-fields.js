/**
* Handles showing and hiding fields conditionally
*/
jQuery( document ).ready( function( $ ) {

	// Show/hide elements as necessary when a conditional field is changed
	$( '#envira-albums-settings input:not([type=hidden]), #envira-albums-settings select' ).conditions( 
		[

			{	// Main Theme Elements
				conditions: {
					element: '[name="_eg_album_data[config][breadcrumbs_enabled]"]',
					type: 'checked',
					operator: 'is'
				},
				actions: {
					if: [
						{
							element: '#envira-breadcrumbs-separator-box',
							action: 'show'
						}
					],
					else: [
						{
							element: '#envira-breadcrumbs-separator-box',
							action: 'hide'
						}
					]
				}
			}

		]
	);

} );